# data/public/index.py
def render():
    return """<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sistema QR Codes</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📱 Sistema de QR Codes</h1>
            <p>Gerencie seus QR Codes facilmente</p>
        </header>
        <nav>
            <a href="/index.py" class="active">🏠 Início</a>
            <a href="/gerar_qr.py">➕ Gerar QR</a>
            <a href="/admin.py">⚙️ Admin</a>
        </nav>
        <main>
            <h2>Bem-vindo!</h2>
            <p>Use o menu acima para gerar ou gerenciar seus QR Codes.</p>
            <div class="actions">
                <a href="/gerar_qr.py" class="btn">➕ Gerar QR Code</a>
                <a href="/admin.py" class="btn">⚙️ Gerenciar</a>
            </div>
        </main>
        <footer>
            <p>Sistema de QR Codes - Python</p>
        </footer>
    </div>
</body>
</html>"""