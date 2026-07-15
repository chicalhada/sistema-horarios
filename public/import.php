<?php
/**
 * import.php - Importação manual do CSV a partir do ficheiro em /uploads
 *
 * Nota: esta página é útil para testes/manutenção manual no servidor.
 * Para importar através do browser (com password), usa antes o painel
 * de administração em admin.php, que usa o mesmo serviço CsvImporter.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/services/CsvImporter.php';

echo "<h1>📥 Importar CSV - Horários ESTGA</h1>";

$csvPath = __DIR__ . '/../uploads/horarios_2S_2026.csv';

if (!file_exists($csvPath)) {
    die("❌ Ficheiro CSV não encontrado em: " . htmlspecialchars($csvPath));
}
echo "✅ Ficheiro CSV encontrado!<br>";

$db = getDatabase();
$existingEvents = getEventCount();

if ($existingEvents > 0) {
    echo "<h2>⚠️ A base de dados já tem dados!</h2>";
    echo "Existem <strong>{$existingEvents}</strong> eventos na base de dados.<br><br>";
    echo "<p>Escolhe uma opção:</p>";
    echo "<ul>";
    echo "<li><a href='?action=append'>➕ Adicionar mais (append) - pode criar duplicados!</a></li>";
    echo "<li><a href='?action=clear'>🗑️ Apagar tudo e importar de novo</a></li>";
    echo "<li><a href='room_list.php'>🏠 Voltar para a lista de salas</a></li>";
    echo "</ul>";

    $action = $_GET['action'] ?? '';

    if ($action !== 'clear' && $action !== 'append') {
        echo "<hr><p>❌ Nenhuma ação selecionada. A importação foi cancelada.</p>";
        echo "<a href='room_list.php'>🏠 Voltar para a lista de salas</a>";
        exit;
    }

    $mode = $action; // 'clear' ou 'append'
} else {
    $mode = 'append';
}

echo "<hr><p>🔄 A importar dados (modo: " . htmlspecialchars($mode) . ")...</p>";

try {
    $importer = new CsvImporter($db);
    $stats = $importer->importFile($csvPath, $mode);

    echo "<h2>✅ Importação Concluída!</h2>";
    echo "📊 Eventos inseridos: <strong>{$stats['inserted']}</strong><br>";
    echo "⏭️ Linhas ignoradas: {$stats['skipped']}<br>";
    echo "📊 Total de linhas processadas: {$stats['rows']}<br>";
    if ($mode === 'clear') {
        echo "🗑️ Eventos anteriores apagados: {$stats['cleared']}<br>";
    }
    echo "📊 Total de eventos agora: " . getEventCount() . "<br>";
} catch (Throwable $e) {
    echo "❌ Erro ao importar: " . htmlspecialchars($e->getMessage());
    exit;
}

// Estatísticas por sala
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

echo "<hr>";
echo "<a href='room_list.php'>🏠 Ver lista de salas</a>";
