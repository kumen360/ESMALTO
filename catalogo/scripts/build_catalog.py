#!/usr/bin/env python3
"""Genera el catálogo de Esmalto listo para importar en WooCommerce.

Entrada  (catalogo/fuente/ + SALIDA_WEBP/):
  - Esmalto_woocommerce_import.csv   catálogo original (33 colecciones, 335 variaciones)
  - Esmalto_woocommerce_import.xlsx  hoja «Imágenes ALT y SEO»
  - SALIDA_WEBP/ECOCERAMIC/...       fotos de ambiente/pieza y fichas JPG

Salida (catalogo/):
  - salida/esmalto-productos.csv     CSV para Productos > Importar (WooCommerce)
  - salida/imagenes-alt.json         ALT/título/leyenda por archivo (lo aplica el plugin esmalto-core)
  - salida/informe.md                incidencias detectadas
  - img/<coleccion>/...              fotos renombradas (sin marca del fabricante), sin duplicados
  - fichas/esmalto-ficha-*.pdf       fichas técnicas en PDF (una por colección)

Uso:
  python -I catalogo/scripts/build_catalog.py --root . \
      --base-url https://raw.githubusercontent.com/kumen360/ESMALTO/main/catalogo/
"""
import argparse
import collections
import csv
import hashlib
import json
import os
import re
import shutil
import sys
import unicodedata

# --------------------------------------------------------------------------- reglas de negocio

DESTACADOS = ['Ambrossia', 'Imperial Calacatta', 'Colosso', 'Harper']  # «Colecciones destacadas» (home)
BORRADOR = {'Lucca'}  # sin fotos ni ficha: se importa como borrador

ESTILO = {
    'Alaska': 'Liso', 'Manley': 'Liso',
    'Ambrossia': 'Piedra', 'Arenisca': 'Piedra', 'Coralina': 'Piedra', 'Lucca': 'Piedra',
    'Southwell Cross': 'Piedra', 'Southwell Vein': 'Piedra', 'Tivoli Cross': 'Piedra',
    'Tivoli Vein': 'Piedra', 'Vermont': 'Piedra',
    'Besana': 'Cemento', 'Capitol': 'Cemento', 'San Francisco': 'Cemento',
    'Colosso': 'Mármol', 'Crema Marfil': 'Mármol', 'Imperial Calacatta': 'Mármol',
    'Pinoso': 'Mármol', 'Siena': 'Mármol', 'Valentino': 'Mármol',
}
FAMILIA_MADERA = 'Eco Woods'  # toda la familia es efecto madera

# Color comercial -> tono de filtro
TONO = {
    'white': 'Blanco', 'blanco': 'Blanco',
    'bone': 'Beige', 'hueso': 'Beige', 'ivory': 'Beige', 'marfil': 'Beige', 'crema': 'Beige',
    'crema marfil': 'Beige', 'light': 'Beige', 'almond': 'Beige', 'sand': 'Beige', 'arena': 'Beige',
    'beige': 'Beige', 'classic': 'Beige', 'gold': 'Beige', 'straw': 'Beige', 'maple': 'Beige',
    'fresno': 'Beige',
    'pearl': 'Gris', 'perla': 'Gris', 'grey': 'Gris', 'gris': 'Gris', 'silver': 'Gris',
    'smoke': 'Gris', 'marengo': 'Gris', 'greige': 'Gris',
    'graphite': 'Negro', 'black': 'Negro', 'negro': 'Negro',
    'haya': 'Marrón', 'roble': 'Marrón', 'oak': 'Marrón', 'nogal': 'Marrón', 'cerezo': 'Marrón',
    'encina': 'Marrón', 'honey': 'Marrón', 'nut': 'Marrón', 'moka': 'Marrón', 'taupe': 'Marrón',
    'aquamarine': 'Verde', 'imperial calacatta': 'Blanco',
}

ESPACIOS = [('baño', 'Baño'), ('bano', 'Baño'), ('cocina', 'Cocina'), ('salón', 'Salón'),
            ('salon', 'Salón'), ('dormitorio', 'Dormitorio'), ('terraza', 'Terraza'),
            ('fachada', 'Fachada'), ('piscina', 'Piscina')]
