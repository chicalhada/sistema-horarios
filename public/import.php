<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';

echo "<h1>A importar CSV...</h1>";

// ============================================
// CSV FUNCTIONS
// ============================================

function getCsvPath() {
    $csvPath = __DIR__ . '/../uploads/horarios_2S_2026.csv';
    return $csvPath;
}

function getCsvHandle() {
    static $handle = null;
    
    if ($handle === null) {
        $csvPath = getCsvPath();
        
        if (!file_exists($csvPath)) {
            die("Ficheiro CSV não encontrado em: " . $csvPath);
        }
        
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            die("Não foi possível abrir o ficheiro CSV");
        }
    }
    
    return $handle;
}

// ============================================
// GET CSV HANDLE ONCE
// ============================================

$csv = getCsvHandle();  // Store the handle in a variable

echo "Ficheiro CSV encontrado!<br>";

// ============================================
// TEST: Read first 5 rows
// ============================================

echo "<h2>Teste: Ler primeiras 5 linhas do CSV</h2>";

// Handle BOM
$bom = fread($csv, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($csv);
}

// Skip headers
$headers = fgetcsv($csv, 0, ';', '"', '\\');
echo "Headers lidos!<br>";

$count = 0;
echo "<ul>";
while (($row = fgetcsv($csv, 0, ';', '"', '\\')) !== false && $count < 5) {
    // Skip empty rows
    if (empty(array_filter($row))) continue;
    
    // Skip rows without ModuleName
    if (empty(trim($row[0] ?? ''))) continue;
    
    // Skip rows without Classroom
    if (empty(trim($row[10] ?? ''))) continue;
    
    $count++;
    $moduleName = trim($row[0]);
    $room = trim($row[10]);
    $dates = explode(',', trim($row[14] ?? ''));
    
    echo "<li><strong>" . htmlspecialchars($moduleName) . "</strong> - Sala: " . htmlspecialchars($room) . " - " . count($dates) . " datas</li>";
}
echo "</ul>";
echo "Teste concluído! Encontradas " . $count . " linhas válidas.<br>";

// ============================================
// ACTUAL IMPORT
// ============================================

echo "<hr>";
echo "<h2>A importar dados...</h2>";

// Re-open the file from the beginning
rewind($csv);

// Skip BOM again
$bom = fread($csv, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($csv);
}

// Skip headers
fgetcsv($csv, 0, ';', '"', '\\');

// Get database connection
$db = getDatabase();

// Start transaction
$db->exec("BEGIN TRANSACTION");

// Prepare insert statement
$stmt = $db->prepare("
    INSERT INTO events 
    (room, weekday_num, start_time, end_time, event_date, 
     module_name, module_acronym, event_type, event_title) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$inserted = 0;
$skipped = 0;
$rowNum = 0;

while (($row = fgetcsv($csv, 0, ';', '"', '\\')) !== false) {
    $rowNum++;
    
    // Skip empty rows
    if (empty(array_filter($row))) {
        $skipped++;
        continue;
    }
    
    // Extract data
    $moduleName = trim($row[0] ?? '');
    $moduleAcronym = trim($row[1] ?? '');
    $room = trim($row[10] ?? '');
    $startTime = trim($row[12] ?? '');
    $endTime = trim($row[13] ?? '');
    $weekString = trim($row[14] ?? '');
    $eventType = trim($row[16] ?? 'Horarios');
    $eventTitle = trim($row[15] ?? '');
    $weekdayNum = (int)($row[21] ?? 0);
    
    // Skip rows without ModuleName or Room
    if (empty($moduleName) || empty($room)) {
        $skipped++;
        continue;
    }
    
    // Parse dates from Week column
    $dates = explode(',', $weekString);
    $dates = array_map('trim', $dates);
    $dates = array_filter($dates);
    
    if (empty($dates)) {
        $skipped++;
        continue;
    }
    
    // Insert one event per date
    foreach ($dates as $date) {
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            continue;
        }
        
        $stmt->bindValue(1, $room, SQLITE3_TEXT);
        $stmt->bindValue(2, $weekdayNum, SQLITE3_INTEGER);
        $stmt->bindValue(3, $startTime, SQLITE3_TEXT);
        $stmt->bindValue(4, $endTime, SQLITE3_TEXT);
        $stmt->bindValue(5, $date, SQLITE3_TEXT);
        $stmt->bindValue(6, $moduleName, SQLITE3_TEXT);
        $stmt->bindValue(7, $moduleAcronym, SQLITE3_TEXT);
        $stmt->bindValue(8, $eventType, SQLITE3_TEXT);
        $stmt->bindValue(9, $eventTitle, SQLITE3_TEXT);
        
        $stmt->execute();
        $inserted++;
    }
}

// Commit transaction
$db->exec("COMMIT");

// ============================================
// SHOW RESULTS
// ============================================

echo "<h2>Importação Concluída!</h2>";
echo "Eventos inseridos: <strong>$inserted</strong><br>";
echo "⏭Linhas ignoradas: $skipped<br>";
echo "Total de linhas processadas: $rowNum<br>";
echo "Total de eventos agora: " . getEventCount() . "<br>";

// Show some statistics
echo "<h3>Estatísticas por Sala</h3>";
$result = $db->query("
    SELECT room, COUNT(*) as count 
    FROM events 
    GROUP BY room 
    ORDER BY count DESC 
    LIMIT 10
");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Sala</th><th>Eventos</th></tr>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['room']) . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Close the file
fclose($csv);

echo "<hr>";
echo "<a href='test_db.php'>Verificar base de dados</a> | ";
echo "<a href='index.php'>Voltar ao início</a>";