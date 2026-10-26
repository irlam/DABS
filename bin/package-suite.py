#!/usr/bin/env python3
"""Create a dedicated-instance code archive, never a legacy installation copy."""
import hashlib
import json
import re
import zipfile
from pathlib import Path

root = Path(__file__).resolve().parents[1]
files = [
    '.htaccess', 'index.php', 'activity_schedule_tabs.php', 'admin_panel.php',
    'logout.php', 'email_briefing.php', 'suite-gate.php', 'suite-login.php',
    'health.php', 'composer.json', 'database/suite-empty-schema.sql',
    'bin/provision-suite.php',
]
if (root / 'composer.lock').is_file():
    files.append('composer.lock')
files += [str(p.relative_to(root)) for p in root.glob('ajax_*.php')]
files += [str(p.relative_to(root)) for p in (root / 'includes').glob('*.php')
          if p.name in {'auth.php', 'request_policy.php', 'SuiteGateway.php',
                        'db_connect.php', 'database_config.php', 'functions.php',
                        'branding.php', 'email_config.php'}]
for directory, suffixes in [('css', {'.css'}), ('js', {'.js'}), ('images', {'.svg'})]:
    files += [str(p.relative_to(root)) for p in (root / directory).rglob('*')
              if p.is_file() and p.suffix in suffixes
              and not any(x.startswith('.') for x in p.relative_to(root).parts)]
files = sorted(set(files))
manifest = {}
for name in files:
    path = root / name
    if not path.is_file() or path.is_symlink():
        raise RuntimeError(f'Missing or linked package source: {name}')
    content = path.read_bytes()
    if path.suffix == '.php' and re.search(
            rb'\$(?:db_pass|db_password)\s*=\s*[\'\"][^\'\"]+[\'\"]', content):
        raise RuntimeError(f'Literal database credential in package source: {name}')
    if path.suffix == '.php' and name != 'includes/db_connect.php' and re.search(rb'\bnew\s+PDO\s*\(', content):
        raise RuntimeError(f'Database configuration bypass in package source: {name}')
    manifest[name] = hashlib.sha256(content).hexdigest()
dist = root / 'dist'
dist.mkdir(exist_ok=True)
archive = dist / 'dabs-suite.zip'
with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as bundle:
    for name in files:
        bundle.write(root / name, name)
    bundle.writestr('suite-manifest.json', json.dumps(manifest, indent=2) + '\n')
digest = hashlib.sha256(archive.read_bytes()).hexdigest()
(dist / 'dabs-suite.sha256').write_text(f'{digest}  dabs-suite.zip\n')
print(json.dumps({'ok': True, 'files': len(files), 'sha256': digest,
                  'tenant_records_included': False, 'vendor_included': False}))
