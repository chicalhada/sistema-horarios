<?php
// room.php - Show timetable for a specific room

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';

// Get room from URL parameter
$room = $_GET['room'] ?? '';

// If no room specified, show list of all rooms
if (empty($room)) {
    header('Location: rooms.php');
    exit;
}

// Clean room name (prevent XSS)
$room = htmlspecialchars(strip_tags($room));

// Get database connection
$db = getDatabase();

// ============================================
// 🔑 NEW: Week Navigation
// ============================================
$weekOffset = (int)($_GET['week_offset'] ?? 0);

// Calculate week based on offset
$today = new DateTime();
$today->modify(($weekOffset * 7) . ' days');

// Get Monday of the current week
$monday = clone $today;
$monday->modify('monday this week');
$sunday = clone $monday;
$sunday->modify('+6 days');

$weekStart = $monday->format('Y-m-d');
$weekEnd = $sunday->format('Y-m-d');

// Query events for this room and week
$stmt = $db->prepare("
    SELECT * FROM events 
    WHERE room = :room 
    AND event_date BETWEEN :start AND :end
    ORDER BY weekday_num, start_time
");

$stmt->bindValue(':room', $room, SQLITE3_TEXT);
$stmt->bindValue(':start', $weekStart, SQLITE3_TEXT);
$stmt->bindValue(':end', $weekEnd, SQLITE3_TEXT);

$result = $stmt->execute();

// Group events by day
$days = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
$weekEvents = array_fill(0, 7, []);

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $dayNum = (int)$row['weekday_num'];
    $weekEvents[$dayNum][] = $row;
}

// Check if today is in this week
$todayNum = (int)date('w') - 1;
if ($todayNum < 0) $todayNum = 6;
$todayDate = date('Y-m-d');
$isTodayInWeek = ($todayDate >= $weekStart && $todayDate <= $weekEnd);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sala <?= $room ?> - Horário</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f0f2f5;
            padding: 10px;
            color: #1a1a2e;
        }
        .container { max-width: 600px; margin: 0 auto; }
        
        .header {
            background: linear-gradient(135deg, #1a3c6e, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            text-align: center;
        }
        .header h1 { font-size: 22px; margin-bottom: 5px; }
        .header .room { font-size: 14px; opacity: 0.8; }
        .header .week { font-size: 13px; opacity: 0.7; margin-top: 5px; }
        
        .nav {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 15px;
        }
        .nav a {
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #1a3c6e;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            font-size: 14px;
            flex: 1;
            text-align: center;
        }
        .nav a:hover { background: #f0f0f0; }
        
        .day-card {
            background: white;
            border-radius: 10px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .day-card.today {
            border: 2px solid #28a745;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
        .day-header {
            padding: 10px 15px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            border-bottom: 1px solid #e9ecef;
        }
        .today-badge {
            background: #28a745;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        .empty-day {
            padding: 15px;
            text-align: center;
            color: #adb5bd;
            font-style: italic;
            font-size: 14px;
        }
        
        .event {
            display: flex;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            gap: 12px;
        }
        .event:last-child { border-bottom: none; }
        .event-time {
            min-width: 65px;
            font-weight: bold;
            color: #1a3c6e;
            font-size: 14px;
        }
        .event-details { flex: 1; }
        .event-module { font-weight: 600; font-size: 15px; }
        .event-title { font-size: 13px; color: #495057; }
        .event-badge {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            color: #495057;
            margin-top: 3px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 12px;
        }
        
        .no-events {
            background: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 10px;
            color: #6c757d;
        }
        .no-events h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Sala <?= $room ?></h1>
            <div class="week">Semana de <?= $monday->format('d/m/Y') ?> a <?= $sunday->format('d/m/Y') ?></div>
        </div>
        
        <!-- ============================================ -->
        <!-- 🔑 NEW: Week Navigation Links -->
        <!-- ============================================ -->
        <div class="nav">
            <a href="?room=<?= urlencode($room) ?>&week_offset=<?= $weekOffset - 1 ?>">⬅️ Anterior</a>
            <a href="?room=<?= urlencode($room) ?>&week_offset=0">📅 Hoje</a>
            <a href="?room=<?= urlencode($room) ?>&week_offset=<?= $weekOffset + 1 ?>">Próxima ➡️</a>
        </div>
        
        <?php
        $hasEvents = false;
        foreach ($weekEvents as $dayEvents) {
            if (!empty($dayEvents)) {
                $hasEvents = true;
                break;
            }
        }
        
        if (!$hasEvents):
        ?>
            <div class="no-events">
                <h3>📭 Sem aulas esta semana</h3>
                <p>Esta sala não tem aulas programadas para esta semana.</p>
                <p style="margin-top:10px;font-size:13px;color:#adb5bd;">
                    💡 Tenta navegar para outra semana com os botões acima.
                </p>
            </div>
        <?php else: ?>
            <?php for ($i = 0; $i < 7; $i++): 
                $dayName = $days[$i];
                $dayEvents = $weekEvents[$i];
                $isToday = ($i === $todayNum && $isTodayInWeek);
            ?>
                <div class="day-card <?= $isToday ? 'today' : '' ?>">
                    <div class="day-header">
                        <span><?= $dayName ?></span>
                        <?php if ($isToday): ?>
                            <span class="today-badge">📍 Hoje</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($dayEvents)): ?>
                        <div class="empty-day">Sem aulas</div>
                    <?php else: ?>
                        <?php foreach ($dayEvents as $event): ?>
                            <div class="event">
                                <div class="event-time">
                                    <?= htmlspecialchars($event['start_time']) ?>
                                    <br>
                                    <span style="font-weight:normal;font-size:12px;color:#6c757d;">
                                        <?= htmlspecialchars($event['end_time']) ?>
                                    </span>
                                </div>
                                <div class="event-details">
                                    <div class="event-module">
                                        <?= htmlspecialchars($event['module_acronym'] ?: $event['module_name']) ?>
                                    </div>
                                    <div class="event-title">
                                        <?= htmlspecialchars($event['module_name']) ?>
                                    </div>
                                    <?php if ($event['event_type'] !== 'Horarios'): ?>
                                        <span class="event-badge"><?= htmlspecialchars($event['event_type']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        <?php endif; ?>
        
        <div class="footer">
            ESTGA - Horários • <?= date('Y') ?>
        </div>
    </div>
</body>
</html>