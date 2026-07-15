<?php
/**
 * views/admin/login.php
 * Espera as variáveis: $error (string|null), $csrfToken (string)
 */
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administração - ESTGA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            width: 100%;
            max-width: 380px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a3c6e, #2a5298);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        .header h1 { font-size: 20px; margin-bottom: 5px; }
        .header p { font-size: 13px; opacity: 0.8; }
        form { padding: 25px 20px; }
        label { display: block; font-size: 13px; color: #495057; margin-bottom: 6px; font-weight: 600; }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 15px;
        }
        input[type="password"]:focus { outline: none; border-color: #2a5298; }
        button {
            width: 100%;
            padding: 12px;
            background: #1a3c6e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #2a5298; }
        .error {
            background: #fdecea;
            color: #b3261e;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .back-link {
            display: block;
            text-align: center;
            padding: 0 20px 20px;
            font-size: 13px;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>🔐 Administração</h1>
            <p>Horários ESTGA</p>
        </div>
        <form method="POST" action="admin.php?action=login">
            <?php if (!empty($error)): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autofocus>

            <button type="submit">Entrar</button>
        </form>
        <a href="room_list.php" class="back-link">← Voltar à lista de salas</a>
    </div>
</body>
</html>
