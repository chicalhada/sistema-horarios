<?php
// rooms.php - List all rooms

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
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salas - ESTGA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        .container { max-width: 600px; margin: 0 auto; }
        
        .header {
            background: linear-gradient(135deg, #1a3c6e, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 { font-size: 24px; }
        .header p { opacity: 0.8; font-size: 14px; margin-top: 5px; }
        
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        .room-card {
            background: white;
            padding: 15px 10px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: #1a3c6e;
            font-weight: 600;
            font-size: 14px;
        }
        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .admin-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 13px;
            text-decoration: none;
        }
        .admin-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏫 Salas ESTGA</h1>
            <p>Escolhe uma sala para ver o horário</p>
        </div>
        
        <div class="room-grid">
            <?php foreach ($rooms as $room): ?>
                <a href="room.php?room=<?= urlencode($room) ?>" class="room-card">
                    <?= htmlspecialchars($room) ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <a href="admin.php" class="admin-link">⚙️ Administração</a>
    </div>
</body>
</html>