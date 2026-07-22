# data/public/admin.py
def render():
    return """<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - QR Codes</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <header><h1>⚙️ Administração</h1></header>
        <nav>
            <a href="/index.py">🏠 Início</a>
            <a href="/gerar_qr.py">➕ Gerar QR</a>
            <a href="/admin.py" class="active">⚙️ Admin</a>
        </nav>
        <main>
            <h2>QR Codes Gerados</h2>
            <div id="qrList"></div>
        </main>
        <footer>
            <p>Sistema de QR Codes - Python</p>
        </footer>
    </div>
    <script>
    fetch('/api/qr-codes')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('qrList');
            if (!data.success) {
                list.innerHTML = '<p>❌ Erro ao carregar QR Codes</p>';
                return;
            }
            if (data.qr_codes.length === 0) {
                list.innerHTML = '<p>📭 Nenhum QR Code gerado ainda</p>';
                return;
            }
            
            let html = '<div class="qr-grid">';
            data.qr_codes.forEach(qr => {
                html += `
                    <div class="qr-card">
                        <img src="${qr.url}" alt="${qr.filename}">
                        <p><strong>${qr.filename}</strong></p>
                        <p style="font-size:12px;color:#999;">${new Date(qr.modified).toLocaleString()}</p>
                        <button onclick="deleteQR('${qr.filename}')" class="btn-delete">🗑️ Remover</button>
                    </div>
                `;
            });
            html += '</div>';
            list.innerHTML = html;
        })
        .catch(error => {
            document.getElementById('qrList').innerHTML = '<p>❌ Erro de conexão</p>';
        });
    
    function deleteQR(filename) {
        if (confirm('Remover ' + filename + '?')) {
            fetch('/api/delete-qr', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({filename})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('❌ Erro ao remover');
                }
            });
        }
    }
    </script>
</body>
</html>"""