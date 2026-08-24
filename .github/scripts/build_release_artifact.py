#!/usr/bin/env python3

from __future__ import annotations

import argparse
import hashlib
import json
import zipfile
from datetime import datetime, timezone
from pathlib import Path, PurePosixPath


ROOT = Path(__file__).resolve().parents[2]
TOP_LEVEL_FILES = (
    'CHANGELOG.md',
    'LICENSE',
    'LICENSE_HISTORY.md',
    'README.md',
    'SECURITY.md',
    'library.json',
)
SOURCE_DIRECTORIES = ('docs', 'libs', 'OpenHomeAlarm')


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description='Build a deterministic OpenHomeAlarm release ZIP.')
    parser.add_argument('--output', type=Path, help='Explicit output ZIP path.')
    return parser.parse_args()


def release_files() -> list[Path]:
    files = [ROOT / name for name in TOP_LEVEL_FILES]
    for directory in SOURCE_DIRECTORIES:
        files.extend(path for path in (ROOT / directory).rglob('*') if path.is_file())
    return sorted(files, key=lambda path: path.relative_to(ROOT).as_posix())


def zip_timestamp(timestamp: int) -> tuple[int, int, int, int, int, int]:
    value = datetime.fromtimestamp(timestamp, timezone.utc)
    return max(1980, value.year), value.month, value.day, value.hour, value.minute, value.second


def build(output: Path) -> str:
    library = json.loads((ROOT / 'library.json').read_text(encoding='utf-8'))
    version = library['version']
    timestamp = zip_timestamp(int(library['date']))
    prefix = PurePosixPath(f'OpenHomeAlarm-{version}')

    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for source in release_files():
            relative = PurePosixPath(source.relative_to(ROOT).as_posix())
            info = zipfile.ZipInfo(str(prefix / relative), timestamp)
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            archive.writestr(info, source.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)

    digest = hashlib.sha256(output.read_bytes()).hexdigest()
    print(f'{output}  sha256:{digest}')
    return digest


def main() -> None:
    args = parse_arguments()
    library = json.loads((ROOT / 'library.json').read_text(encoding='utf-8'))
    output = args.output or ROOT / 'dist' / f'OpenHomeAlarm-{library["version"]}.zip'
    build(output.resolve())


if __name__ == '__main__':
    main()
