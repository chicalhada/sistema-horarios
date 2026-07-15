<?php
/**
 * admin_config.php
 *
 * Configuração da área de administração.
 *
 * ⚠️ IMPORTANTE - MUDA A PASSWORD ANTES DE PÔR ISTO EM PRODUÇÃO! ⚠️
 * A password por defeito é "estga2026". Para gerar o hash de uma password
 * nova, corre no terminal:
 *
 *   php -r "echo password_hash('a-tua-password-aqui', PASSWORD_DEFAULT), PHP_EOL;"
 *
 * e substitui o valor de ADMIN_PASSWORD_HASH abaixo pelo resultado.
 * Nunca guardes a password em texto simples no código.
 */

if (!defined('ADMIN_PASSWORD_HASH')) {
    // Hash correspondente à password "estga2026" (ALTERAR!)
    define('ADMIN_PASSWORD_HASH', '$2b$10$4mcukvCDh/YYTqLPK2KK6.B97doqe2Qk5sg18pGqbTyG7BxULmvAW');
}

// Quantos segundos dura a sessão de administrador sem atividade
if (!defined('ADMIN_SESSION_LIFETIME')) {
    define('ADMIN_SESSION_LIFETIME', 60 * 30); // 30 minutos
}

// Tamanho máximo do ficheiro CSV, em bytes (5 MB)
if (!defined('ADMIN_MAX_UPLOAD_SIZE')) {
    define('ADMIN_MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
}

// Extensões e tipos MIME aceites para o CSV
if (!defined('ADMIN_ALLOWED_EXTENSIONS')) {
    define('ADMIN_ALLOWED_EXTENSIONS', ['csv']);
}
if (!defined('ADMIN_ALLOWED_MIME_TYPES')) {
    define('ADMIN_ALLOWED_MIME_TYPES', [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel', // alguns navegadores/SO reportam CSV como isto
    ]);
}

// Pasta onde os CSV importados ficam guardados (fora da pasta pública!)
if (!defined('ADMIN_UPLOAD_DIR')) {
    define('ADMIN_UPLOAD_DIR', __DIR__ . '/../../uploads');
}

// Número máximo de tentativas de login falhadas antes de bloquear temporariamente
if (!defined('ADMIN_MAX_LOGIN_ATTEMPTS')) {
    define('ADMIN_MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('ADMIN_LOGIN_LOCKOUT_SECONDS')) {
    define('ADMIN_LOGIN_LOCKOUT_SECONDS', 60 * 5); // 5 minutos
}
