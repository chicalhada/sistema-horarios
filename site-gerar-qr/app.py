# app.py - Servidor QR Code
from http.server import HTTPServer, SimpleHTTPRequestHandler
import json
import os
import qrcode
import uuid
from datetime import datetime
from urllib.parse import urlparse
import importlib.util

class QRHandler(SimpleHTTPRequestHandler):
    def do_GET(self):
        parsed = urlparse(self.path)
        path = parsed.path
        
        if path == '/' or path == '/index.py':
            self.serve_page('data/public/index.py')
        elif path == '/admin.py':
            self.serve_page('data/public/admin.py')
        elif path == '/gerar_qr.py':
            self.serve_page('data/public/gerar_qr.py')
        elif path == '/api/qr-codes':
            self.api_list_qr_codes()
        elif path.startswith('/css/') or path.startswith('/js/') or path.startswith('/qr-codes/'):
            self.serve_static(path)
        else:
            self.send_error(404)
    
    def do_POST(self):
        parsed = urlparse(self.path)
        path = parsed.path
        
        if path == '/api/generate-qr':
            self.api_generate_qr()
        elif path == '/api/delete-qr':
            self.api_delete_qr()
        else:
            self.send_error(404)
    
    def serve_page(self, filename):
        try:
            spec = importlib.util.spec_from_file_location("page", filename)
            module = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(module)
            if hasattr(module, 'render'):
                content = module.render()
                self.send_response(200)
                self.send_header('Content-Type', 'text/html; charset=utf-8')
                self.end_headers()
                self.wfile.write(content.encode('utf-8'))
        except Exception as e:
            self.send_error(500, str(e))
    
    def serve_static(self, path):
        filepath = 'data/public' + path
        try:
            with open(filepath, 'rb') as f:
                content = f.read()
            self.send_response(200)
            if path.endswith('.css'):
                self.send_header('Content-Type', 'text/css')
            elif path.endswith('.png'):
                self.send_header('Content-Type', 'image/png')
            self.end_headers()
            self.wfile.write(content)
        except:
            self.send_error(404)
    
    def api_generate_qr(self):
        content_length = int(self.headers.get('Content-Length', 0))
        post_data = self.rfile.read(content_length)
        
        try:
            data = json.loads(post_data.decode('utf-8'))
        except:
            data = {}
        
        text = data.get('data', '')
        name = data.get('name', None)
        
        if not text:
            self.send_json({'success': False, 'error': 'Dados não fornecidos'})
            return
        
        try:
            if not name:
                timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                unique_id = str(uuid.uuid4())[:8]
                name = f"qrcode_{timestamp}_{unique_id}"
            
            filename = f"{name}.png"
            filepath = f"data/public/qr-codes/{filename}"
            os.makedirs(os.path.dirname(filepath), exist_ok=True)
            
            qr = qrcode.QRCode(
                version=1,
                error_correction=qrcode.constants.ERROR_CORRECT_L,
                box_size=10,
                border=4,
            )
            qr.add_data(text)
            qr.make(fit=True)
            
            img = qr.make_image(fill_color="black", back_color="white")
            img.save(filepath)
            
            self.send_json({
                'success': True,
                'qr_code': {
                    'filename': filename,
                    'filepath': filepath,
                    'url': f"/qr-codes/{filename}",
                    'data': text,
                    'created_at': datetime.now().isoformat()
                }
            })
        except Exception as e:
            self.send_json({'success': False, 'error': str(e)})
    
    def api_list_qr_codes(self):
        qr_dir = "data/public/qr-codes"
        qr_files = []
        if os.path.exists(qr_dir):
            for file in os.listdir(qr_dir):
                if file.endswith('.png'):
                    filepath = os.path.join(qr_dir, file)
                    stat = os.stat(filepath)
                    qr_files.append({
                        'filename': file,
                        'size': stat.st_size,
                        'modified': datetime.fromtimestamp(stat.st_mtime).isoformat(),
                        'url': f"/qr-codes/{file}"
                    })
        self.send_json({'success': True, 'qr_codes': sorted(qr_files, key=lambda x: x['modified'], reverse=True)})
    
    def api_delete_qr(self):
        content_length = int(self.headers.get('Content-Length', 0))
        post_data = self.rfile.read(content_length)
        
        try:
            data = json.loads(post_data.decode('utf-8'))
            filename = data.get('filename')
        except:
            filename = None
        
        if not filename:
            self.send_json({'success': False, 'error': 'Filename não fornecido'})
            return
        
        filepath = f"data/public/qr-codes/{filename}"
        if os.path.exists(filepath):
            os.remove(filepath)
            self.send_json({'success': True})
        else:
            self.send_json({'success': False, 'error': 'Arquivo não encontrado'})
    
    def send_json(self, data):
        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps(data, ensure_ascii=False).encode('utf-8'))

def main():
    port = 8080
    host = 'localhost'
    
    os.makedirs('data/public/qr-codes', exist_ok=True)
    os.makedirs('data/public/css', exist_ok=True)
    os.makedirs('data/public/js', exist_ok=True)
    
    print("=" * 60)
    print("   📱 SISTEMA DE QR CODES")
    print("=" * 60)
    print()
    print(f"🌐 Servidor: http://{host}:{port}")
    print(f"📁 QR Codes: data/public/qr-codes/")
    print()
    print("📋 Páginas:")
    print(f"   • http://{host}:{port}/")
    print(f"   • http://{host}:{port}/admin.py")
    print(f"   • http://{host}:{port}/gerar_qr.py")
    print()
    print("🔴 Pressione CTRL+C para parar")
    print("=" * 60)
    
    server = HTTPServer((host, port), QRHandler)
    
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n\n👋 Servidor encerrado!")

if __name__ == '__main__':
    main()