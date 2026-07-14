<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';

$db = getDatabase();

// Get all unique rooms
$result = $db->query("SELECT DISTINCT room FROM events ORDER BY room");
$rooms = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rooms[] = $row['room'];
}

// DEBUG: Show what we found
echo "<h1>Debug - Room List</h1>";
echo "Total rooms found: " . count($rooms) . "<br><br>";

echo "First 20 rooms:<br>";
echo "<ul>";
foreach (array_slice($rooms, 0, 20) as $room) {
    echo "<li>" . htmlspecialchars($room) . "</li>";
}
echo "</ul>";
?>