ORDEN_ESPACIOS = ['Baño', 'Cocina', 'Salón', 'Dormitorio', 'Terraza', 'Fachada', 'Piscina']
INTERIOR = ['Baño', 'Cocina', 'Salón', 'Dormitorio']

USO = {
    'pavimento y revestimiento': ['Pavimento', 'Revestimiento'],
    'pavimento/revestimiento': ['Pavimento', 'Revestimiento'],
    'revestimiento': ['Revestimiento'],
    'pavimento/exterior': ['Pavimento'],
    'pavimento': ['Pavimento'],
}

VARIATION_ATTRS = ['Color', 'Formato', 'Tipo de pieza', 'Espesor', 'Acabado']

# --------------------------------------------------------------------------- utilidades


def slug(text):
    text = unicodedata.normalize('NFKD', text).encode('ascii', 'ignore').decode().lower()
    return re.sub(r'[^a-z0-9.]+', '-', text).strip('-')


def limpiar(text):
    """Quita la marca del fabricante y corrige plurales del texto generado."""
    if not text:
        return text
    text = re.sub(r'\s+de\s+Ecoceramic\b', '', text, flags=re.I)
    text = re.sub(r'\bEcoceramic\b', 'Esmalto', text, flags=re.I)
    text = re.sub(r'\b1 color\(es\)', '1 color', text)
    text = re.sub(r'(\d+) color\(es\)', r'\1 colores', text)
    text = re.sub(r'\b1 formato\(s\)', '1 formato', text)
    text = re.sub(r'(\d+) formato\(s\)', r'\1 formatos', text)
    text = re.sub(r'un total de 1 referencias', 'una única referencia', text)
    text = re.sub(r'\b(\d{2,3}(?:\.\d)?)X(\d{2,3}(?:\.\d)?)\b', r'\1x\2', text)
    return text


def num(value):
    """'51,84' -> '51.84'; '' -> ''."""
    value = (value or '').strip().replace(',', '.')
    return value


def norm_formato(v):
    return v.strip().replace('X', 'x')


def norm_espesor(v):
    v = v.strip().upper()
    m = re.match(r'^([\d.,]+)\s*(MM|CM)$', v)
    if not m:
        return v.lower()
    n = float(m.group(1).replace(',', '.'))
    if m.group(2) == 'CM':
        n *= 10
    return ('%g mm' % n)


def norm_value(attr, v):
    v = v.strip()
    if attr == 'Formato':
        return norm_formato(v)
    if attr == 'Espesor':
        return norm_espesor(v)
    return v


def split_values(raw):
    return [x.strip() for x in re.split(r'\s*\|\s*', raw or '') if x.strip()]


def csv_values(values):
    """Lista -> 'a, b, c' (formato del importador de WooCommerce). Escapa comas internas."""
    return ', '.join(v.replace(',', '\\,') for v in values)


def uniq(seq):
    seen, out = set(), []
    for x in seq:
        if x not in seen:
            seen.add(x)
            out.append(x)
    return out


def sha1(path):
    h = hashlib.sha1()
    with open(path, 'rb') as f:
        for chunk in iter(lambda: f.read(1 << 16), b''):
            h.update(chunk)
    return h.hexdigest()


def attrs_of(row):
    out = collections.OrderedDict()
    for i in range(1, 30):
        name = row.get('Attribute %d name' % i)
        if name is None:
            break
        if name:
            out[name] = {
                'values': split_values(row['Attribute %d value(s)' % i]),
                'visible': row['Attribute %d visible' % i],
                'global': row['Attribute %d global' % i],
                'default': row.get('Attribute %d default' % i, ''),
            }
    return out

# --------------------------------------------------------------------------- imágenes


