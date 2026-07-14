<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';

echo "<h1>📥 Importar CSV - Horários ESTGA</h1>";

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
            die("❌ Ficheiro CSV não encontrado em: " . $csvPath);
        }
        
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            die("❌ Não foi possível abrir o ficheiro CSV");
        }
    }
    
    return $handle;
}

// ============================================
// GET CSV HANDLE
// ============================================

$csv = getCsvHandle();
echo "✅ Ficheiro CSV encontrado!<br>";

// ============================================
// CHECK IF DATA ALREADY EXISTS
// ============================================

$db = getDatabase();

$result = $db->query("SELECT COUNT(*) as count FROM events");
$row = $result->fetchArray(SQLITE3_ASSOC);
$existingEvents = $row['count'];

if ($existingEvents > 0) {
    echo "<h2>⚠️ A base de dados já tem dados!</h2>";
    echo "Existem <strong>$existingEvents</strong> eventos na base de dados.<br><br>";
    echo "<p>Escolhe uma opção:</p>";
    echo "<ul>";
    echo "<li><a href='?action=append'>➕ Adicionar mais (append) - pode criar duplicados!</a></li>";
    echo "<li><a href='?action=clear'>🗑️ Apagar tudo e importar de novo</a></li>";
    echo "<li><a href='rooms.php'>🏠 Voltar para a lista de salas</a></li>";
    echo "</ul>";
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'clear') {
        echo "<hr><p>🗑️ A apagar dados existentes...</p>";
        $db->exec("DELETE FROM events");
        echo "✅ Dados apagados! A importar novamente...<br><br>";
    } elseif ($action === 'append') {
        echo "<hr><p>📥 A adicionar mais eventos (append)...</p>";
    } else {
        echo "<hr><p>❌ Nenhuma ação selecionada. A importação foi cancelada.</p>";
        echo "<a href='rooms.php'>🏠 Voltar para a lista de salas</a>";
        exit;
    }
}

// ============================================
// TEST: Read first 5 rows
// ============================================

echo "<h2>🔍 Teste: Ler primeiras 5 linhas do CSV</h2>";

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
    if (empty(array_filter($row))) continue;
    if (empty(trim($row[0] ?? ''))) continue;
    if (empty(trim($row[10] ?? ''))) continue;
    
    $count++;
    $moduleName = trim($row[0]);
    $roomField = trim($row[10]);
    $roomList = array_map('trim', explode(',', $roomField));
    $dates = explode(',', trim($row[14] ?? ''));
    
    echo "<li><strong>" . htmlspecialchars($moduleName) . "</strong> - Sala: " . htmlspecialchars($roomField) . " (" . count($roomList) . " salas) - " . count($dates) . " datas</li>";
}
echo "</ul>";
echo "✅ Teste concluído! Encontradas " . $count . " linhas válidas.<br>";

// ============================================
// ACTUAL IMPORT
// ============================================

echo "<hr>";
echo "<h2>🔄 A importar dados...</h2>";

// Re-open the file from the beginning
rewind($csv);

// Skip BOM again
$bom = fread($csv, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($csv);
}

// Skip headers
fgetcsv($csv, 0, ';', '"', '\\');

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
    
    if (empty(array_filter($row))) {
        $skipped++;
        continue;
    }
    
    $moduleName = trim($row[0] ?? '');
    $moduleAcronym = trim($row[1] ?? '');
    $roomField = trim($row[10] ?? '');
    $startTime = trim($row[12] ?? '');
    $endTime = trim($row[13] ?? '');
    $weekString = trim($row[14] ?? '');
    $eventType = trim($row[16] ?? 'Horarios');
    $eventTitle = trim($row[15] ?? '');
    $weekdayNum = (int)($row[21] ?? 0);
    
    if (empty($moduleName) || empty($roomField)) {
        $skipped++;
        continue;
    }
    
    // ============================================
    // 🔑 KEY CHANGE: Split multiple rooms
    // ============================================
    // Example: "3.1.02,3.1.10" becomes ["3.1.02", "3.1.10"]
    $roomList = array_map('trim', explode(',', $roomField));
    $roomList = array_filter($roomList);
    
    if (empty($roomList)) {
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
    
    // ============================================
    // 🔑 KEY CHANGE: Loop through rooms AND dates
    // ============================================
    // One event per date AND per room
    foreach ($dates as $date) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            continue;
        }
        
        foreach ($roomList as $room) {
            if (empty($room)) continue;
            
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
}

// Commit transaction
$db->exec("COMMIT");

// ============================================
// SHOW RESULTS
// ============================================

echo "<h2>✅ Importação Concluída!</h2>";
echo "📊 Eventos inseridos: <strong>$inserted</strong><br>";
echo "⏭️ Linhas ignoradas: $skipped<br>";
echo "📊 Total de linhas processadas: $rowNum<br>";
echo "📊 Total de eventos agora: " . getEventCount() . "<br>";

// Show some statistics
echo "<h3>📊 Estatísticas por Sala</h3>";
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
echo "<a href='rooms.php'>🏠 Ver lista de salas</a>";