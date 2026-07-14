<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Testing SQLite3</h1>";

// Check if class exists
if (class_exists('SQLite3')) {
    echo "✅ SQLite3 class exists!<br>";
} else {
    echo "❌ SQLite3 class does NOT exist!<br>";
    echo "Please install: sudo pacman -S php-sqlite<br>";
}

// Try to create a connection
try {
    $testDb = new SQLite3(':memory:');
    echo "✅ Can create SQLite3 connection!<br>";
    $testDb->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Check if our database file exists
$dbPath = __DIR__ . '/../data/timetable.sqlite';
if (file_exists($dbPath)) {
    echo "✅ Database file exists at: " . $dbPath . "<br>";
    echo "File size: " . filesize($dbPath) . " bytes<br>";
} else {
    echo "❌ Database file NOT found at: " . $dbPath . "<br>";
}

// Try to connect to our database
try {
    $db = new SQLite3($dbPath);
    echo "✅ Connected to our database!<br>";
    
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    echo "Tables:<br>";
    while ($row = $result->fetchArray()) {
        echo "- " . $row['name'] . "<br>";
    }
    
    $db->close();
} catch (Exception $e) {
    echo "❌ Error connecting: " . $e->getMessage() . "<br>";
}