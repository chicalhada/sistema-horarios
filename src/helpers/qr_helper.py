# src/helpers/qr_helper.py
"""Helper para gerenciar QR Codes"""

import os
import uuid
from datetime import datetime
import struct
import zlib

class QRCodeGenerator:
    """Gerador simples de QR Code"""
    
    def __init__(self, version=1):
        self.version = version
        self.size = 21 + 4 * (version - 1)
        self.matrix = []
    
    def generate(self, text):
        """Gera o QR Code"""
        self.matrix = [[0] * self.size for _ in range(self.size)]
        
        # Desenhar padrões de localização
        self._draw_finder_patterns()
        self._draw_timing_patterns()
        
        # Codificar dados (simplificado)
        self._fill_simple_data(text)
        
        return True
    
    def _draw_finder_patterns(self):
        """Desenha padrões de localização"""
        pattern = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1]
        ]
        
        def draw(x_off, y_off):
            for y in range(7):
                for x in range(7):
                    self.matrix[y_off + y][x_off + x] = pattern[y][x]
        
        draw(0, 0)
        draw(self.size - 7, 0)
        draw(0, self.size - 7)
    
    def _draw_timing_patterns(self):
        """Desenha padrões de temporização"""
        for x in range(8, self.size - 8):
            self.matrix[6][x] = 1 if x % 2 == 0 else 0
        for y in range(8, self.size - 8):
            self.matrix[y][6] = 1 if y % 2 == 0 else 0
    
    def _fill_simple_data(self, text):
        """Preenche dados (simplificado)"""
        # Converter texto para binário
        bits = ''
        for char in text[:20]:  # Limitar para versão 1
            bits += format(ord(char), '08b')
        
        # Preencher a matriz
        bit_index = 0
        for col in range(self.size - 1, 0, -2):
            if col == 6:
                col -= 1
            for row in range(self.size - 1, -1, -1):
                if col > 0 and self.matrix[row][col] == 0:
                    if bit_index < len(bits):
                        self.matrix[row][col] = 1 if bits[bit_index] == '1' else 0
                        bit_index += 1
                if col - 1 >= 0 and self.matrix[row][col - 1] == 0:
                    if bit_index < len(bits):
                        self.matrix[row][col - 1] = 1 if bits[bit_index] == '1' else 0
                        bit_index += 1
    
    def save_png(self, filename, pixel_size=10):
        """Salva como PNG"""
        os.makedirs(os.path.dirname(filename) if os.path.dirname(filename) else '.', exist_ok=True)
        
        width = self.size * pixel_size
        height = self.size * pixel_size
        
        # Criar dados da imagem
        pixels = []
        for y in range(self.size):
            for py in range(pixel_size):
                row = []
                for x in range(self.size):
                    color = 0 if self.matrix[y][x] == 1 else 255
                    for px in range(pixel_size):
                        row.extend([color, color, color, 255])
                pixels.extend(row)
        
        image_data = bytes(pixels)
        
        # Escrever PNG
        png_header = b'\x89PNG\r\n\x1a\n'
        
        # IHDR
        width_bytes = struct.pack('>I', width)
        height_bytes = struct.pack('>I', height)
        ihdr_data = width_bytes + height_bytes + b'\x08\x06\x00\x00\x00'
        ihdr = b'IHDR' + ihdr_data
        ihdr = struct.pack('>I', len(ihdr_data)) + ihdr
        ihdr += struct.pack('>I', zlib.crc32(ihdr[4:]) & 0xffffffff)
        
        # IDAT
        raw_data = b'\x00' + image_data
        compressed = zlib.compress(raw_data, 9)
        idat = b'IDAT' + compressed
        idat = struct.pack('>I', len(compressed)) + idat
        idat += struct.pack('>I', zlib.crc32(idat[4:]) & 0xffffffff)
        
        # IEND
        iend = b'IEND'
        iend = struct.pack('>I', 0) + iend
        iend += struct.pack('>I', zlib.crc32(iend[4:]) & 0xffffffff)
        
        with open(filename, 'wb') as f:
            f.write(png_header)
            f.write(ihdr)
            f.write(idat)
            f.write(iend)

class QRHelper:
    @staticmethod
    def generate_qr(data, nome=None, tamanho=10):
        """Gera um QR Code e salva"""
        if not nome:
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            unique_id = str(uuid.uuid4())[:8]
            nome = f"qrcode_{timestamp}_{unique_id}"
        
        filename = f"{nome}.png"
        filepath = f"data/public/qr-codes/{filename}"
        
        # Gerar QR Code
        qr = QRCodeGenerator(version=1)
        qr.generate(data)
        qr.save_png(filepath, tamanho)
        
        return {
            'filename': filename,
            'filepath': filepath,
            'url': f"/qr-codes/{filename}",
            'data': data,
            'created_at': datetime.now().isoformat()
        }
    
    @staticmethod
    def list_qr_codes():
        """Lista todos os QR Codes"""
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
        
        return sorted(qr_files, key=lambda x: x['modified'], reverse=True)
    
    @staticmethod
    def delete_qr(filename):
        """Remove um QR Code"""
        filepath = f"data/public/qr-codes/{filename}"
        if os.path.exists(filepath):
            os.remove(filepath)
            return True
        return Falsex