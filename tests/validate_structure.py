#!/usr/bin/env python3
"""Validate the repository structure against the Symcon module contract."""

from __future__ import annotations

import json
import re
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
GUID_PATTERN = re.compile(
    r"^\{[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}\}$"
)
PREFIX_PATTERN = re.compile(r"^[A-Za-z0-9]+$")
PUBLIC_METHOD_PATTERN = re.compile(r"^\s*public function\s+([A-Za-z0-9]+)\s*\(", re.MULTILINE)
PROPERTY_CONSTANT_PATTERN = re.compile(
    r"private const PROPERTY_[A-Z0-9_]+\s*=\s*'([^']+)';"
)

FORM_MARKER_CAPTIONS = {
    "Configure optional IPSView HTML output.",
    "Configure the shared IPSView style used by the standalone HTML page.",
}


def fail(message: str) -> None:
    raise SystemExit(message)


def reject_duplicate_pairs(pairs: list[tuple[str, Any]]) -> dict[str, Any]:
    result: dict[str, Any] = {}
    for key, value in pairs:
        if key in result:
            fail(f"Duplicate JSON key: {key}")
        result[key] = value
    return result


def load_json(path: Path) -> dict[str, Any]:
    try:
        value = json.loads(
            path.read_text(encoding="utf-8"),
            object_pairs_hook=reject_duplicate_pairs,
        )
    except (OSError, UnicodeError, json.JSONDecodeError) as exception:
        fail(f"Invalid JSON file {path.relative_to(ROOT)}: {exception}")
    if not isinstance(value, dict):
        fail(f"JSON root must be an object: {path.relative_to(ROOT)}")
    return value


def require_guid(value: Any, context: str, known_guids: set[str]) -> None:
    if not isinstance(value, str) or GUID_PATTERN.fullmatch(value) is None:
        fail(f"{context} must be an uppercase Symcon GUID.")
    if value in known_guids:
        fail(f"Duplicate Symcon GUID: {value}")
    known_guids.add(value)


def collect_form_properties(elements: Any) -> set[str]:
    if not isinstance(elements, list):
        fail("form.json elements must be a list.")

    property_types = {
        "CheckBox",
        "Color",
        "NumberSpinner",
        "PasswordTextBox",
        "Select",
        "SelectCategory",
        "SelectInstance",
        "SelectObject",
        "SelectScript",
        "SelectVariable",
        "ValidationTextBox",
        "List",
    }
    names: set[str] = set()
    for element in elements:
        if not isinstance(element, dict):
            fail("Every form element must be an object.")
        element_type = element.get("type")
        name = element.get("name")
        if element_type in property_types and isinstance(name, str) and name:
            names.add(name)
        if "items" in element:
            names.update(collect_form_properties(element["items"]))
    return names


def collect_form_captions(value: Any) -> set[str]:
    captions: set[str] = set()
    if isinstance(value, dict):
        for key, item in value.items():
            if key in {"caption", "label"} and isinstance(item, str) and item:
                captions.add(item)
            captions.update(collect_form_captions(item))
    elif isinstance(value, list):
        for item in value:
            captions.update(collect_form_captions(item))
    return captions


def validate_public_phpdocs(source: str, source_path: Path) -> None:
    for match in PUBLIC_METHOD_PATTERN.finditer(source):
        preceding = source[: match.start()].rstrip()
        if not preceding.endswith("*/"):
            fail(
                f"Public method {match.group(1)}() in "
                f"{source_path.relative_to(ROOT)} needs a PHPDoc block."
            )
        comment_start = preceding.rfind("/**")
        comment_end = preceding.rfind("*/")
        if comment_start < 0 or comment_end < comment_start:
            fail(
                f"Public method {match.group(1)}() in "
                f"{source_path.relative_to(ROOT)} needs a PHPDoc block."
            )


