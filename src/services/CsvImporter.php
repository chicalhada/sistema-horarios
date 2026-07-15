<?php
/**
 * CsvImporter.php
 *
 * Serviço responsável por ler o CSV de horários e inserir os eventos
 * na base de dados. Extraído de import.php para poder ser reutilizado
 * tanto pelo script de importação original como pelo painel de
 * administração (upload feito através do browser).
 *
 * Formato do CSV esperado (separador ';'), colunas relevantes:
 *   0  -> module_name
 *   1  -> module_acronym
 *   10 -> room (pode ter várias salas separadas por ',')
 *   12 -> start_time
 *   13 -> end_time
 *   14 -> datas do evento, separadas por ',' (formato YYYY-MM-DD)
 *   15 -> event_title
 *   16 -> event_type
 *   21 -> weekday_num
 */
class CsvImporter
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    /**
     * Importa um ficheiro CSV.
     *
     * @param string $csvPath Caminho absoluto do ficheiro CSV a importar
     * @param string $mode    'append' para adicionar aos dados existentes,
     *                        'clear' para apagar tudo antes de importar
     *
     * @return array{inserted:int, skipped:int, rows:int, cleared:int}
     *
     * @throws RuntimeException se o ficheiro não puder ser aberto
     */
    public function importFile(string $csvPath, string $mode = 'append'): array
    {
        if (!file_exists($csvPath)) {
            throw new RuntimeException("Ficheiro CSV não encontrado em: {$csvPath}");
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            throw new RuntimeException("Não foi possível abrir o ficheiro CSV.");
        }

        $cleared = 0;
        if ($mode === 'clear') {
            $cleared = clearAllEvents();
        }

        // Ignora BOM UTF-8, se existir
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Salta a linha de cabeçalhos
        fgetcsv($handle, 0, ';', '"', '\\');

        $this->db->exec('BEGIN TRANSACTION');

        $stmt = $this->db->prepare("
            INSERT INTO events
            (room, weekday_num, start_time, end_time, event_date,
             module_name, module_acronym, event_type, event_title)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $inserted = 0;
        $skipped = 0;
        $rowNum = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            $rowNum++;

            if (empty(array_filter($row))) {
                $skipped++;
                continue;
            }

            $moduleName    = trim($row[0] ?? '');
            $moduleAcronym = trim($row[1] ?? '');
            $roomField     = trim($row[10] ?? '');
            $startTime     = trim($row[12] ?? '');
            $endTime       = trim($row[13] ?? '');
            $weekString    = trim($row[14] ?? '');
            $eventTitle    = trim($row[15] ?? '');
            $eventType     = trim($row[16] ?? 'Horarios');
            $weekdayNum    = (int) ($row[21] ?? 0);

            if (empty($moduleName) || empty($roomField)) {
                $skipped++;
                continue;
            }

            // Uma linha do CSV pode ter várias salas, ex: "3.1.02,3.1.10"
            $roomList = array_filter(array_map('trim', explode(',', $roomField)));
            if (empty($roomList)) {
                $skipped++;
                continue;
            }

            // E várias datas na coluna "Week", ex: "2026-02-10,2026-02-17"
            $dates = array_filter(array_map('trim', explode(',', $weekString)));
            if (empty($dates)) {
                $skipped++;
                continue;
            }

            // Um evento por data E por sala
            foreach ($dates as $date) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }

                foreach ($roomList as $room) {
                    if ($room === '') {
                        continue;
                    }

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

        $this->db->exec('COMMIT');
        fclose($handle);

        return [
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'rows'     => $rowNum,
            'cleared'  => $cleared,
        ];
    }
}