class Imagenes:
    def __init__(self, src_root, out_root, base_url):
        self.src_root = src_root
        self.out_root = out_root
        self.base_url = base_url.rstrip('/') + '/'
        self.by_hash = {}       # hash -> nueva ruta relativa (img/...)
        self.by_old = {}        # ruta original -> nueva ruta relativa
        self.names = {}         # basename nuevo -> hash
        self.missing = []

    def _new_rel(self, old_rel, digest):
        parts = old_rel.replace('\\', '/').split('/')
        folders = [slug(p) for p in parts[1:-1]]     # sin «ECOCERAMIC/»
        base, ext = os.path.splitext(parts[-1])
        name = slug(re.sub('ecoceramic', 'esmalto', base, flags=re.I).replace('_', '-'))
        cand, n = name, 2
        while cand + ext.lower() in self.names and self.names[cand + ext.lower()] != digest:
            cand = '%s-%d' % (name, n)
            n += 1
        self.names[cand + ext.lower()] = digest
        return 'img/' + '/'.join(folders + [cand + ext.lower()])

    def map(self, old_rel):
        old_rel = old_rel.strip()
        if old_rel in self.by_old:
            return self.by_old[old_rel]
        src = os.path.join(self.src_root, old_rel)
        if not os.path.exists(src):
            self.missing.append(old_rel)
            return None
        digest = sha1(src)
        if digest not in self.by_hash:
            new_rel = self._new_rel(old_rel, digest)
            dst = os.path.join(self.out_root, new_rel)
            os.makedirs(os.path.dirname(dst), exist_ok=True)
            if not os.path.exists(dst) or os.path.getsize(dst) != os.path.getsize(src):
                shutil.copy2(src, dst)
            self.by_hash[digest] = new_rel
        self.by_old[old_rel] = self.by_hash[digest]
        return self.by_old[old_rel]

    def url(self, new_rel):
        return self.base_url + new_rel


def build_ficha_pdf(jpgs, dst):
    from PIL import Image
    pages = [Image.open(p).convert('RGB') for p in jpgs]
    os.makedirs(os.path.dirname(dst), exist_ok=True)
    pages[0].save(dst, 'PDF', resolution=200.0, quality=88, save_all=True, append_images=pages[1:])

