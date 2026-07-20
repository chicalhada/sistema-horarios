<?php
/**
 * CsvImporter.php
 *
 * Serviço responsável por ler o CSV de horários e inserir os eventos
 * na base de dados. Extraído de import.php para poder ser reutilizado
 * tanto pelo script de importação original como pelo painel de
 * administração (upload feito através do browser).
 *
 * Formato do CSV esperado (separador ';'), com cabeçalhos:
 *   ModuleName, ModuleAcronym, Classroom, StartTime, EndTime, Week,
 *   EventTitle, EventType, WeekdayNum, etc.
 *
 * AGORA: Utiliza os nomes das colunas do cabeçalho para identificar
 *        os campos, em vez de números fixos.
 */
class CsvImporter
{
    private SQLite3 $db;

    // Mapeamento dos nomes das colunas (caso o CSV tenha nomes diferentes)
    private array $columnMap = [
        'module_name' => 'ModuleName',
        'module_acronym' => 'ModuleAcronym',
        'classroom' => 'Classroom',
        'start_time' => 'StartTime',
        'end_time' => 'EndTime',
        'week' => 'Week',
        'event_title' => 'EventTitle',
        'event_type' => 'EventType',
        'weekday_num' => 'WeekdayNum',
        'num_students' => 'NumStudents',
        'event_identifier' => 'EventIdentifier',
        'student_group' => 'StudentGroup',
    ];

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

        // ============================================
        // 🔑 LER CABEÇALHOS E CRIAR MAPEAMENTO
        // ============================================
        $headers = fgetcsv($handle, 0, ';', '"', '\\');
        if (!$headers) {
            throw new RuntimeException("Não foi possível ler os cabeçalhos do CSV.");
        }

        // Normalizar nomes dos cabeçalhos (remover espaços, caracteres especiais)
        $headers = array_map(function ($h) {
            return trim(preg_replace('/[^a-zA-Z0-9]/', '', $h));
        }, $headers);

        // Encontrar o índice de cada coluna que precisamos
        $columnIndexes = $this->getColumnIndexes($headers);

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

            // Usar os índices mapeados para extrair os dados
            $moduleName    = trim($this->getColumnValue($row, $columnIndexes, 'module_name'));
            $moduleAcronym = trim($this->getColumnValue($row, $columnIndexes, 'module_acronym'));
            $roomField     = trim($this->getColumnValue($row, $columnIndexes, 'classroom'));
            $startTime     = trim($this->getColumnValue($row, $columnIndexes, 'start_time'));
            $endTime       = trim($this->getColumnValue($row, $columnIndexes, 'end_time'));
            $weekString    = trim($this->getColumnValue($row, $columnIndexes, 'week'));
            $eventTitle    = trim($this->getColumnValue($row, $columnIndexes, 'event_title'));
            $eventType     = trim($this->getColumnValue($row, $columnIndexes, 'event_type'));
            $weekdayNum    = (int) ($this->getColumnValue($row, $columnIndexes, 'weekday_num'));

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

    /**
     * Obtém o índice de cada coluna que precisamos, com base no cabeçalho.
     *
     * @param array $headers Array com os nomes das colunas do CSV
     * @return array Mapeamento: nome_interno => índice
     * @throws RuntimeException se alguma coluna obrigatória não for encontrada
     */
    private function getColumnIndexes(array $headers): array
    {
        $indexes = [];
        $required = ['module_name', 'classroom', 'week']; // Colunas obrigatórias
        $optional = ['module_acronym', 'start_time', 'end_time', 'event_title', 'event_type', 'weekday_num', 'num_students', 'event_identifier', 'student_group'];

        foreach ($this->columnMap as $internalName => $csvHeaderName) {
            // Limpar o nome do cabeçalho para comparação
            $cleanHeaderName = trim(preg_replace('/[^a-zA-Z0-9]/', '', $csvHeaderName));
            $index = array_search($cleanHeaderName, $headers);

            if ($index !== false) {
                $indexes[$internalName] = $index;
            } elseif (in_array($internalName, $required)) {
                throw new RuntimeException("Coluna obrigatória não encontrada no CSV: {$csvHeaderName}");
            } else {
                // Coluna opcional não encontrada - usar valor padrão
                $indexes[$internalName] = null;
            }
        }

        return $indexes;
    }

    /**
     * Obtém o valor de uma coluna, com base no índice mapeado.
     *
     * @param array $row        A linha do CSV
     * @param array $indexes   Mapeamento de índices
     * @param string $column   Nome interno da coluna
     * @return string O valor da célula, ou string vazia se não existir
     */
    private function getColumnValue(array $row, array $indexes, string $column): string
    {
        $index = $indexes[$column] ?? null;
        if ($index === null) {
            return '';
        }
        return $row[$index] ?? '';
    }
}