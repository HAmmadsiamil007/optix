import csv, io, re

with open('Divi-Engine-WooCommerce-Sample-Products.csv', 'r', encoding='utf-8') as f:
    content = f.read()

reader = csv.DictReader(io.StringIO(content))
rows = list(reader)
fieldnames = reader.fieldnames

def make_sku(name):
    s = name.lower().strip()
    s = re.sub(r'[^a-z0-9]+', '-', s)
    s = s.strip('-')
    return 'de-' + s[:40]

sku_map = {}
for r in rows:
    if r['ID'] and r['Type'] in ('variable', 'simple'):
        sku = make_sku(r['Name'])
        base = sku
        i = 1
        while sku in sku_map.values():
            sku = base + '-' + str(i)
            i += 1
        sku_map[r['ID']] = sku
        r['SKU'] = sku

for r in rows:
    if r['Type'] == 'variation' and r['Parent']:
        parent_ref = r['Parent'].strip()
        if parent_ref.startswith('id:'):
            parent_id = parent_ref[3:]
            if parent_id in sku_map:
                r['Parent'] = sku_map[parent_id]

new_fieldnames = [f for f in fieldnames if f != 'ID']

output = io.StringIO()
writer = csv.DictWriter(output, fieldnames=new_fieldnames, extrasaction='ignore')
writer.writeheader()
for r in rows:
    writer.writerow(r)

with open('Divi-Engine-WooCommerce-Sample-Products-cleaned.csv', 'w', encoding='utf-8') as f:
    f.write(output.getvalue())

print('=== SKU / Parent mappings ===')
for r in rows:
    if r['Type'] in ('variable', 'simple'):
        print(f"SKU {r['SKU']:<45} <- ID {r['ID']:>3}  {r['Name']}")
    elif r['Type'] == 'variation':
        print(f"    parent={r['Parent']:<45}  {r['Name'][:50]}")
