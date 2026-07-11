<?php

error_reporting(E_ALL);
ini_set('display_errors', 1); 

require_once __DIR__ . '/../src/config/database.php';

echo "<h1>A importar o CSV</h1>";


function getCsvPath() {
    $csvPath = __DIR__ . '/../uploads/horarios_2S_2026.csv';  // Fixed filename
    return $csvPath;
}

// Function to open and read the CSV
function getCsv() {
    static $handle = null;  // Store the file handle, not the file content
    
    if ($handle === null) {
        $csvPath = getCsvPath();
        
        // Check if file exists
        if (!file_exists($csvPath)) {
            die("Nenhum ficheiro CSV detetado em: " . $csvPath);
        }
        
        // Open the file
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            die("Não foi possível abrir o ficheiro: " . $csvPath);
        }
    }
    
    return $handle;
}

// Now let's test if we can read the CSV
echo "<h2>Test: Reading first 5 rows</h2>";

$csv = getCsv();

// Skip BOM if present
$bom = fread($csv, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($csv);
}

// Skip headers
fgetcsv($csv, 0, ';');

$count = 0;
while (($row = fgetcsv($csv, 0, ';')) !== false && $count < 5) {
    // Skip empty rows
    if (empty(array_filter($row))) continue;
    
    // Skip rows without ModuleName
    if (empty(trim($row[0] ?? ''))) continue;
    
    // Skip rows without Classroom
    if (empty(trim($row[10] ?? ''))) continue;
    
    $count++;
    echo "<strong>Row $count:</strong> " . htmlspecialchars($row[0]) . " - " . htmlspecialchars($row[10]) . "<br>";
}

echo "<hr>";
echo "CSV reading test complete!";