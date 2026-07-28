<?php

declare(strict_types=1);

function assertIPSView(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$module = (string) file_get_contents($root . '/OpenHomeAlarm/module.php');
$form = (string) file_get_contents($root . '/OpenHomeAlarm/form.json');
$html = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/index.html');
$css = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/style.css');
$javascript = (string) file_get_contents($root . '/OpenHomeAlarm/visualization/app.js');
$readme = (string) file_get_contents($root . '/OpenHomeAlarm/README.md');

assertIPSView(
    str_contains($module, "private const PROPERTY_ENABLE_IPSVIEW = 'EnableIPSView';")
        && str_contains($module, "private const IDENT_IPSVIEW_ALARM = 'IPSViewAlarm';"),
    'IPSView configuration and WebContent identifiers must be stable.'
);
assertIPSView(
    str_contains($module, 'VARIABLE_PRESENTATION_WEB_CONTENT')
        && str_contains($module, '$this->MaintainVariable(')
        && str_contains($module, "'HTML_TYPE'    => 0"),
    'IPSView must be exposed as a WebContent string variable.'
);
assertIPSView(
    str_contains($module, 'public function GetIPSViewHTML(): string')
        && str_contains($module, 'return $this->RenderVisualizationHTML(true);'),
    'The module must expose the standalone IPSView page.'
);
assertIPSView(
    str_contains($module, '$this->RegisterHook($this->IPSViewHookAddress());')
        && str_contains($module, 'return \'openhomealarm/\' . $this->InstanceID;'),
    'Every instance must register a unique IPSView action WebHook.'
);
assertIPSView(
    str_contains($module, 'protected function ProcessHookData(): void')
        && str_contains($module, "strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST'")
        && str_contains($module, 'hash_equals($this->IPSViewToken(), $token)'),
    'The IPSView bridge must require POST and its per-instance token.'
);
assertIPSView(
    str_contains($module, "case 'Arm':")
        && str_contains($module, "case 'DisarmWithCode':")
        && str_contains($module, "case 'BypassSensor':")
        && str_contains($module, "case 'ResetAlarmOutput':")
        && str_contains($module, "throw new InvalidArgumentException('Unknown visualization action.');"),
    'The WebHook must share the explicit visualization action whitelist.'
);
assertIPSView(
    !str_contains($module, 'PHP_AUTH_PW')
        && !str_contains($javascript, "'/api/'")
        && !str_contains($javascript, 'Authorization'),
    'The IPSView page must not embed Symcon credentials or call JSON-RPC.'
);
assertIPSView(
    str_contains($html, '{{OHA_RUNTIME_CONFIG}}')
        && str_contains($html, '{{OHA_TRANSLATIONS}}')
        && str_contains($html, '{{OHA_HTML_CLASSES}}')
        && str_contains($html, '{{OHA_FONT_SCALE}}'),
    'The shared visualization template must support the IPSView runtime.'
);
assertIPSView(
    str_contains($javascript, 'async function ohaIPSViewRequest(action, value)')
        && str_contains($javascript, "body.set('token', String(ohaIPSViewConfig.token));")
        && str_contains($javascript, "'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'")
        && str_contains($javascript, "handleMessage(payload);"),
    'IPSView actions must use the authenticated WebHook and feed its state back into the common renderer.'
);
assertIPSView(
    str_contains($javascript, "await ohaIPSViewRequest('GetState', null);")
        && str_contains($javascript, 'function ohaIPSViewPollInterval()')
        && str_contains($javascript, "stateName === 'exit_delay'")
        && str_contains($javascript, "stateName === 'entry_delay'"),
    'IPSView must poll the backend faster while a countdown is active.'
);
assertIPSView(
    str_contains($javascript, 'window.OHA_TRANSLATIONS?.[text]')
        && str_contains($css, 'html.oha-ipsview.oha-theme-dark')
        && str_contains($css, 'html.oha-ipsview.oha-theme-adaptive')
        && str_contains($css, 'html.oha-ipsview.oha-transparent')
        && str_contains($module, "default => 'oha-theme-adaptive'"),
    'IPSView must provide standalone localization, fixed themes, adaptive environment colors and transparency.'
);
assertIPSView(
    str_contains($javascript, 'function ohaDetectHostPalette()')
        && str_contains($javascript, 'window.parent.document')
        && str_contains($javascript, 'elementFromPoint(')
        && str_contains($javascript, 'function ohaApplyAdaptiveTheme()')
        && str_contains($javascript, "root.style.setProperty('--oha-surface-strong'")
        && str_contains($javascript, 'ohaInitializeAdaptiveTheme();')
        && str_contains($css, 'backdrop-filter: var(--oha-adaptive-backdrop);'),
    'Adaptive IPSView colors must inspect the surrounding host when possible and derive translucent dashboard surfaces.'
);
assertIPSView(
    str_contains($javascript, 'function ohaContrastRatio(first, second)')
        && str_contains($javascript, 'function ohaReadableText(background)')
        && str_contains($javascript, 'function ohaEnsureContrast(foreground, background, minimumRatio = 4.5)')
        && str_contains($javascript, "root.dataset.paletteSource = palette.detected ? 'host' : 'safe-fallback';")
        && str_contains($javascript, "root.style.setProperty('--oha-panel-text'")
        && str_contains($javascript, "root.style.setProperty('--oha-page-label-bg'")
        && str_contains($css, 'html.oha-ipsview.oha-theme-adaptive .oha-section-heading')
        && str_contains($css, 'background: var(--oha-page-label-bg);'),
    'Adaptive IPSView colors must calculate readable text for every generated surface and protect labels on textured backgrounds.'
);
assertIPSView(
    str_contains($module, "private const PROPERTY_IPSVIEW_PAGE_COLOR_VARIABLE = 'IPSViewPageColorVariable';")
        && str_contains($module, "private const PROPERTY_IPSVIEW_SURFACE_COLOR_VARIABLE = 'IPSViewSurfaceColorVariable';")
        && str_contains($module, "private const PROPERTY_IPSVIEW_TEXT_COLOR_VARIABLE = 'IPSViewTextColorVariable';")
        && str_contains($module, 'private function IPSViewPalette(): array')
        && str_contains($module, "preg_match('/^#?([0-9a-fA-F]{6})$/'")
        && str_contains($module, "'palette'            => \$ipsViewPalette")
        && str_contains($module, "? 'oha-theme-linked'"),
    'IPSView must be able to consume the same RGB hexadecimal color variables as the surrounding View.'
);
assertIPSView(
    str_contains($javascript, 'function ohaConfiguredIPSViewPalette()')
        && str_contains($javascript, 'function ohaApplyLinkedIPSViewTheme(root, palette)')
        && str_contains($javascript, "root.dataset.paletteSource = 'ipsview-variables';")
        && str_contains($javascript, 'function ohaBackgroundForText(background, text, minimumRatio = 4.5)')
        && str_contains($css, 'html.oha-ipsview.oha-theme-linked')
        && str_contains($css, 'font-family: Roboto, "Segoe UI", Arial, sans-serif;')
        && str_contains($css, 'box-shadow: none;'),
    'A linked IPSView palette must use the View colors directly while preserving readable text and native-looking flat surfaces.'
);
assertIPSView(
    str_contains($css, 'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;')
        && str_contains($css, 'html.oha-ipsview .oha-topbar-actions')
        && str_contains($css, 'grid-template-areas:')
        && str_contains($css, 'html.oha-ipsview .oha-operation-list')
        && str_contains($css, 'overflow-y: auto;'),
    'IPSView must use its own compact typography, remove the empty toolbar row and scroll only operation lists.'
);
assertIPSView(
    str_contains($form, '"name": "EnableIPSView"')
        && str_contains($form, '"name": "IPSViewTransparent"')
        && str_contains($form, '"name": "IPSViewTheme"')
        && str_contains($form, '"caption": "Adaptive to environment"')
        && str_contains($form, '"name": "IPSViewFontScale"')
        && str_contains($form, '"name": "IPSViewPageColorVariable"')
        && str_contains($form, '"name": "IPSViewSurfaceColorVariable"')
        && str_contains($form, '"name": "IPSViewTextColorVariable"'),
    'The configuration form must expose the IPSView settings including linked View color variables.'
);
assertIPSView(
    str_contains($readme, 'IPSView')
        && str_contains($readme, 'Browser des Clients')
        && str_contains($readme, 'RGB-Hexadezimalwerte'),
    'The module documentation must explain IPSView setup.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView integration checks passed.\n");
