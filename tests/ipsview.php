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
$locale = (string) file_get_contents($root . '/OpenHomeAlarm/locale.json');
$styleHelper = (string) file_get_contents($root . '/libs/helper/IPSViewStyleHelper.php');
$styleConfigurationHelper = (string) file_get_contents($root . '/libs/helper/IPSViewStyleConfigurationHelper.php');
$controlThemeHelper = (string) file_get_contents($root . '/libs/helper/IPSViewControlThemeHelper.php');
$htmlPageHelper = (string) file_get_contents($root . '/libs/helper/IPSViewHTMLPageHelper.php');

assertIPSView(
    str_contains($module, "private const IDENT_IPSVIEW_ALARM = 'IPSViewAlarm';")
        && str_contains($module, '$this->RegisterIPSViewHTMLPageProperties();')
        && str_contains($module, '$this->MaintainIPSViewHTMLVariable(')
        && str_contains($module, '$this->UpdateIPSViewHTMLVariable(')
        && !str_contains($module, "private const PROPERTY_ENABLE_IPSVIEW = 'EnableIPSView';"),
    'IPSView configuration and the stable WebContent ident must use the central HTML page helper.'
);
assertIPSView(
    str_contains($htmlPageHelper, 'VARIABLE_PRESENTATION_WEB_CONTENT')
        && str_contains($htmlPageHelper, "'HTML_TYPE'    => 0")
        && str_contains($htmlPageHelper, 'protected function MaintainIPSViewHTMLVariable(')
        && str_contains($htmlPageHelper, 'protected function UpdateIPSViewHTMLVariable(')
        && str_contains($htmlPageHelper, 'protected function IsIPSViewHTMLPageEnabled(): bool'),
    'The central helper must expose optional IPSView output as a WebContent string variable.'
);
assertIPSView(
    str_contains($htmlPageHelper, 'When disabled, existing IPSView variables are retained')
        && str_contains($htmlPageHelper, 'private function IPSViewHTMLDeleteVariablesPopup(')
        && str_contains($htmlPageHelper, '$this->UnregisterVariable($ident);')
        && !str_contains($module, 'MaintainVariable('),
    'Disabling IPSView must retain the variable until the user confirms deletion through the helper.'
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
        && str_contains($module, "'language'          => \$this->NormalizeHelperTranslationLanguage(")
        && str_contains($module, '$this->ResolveHelperTranslationLanguage()')
        && str_contains($module, '\'state\'             => $this->ControlStatePayload()')
        && str_contains($module, '\'runtime\'           => $runtime')
        && str_contains($module, '\'translations\'      => $ipsView ? $this->IPSViewTranslationsFromLocale() : []'),
    'OpenHomeAlarm must delegate both page modes, the active Symcon language and their bootstrap data to the shared HTML page helper.'
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
    str_contains($module, "require_once __DIR__ . '/../libs/helper/IPSViewStyleConfigurationHelper.php';")
        && str_contains($module, 'use \Burki24\SymconModuleHelper\IPSViewStyleConfigurationHelper;')
        && str_contains($module, '$this->RegisterIPSViewStyleProperties();')
        && str_contains($module, "\$this->InsertIPSViewStyleFormItems(\$form['elements'], colorWidth: '220px')")
        && str_contains($module, "\$this->IPSViewStyleCSSVariables(':root')")
        && str_contains($module, '$this->RegisterIPSViewStyleMediaMessages();')
        && str_contains($module, '$this->IsIPSViewStyleMediaUpdate($SenderID, $Message)')
        && !str_contains($module, 'IPSViewColorPaletteHelper'),
    'OpenHomeAlarm must consume the universal IPSView style configuration helper including media updates.'
);
assertIPSView(
    str_contains($styleConfigurationHelper, 'use IPSViewStyleHelper {')
        && str_contains($styleConfigurationHelper, "private const IPSVIEW_NATIVE_FORM_PANEL = 'IPSViewStyleNativeColorsPanel';")
        && str_contains($styleConfigurationHelper, "=> 'ExpansionPanel'")
        && str_contains($styleConfigurationHelper, "=> 'List'")
        && str_contains($styleConfigurationHelper, "=> 'CheckBox'")
        && str_contains($styleConfigurationHelper, "=> 'SelectColor'")
        && str_contains($controlThemeHelper, "public const FAMILY_BASE = 'base';")
        && str_contains($controlThemeHelper, "public const FAMILY_CALENDAR = 'calendar';"),
    'The shared editing form must expose the same grouped native IPSView color overrides as OpenCalendar.'
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
    str_contains($css, '--oha-bg: var(--ipsview-role-view-background);')
        && str_contains($css, '--oha-page: var(--ipsview-role-page-background);')
        && str_contains($css, '--oha-surface: var(--ipsview-role-control-background);')
        && str_contains($css, '--oha-text: var(--ipsview-role-text-primary);')
        && str_contains($css, '--oha-text-active: var(--ipsview-role-text-active);')
        && str_contains($css, '--oha-text-inactive: var(--ipsview-role-text-inactive);')
        && str_contains($css, '--oha-label: var(--ipsview-role-text-label);')
        && str_contains($css, '--oha-page-label-text: var(--ipsview-role-text-label);')
        && str_contains($css, '--oha-muted: var(--ipsview-role-text-secondary);')
        && str_contains($css, '--oha-faint: var(--ipsview-role-text-faint);')
        && str_contains($css, '--oha-icon: var(--ipsview-role-icon);')
        && str_contains($css, '--oha-success: var(--ipsview-role-positive);')
        && str_contains($css, '--oha-danger: var(--ipsview-role-critical);')
        && str_contains($css, '--oha-disabled-opacity: var(--ipsview-role-disabled-opacity);')
        && str_contains($css, '--oha-gradient-success: var(--ipsview-role-gradient-positive);')
        && str_contains($css, '--oha-gradient-danger: var(--ipsview-role-gradient-critical);'),
    'The module stylesheet must assign the canonical IPSView role contract to OpenHomeAlarm components.'
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
        && str_contains($css, 'color: var(--oha-text-inactive);'),
    'Unavailable IPSView controls must use the shared inactive style and opacity.'
);
assertIPSView(
    str_contains($css, '.oha-mode-button[data-active="true"] .oha-mode-button-copy strong {')
        && str_contains($css, '.oha-mode-button[data-can-arm="false"]:not([data-active="true"]) .oha-mode-button-copy strong,')
        && str_contains($css, 'color: var(--oha-text-active);')
        && str_contains($css, 'color: var(--oha-text-inactive);')
        && str_contains($css, 'color: var(--oha-icon);')
        && !str_contains($css, 'color: var(--ipsview-text-inactive);')
        && !str_contains($css, 'border-color: var(--ipsview-line);'),
    'Active controls, inactive controls and neutral icons must use the local aliases of the canonical role contract.'
);
assertIPSView(
    str_contains($css, 'html.oha-ipsview .oha-status-card,')
        && str_contains($css, 'background-color: var(--oha-page);')
        && str_contains($css, 'html.oha-ipsview .oha-mode-button {')
        && str_contains($css, 'background-color: var(--oha-surface);')
        && str_contains($css, 'html.oha-ipsview .oha-hero {')
        && !str_contains($css, 'html.oha-ipsview .oha-hero {
    background-color: var(--oha-surface-strong);')
        && !str_contains($css, 'html.oha-ipsview .oha-hero,
html.oha-ipsview .oha-mode-button,'),
    'IPSView page panels must use the page background while interactive controls use control backgrounds.'
);
assertIPSView(
    str_contains($css, '.oha-eyebrow,')
        && str_contains($css, '.oha-section-kicker {')
        && str_contains($css, '.oha-status-card-label {')
        && str_contains($css, 'color: var(--oha-label);')
        && str_contains($css, '.oha-status-card-icon {')
        && str_contains($css, '.oha-code-key-secondary {')
        && str_contains($css, '.oha-codepad-close {'),
    'Eyebrows and field labels must use label text while neutral symbols use the icon role.'
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
    str_contains($css, 'font-family: var(--ipsview-role-font-family);')
        && str_contains($module, '$this->IPSViewStyleRootFontSize()')
        && str_contains($styleHelper, 'protected function IPSViewStyleRootFontSize(')
        && !str_contains($module, 'private function IPSViewRootFontSize(): string'),
    'Typography and whole-pixel root sizing must originate from the shared style helper.'
);
assertIPSView(
    str_contains($css, 'grid-template-columns: minmax(0, 1fr) clamp(310px, 18rem, 350px);')
        && str_contains($css, 'html.oha-ipsview .oha-inline-codepad-head > div')
        && str_contains($css, 'overflow-x: hidden;'),
    'The IPSView codepad column must scale with the configured font size without horizontal overflow.'
);
assertIPSView(
    str_contains($form, '"caption": "Configure optional IPSView HTML output."')
        && str_contains($form, '"caption": "Configure the shared IPSView style used by the standalone HTML page."')
        && !str_contains($form, '"name": "EnableIPSView"')
        && !str_contains($form, '"name": "IPSViewTheme"')
        && !str_contains($form, '"name": "IPSViewTransparent"')
        && !str_contains($form, '"name": "IPSViewFontScale"')
        && str_contains($module, '$this->InsertIPSViewHTMLPageFormItems('),
    'The static form must delegate optional output and all common style controls to the central helpers.'
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
    !str_contains($locale, '"Provide IPSView HTML page"')
        && !str_contains($locale, '"Style source"')
        && !str_contains($locale, '"Base font size"')
        && str_contains($locale, '"IPSView alarm system"')
        && str_contains($locale, '"Creates a WebContent variable with a fully operable alarm dashboard for an IPSView HTML-Box."'),
    'Helper-owned captions must come from the central catalogs while module-specific texts remain local.'
);
assertIPSView(
    str_contains($readme, 'IPSViewHTMLPageHelper')
        && str_contains($readme, 'IPSViewStyleConfigurationHelper')
        && str_contains($readme, 'IPSView-Standardstil')
        && str_contains($readme, 'Medienobjekt')
        && str_contains($readme, 'Browser des Clients')
        && str_contains($readme, 'ausdrücklicher Bestätigung'),
    'The module documentation must explain central output management, deletion confirmation and the shared style.'
);

fwrite(STDOUT, "OpenHomeAlarm IPSView integration checks passed.\n");
