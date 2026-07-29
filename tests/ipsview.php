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
$styleHelper = (string) file_get_contents($root . '/libs/helper/IPSViewStyleHelper.php');
$htmlPageHelper = (string) file_get_contents($root . '/libs/helper/IPSViewHTMLPageHelper.php');

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
        && str_contains($module, "return 'openhomealarm/' . \$this->InstanceID;"),
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
    str_contains($html, '{{HTML_LANGUAGE}}')
        && str_contains($html, '{{HTML_CLASSES}}')
        && str_contains($html, '{{VIEWPORT_CONTENT}}')
        && str_contains($html, '{{ROOT_FONT_SIZE}}')
        && str_contains($html, '{{VISUALIZATION_THEME}}')
        && str_contains($html, '{{MODULE_STYLE}}')
        && str_contains($html, '{{IPSVIEW_STYLE}}')
        && str_contains($html, '{{BOOTSTRAP_JSON}}')
        && str_contains($html, '{{MODULE_SCRIPT}}'),
    'The visualization template must implement the shared HTML page contract.'
);
assertIPSView(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/IPSViewHTMLPageHelper.php';")
        && str_contains($module, 'use \\Burki24\\SymconModuleHelper\\IPSViewHTMLPageHelper;')
        && str_contains($module, '$this->RenderVisualizationHTMLPage($ipsView, [')
        && str_contains($module, '\'state\'             => $this->ControlStatePayload()')
        && str_contains($module, '\'runtime\'           => $runtime')
        && str_contains($module, '\'translations\'      => $ipsView ? $this->IPSViewTranslationsFromLocale() : []'),
    'OpenHomeAlarm must delegate both page modes and their bootstrap data to the shared HTML page helper.'
);
assertIPSView(
    str_contains($htmlPageHelper, '\'contractVersion\' => self::IPSVIEW_HTML_CONTRACT_VERSION')
        && str_contains($htmlPageHelper, '\'mode\'            => $ipsView ? \'ipsview\' : \'symcon\'')
        && str_contains($htmlPageHelper, 'protected function EncodeVisualizationHTMLJSON(')
        && str_contains($htmlPageHelper, 'protected function IPSViewTranslationsFromLocale('),
    'The shared HTML helper must own the bootstrap contract, safe JSON encoding and locale translation map.'
);
assertIPSView(
    str_contains($javascript, 'const ohaVisualization = window.SYMC_VISUALIZATION')
        && str_contains($javascript, "ohaVisualization.mode === 'ipsview'")
        && str_contains($javascript, 'let ohaState = ohaVisualization.state ?? null;')
        && str_contains($javascript, 'const translated = ohaTranslations[text];')
        && !str_contains($javascript, 'window.OHA_INITIAL_STATE')
        && !str_contains($javascript, 'window.OHA_IPSVIEW')
        && !str_contains($javascript, 'window.OHA_TRANSLATIONS'),
    'The alarm application must consume the shared bootstrap without legacy page globals.'
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
    str_contains($module, "require_once __DIR__ . '/../libs/helper/IPSViewStyleHelper.php';")
        && str_contains($module, 'use \Burki24\SymconModuleHelper\IPSViewStyleHelper;')
        && str_contains($module, '$this->RegisterIPSViewStyleProperties();')
        && str_contains($module, "\$this->IPSViewStyleFormItems('220px')")
        && str_contains($module, "\$this->IPSViewStyleCSSVariables(':root')")
        && str_contains($module, '$this->RegisterIPSViewStyleMediaMessages();')
        && str_contains($module, '$this->IsIPSViewStyleMediaUpdate($SenderID, $Message)')
        && !str_contains($module, 'IPSViewColorPaletteHelper'),
    'OpenHomeAlarm must consume the universal IPSView style helper including media updates.'
);
assertIPSView(
    str_contains($styleHelper, "'IPSView standard style'")
        && str_contains($styleHelper, "'type'    => 'SelectMedia'")
        && str_contains($styleHelper, "'Positive'                  => 'IPSViewStylePositiveColor'")
        && str_contains($styleHelper, "'Critical'                  => 'IPSViewStyleCriticalColor'")
        && str_contains($styleHelper, "'--ipsview-gradient-positive'")
        && str_contains($styleHelper, "'--ipsview-disabled-opacity'")
        && str_contains($styleHelper, 'protected function IPSViewStyleCSSVariables('),
    'The shared helper must provide standard-style import and universal semantic tokens.'
);
assertIPSView(
    str_contains($css, '--oha-bg: var(--ipsview-background);')
        && str_contains($css, '--oha-surface: var(--ipsview-control-background);')
        && str_contains($css, '--oha-text: var(--ipsview-text);')
        && str_contains($css, '--oha-success: var(--ipsview-positive);')
        && str_contains($css, '--oha-danger: var(--ipsview-critical);')
        && str_contains($css, '--oha-disabled-opacity: var(--ipsview-disabled-opacity);')
        && str_contains($css, '--oha-gradient-success: var(--ipsview-gradient-positive);')
        && str_contains($css, '--oha-gradient-danger: var(--ipsview-gradient-critical);'),
    'The module stylesheet must only assign universal IPSView roles to OpenHomeAlarm components.'
);
assertIPSView(
    str_contains($css, '--oha-state-gradient: var(--oha-gradient-accent);')
        && str_contains($css, '--oha-state-gradient: var(--oha-gradient-success);')
        && str_contains($css, '--oha-state-gradient: var(--oha-gradient-warning);')
        && str_contains($css, '--oha-state-gradient: var(--oha-gradient-danger);')
        && str_contains($css, 'background-image: var(--oha-state-gradient);')
        && str_contains($css, 'background-image: var(--oha-gradient-accent);')
        && !str_contains($css, '--oha-state-gradient: color-mix(')
        && !str_contains($css, '--oha-active-mode-gradient: color-mix('),
    'Alarm states and active modes must use the gradients generated by the shared helper.'
);
assertIPSView(
    str_contains($css, 'opacity: var(--oha-disabled-opacity);')
        && str_contains($css, 'background-color: var(--oha-control-inactive);')
        && str_contains($css, 'color: var(--ipsview-text-inactive);'),
    'Unavailable IPSView controls must use the shared inactive style and opacity.'
);
assertIPSView(
    !str_contains($css, 'html.oha-ipsview.oha-theme-dark')
        && !str_contains($css, 'html.oha-ipsview.oha-theme-custom')
        && !str_contains($css, 'html.oha-ipsview.oha-theme-linked')
        && !str_contains($css, 'html.oha-ipsview.oha-transparent')
        && str_contains($module, "\$ipsView ? ['oha-ipsview', 'oha-style-shared'] : []"),
    'Legacy module-owned IPSView theme classes must be removed.'
);
assertIPSView(
    str_contains($css, 'font-family: var(--ipsview-font-family);')
        && str_contains($module, 'private function IPSViewRootFontSize(): string')
        && str_contains($module, '$style = $this->IPSViewResolvedStyle();')
        && str_contains($module, "ReadPropertyInteger('IPSViewStyleFontScale')"),
    'Typography must originate from the shared style and still resolve to a whole-pixel root size.'
);
assertIPSView(
    str_contains($css, 'grid-template-columns: minmax(0, 1fr) clamp(310px, 18rem, 350px);')
        && str_contains($css, 'html.oha-ipsview .oha-inline-codepad-head > div')
        && str_contains($css, 'overflow-x: hidden;'),
    'The IPSView codepad column must scale with the configured font size without horizontal overflow.'
);
assertIPSView(
    str_contains($form, '"name": "EnableIPSView"')
        && str_contains($form, '"caption": "Configure the shared IPSView style used by the standalone HTML page."')
        && !str_contains($form, '"name": "IPSViewTheme"')
        && !str_contains($form, '"name": "IPSViewTransparent"')
        && !str_contains($form, '"name": "IPSViewFontScale"'),
    'The static form must delegate all common style controls to the shared helper.'
);
assertIPSView(
    str_contains($module, 'LEGACY_IPSVIEW_STRING_COLOR_PROPERTIES')
        && str_contains($module, 'LEGACY_IPSVIEW_STYLE_PROPERTIES')
        && str_contains($module, "'IPSViewTransparent' => 'IPSViewStyleTransparentBackground'")
        && str_contains($module, "'IPSViewFontScale'   => 'IPSViewStyleFontScale'")
        && str_contains($module, "'IPSViewStyleSource'] = match")
        && str_contains($module, 'self::IPSVIEW_STYLE_SOURCE_LIGHT')
        && str_contains($module, 'self::IPSVIEW_STYLE_SOURCE_DARK'),
    'Existing IPSView palette, theme, transparency and font settings must migrate to the shared style.'
);
assertIPSView(
    str_contains($readme, 'IPSViewStyleHelper')
        && str_contains($readme, 'IPSView-Standardstil')
        && str_contains($readme, 'Medienobjekt')
        && str_contains($readme, 'Browser des Clients'),
    'The module documentation must explain the universal IPSView style and media source.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView integration checks passed.\n");