# --------------------------------------------------------------------------- principal


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--root', default='.')
    ap.add_argument('--base-url', default='https://raw.githubusercontent.com/kumen360/ESMALTO/main/catalogo/')
    ap.add_argument('--skip-pdf', action='store_true')
    args = ap.parse_args()

    root = os.path.abspath(args.root)
    fuente = os.path.join(root, 'catalogo', 'fuente')
    salida_dir = os.path.join(root, 'catalogo', 'salida')
    cat_dir = os.path.join(root, 'catalogo')
    src_imgs = os.path.join(root, 'SALIDA_WEBP')
    os.makedirs(salida_dir, exist_ok=True)

    with open(os.path.join(fuente, 'Esmalto_woocommerce_import.csv'), encoding='utf-8-sig', newline='') as f:
        rows = list(csv.DictReader(f))

    parents = [r for r in rows if r['Type'] == 'variable']
    variations = collections.defaultdict(list)
    for r in rows:
        if r['Type'] == 'variation':
            variations[r['Parent']].append(r)

    imgs = Imagenes(src_imgs, cat_dir, args.base_url)
    incid = collections.defaultdict(list)
    out_rows = []
    max_attrs = 0
    ficha_map = {}

    order = sorted(parents, key=lambda p: (p['Name'] not in DESTACADOS,
                                           DESTACADOS.index(p['Name']) if p['Name'] in DESTACADOS else 0,
                                           p['Name']))
    for pos, p in enumerate(order):
        name = p['Name']
        vs = variations[p['SKU']]
        pa = attrs_of(p)
        familia = p['Meta: _familia'] or (p['Categories'].split('>')[1].strip() if '>' in p['Categories'] else '')

        # ---- atributos derivados
        esp_txt = (p['Meta: _espacio'] or '').lower()
        rooms = uniq([label for key, label in ESPACIOS if key in esp_txt])
        if not any(r in INTERIOR for r in rooms) and 'interior' in esp_txt:
            rooms = INTERIOR + rooms
        if 'exterior' in esp_txt and 'Terraza' not in rooms:
            rooms.append('Terraza')
        rooms = [r for r in ORDEN_ESPACIOS if r in rooms]
        ubic = (['Interior'] if 'interior' in esp_txt else []) + (['Exterior'] if 'exterior' in esp_txt else [])
        if not ubic:
            ubic = ['Interior']

        usos = uniq(u for v in vs for u in USO.get((v['Meta: _uso'] or '').strip().lower(), []))
        usos = [u for u in ['Pavimento', 'Revestimiento'] if u in usos]
        antides = 'Sí' if any((v['Meta: _antideslizante'] or '').strip() == 'Sí' for v in vs) else 'No'

        colores = pa.get('Color', {}).get('values', [])
        tonos = []
        for c in colores:
            key = c.strip().lower()
            if key == 'natural':
                tonos.append('Marrón' if familia == FAMILIA_MADERA else 'Beige')
            elif key in TONO:
                tonos.append(TONO[key])
            else:
                incid['Color sin tono asignado'].append('%s: %s' % (name, c))
        tonos = uniq(tonos)
        estilo = 'Madera' if familia == FAMILIA_MADERA else ESTILO.get(name, '')
        if not estilo:
            incid['Colección sin estilo'].append(name)

        def union_meta(key):
            return uniq(v[key].strip() for v in vs if (v.get(key) or '').strip())

        attrs = []  # (name, values, visible, global, default)

        def add(attr, values, visible='1', is_global='1', default=''):
            values = uniq(norm_value(attr, x) for x in values if x)
            if values:
                attrs.append((attr, values, visible, is_global, norm_value(attr, default) if default else ''))

        for a in ['Color', 'Formato', 'Tipo de pieza', 'Espesor', 'Acabado', 'Material']:
            if a in pa:
                add(a, pa[a]['values'], default=pa[a]['default'])
        add('Uso', usos)
        add('Familia', [familia])
        add('Estilo', [estilo])
        add('Tono', tonos, visible='0')
        add('Espacio', rooms)
        add('Ubicación', ubic)
        add('Antideslizante', [antides])
        rect = pa['Rectificado']['values'] if 'Rectificado' in pa else union_meta('Meta: _rectificado')
        add('Rectificado', rect, is_global='0')
        dest = pa['Destonificación']['values'] if 'Destonificación' in pa else union_meta('Meta: _destonificacion')
        add('Destonificación', dest, is_global='0')
        if 'Versión antideslizante' in pa:
            add('Versión antideslizante', pa['Versión antideslizante']['values'], is_global='0')
        max_attrs = max(max_attrs, len(attrs))

        # ---- imágenes del producto
        urls = []
        for old in [u.strip() for u in p['Images'].split(',') if u.strip()]:
            new = imgs.map(old)
            if new:
                urls.append(imgs.url(new))
        urls = uniq(urls)
        if not urls:
            incid['Producto sin fotos'].append(name)

        # ---- ficha técnica
        fichas = [x.strip() for x in (p['Meta: _ficha_tecnica'] or '').split(';') if x.strip()]
        ficha_rel = ''
        if fichas:
            ficha_rel = 'fichas/esmalto-ficha-%s.pdf' % slug(name)
            ficha_map[ficha_rel] = [os.path.join(src_imgs, x) for x in fichas]
        else:
            incid['Producto sin ficha técnica'].append(name)

        prow = {
            'Type': 'variable', 'SKU': p['SKU'], 'Name': name,
            'Published': '0' if name in BORRADOR else '1',
            'Is featured?': '1' if name in DESTACADOS else '0',
            'Visibility in catalog': 'visible',
            'Short description': limpiar(p['Short description']),
            'Description': limpiar(p['Description']),
            'Tax status': 'taxable', 'Tax class': '', 'In stock?': '1', 'Stock': '',
            'Backorders allowed?': '0', 'Sold individually?': '0', 'Weight (kg)': '',
            'Allow customer reviews?': '0', 'Sale price': '', 'Regular price': '',
            'Categories': 'Pavimentos y revestimientos > %s' % familia if familia else 'Pavimentos y revestimientos',
            'Tags': '', 'Images': ', '.join(urls), 'Parent': '', 'Position': str(pos),
            '_attrs': attrs,
            'Meta: _espacio': p['Meta: _espacio'], 'Meta: _familia': familia,
            'Meta: _modelo': p['Meta: _modelo'], 'Meta: _marca': 'Esmalto',
            'Meta: _fabricante': p['Meta: _fabricante'],
            'Meta: _ficha_tecnica': ficha_rel,
            'Meta: _estilo': estilo,
            'Meta: _caracteristicas_tecnicas': limpiar(p['Meta: _caracteristicas_tecnicas']),
            'Meta: _yoast_wpseo_title': limpiar(p['Meta: _yoast_wpseo_title']),
            'Meta: _yoast_wpseo_metadesc': limpiar(p['Meta: _yoast_wpseo_metadesc']),
        }
        out_rows.append(prow)

        # ---- variaciones
        for v in sorted(vs, key=lambda r: int(r['Position'] or 0)):
            va = attrs_of(v)
            vattrs = []
            for a in VARIATION_ATTRS:
                if a in va:
                    vattrs.append((a, [norm_value(a, va[a]['values'][0])], '1', '1', ''))
            vimg = ''
            vim_list = [u.strip() for u in v['Images'].split(',') if u.strip()]
            if vim_list:
                new = imgs.map(vim_list[0])
                vimg = imgs.url(new) if new else ''
            else:
                incid['Variación sin foto de pieza'].append(v['SKU'])
            if not num(v['Meta: _m2_por_caja']):
                incid['Variación sin m²/caja (sin calculadora)'].append(v['SKU'])
            if not num(v['Meta: _cajas_por_pallet']):
                incid['Variación sin datos de palé'].append(v['SKU'])
            out_rows.append({
                'Type': 'variation', 'SKU': v['SKU'], 'Name': limpiar(v['Name']),
                'Published': '1', 'Is featured?': '0', 'Visibility in catalog': 'visible',
                'Short description': '', 'Description': '',
                'Tax status': 'taxable', 'Tax class': 'parent', 'In stock?': '1', 'Stock': '',
                'Backorders allowed?': '0', 'Sold individually?': '0',
                'Weight (kg)': num(v['Weight (kg)']), 'Allow customer reviews?': '0',
                'Sale price': '', 'Regular price': '', 'Categories': '', 'Tags': '',
                'Images': vimg, 'Parent': p['SKU'], 'Position': v['Position'],
                '_attrs': vattrs,
                'Meta: _m2_por_caja': num(v['Meta: _m2_por_caja']),
                'Meta: _kg_por_caja': num(v['Meta: _kg_por_caja']),
                'Meta: _piezas_por_caja': num(v['Meta: _piezas_por_caja']),
                'Meta: _cajas_por_pallet': num(v['Meta: _cajas_por_pallet']),
                'Meta: _m2_por_pallet': num(v['Meta: _m2_por_pallet']),
                'Meta: _kg_por_pallet': num(v['Meta: _kg_por_pallet']),
                'Meta: _disenos': v['Meta: _disenos'],
                'Meta: _formato': norm_formato(v['Meta: _formato']),
                'Meta: _espesor': norm_espesor(v['Meta: _espesor']) if v['Meta: _espesor'] else '',
                'Meta: _material': v['Meta: _material'], 'Meta: _acabado': v['Meta: _acabado'],
                'Meta: _uso': v['Meta: _uso'], 'Meta: _rectificado': v['Meta: _rectificado'],
                'Meta: _antideslizante': v['Meta: _antideslizante'],
                'Meta: _version_antideslizante': v['Meta: _version_antideslizante'],
                'Meta: _destonificacion': v['Meta: _destonificacion'],
            })

    # ---- CSV de salida
    base_cols = ['ID', 'Type', 'SKU', 'Name', 'Published', 'Is featured?', 'Visibility in catalog',
                 'Short description', 'Description', 'Tax status', 'Tax class', 'In stock?', 'Stock',
                 'Backorders allowed?', 'Sold individually?', 'Weight (kg)', 'Allow customer reviews?',
                 'Sale price', 'Regular price', 'Categories', 'Tags', 'Images', 'Parent', 'Position']
    attr_cols = []
    for i in range(1, max_attrs + 1):
        attr_cols += ['Attribute %d name' % i, 'Attribute %d value(s)' % i, 'Attribute %d visible' % i,
                      'Attribute %d global' % i, 'Attribute %d default' % i]
    meta_cols = uniq(k for r in out_rows for k in r if k.startswith('Meta: '))
    header = base_cols + attr_cols + meta_cols

    out_csv = os.path.join(salida_dir, 'esmalto-productos.csv')
    with open(out_csv, 'w', encoding='utf-8', newline='') as f:
        w = csv.writer(f, quoting=csv.QUOTE_MINIMAL)
        w.writerow(header)
        for r in out_rows:
            line = {k: r.get(k, '') for k in base_cols + meta_cols}
            for i, (an, av, vis, glob, dflt) in enumerate(r['_attrs'], start=1):
                line['Attribute %d name' % i] = an
                line['Attribute %d value(s)' % i] = csv_values(av)
                line['Attribute %d visible' % i] = vis
                line['Attribute %d global' % i] = glob
                line['Attribute %d default' % i] = dflt
            w.writerow([line.get(c, '') for c in header])

    # ---- fichas PDF
    if not args.skip_pdf:
        for rel, jpgs in ficha_map.items():
            dst = os.path.join(cat_dir, rel)
            if not os.path.exists(dst):
                build_ficha_pdf(jpgs, dst)

    # ---- ALT / SEO de imágenes
    alt = {}
    try:
        import openpyxl
        wb = openpyxl.load_workbook(os.path.join(fuente, 'Esmalto_woocommerce_import.xlsx'), read_only=True, data_only=True)
        ws = wb['Imágenes ALT y SEO']
        for row in list(ws.iter_rows(values_only=True))[1:]:
            ruta = row[1]
            if not ruta or ruta not in imgs.by_old:
                continue
            new_name = os.path.basename(imgs.by_old[ruta])
            if new_name in alt:
                continue

            def fx(t):
                t = limpiar(t or '')
                t = re.sub(r'\b([A-ZÁÉÍÓÚÑ]{3,}(?:\s[A-ZÁÉÍÓÚÑ]{3,})*)\b', lambda m: m.group(1).title(), t)
                t = re.sub(r'^(Ambiente con .*?) en ambiente\b', r'\1', t)
                t = t.replace('Serie ', 'Colección ').replace('. Colección', '. Colección')
                return t.strip()
            coleccion = row[2] or ''
            alt[new_name] = {
                'title': fx(row[6]), 'alt': fx(row[7]), 'caption': fx(row[8]),
                'description': re.sub(r'Colección (.+?)\.$', r'Colección \1 de Esmalto.', fx(row[9])),
                'coleccion': coleccion,
            }
    except Exception as exc:  # pragma: no cover
        incid['Aviso'].append('No se pudo leer la hoja ALT: %s' % exc)
    with open(os.path.join(salida_dir, 'imagenes-alt.json'), 'w', encoding='utf-8') as f:
        json.dump(alt, f, ensure_ascii=False, indent=0, sort_keys=True)

    # ---- comprobaciones
    bad = []
    with open(out_csv, encoding='utf-8') as f:
        for i, rec in enumerate(csv.DictReader(f)):
            for k, val in rec.items():
                if k != 'Meta: _fabricante' and val and re.search('ecoceramic', val, re.I):
                    bad.append('%s/%s' % (rec['SKU'], k))
    if bad:
        print('ERROR: queda «Ecoceramic» en:', bad[:10], file=sys.stderr)
        sys.exit(1)

    # ---- informe
    n_par = sum(1 for r in out_rows if r['Type'] == 'variable')
    n_var = len(out_rows) - n_par
    lines = ['# Informe de catálogo', '',
             '- Productos (colecciones): **%d** · variaciones: **%d**' % (n_par, n_var),
             '- Imágenes únicas publicadas: **%d** (de %d referencias; duplicados por contenido eliminados)'
             % (len(imgs.by_hash), len(imgs.by_old)),
             '- Fichas técnicas PDF: **%d**' % len(ficha_map),
             '- Precios: **ninguno** en origen → productos en modo «Solicitar presupuesto»', '']
    for k, items in incid.items():
        lines.append('## %s (%d)' % (k, len(items)))
        lines += ['- %s' % x for x in items]
        lines.append('')
    if imgs.missing:
        lines.append('## Imágenes referenciadas que no existen (%d)' % len(imgs.missing))
        lines += ['- %s' % x for x in imgs.missing]
    with open(os.path.join(salida_dir, 'informe.md'), 'w', encoding='utf-8') as f:
        f.write('\n'.join(lines) + '\n')
    print('OK: %d productos, %d variaciones, %d imágenes, %d fichas -> %s'
          % (n_par, n_var, len(imgs.by_hash), len(ficha_map), out_csv))


if __name__ == '__main__':
    main()
