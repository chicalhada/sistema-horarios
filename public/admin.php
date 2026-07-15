<?php
/**
 * admin.php - Ponto de entrada da área de administração
 *
 * Rotas:
 *   GET  admin.php                -> mostra login ou painel, consoante autenticação
 *   POST admin.php?action=login   -> processa o login
 *   GET  admin.php?action=logout  -> termina a sessão
 *   POST admin.php?action=upload  -> processa o upload + importação do CSV
 *   POST admin.php?action=clear   -> apaga todos os eventos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/admin_config.php';
require_once __DIR__ . '/../src/controllers/AdminController.php';

$db = getDatabase();
$admin = new AdminController($db);

$action = $_GET['action'] ?? '';
$message = null;
$messageType = null;

// ============================================
// LOGOUT
// ============================================
if ($action === 'logout') {
    $admin->logout();
    header('Location: admin.php');
    exit;
}

// ============================================
// LOGIN
// ============================================
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$admin->validateCsrfToken($token)) {
        $loginError = 'Pedido inválido. Tenta novamente.';
    } elseif ($admin->isLockedOut()) {
        $loginError = 'Demasiadas tentativas falhadas. Tenta novamente dentro de alguns minutos.';
    } elseif ($admin->login($password)) {
        header('Location: admin.php');
        exit;
    } else {
        $loginError = 'Password incorreta.';
    }
}

// ============================================
// A PARTIR DAQUI, É PRECISO ESTAR AUTENTICADO
// ============================================
if (!$admin->isLoggedIn()) {
    $error = $loginError ?? null;
    $csrfToken = $admin->getCsrfToken();
    require __DIR__ . '/../views/admin/login.php';
    exit;
}

// ============================================
// UPLOAD (requer autenticação)
// ============================================
if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!$admin->validateCsrfToken($token)) {
        $message = 'Pedido inválido. Tenta novamente.';
        $messageType = 'error';
    } else {
        $mode = ($_POST['mode'] ?? 'append') === 'clear' ? 'clear' : 'append';
        $file = $_FILES['csv_file'] ?? [];

        $result = $admin->handleUpload($file, $mode);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';

        if ($result['success'] && isset($result['stats'])) {
            $s = $result['stats'];
            $message .= " Inseridos: {$s['inserted']}, ignorados: {$s['skipped']}.";
        }
    }
}

// ============================================
// LIMPAR DADOS (requer autenticação)
// ============================================
if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!$admin->validateCsrfToken($token)) {
        $message = 'Pedido inválido. Tenta novamente.';
        $messageType = 'error';
    } else {
        $result = $admin->clearAllData();
        $message = $result['message'];
        $messageType = 'success';
    }
}

// ============================================
// MOSTRA O PAINEL
// ============================================
$stats = $admin->getStats();
$logs = $admin->getLogs(20);
$csrfToken = $admin->getCsrfToken();

require __DIR__ . '/../views/admin/upload.php';
