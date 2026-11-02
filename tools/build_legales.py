#!/usr/bin/env python3
"""Convierte los Word de textos legales en los datos que usan las páginas de la web.

Uso: python -I tools/build_legales.py <Aviso_legal_Privacidad_Cookies.docx> <Condiciones_Generales.docx>

Genera (los dos van en el plugin):
  data/textos-legales.json              Títulos, párrafos y listas de las cuatro páginas, con el texto literal del Word.
  assets/docs/esmalto-condiciones-generales.pdf   Copia que acompaña al correo de confirmación del pedido.

Convenciones del Word: párrafo entero en negrita = título; «–  » al principio = elemento de lista. La línea
«Versión … · Texto para publicar… · Pendiente…» es una nota interna: en la web solo queda la versión y la fecha.
Después hay que aplicarlos en esmalto.com con Esmalto → Herramientas → 7.
"""
import html
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
import zipfile
import xml.etree.ElementTree as ET

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PLUGIN = os.path.join(RAIZ, 'wp-content', 'plugins', 'esmalto-core')
EDGE = r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'


def enlaces(texto):
    texto = texto.replace('hola@esmalto.com', '<a href="mailto:hola@esmalto.com">hola@esmalto.com</a>')
    texto = texto.replace('+34 614 05 10 42', '<a href="tel:+34614051042">+34 614 05 10 42</a>')
    return texto.replace('www.aepd.es', '<a href="https://www.aepd.es" target="_blank" rel="noreferrer noopener">www.aepd.es</a>')


def parrafos(ruta):
    """Devuelve [(html, todo_en_negrita)] de cada párrafo con texto."""
    raiz = ET.fromstring(zipfile.ZipFile(ruta).read('word/document.xml'))
    salida = []
    for p in raiz.find(W + 'body').findall(W + 'p'):
        trozos = []
        for r in p.iter(W + 'r'):
            texto = ''.join((t.text or '') for t in r.findall(W + 't'))
            if not texto:
                continue
            rpr = r.find(W + 'rPr')
            negrita = rpr is not None and rpr.find(W + 'b') is not None and rpr.find(W + 'b').get(W + 'val') not in ('0', 'false')
            trozos.append((texto, negrita))
        if not ''.join(t for t, _ in trozos).strip():
            continue
        todo = all(n or not t.strip() for t, n in trozos)
        # Une tramos seguidos con el mismo formato antes de marcar la negrita.
        unidos = []
        for t, n in trozos:
            if unidos and unidos[-1][1] == n:
                unidos[-1][0] += t
            else:
                unidos.append([t, n])
        cuerpo = ''.join('<strong>%s</strong>' % html.escape(t, quote=False) if n and t.strip() else html.escape(t, quote=False) for t, n in unidos)
        salida.append((cuerpo.strip(), todo))
    return salida


def apartados(lista):
    """Agrupa los párrafos en apartados {titulo, bloques}."""
    grupos = []
    for cuerpo, es_titulo in lista:
        if es_titulo:
            grupos.append({'titulo': re.sub(r'</?strong>', '', cuerpo), 'bloques': []})
            continue
        if not grupos:
            raise SystemExit('Párrafo antes del primer título: ' + cuerpo[:80])
        bloques = grupos[-1]['bloques']
        texto_lista = re.match(r'^(?:<strong>)?–\s+', cuerpo)
        if texto_lista:
            item = re.sub(r'^(<strong>)?–\s+', r'\1', cuerpo)
            # «–  » suele ir fuera de la negrita: «–  <strong>Tarjeta.</strong> …»
            if bloques and 'lista' in bloques[-1]:
                bloques[-1]['lista'].append(enlaces(item))
            else:
                bloques.append({'lista': [enlaces(item)]})
        elif cuerpo.startswith('[Tabla que completa'):
            bloques.append({'tabla_cookies': True})
        else:
            bloques.append({'p': enlaces(cuerpo)})
    return grupos


def version(nota):
    m = re.search(r'(Versión [^·]+· [^·]+?)\s*·', nota)
    if not m:
        raise SystemExit('No encuentro la versión en: ' + nota)
    return m.group(1).strip()


