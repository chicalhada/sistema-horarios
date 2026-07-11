<?php
// src/config/database.php
// Database configuration and connection

function getDatabasePath() {
    $dbPath = __DIR__ . '/../../data/timetable.sqlite';
    $dataDir = dirname($dbPath);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    return $dbPath;
}

function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        $dbPath = getDatabasePath();
        
        try {
            $db = new SQLite3($dbPath);
            $db->enableExceptions(true);
            
            $db->exec('PRAGMA journal_mode = WAL');
            $db->exec('PRAGMA synchronous = NORMAL');
            $db->exec('PRAGMA cache_size = 10000');
            
            createTables($db);
            
        } catch (Exception $e) {
            die('❌ Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $db;
}

function createTables($db) {
    // Drop existing table if you want to start fresh
    // $db->exec("DROP TABLE IF EXISTS events");
    
    // Create the events table with ALL columns
    $db->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room TEXT NOT NULL,
            weekday_num INTEGER NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            event_date TEXT NOT NULL,
            module_name TEXT,
            module_acronym TEXT,
            event_type TEXT DEFAULT 'Class',
            event_title TEXT,
            event_identifier TEXT,
            student_group TEXT,
            num_students INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create indexes for faster queries
    $db->exec("
        CREATE INDEX IF NOT EXISTS idx_room_date 
        ON events(room, event_date)
    ");
    
    $db->exec("
        CREATE INDEX IF NOT EXISTS idx_room_weekday 
        ON events(room, weekday_num)
    ");
}

function hasEvents() {
    $db = getDatabase();
    $result = $db->query("SELECT COUNT(*) as count FROM events");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row['count'] > 0;
}

function getEventCount() {
    $db = getDatabase();
    $result = $db->query("SELECT COUNT(*) as count FROM events");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return (int)$row['count'];
}

function getAllRooms() {
    $db = getDatabase();
    $result = $db->query("SELECT DISTINCT room FROM events ORDER BY room");
    
    $rooms = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rooms[] = $row['room'];
    }
    
    return $rooms;
}
