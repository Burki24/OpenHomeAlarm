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
$paletteHelper = (string) file_get_contents($root . '/libs/helper/IPSViewColorPaletteHelper.php');

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
        && str_contains($html, '{{OHA_FONT_SCALE}}')
        && str_contains($html, '{{OHA_IPSVIEW_THEME}}'),
    'The shared visualization template must support the IPSView runtime.'
);
assertIPSView(
    str_contains($javascript, 'async function ohaIPSViewRequest(action, value)')
        && str_contains($javascript, "body.set('token', String(ohaIPSViewConfig.token));")
        && str_contains($javascript, "'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'")
        && str_contains($javascript, 'handleMessage(payload);'),
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
        && str_contains($css, 'html.oha-ipsview.oha-theme-custom')
        && str_contains($css, 'html.oha-ipsview.oha-transparent')
        && str_contains($module, "default => 'oha-theme-custom'"),
    'IPSView must provide standalone localization, fixed themes, custom colors and transparency.'
);
assertIPSView(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/IPSViewColorPaletteHelper.php';")
        && str_contains($module, 'use \Burki24\SymconModuleHelper\IPSViewColorPaletteHelper;')
        && str_contains($module, '$this->RegisterIPSViewColorProperties([')
        && str_contains($module, "\$this->IPSViewColorFormItems('250px')")
        && str_contains($module, "\$this->IPSViewColorCSSVariables(\$transparent, ':root')")
        && str_contains($paletteHelper, 'protected function IPSViewResolvedColorPalette(): array')
        && str_contains($paletteHelper, 'private function IPSViewAdjustBackgroundsForContrast(')
        && str_contains($paletteHelper, 'private function IPSViewEnsureForegroundContrast(')
        && !str_contains($javascript, 'function ohaContrastRatio(first, second)')
        && !str_contains($javascript, 'function ohaApplyCustomIPSViewTheme(root, palette)')
        && !str_contains($module, 'private function IPSViewPalette(): array')
        && str_contains($module, '--oha-page-label-muted: var(--ipsview-muted);')
        && str_contains($css, 'html.oha-ipsview.oha-theme-linked')
        && str_contains($css, 'font-family: Roboto, "Segoe UI", Arial, sans-serif;')
        && str_contains($css, 'color: var(--oha-page-label-muted);')
        && str_contains($css, '.oha-mode-button[data-can-arm="false"]')
        && str_contains($css, 'opacity: 0.78;')
        && str_contains($css, 'linear-gradient(135deg, var(--oha-state-soft), transparent 46%)')
        && str_contains($css, 'box-shadow: none;'),
    'The shared IPSView palette helper must provide contrast handling without changing inactive states or gradients.'
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
    str_contains($css, 'grid-template-columns: minmax(0, 1fr) clamp(310px, 18rem, 350px);')
        && str_contains($css, 'html.oha-ipsview .oha-inline-codepad-head > div')
        && str_contains($css, 'overflow-x: hidden;'),
    'The IPSView codepad column must scale with the configured font size without horizontal overflow.'
);
assertIPSView(
    str_contains($form, '"name": "EnableIPSView"')
        && str_contains($form, '"name": "IPSViewTransparent"')
        && str_contains($form, '"name": "IPSViewTheme"')
        && str_contains($form, '"caption": "Custom colors"')
        && str_contains($form, '"name": "IPSViewFontScale"')
        && str_contains($form, '"caption": "Choose the IPSView palette directly. The colors are stored in the module configuration."')
        && str_contains($module, "\$this->IPSViewColorFormItems('250px')")
        && str_contains($paletteHelper, "'type'             => 'SelectColor'")
        && str_contains($paletteHelper, "'Page'          => 'IPSViewPageColorValue'")
        && str_contains($paletteHelper, "'Danger'        => 'IPSViewDangerColorValue'"),
    'The configuration form must inject all manually selectable colors from the shared helper.'
);

assertIPSView(
    str_contains($module, '$fontScalePercent = max(80, min(200, $this->ReadPropertyInteger(self::PROPERTY_IPSVIEW_FONT_SCALE)));')
        && str_contains($module, 'round(16 * $fontScalePercent / 100)')
        && str_contains($module, ". 'px'"),
    'IPSView font scaling must resolve to whole-pixel root sizes for clearer browser rendering.'
);

assertIPSView(
    str_contains($module, "'Page'          => 0xD8C59B")
        && str_contains($module, "'Text'          => 0xFFFFFF")
        && str_contains($paletteHelper, '$this->RegisterPropertyInteger(self::IPSVIEW_COLOR_PROPERTY_NAMES[$key], $value);')
        && str_contains($paletteHelper, "return sprintf('#%06X', \$value);")
        && str_contains($module, 'public function Migrate(string $JSONData): string')
        && str_contains($module, 'LEGACY_IPSVIEW_COLOR_PROPERTIES')
        && str_contains($module, "\$persistence['configuration'][\$integerProperty] = hexdec(\$matches[1]);"),
    'SelectColor fields must use shared integer properties and retain the legacy string migration.'
);


assertIPSView(
    str_contains($readme, 'IPSView')
        && str_contains($readme, 'Browser des Clients')
        && str_contains($readme, 'Benutzerdefinierte Farben'),
    'The module documentation must explain IPSView setup.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView integration checks passed.\n");