def validate_module(module_json_path: Path, known_guids: set[str]) -> None:
    module = load_json(module_json_path)
    module_dir = module_json_path.parent
    module_name = module.get("name")
    if not isinstance(module_name, str) or not module_name:
        fail(f"Missing module name in {module_json_path.relative_to(ROOT)}.")
    expected_class_name = module_name.replace(" ", "")
    if module_dir.name != expected_class_name:
        fail(
            f"Module directory {module_dir.name} must match class "
            f"{expected_class_name}."
        )

    require_guid(module.get("id"), f"{module_name} module id", known_guids)
    if module.get("type") not in {0, 1, 2, 3, 4, 5}:
        fail(f"Invalid module type for {module_name}.")
    prefix = module.get("prefix")
    if not isinstance(prefix, str) or PREFIX_PATTERN.fullmatch(prefix) is None:
        fail(f"Invalid module prefix for {module_name}.")
    url = module.get("url")
    if not isinstance(url, str) or (url and not url.startswith("https://")):
        fail(f"Module documentation URL for {module_name} must use HTTPS.")
    for requirement in ("aliases", "parentRequirements", "childRequirements", "implemented"):
        if not isinstance(module.get(requirement), list):
            fail(f"{requirement} must be a list in {module_json_path.relative_to(ROOT)}.")

    source_path = module_dir / "module.php"
    if not source_path.is_file():
        fail(f"Missing module.php for {module_name}.")
    source = source_path.read_text(encoding="utf-8")
    if "<?" in source.replace("<?php", ""):
        fail(f"Short PHP tags are not allowed in {source_path.relative_to(ROOT)}.")
    if "declare(strict_types=1);" not in source:
        fail(f"Strict types are required in {source_path.relative_to(ROOT)}.")
    if re.search(
        rf"\bclass\s+{re.escape(expected_class_name)}\s+extends\s+IPSModuleStrict\b",
        source,
    ) is None:
        fail(f"{expected_class_name} must extend IPSModuleStrict.")
    for lifecycle_call in ("parent::Create();", "parent::ApplyChanges();"):
        if lifecycle_call not in source:
            fail(f"Missing {lifecycle_call} in {source_path.relative_to(ROOT)}.")
    validate_public_phpdocs(source, source_path)

    form_path = module_dir / "form.json"
    locale_path = module_dir / "locale.json"
    if form_path.is_file():
        form = load_json(form_path)
        form_properties = collect_form_properties(form.get("elements"))
        registered_properties = set(PROPERTY_CONSTANT_PATTERN.findall(source))
        unknown_properties = sorted(form_properties - registered_properties)
        if unknown_properties:
            fail(
                f"form.json references unregistered properties: "
                f"{', '.join(unknown_properties)}"
            )
        if not locale_path.is_file():
            fail(f"form.json requires a locale.json for {module_name}.")

        locale = load_json(locale_path)
        translations = locale.get("translations")
        german = translations.get("de") if isinstance(translations, dict) else None
        if not isinstance(german, dict):
            fail(f"locale.json for {module_name} needs a German translation object.")
        missing_captions = sorted(
            collect_form_captions(form) - set(german) - FORM_MARKER_CAPTIONS
        )
        if missing_captions:
            fail(
                f"Missing German form translations: {', '.join(missing_captions)}"
            )

    visualization_dir = module_dir / "visualization"
    if visualization_dir.is_dir():
        required_assets = {"index.html", "style.css", "app.js"}
        missing_assets = sorted(
            asset for asset in required_assets if not (visualization_dir / asset).is_file()
        )
        if missing_assets:
            fail(f"Missing visualization assets: {', '.join(missing_assets)}")
        html = (visualization_dir / "index.html").read_text(encoding="utf-8")
        if '<script src="/icons.js"></script>' not in html:
            fail("HTML-SDK visualization must use Symcon's local icons.js.")
        if re.search(r'<(?:script|link)\b[^>]+(?:src|href)="https?://', html):
            fail("HTML-SDK visualization must not load external web assets.")

    if not (module_dir / "README.md").is_file():
        fail(f"Missing module README for {module_name}.")


def main() -> None:
    for required_file in ("library.json", "LICENSE", "README.md", "SECURITY.md"):
        if not (ROOT / required_file).is_file():
            fail(f"Missing repository file: {required_file}")

    library = load_json(ROOT / "library.json")
    known_guids: set[str] = set()
    require_guid(library.get("id"), "Library id", known_guids)
    for field in ("name", "author", "url", "version"):
        if not isinstance(library.get(field), str) or not library[field]:
            fail(f"library.json field {field} must be a non-empty string.")
    if not str(library["url"]).startswith("https://"):
        fail("library.json URL must use HTTPS.")
    compatibility = library.get("compatibility")
    if not isinstance(compatibility, dict) or not isinstance(
        compatibility.get("version"), str
    ):
        fail("library.json needs a compatibility version.")
    if not isinstance(library.get("build"), int) or not isinstance(library.get("date"), int):
        fail("library.json build and date must be integers.")

    module_json_paths = sorted(ROOT.glob("*/module.json"))
    if not module_json_paths:
        fail("The repository does not contain a Symcon module.")
    for module_json_path in module_json_paths:
        validate_module(module_json_path, known_guids)

    print(
        f"Symcon repository structure verified "
        f"({len(module_json_paths)} module(s), {len(known_guids)} GUIDs)."
    )


if __name__ == "__main__":
    main()
