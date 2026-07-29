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
        && str_contains($css, 'html.oha-ipsview.oha-theme-custom')
        && str_contains($css, 'html.oha-ipsview.oha-transparent')
        && str_contains($module, "default => 'oha-theme-custom'"),
    'IPSView must provide standalone localization, fixed themes, custom colors and transparency.'
);
assertIPSView(
    str_contains($javascript, 'palette.Custom !== true')
        && str_contains($javascript, 'surfaceStrong: ohaNormalizeCSSColor(palette.SurfaceStrong)')
        && str_contains($javascript, 'mutedText: ohaNormalizeCSSColor(palette.MutedText)')
        && str_contains($css, 'html.oha-ipsview.oha-theme-linked')
        && str_contains($css, 'font-family: Roboto, "Segoe UI", Arial, sans-serif;')
        && str_contains($css, 'box-shadow: none;'),
    'The manual IPSView palette must drive the compact flat dashboard style.'
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
        && str_contains($form, '"name": "IPSViewPageColorValue"')
        && str_contains($form, '"name": "IPSViewSurfaceColorValue"')
        && str_contains($form, '"name": "IPSViewSurfaceStrongColorValue"')
        && str_contains($form, '"name": "IPSViewTextColorValue"')
        && str_contains($form, '"name": "IPSViewMutedTextColorValue"')
        && str_contains($form, '"name": "IPSViewAccentColorValue"')
        && str_contains($form, '"name": "IPSViewSuccessColorValue"')
        && str_contains($form, '"name": "IPSViewWarningColorValue"')
        && str_contains($form, '"name": "IPSViewDangerColorValue"')
        && substr_count($form, '"type": "SelectColor"') >= 9,
    'The configuration form must expose all manually selectable IPSView colors.'
);

assertIPSView(
    str_contains($module, "RegisterPropertyInteger(self::PROPERTY_IPSVIEW_PAGE_COLOR, 0xD8C59B)")
        && str_contains($module, "RegisterPropertyInteger(self::PROPERTY_IPSVIEW_TEXT_COLOR, 0xFFFFFF)")
        && str_contains($module, "sprintf('#%06X', \$value)")
        && str_contains($module, 'public function Migrate(string $JSONData): string')
        && str_contains($module, 'LEGACY_IPSVIEW_COLOR_PROPERTIES')
        && str_contains($module, '$persistence[\'configuration\'][$integerProperty] = hexdec($matches[1]);'),
    'SelectColor fields must use integer properties and migrate the temporary string configuration.'
);

assertIPSView(
    str_contains($readme, 'IPSView')
        && str_contains($readme, 'Browser des Clients')
        && str_contains($readme, 'Benutzerdefinierte Farben'),
    'The module documentation must explain IPSView setup.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView integration checks passed.\n");
