#!/usr/bin/env python3

from __future__ import annotations

import hashlib
import json
import subprocess
import sys
import tempfile
import zipfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SCRIPT = ROOT / '.github' / 'scripts' / 'build_release_artifact.py'


def build(output: Path) -> str:
    subprocess.run([sys.executable, str(SCRIPT), '--output', str(output)], check=True)
    return hashlib.sha256(output.read_bytes()).hexdigest()


def main() -> None:
    version = json.loads((ROOT / 'library.json').read_text(encoding='utf-8'))['version']
    prefix = f'OpenHomeAlarm-{version}/'

    with tempfile.TemporaryDirectory() as directory:
        first = Path(directory) / 'first.zip'
        second = Path(directory) / 'second.zip'
        if build(first) != build(second):
            raise SystemExit('Release ZIP is not reproducible.')

        with zipfile.ZipFile(first) as archive:
            names = archive.namelist()

        required = {
            prefix + 'library.json',
            prefix + 'LICENSE',
            prefix + 'OpenHomeAlarm/module.php',
            prefix + 'OpenHomeAlarm/module.json',
            prefix + 'libs/AlarmCodeProtection.php',
            prefix + 'docs/INSTALLATION.md',
        }
        if not required.issubset(names):
            raise SystemExit('Release ZIP is missing required runtime or documentation files.')

        forbidden_parts = ('/.git', '/tests/', '/.github/', '/.style/')
        if any(part in name for name in names for part in forbidden_parts):
            raise SystemExit('Release ZIP contains development-only files.')

    print('Release artifact regression tests passed')


if __name__ == '__main__':
    main()
