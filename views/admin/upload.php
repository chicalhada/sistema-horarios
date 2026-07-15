<?php
/**
 * views/admin/upload.php
 * Espera as variáveis: $stats (array), $logs (array), $csrfToken (string),
 * $message (string|null), $messageType ('success'|'error'|null)
 */
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - ESTGA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f0f2f5;
            padding: 15px;
            color: #1a1a2e;
        }
        .container { max-width: 700px; margin: 0 auto; }

        .header {
            background: linear-gradient(135deg, #1a3c6e, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 20px; }
        .header a {
            color: white;
            background: rgba(255,255,255,0.15);
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
        }
        .header a:hover { background: rgba(255,255,255,0.25); }

        .stats-row { display: flex; gap: 12px; margin-bottom: 15px; }
        .stat-card {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-card .value { font-size: 26px; font-weight: bold; color: #1a3c6e; }
        .stat-card .label { font-size: 12px; color: #6c757d; margin-top: 4px; }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .card h2 { font-size: 16px; margin-bottom: 14px; color: #1a3c6e; }

        .message {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .message.success { background: #eafaf1; color: #1e7e34; }
        .message.error { background: #fdecea; color: #b3261e; }

        label { display: block; font-size: 13px; color: #495057; margin-bottom: 6px; font-weight: 600; }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px dashed #adb5bd;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            background: #f8f9fa;
        }

        .radio-group { display: flex; gap: 15px; margin-bottom: 18px; }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: normal;
            margin-bottom: 0;
        }

        button, .btn {
            padding: 11px 18px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #1a3c6e; color: white; }
        .btn-primary:hover { background: #2a5298; }
        .btn-danger { background: #fff; color: #b3261e; border: 1px solid #f2b8b5; }
        .btn-danger:hover { background: #fdecea; }

        .hint { font-size: 12px; color: #6c757d; margin-top: -8px; margin-bottom: 15px; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #f0f0f0; }
        th { color: #6c757d; font-weight: 600; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .badge.success { background: #eafaf1; color: #1e7e34; }
        .badge.error { background: #fdecea; color: #b3261e; }
        .empty { color: #adb5bd; font-style: italic; font-size: 13px; text-align: center; padding: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Administração</h1>
            <a href="admin.php?action=logout">Sair</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?= $messageType === 'error' ? 'error' : 'success' ?>">
                <?= $messageType === 'error' ? '⚠️' : '✅' ?> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-card">
                <div class="value"><?= (int) $stats['events'] ?></div>
                <div class="label">Eventos</div>
            </div>
            <div class="stat-card">
                <div class="value"><?= (int) $stats['rooms'] ?></div>
                <div class="label">Salas</div>
            </div>
        </div>

        <div class="card">
            <h2>📥 Importar CSV</h2>
            <form method="POST" action="admin.php?action=upload" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <!-- Limite de tamanho também aplicado no lado do servidor (AdminController) -->
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= (int) ADMIN_MAX_UPLOAD_SIZE ?>">

                <label for="csv_file">Ficheiro CSV</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
                <div class="hint">Máximo <?= round(ADMIN_MAX_UPLOAD_SIZE / 1024 / 1024, 1) ?> MB. Apenas ficheiros .csv.</div>

                <div class="radio-group">
                    <label><input type="radio" name="mode" value="append" checked> ➕ Adicionar aos dados existentes</label>
                    <label><input type="radio" name="mode" value="clear"> 🗑️ Apagar tudo e importar de novo</label>
                </div>

                <button type="submit" class="btn-primary">Importar</button>
            </form>
        </div>

        <div class="card">
            <h2>🧹 Manutenção</h2>
            <p style="font-size:13px;color:#6c757d;margin-bottom:12px;">
                Apaga todos os eventos da base de dados sem importar um novo ficheiro.
            </p>
            <form method="POST" action="admin.php?action=clear"
                  onsubmit="return confirm('Tens a certeza que queres apagar TODOS os eventos? Esta ação não pode ser desfeita.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn-danger">🗑️ Apagar todos os dados</button>
            </form>
        </div>

        <div class="card">
            <h2>📋 Últimas Ações</h2>
            <?php if (empty($logs)): ?>
                <div class="empty">Ainda não há registos.</div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Data</th>
                        <th>Ficheiro</th>
                        <th>Modo</th>
                        <th>Inseridos</th>
                        <th>Ignorados</th>
                        <th>Estado</th>
                    </tr>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                            <td><?= htmlspecialchars($log['filename']) ?></td>
                            <td><?= htmlspecialchars($log['mode']) ?></td>
                            <td><?= (int) $log['inserted_count'] ?></td>
                            <td><?= (int) $log['skipped_count'] ?></td>
                            <td>
                                <span class="badge <?= $log['status'] === 'success' ? 'success' : 'error' ?>">
                                    <?= htmlspecialchars($log['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <a href="room_list.php" class="btn" style="background:none;color:#6c757d;font-size:13px;">← Voltar à lista de salas</a>
    </div>
</body>
</html>
