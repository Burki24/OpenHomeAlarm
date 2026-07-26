#!/usr/bin/env python3

from __future__ import annotations

import json
import subprocess
import sys
import tempfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SCRIPT = ROOT / '.github' / 'scripts' / 'update_library_metadata.py'
SOURCE_SHA = '1234567890abcdef1234567890abcdef12345678'


def run_update(file_path: Path, *, base_version: str, increment: int) -> dict[str, object]:
    subprocess.run(
        [
            sys.executable,
            str(SCRIPT),
            '--file',
            str(file_path),
            '--sha',
            SOURCE_SHA,
            '--date',
            '1785081600',
            '--increment',
            str(increment),
            '--base-version',
            base_version,
        ],
        check=True,
    )
    return json.loads(file_path.read_text(encoding='utf-8'))


def main() -> None:
    with tempfile.TemporaryDirectory() as temporary_directory:
        library_file = Path(temporary_directory) / 'library.json'
        library_file.write_text(
            json.dumps(
                {
                    'id': '{00000000-0000-0000-0000-000000000000}',
                    'version': '1.0',
                    'build': 0,
                    'date': 0,
                }
            ),
            encoding='utf-8',
        )

        updated = run_update(library_file, base_version='1.0', increment=1)
        if updated['version'] != '1.1':
            raise SystemExit('Expected base version 1.0 to advance to 1.1.')

        if updated['build'] != int(SOURCE_SHA[:7], 16):
            raise SystemExit('Build number was not derived from the first seven SHA characters.')

        if updated['date'] != 1785081600:
            raise SystemExit('Unix timestamp was not written correctly.')

        updated = run_update(library_file, base_version='1.1', increment=2)
        if updated['version'] != '1.3':
            raise SystemExit('Expected two source commits to advance 1.1 to 1.3.')

        # A subsequent metadata-only run must never move an already newer
        # working-tree version backwards.
        updated = run_update(library_file, base_version='1.0', increment=1)
        if updated['version'] != '1.3':
            raise SystemExit('Expected an existing newer version to be preserved.')

    print('Metadata updater regression tests passed')


if __name__ == '__main__':
    main()
