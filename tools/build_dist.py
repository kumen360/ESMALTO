#!/usr/bin/env python3
"""Empaqueta el tema y el plugin en dist/*.zip (listos para Plugins/Temas → Añadir → Subir).

Uso: python -I tools/build_dist.py
"""
import os
import shutil
import sys
import zipfile

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DIST = os.path.join(RAIZ, 'dist')
PAQUETES = {
    'esmalto.zip': os.path.join(RAIZ, 'wp-content', 'themes', 'esmalto'),
    'esmalto-core.zip': os.path.join(RAIZ, 'wp-content', 'plugins', 'esmalto-core'),
}
IGNORAR = {'.DS_Store', 'Thumbs.db', 'desktop.ini', '__pycache__'}


def empaquetar(destino, carpeta):
    nombre = os.path.basename(carpeta)
    with zipfile.ZipFile(destino, 'w', zipfile.ZIP_DEFLATED) as z:
        for raiz, dirs, archivos in os.walk(carpeta):
            dirs[:] = [d for d in dirs if d not in IGNORAR]
            for archivo in sorted(archivos):
                if archivo in IGNORAR:
                    continue
                ruta = os.path.join(raiz, archivo)
                rel = os.path.relpath(ruta, carpeta).replace(os.sep, '/')
                z.write(ruta, nombre + '/' + rel)
    return os.path.getsize(destino)


def main():
    # Datos del catálogo que usa el plugin.
    alt = os.path.join(RAIZ, 'catalogo', 'salida', 'imagenes-alt.json')
    if os.path.exists(alt):
        shutil.copy2(alt, os.path.join(PAQUETES['esmalto-core.zip'], 'data', 'imagenes-alt.json'))
    os.makedirs(DIST, exist_ok=True)
    for zipname, carpeta in PAQUETES.items():
        tam = empaquetar(os.path.join(DIST, zipname), carpeta)
        print('%-18s %7.1f KB' % (zipname, tam / 1024))
    return 0


if __name__ == '__main__':
    sys.exit(main())
