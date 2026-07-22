# server.py
"""Servidor completo do sistema de QR Codes"""

from http.server import HTTPServer, SimpleHTTPRequestHandler
import json
import os
from urllib.parse import urlparse
import importlib.util

class SistemaHandler(SimpleHTTPRequestHandler):
    """Handler principal"""
    
    def do_GET(self):
        parsed = urlparse(self.path)
        path = parsed.path
        
        # Páginas
        if path == '/' or path == '/index.py':
            self.serve_page('data/public/index.py')
        elif path == '/admin.py':
            self.serve_page('data/public/admin.py')
        elif path == '/gerar_qr.py':
            self.serve_page('data/public/gerar_qr.py')
        elif path == '/room_list.py':
            self.serve_page('data/public/room_list.py')
        
        # API
        elif path == '/api/qr-codes':
            self.api_list_qr_codes()
        
        # Arquivos estáticos
        elif path.startswith('/css/') or path.startswith('/js/') or path.startswith('/qr-codes/'):
            self.serve_static(path)
        
        else:
            self.send_error(404, f"Página não encontrada: {path}")
    
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
        """Serve uma página Python"""
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
        """Serve arquivos estáticos"""
        filepath = 'data/public' + path
        try:
            with open(filepath, 'rb') as f:
                content = f.read()
            
            self.send_response(200)
            if path.endswith('.css'):
                self.send_header('Content-Type', 'text/css')
            elif path.endswith('.js'):
                self.send_header('Content-Type', 'application/javascript')
            elif path.endswith('.png'):
                self.send_header('Content-Type', 'image/png')
            self.end_headers()
            self.wfile.write(content)
        except:
            self.send_error(404)
    
    def api_list_qr_codes(self):
        """Lista todos os QR Codes"""
        from src.helpers.qr_helper import QRHelper
        qr_codes = QRHelper.list_qr_codes()
        self.send_json({'success': True, 'qr_codes': qr_codes})
    
    def api_generate_qr(self):
        """Gera um novo QR Code"""
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
            from src.helpers.qr_helper import QRHelper
            result = QRHelper.generate_qr(text, name)
            self.send_json({'success': True, 'qr_code': result})
        except Exception as e:
            self.send_json({'success': False, 'error': str(e)})
    
    def api_delete_qr(self):
        """Remove um QR Code"""
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
        
        from src.helpers.qr_helper import QRHelper
        success = QRHelper.delete_qr(filename)
        self.send_json({'success': success})
    
    def send_json(self, data):
        """Envia resposta JSON"""
        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps(data, ensure_ascii=False).encode('utf-8'))

def main():
    """Inicia o servidor"""
    port = 8000
    host = 'localhost'
    
    # Criar diretórios
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
    
    server = HTTPServer((host, port), SistemaHandler)
    
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n\n👋 Servidor encerrado!")

if __name__ == '__main__':
    main()