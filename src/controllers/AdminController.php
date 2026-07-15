<?php
/**
 * AdminController.php
 *
 * Toda a lógica da área de administração:
 *  - login / logout (com bloqueio após várias tentativas falhadas)
 *  - validação e receção do upload do CSV
 *  - chamada ao CsvImporter
 *  - manutenção (apagar dados, ver estatísticas)
 *  - registo de logs (quem fez upload e quando)
 *
 * A parte de apresentação (HTML) está separada em views/admin/*.php.
 * Este ficheiro não deve imprimir HTML diretamente.
 */

require_once __DIR__ . '/../services/CsvImporter.php';

class AdminController
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ============================================
    // AUTENTICAÇÃO
    // ============================================

    public function isLoggedIn(): bool
    {
        if (empty($_SESSION['admin_logged_in'])) {
            return false;
        }

        // Expira a sessão por inatividade
        $lastActivity = $_SESSION['admin_last_activity'] ?? 0;
        if (time() - $lastActivity > ADMIN_SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        $_SESSION['admin_last_activity'] = time();
        return true;
    }

    /**
     * Verifica se o IP está temporariamente bloqueado por demasiadas
     * tentativas de login falhadas recentes.
     */
    public function isLockedOut(): bool
    {
        $ip = $this->getClientIp();
        $since = date('Y-m-d H:i:s', time() - ADMIN_LOGIN_LOCKOUT_SECONDS);

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM admin_login_attempts
            WHERE ip_address = :ip AND success = 0 AND created_at >= :since
        ");
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':since', $since, SQLITE3_TEXT);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return (int) $row['count'] >= ADMIN_MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Tenta autenticar com a password fornecida.
     * Devolve true em caso de sucesso, false caso contrário.
     */
    public function login(string $password): bool
    {
        if ($this->isLockedOut()) {
            return false;
        }

        $success = password_verify($password, ADMIN_PASSWORD_HASH);
        $this->recordLoginAttempt($success);

        if ($success) {
            // Regenera o ID de sessão para evitar session fixation
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_last_activity'] = time();
        }

        return $success;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    private function recordLoginAttempt(bool $success): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO admin_login_attempts (success, ip_address) VALUES (:success, :ip)
        ");
        $stmt->bindValue(':success', $success ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(':ip', $this->getClientIp(), SQLITE3_TEXT);
        $stmt->execute();
    }

    // ============================================
    // UPLOAD / IMPORTAÇÃO
    // ============================================

    /**
     * Valida e processa o upload de um CSV.
     *
     * @param array  $file Entrada de $_FILES['csv_file']
     * @param string $mode 'append' ou 'clear'
     *
     * @return array{success:bool, message:string, stats?:array}
     */
    public function handleUpload(array $file, string $mode): array
    {
        $mode = ($mode === 'clear') ? 'clear' : 'append';

        $validation = $this->validateUploadedFile($file);
        if (!$validation['valid']) {
            $this->logImport($file['name'] ?? 'desconhecido', $mode, 0, 0, 'error', $validation['message']);
            return ['success' => false, 'message' => $validation['message']];
        }

        // Guarda o ficheiro com um nome único fora da pasta pública
        $safeName = $this->sanitizeFilename($file['name']);
        $storedName = date('Ymd_His') . '_' . $safeName;
        $destination = rtrim(ADMIN_UPLOAD_DIR, '/') . '/' . $storedName;

        if (!is_dir(ADMIN_UPLOAD_DIR)) {
            mkdir(ADMIN_UPLOAD_DIR, 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $message = 'Não foi possível guardar o ficheiro no servidor.';
            $this->logImport($safeName, $mode, 0, 0, 'error', $message);
            return ['success' => false, 'message' => $message];
        }

        try {
            $importer = new CsvImporter($this->db);
            $stats = $importer->importFile($destination, $mode);

            $this->logImport(
                $safeName,
                $mode,
                $stats['inserted'],
                $stats['skipped'],
                'success',
                "Linhas processadas: {$stats['rows']}" . ($mode === 'clear' ? "; eventos anteriores apagados: {$stats['cleared']}" : '')
            );

            return ['success' => true, 'message' => 'Importação concluída com sucesso.', 'stats' => $stats];
        } catch (Throwable $e) {
            $this->logImport($safeName, $mode, 0, 0, 'error', $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao importar: ' . $e->getMessage()];
        }
    }

    /**
     * Valida tamanho, extensão e tipo MIME do ficheiro enviado.
     */
    private function validateUploadedFile(array $file): array
    {
        if (empty($file) || !isset($file['error'])) {
            return ['valid' => false, 'message' => 'Nenhum ficheiro foi enviado.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'O ficheiro excede o tamanho máximo permitido pelo servidor.',
                UPLOAD_ERR_FORM_SIZE  => 'O ficheiro excede o tamanho máximo permitido pelo formulário.',
                UPLOAD_ERR_PARTIAL    => 'O ficheiro só foi parcialmente enviado.',
                UPLOAD_ERR_NO_FILE    => 'Nenhum ficheiro foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Erro no servidor: pasta temporária em falta.',
                UPLOAD_ERR_CANT_WRITE => 'Erro no servidor: falha ao escrever o ficheiro.',
            ];
            return ['valid' => false, 'message' => $errors[$file['error']] ?? 'Erro desconhecido no upload.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'Upload inválido.'];
        }

        if ($file['size'] <= 0) {
            return ['valid' => false, 'message' => 'O ficheiro está vazio.'];
        }

        if ($file['size'] > ADMIN_MAX_UPLOAD_SIZE) {
            $maxMb = round(ADMIN_MAX_UPLOAD_SIZE / 1024 / 1024, 1);
            return ['valid' => false, 'message' => "O ficheiro excede o tamanho máximo de {$maxMb} MB."];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ADMIN_ALLOWED_EXTENSIONS, true)) {
            return ['valid' => false, 'message' => 'Só são aceites ficheiros .csv.'];
        }

        // Verifica o tipo MIME real do ficheiro (não confia na extensão nem no
        // Content-Type enviado pelo browser, que podem ser falsificados)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($mimeType !== false && !in_array($mimeType, ADMIN_ALLOWED_MIME_TYPES, true)) {
                return ['valid' => false, 'message' => "Tipo de ficheiro não permitido ({$mimeType}). Envia um .csv válido."];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Remove caracteres perigosos do nome do ficheiro, mantendo apenas
     * letras, números, pontos, hífens e underscores.
     */
    private function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $filename);
        return $filename !== '' ? $filename : 'ficheiro.csv';
    }

    private function logImport(string $filename, string $mode, int $inserted, int $skipped, string $status, string $message = ''): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO import_logs (filename, mode, inserted_count, skipped_count, status, message, ip_address)
            VALUES (:filename, :mode, :inserted, :skipped, :status, :message, :ip)
        ");
        $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
        $stmt->bindValue(':mode', $mode, SQLITE3_TEXT);
        $stmt->bindValue(':inserted', $inserted, SQLITE3_INTEGER);
        $stmt->bindValue(':skipped', $skipped, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $this->getClientIp(), SQLITE3_TEXT);
        $stmt->execute();
    }

    // ============================================
    // MANUTENÇÃO
    // ============================================

    /**
     * Apaga todos os eventos da base de dados e regista a ação nos logs.
     */
    public function clearAllData(): array
    {
        $removed = clearAllEvents();
        $this->logImport('(sem ficheiro)', 'clear_only', 0, 0, 'success', "Eventos apagados manualmente: {$removed}");

        return ['success' => true, 'message' => "{$removed} evento(s) apagado(s) com sucesso."];
    }

    /**
     * Estatísticas gerais para o painel.
     */
    public function getStats(): array
    {
        return [
            'events' => getEventCount(),
            'rooms'  => getRoomCount(),
        ];
    }

    /**
     * Últimos registos de importação/manutenção, mais recentes primeiro.
     */
    public function getLogs(int $limit = 20): array
    {
        $stmt = $this->db->prepare("SELECT * FROM import_logs ORDER BY id DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $logs = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $logs[] = $row;
        }
        return $logs;
    }

    // ============================================
    // CSRF
    // ============================================

    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(?string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && !empty($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    // ============================================
    // HELPERS
    // ============================================

    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
