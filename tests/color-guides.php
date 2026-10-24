<?php

declare(strict_types=1);

function assertColorGuide(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$module = (string) file_get_contents($root . '/OpenHomeAlarm/module.php');
$css = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/style.css');
$locale = json_decode(
    (string) file_get_contents($root . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$tileCatalog = json_decode(
    (string) file_get_contents($root . '/libs/helper/translations/VisualizationThemeConfigurationHelper.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$ipsViewCatalog = json_decode(
    (string) file_get_contents($root . '/libs/helper/translations/IPSViewStyleHelper.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$assets = [
    'color-guide-tile'       => ['catalog' => $tileCatalog, 'keys' => [
        'color.background', 'color.text', 'color.heading', 'color.subheading', 'color.accent',
        'color.information', 'color.success', 'color.warning', 'color.danger'
    ]],
    'color-guide-ipsview'    => ['catalog' => $ipsViewCatalog, 'keys' => [
        'color.view_background', 'color.page_background', 'color.label_background',
        'color.control_background', 'color.control_active_background', 'color.control_inactive_background',
        'color.text_primary', 'color.text_active', 'color.text_inactive', 'color.label_text', 'color.icon',
        'color.border', 'color.line', 'color.popup_background', 'color.popup_border', 'color.accent',
        'color.information', 'color.positive', 'color.warning', 'color.critical', 'color.shadow'
    ]]
];

foreach ($assets as $stem => $definition) {
    foreach (['en' => '', 'de' => '-de'] as $language => $suffix) {
        $svgPath = $root . '/OpenHomeAlarm/visualization/' . $stem . $suffix . '.svg';
        $imagePath = $root . '/OpenHomeAlarm/visualization/' . $stem . $suffix . '.image';
        $svg = (string) file_get_contents($svgPath);
        $image = trim((string) file_get_contents($imagePath));

        assertColorGuide($svg !== '', $svgPath . ' must not be empty.');
        assertColorGuide(
            $image === 'data:image/svg+xml;base64,' . base64_encode($svg),
            $imagePath . ' must be the exact data URI of its versioned SVG source.'
        );
        assertColorGuide(
            !preg_match('/<(?:script|foreignObject|image)\b|\bhref\s*=|<!DOCTYPE/i', $svg),
            $svgPath . ' must not contain scripts, embedded external content, links or a doctype.'
        );

        $document = new DOMDocument();
        assertColorGuide(@$document->loadXML($svg), $svgPath . ' must be valid XML.');

        foreach ($definition['keys'] as $key) {
            $label = $definition['catalog']['translations'][$key][$language] ?? null;
            assertColorGuide(
                is_string($label) && $label !== '' && str_contains($svg, escapeGuideLabel($label)),
                $svgPath . ' must use the exact localized helper label for ' . $key . '.'
            );
        }
    }
}

foreach ([
    'Color mapping: OpenHomeAlarm tile',
    'Color mapping: OpenHomeAlarm IPSView',
    'Reserved by the shared tile theme; OpenHomeAlarm currently has no separate information-colored element.',
    'Reserved by the shared IPSView style; OpenHomeAlarm currently has no separate information-colored element.'
] as $localeKey) {
    assertColorGuide(
        isset($locale['translations']['de'][$localeKey])
        && trim((string) $locale['translations']['de'][$localeKey]) !== '',
        'The German module locale must translate the color-guide text: ' . $localeKey
    );
}

assertColorGuide(
    str_contains($module, '$this->OpenHomeAlarmTileColorGuide()')
    && str_contains($module, '$this->OpenHomeAlarmIPSViewColorGuide()')
    && str_contains($module, "'Tile view'")
    && str_contains($module, "'IPSView'")
    && str_contains($module, "'width' => '100%'")
    && str_contains($module, "'color.information', 'Information'")
    && str_contains($module, "'color.information', 'Information color'"),
    'Both designers must embed a responsive module-specific guide and explicitly describe their reserved information field.'
);

foreach ([
    '--oha-bg: var(--symc-background);',
    '--oha-text: var(--symc-text);',
    '--oha-heading: var(--symc-heading);',
    '--oha-subheading: var(--symc-subheading);',
    '--oha-accent: var(--symc-accent);',
    '--oha-success: var(--symc-success);',
    '--oha-warning: var(--symc-warning);',
    '--oha-danger: var(--symc-danger);',
    '--oha-bg: var(--ipsview-role-view-background);',
    '--oha-page: var(--ipsview-role-page-background);',
    '--oha-surface: var(--ipsview-role-control-background);',
    '--oha-surface-strong: var(--ipsview-role-control-active-background);',
    '--oha-control-inactive: var(--ipsview-role-control-inactive-background);',
    '--oha-text: var(--ipsview-role-text-primary);',
    '--oha-text-active: var(--ipsview-role-text-active);',
    '--oha-text-inactive: var(--ipsview-role-text-inactive);',
    '--oha-label: var(--ipsview-role-text-label);',
    '--oha-icon: var(--ipsview-role-icon);',
    '--oha-border: var(--ipsview-role-border);',
    '--oha-line: var(--ipsview-role-line);',
    '--oha-popup-background: var(--ipsview-role-popup-background);',
    '--oha-popup-border: var(--ipsview-role-popup-border);',
    '--oha-accent: var(--ipsview-role-accent);',
    '--oha-success: var(--ipsview-role-positive);',
    '--oha-warning: var(--ipsview-role-warning);',
    '--oha-danger: var(--ipsview-role-critical);',
    '--oha-shadow: var(--ipsview-role-shadow);'
] as $mapping) {
    assertColorGuide(str_contains($css, $mapping), 'The documented color guide requires CSS mapping: ' . $mapping);
}

echo "OpenHomeAlarm color guide checks passed.\n";

function escapeGuideLabel(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}
