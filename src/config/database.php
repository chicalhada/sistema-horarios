<?php
/**
 * database.php
 *
 * Ligação central à base de dados SQLite + criação automática do esquema
 * (tabela de eventos e tabela de logs de administração, caso ainda não existam).
 *
 * Todas as outras páginas (room.php, room_list.php, import.php, admin.php, ...)
 * devem usar sempre getDatabase() para obter a ligação, em vez de abrir o
 * ficheiro SQLite diretamente. Isto garante que só existe UMA ligação por
 * pedido e que o esquema está sempre atualizado.
 */

/**
 * Devolve (e cria, se necessário) a ligação à base de dados SQLite.
 * Usa um "singleton" simples para que múltiplas chamadas na mesma
 * página reutilizem a mesma ligação.
 */
function getDatabase(): SQLite3
{
    static $db = null;

    if ($db === null) {
        $dbPath = __DIR__ . '/../../data/timetable.sqlite';

        // Garante que a pasta /data existe
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0775, true);
        }

        $db = new SQLite3($dbPath);
        $db->enableExceptions(true);

        // Melhora a fiabilidade em escritas concorrentes (upload + leitura em simultâneo)
        $db->exec('PRAGMA journal_mode = WAL');
        $db->exec('PRAGMA foreign_keys = ON');

        ensureSchema($db);
    }

    return $db;
}

/**
 * Cria as tabelas necessárias caso ainda não existam.
 * É seguro chamar isto sempre - o "IF NOT EXISTS" impede que apague dados.
 */
function ensureSchema(SQLite3 $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS events (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            room            TEXT NOT NULL,
            weekday_num     INTEGER NOT NULL DEFAULT 0,
            start_time      TEXT,
            end_time        TEXT,
            event_date      TEXT NOT NULL,
            module_name     TEXT,
            module_acronym  TEXT,
            event_type      TEXT DEFAULT 'Horarios',
            event_title     TEXT
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_events_room_date ON events (room, event_date)");

    // Tabela usada pelo painel de administração para registar
    // quem fez upload de um CSV, quando, e o resultado da importação.
    $db->exec("
        CREATE TABLE IF NOT EXISTS import_logs (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            filename        TEXT NOT NULL,
            mode            TEXT NOT NULL,
            inserted_count  INTEGER NOT NULL DEFAULT 0,
            skipped_count   INTEGER NOT NULL DEFAULT 0,
            status          TEXT NOT NULL DEFAULT 'success',
            message         TEXT,
            ip_address      TEXT,
            created_at      TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
        )
    ");

    // Regista tentativas de login (sucesso/falha) para deteção de abuso
    $db->exec("
        CREATE TABLE IF NOT EXISTS admin_login_attempts (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            success     INTEGER NOT NULL,
            ip_address  TEXT,
            created_at  TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
        )
    ");
}

/**
 * Número total de eventos na base de dados.
 */
function getEventCount(): int
{
    $db = getDatabase();
    $result = $db->query("SELECT COUNT(*) as count FROM events");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return (int) $row['count'];
}

/**
 * Número de salas distintas com pelo menos um evento.
 */
function getRoomCount(): int
{
    $db = getDatabase();
    $result = $db->query("SELECT COUNT(DISTINCT room) as count FROM events");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return (int) $row['count'];
}

/**
 * Apaga TODOS os eventos da base de dados. Usado pela manutenção do admin.
 * Devolve o número de linhas apagadas.
 */
function clearAllEvents(): int
{
    $before = getEventCount();
    $db = getDatabase();
    $db->exec("DELETE FROM events");
    // Reinicia o contador de auto-incremento para ficar arrumado
    $db->exec("DELETE FROM sqlite_sequence WHERE name = 'events'");
    return $before;
}
