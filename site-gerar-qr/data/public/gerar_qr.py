# data/public/gerar_qr.py
def render():
    return """<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gerar QR Code</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <header><h1>➕ Gerar QR Code</h1></header>
        <nav>
            <a href="/index.py">🏠 Início</a>
            <a href="/gerar_qr.py" class="active">➕ Gerar QR</a>
            <a href="/admin.py">⚙️ Admin</a>
        </nav>
        <main>
            <h2>Preencha os dados</h2>
            <form onsubmit="generateQR(event)">
                <div class="form-group">
                    <label>Dados para codificar:</label>
                    <input type="text" id="data" placeholder="URL ou texto" required>
                </div>
                <div class="form-group">
                    <label>Nome do arquivo (opcional):</label>
                    <input type="text" id="name" placeholder="nome_do_qr">
                </div>
                <button type="submit" class="btn">🔄 Gerar QR Code</button>
            </form>
            <div id="result" style="margin-top:20px;"></div>
        </main>
        <footer>
            <p>Sistema de QR Codes - Python</p>
        </footer>
    </div>
    <script>
    function generateQR(e) {
        e.preventDefault();
        const data = document.getElementById('data').value;
        const name = document.getElementById('name').value;
        
        fetch('/api/generate-qr', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({data, name})
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                document.getElementById('result').innerHTML = `
                    <div style="background:#e8f5e9;padding:20px;border-radius:10px;text-align:center;">
                        <h3 style="color:#2e7d32;">✅ QR Code gerado!</h3>
                        <img src="${result.qr_code.url}" style="max-width:200px;border:2px solid #ddd;padding:10px;border-radius:10px;background:white;">
                        <p><strong>Arquivo:</strong> ${result.qr_code.filename}</p>
                        <p><strong>Dados:</strong> ${result.qr_code.data}</p>
                        <a href="${result.qr_code.url}" download class="btn" style="display:inline-block;margin-top:10px;">⬇️ Baixar</a>
                    </div>
                `;
            } else {
                alert('❌ Erro: ' + result.error);
            }
        })
        .catch(error => {
            alert('❌ Erro ao gerar QR Code: ' + error);
        });
    }
    </script>
</body>
</html>"""