def leer(ruta_aviso, ruta_condiciones):
    aviso = parrafos(ruta_aviso)
    cond = parrafos(ruta_condiciones)
    # Las dos primeras líneas de cada Word son el título y la nota interna.
    datos = {'condiciones': {'version': version(cond[1][0]), 'apartados': apartados(cond[2:])}}
    v = version(aviso[1][0])
    actual = None
    trozos = {'AVISO LEGAL': 'aviso_legal', 'POLÍTICA DE PRIVACIDAD': 'privacidad', 'POLÍTICA DE COOKIES': 'cookies'}
    partes = {}
    for cuerpo, es_titulo in aviso[2:]:
        limpio = re.sub(r'</?strong>', '', cuerpo)
        if es_titulo and limpio in trozos:
            actual = trozos[limpio]
            partes[actual] = []
            continue
        partes[actual].append((cuerpo, es_titulo))
    for clave, lista in partes.items():
        datos[clave] = {'version': v, 'apartados': apartados(lista)}
    return datos


def pdf_condiciones(doc):
    """Copia en PDF de las condiciones generales, con Edge sin ventana."""
    filas = []
    for a in doc['apartados']:
        filas.append('<h2>%s</h2>' % a['titulo'])
        for b in a['bloques']:
            if 'lista' in b:
                filas.append('<ul>%s</ul>' % ''.join('<li>%s</li>' % i for i in b['lista']))
            else:
                filas.append('<p>%s</p>' % b['p'])
    pagina = (
        '<!doctype html><html lang="es"><meta charset="utf-8"><title>Condiciones generales de contratación · Esmalto Cerámica</title>'
        '<style>@page{margin:20mm 18mm}body{font:10.5pt/1.5 Arial,sans-serif;color:#1e1e1e}h1{font-size:18pt;margin:0 0 4pt}'
        'h2{font-size:11.5pt;margin:14pt 0 4pt;break-after:avoid}p{margin:0 0 6pt}ul{margin:0 0 6pt;padding-left:16pt}li{margin:0 0 3pt}'
        'a{color:inherit;text-decoration:none}.v{color:#555;margin-bottom:12pt}</style>'
        '<h1>Condiciones generales de contratación</h1><p class="v">Esmalto Cerámica · Kumen Tres Sesenta, S.L. · NIF B88655014 · %s</p>%s</html>'
    ) % (doc['version'], ''.join(filas))
    destino = os.path.join(PLUGIN, 'assets', 'docs', 'esmalto-condiciones-generales.pdf')
    os.makedirs(os.path.dirname(destino), exist_ok=True)
    carpeta = tempfile.mkdtemp()
    try:
        ruta_html = os.path.join(carpeta, 'condiciones.html')
        with open(ruta_html, 'w', encoding='utf-8') as f:
            f.write(pagina)
        subprocess.run(
            [EDGE, '--headless=new', '--disable-gpu', '--no-pdf-header-footer', '--user-data-dir=' + os.path.join(carpeta, 'perfil'), '--print-to-pdf=' + destino, 'file:///' + ruta_html.replace(os.sep, '/')],
            check=True, capture_output=True, timeout=120,
        )
    finally:
        shutil.rmtree(carpeta, ignore_errors=True)
    return destino


def main():
    if len(sys.argv) != 3:
        print(__doc__)
        return 1
    datos = leer(sys.argv[1], sys.argv[2])
    destino = os.path.join(PLUGIN, 'data', 'textos-legales.json')
    with open(destino, 'w', encoding='utf-8', newline='\n') as f:
        json.dump(datos, f, ensure_ascii=False, indent=1)
        f.write('\n')
    print('%s: %s' % (os.path.relpath(destino, RAIZ), ', '.join('%s (%d apartados)' % (k, len(v['apartados'])) for k, v in datos.items())))
    pdf = pdf_condiciones(datos['condiciones'])
    print('%s: %.0f KB' % (os.path.relpath(pdf, RAIZ), os.path.getsize(pdf) / 1024))
    return 0


if __name__ == '__main__':
    sys.exit(main())
