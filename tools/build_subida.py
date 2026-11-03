#!/usr/bin/env python3
"""Página local para subir dist/*.zip a wp-admin desde el navegador integrado de Claude (repositorio privado).

Uso: python -I tools/build_subida.py <carpeta temporal fuera del repositorio>

1. Servir la carpeta: python -I -m http.server 9431 --bind 127.0.0.1 --directory <carpeta>
2. Abrir http://127.0.0.1:9431/subir.html y pulsar «Tema» o «Plugin»: el ZIP se guarda en window.name, que
   se conserva al pasar a wp-admin en la misma pestaña.
3. En la página de subida de wp-admin, ejecutar el fragmento que muestra subir.html: comprueba el SHA-256 y
   pone el ZIP en el campo de subida. Después, «Instalar ahora» y «Reemplazar el actual con el subido».
"""
import base64
import hashlib
import json
import os
import sys

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
WEB = 'https://www.esmalto.com'
PAQUETES = {
    'esmalto.zip': ('Tema', '/wp-admin/theme-install.php?upload', '#themezip'),
    'esmalto-core.zip': ('Plugin', '/wp-admin/plugin-install.php?tab=upload', '#pluginzip'),
}

FRAGMENTO = """const [marca, nombre, huella, b64] = window.name.split(':');
window.name = '';
const bin = atob(b64 || ''); const u = new Uint8Array(bin.length);
for (let i = 0; i < bin.length; i++) u[i] = bin.charCodeAt(i);
const h = Array.from(new Uint8Array(await crypto.subtle.digest('SHA-256', u))).map((x) => x.toString(16).padStart(2, '0')).join('');
const campo = document.querySelector(nombre === 'esmalto.zip' ? '#themezip' : '#pluginzip');
const ok = marca === 'ESMZIP' && h === huella && !!campo;
if (ok) { const dt = new DataTransfer(); dt.items.add(new File([u], nombre, { type: 'application/zip' })); campo.files = dt.files; campo.dispatchEvent(new Event('change', { bubbles: true })); }
({ ok, nombre, bytes: u.length })"""


def main():
    if len(sys.argv) != 2:
        print(__doc__)
        return 1
    salida = os.path.abspath(sys.argv[1])
    if salida == RAIZ or salida.startswith(RAIZ + os.sep):
        print('La carpeta tiene que estar fuera del repositorio (la página lleva los ZIP dentro).')
        return 1
    os.makedirs(salida, exist_ok=True)
    botones, bloques = [], []
    for nombre, (etiqueta, ruta, _campo) in PAQUETES.items():
        datos = open(os.path.join(RAIZ, 'dist', nombre), 'rb').read()
        huella = hashlib.sha256(datos).hexdigest()
        bloques.append('<script type="text/plain" id="%s">%s</script>' % (nombre, base64.b64encode(datos).decode('ascii')))
        botones.append('<button data-zip="%s" data-huella="%s" data-destino="%s">%s · %s (%d KB)</button>' % (
            nombre, huella, WEB + ruta, etiqueta, nombre, len(datos) // 1024))
        print('%-18s %s' % (nombre, huella))
    html = """<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Subir ZIP a esmalto.com</title>
<style>body{font:16px system-ui;margin:24px;max-width:900px}button{font-size:17px;padding:10px 16px;margin:0 8px 8px 0}pre{background:#f3f3f3;padding:12px;white-space:pre-wrap}</style>
</head><body>
<h1>Subir ZIP a esmalto.com</h1>
<p>%s</p>
<p>En la página de subida de wp-admin, ejecuta:</p>
<pre>%s</pre>
%s
<script>
document.querySelectorAll('button[data-zip]').forEach((b) => b.addEventListener('click', () => {
  const nombre = b.dataset.zip;
  window.name = ['ESMZIP', nombre, b.dataset.huella, document.getElementById(nombre).textContent.trim()].join(':');
  location.href = b.dataset.destino;
}));
</script>
</body></html>
""" % (''.join(botones), FRAGMENTO.replace('&', '&amp;').replace('<', '&lt;'), '\n'.join(bloques))
    destino = os.path.join(salida, 'subir.html')
    open(destino, 'w', encoding='utf-8').write(html)
    print(destino)
    return 0


if __name__ == '__main__':
    sys.exit(main())
