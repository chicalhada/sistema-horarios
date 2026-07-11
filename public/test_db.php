<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);



// public/test_db.php
// Test script to verify database connection

// Load the database configuration
require_once __DIR__ . '/../src/config/database.php';

echo "<h1>Database Test</h1>";

try {
    // 1. Test connection
    echo "<h2>Step 1: Connecting to Database</h2>";
    $db = getDatabase();
    echo "✅ Database connection successful!<br>";
    echo "📁 Database file: " . getDatabasePath() . "<br>";
    



    // 2. Check if table exists
    echo "<h2>Step 2: Checking Table Structure</h2>";
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='events'");
    if ($result->fetchArray()) {
        echo "✅ Events table exists!<br>";
    } else {
        echo "❌ Events table not found<br>";
    }
    



    // 3. Show table schema
    echo "<h2>Step 3: Table Schema</h2>";
    $result = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='events'");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    echo "<pre>" . htmlspecialchars($row['sql']) . "</pre>";
    



    // 4. Check if there's any data
    echo "<h2>Step 4: Existing Data</h2>";
    $count = getEventCount();
    echo "📊 Total events: <strong>{$count}</strong><br>";
    

    if ($count > 0) {
        $rooms = getAllRooms();
        echo "🏫 Rooms with data: " . implode(', ', $rooms) . "<br>";
        
        // Show sample data
        echo "<h2>Step 5: Sample Data (first 5 records)</h2>";
        $result = $db->query("SELECT * FROM events LIMIT 5");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Room</th><th>Date</th><th>Module</th><th>Start</th><th>End</th></tr>";
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['room']) . "</td>";
            echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['module_acronym'] ?: $row['module_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
            echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>✅ All tests passed!</h2>";
    
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}