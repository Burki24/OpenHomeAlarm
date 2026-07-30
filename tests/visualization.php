<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

function assertVisualization(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$module = file_get_contents($root . '/OpenHomeAlarm/module.php');
$html = file_get_contents($root . '/OpenHomeAlarm/visualization/index.html');
$css = file_get_contents($root . '/OpenHomeAlarm/visualization/style.css');
$javascript = file_get_contents($root . '/OpenHomeAlarm/visualization/app.js');
$locale = json_decode(
    (string) file_get_contents($root . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertVisualization(is_string($module), 'module.php must be readable.');
assertVisualization(is_string($html) && $html !== '', 'Visualization HTML must be present.');
assertVisualization(is_string($css) && $css !== '', 'Visualization CSS must be present.');
assertVisualization(is_string($javascript) && $javascript !== '', 'Visualization JavaScript must be present.');

assertVisualization(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/VisualizationAssetHelper.php';"),
    'VisualizationAssetHelper must be required.'
);
assertVisualization(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/IPSViewHTMLPageHelper.php';")
        && str_contains($module, 'use \\Burki24\\SymconModuleHelper\\IPSViewHTMLPageHelper;')
        && str_contains($module, '$this->RenderVisualizationHTMLPage($ipsView, ['),
    'Native and IPSView documents must use the shared HTML page helper.'
);
assertVisualization(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/VisualizationThemeHelper.php';")
        && str_contains($module, 'use \\Burki24\\SymconModuleHelper\\VisualizationThemeHelper;')
        && str_contains($module, '$this->VisualizationThemeCSS()'),
    'The visualization must use the shared Symcon theme helper.'
);
assertVisualization(
    str_contains($module, 'use \\Burki24\\SymconModuleHelper\\VisualizationAssetHelper;'),
    'VisualizationAssetHelper trait must be used.'
);
assertVisualization(
    str_contains($module, '$this->SetVisualizationType(1);'),
    'HTML-SDK visualization must be enabled.'
);
assertVisualization(
    str_contains($module, 'public function GetVisualizationTile(): string'),
    'GetVisualizationTile must be implemented.'
);
assertVisualization(
    str_contains($module, 'public function RequestAction(string $Ident, mixed $Value): void'),
    'Visualization RequestAction gateway must be implemented.'
);
assertVisualization(
    str_contains($module, '$this->UpdateVisualizationValue($this->GetControlState());'),
    'Live control-state updates must send the JSON control state as a scalar string.'
);
assertVisualization(
    !str_contains($module, '$this->UpdateVisualizationValue($state);'),
    'UpdateVisualizationValue must not receive a decoded PHP array.'
);

foreach ([
    '{{HTML_LANGUAGE}}',
    '{{HTML_CLASSES}}',
    '{{VIEWPORT_CONTENT}}',
    '{{ROOT_FONT_SIZE}}',
    '{{VISUALIZATION_THEME}}',
    '{{MODULE_STYLE}}',
    '{{IPSVIEW_STYLE}}',
    '{{BOOTSTRAP_JSON}}',
    '{{MODULE_SCRIPT}}'
] as $placeholder) {
    assertVisualization(
        str_contains($html, $placeholder),
        'HTML must contain the shared page placeholder ' . $placeholder . '.'
    );
}
assertVisualization(
    str_contains($html, 'window.SYMC_VISUALIZATION = {{BOOTSTRAP_JSON}};')
        && !str_contains($html, 'window.OHA_INITIAL_STATE')
        && !str_contains($html, 'window.OHA_IPSVIEW')
        && !str_contains($html, 'window.OHA_TRANSLATIONS'),
    'The template must use the shared visualization bootstrap only.'
);
assertVisualization(
    str_contains($javascript, 'const ohaVisualization = window.SYMC_VISUALIZATION')
        && str_contains($javascript, 'let ohaState = ohaVisualization.state ?? null;'),
    'The application must initialize from the shared visualization bootstrap.'
);
assertVisualization(str_contains($javascript, 'function handleMessage(data)'), 'HTML-SDK handleMessage must be implemented.');
assertVisualization(
    !str_contains($javascript, '.innerHTML ='),
    'Visualization state must be rendered without assigning HTML strings.'
);
assertVisualization(str_contains($javascript, "ohaRequestAction('Arm'"), 'Visualization must arm through RequestAction.');
assertVisualization(str_contains($javascript, "ohaRequestAction('Disarm'"), 'Visualization must disarm through RequestAction.');
foreach ([
    'BypassSensor',
    'RemoveSensorBypass',
    'ClearSensorBypasses',
    'ClearAlarmMemory',
    'ResetAlarmOutput'
] as $operation) {
    assertVisualization(
        str_contains($module, "case '" . $operation . "':"),
        'Visualization RequestAction must support ' . $operation . '.'
    );
    assertVisualization(
        str_contains($html, 'data-operation="' . $operation . '"')
            || str_contains($javascript, "'" . $operation . "'"),
        'Visualization controls must expose ' . $operation . '.'
    );
}
assertVisualization(
    str_contains($javascript, "document.addEventListener('click', ohaHandleInteractiveClick, true);")
        && str_contains($javascript, "if (control.id === 'refreshButton')")
        && str_contains($javascript, "ohaRequestAction('RefreshVisualization', true);"),
    'Visualization actions must use the shared capture-phase click dispatcher.'
);

assertVisualization(str_contains($html, 'class="oha-hero"'), 'Visualization must use a state-focused hero area.');
assertVisualization(str_contains($html, 'id="armingSection"'), 'Visualization must provide a dedicated arming section.');
assertVisualization(str_contains($html, 'id="contextGrid"'), 'Visualization must provide contextual status cards.');
assertVisualization(str_contains($javascript, 'function ohaRenderHero(state)'), 'Visualization must render the main state contextually.');
assertVisualization(
    str_contains($html, 'class="oha-mode-button"')
        && !str_contains($html, 'onclick=')
        && str_contains($javascript, "if (control.matches('[data-action=\"arm\"]'))")
        && str_contains($javascript, 'ohaHandleModeButton(control);')
        && str_contains($javascript, "button.dataset.canArm = modeState.CanArm ? 'true' : 'false';")
        && !str_contains($javascript, 'button.disabled = !modeState.CanArm;')
        && str_contains($javascript, 'button.dataset.active = !isDisarmed'),
    'Arming modes must be rendered as direct full-width controls through the shared click dispatcher and without native disabled buttons.'
);
assertVisualization(
    strpos($html, 'id="statusHero"') < strpos($html, 'id="armingSection"')
        && strpos($html, 'id="armingSection"') < strpos($html, 'id="controlBar"')
        && strpos($html, 'id="controlBar"') < strpos($html, 'id="statusGrid"'),
    'Arming-mode controls and the disarm control must sit directly below the security-status hero and before secondary status information.'
);
assertVisualization(str_contains($html, 'id="statusGrid"'), 'Visualization must provide a compact always-visible system overview.');
assertVisualization(str_contains($html, 'id="sensorManagementPanel"'), 'Visualization must provide contextual sensor management.');
assertVisualization(str_contains($html, 'id="eventHistoryPanel"'), 'Visualization must provide recent security events.');
assertVisualization(
    str_contains($javascript, 'function ohaRenderSensorManagement(state)')
        && str_contains($javascript, 'function ohaRenderEventHistory(state)')
        && str_contains($module, "'RecentEvents'"),
    'Sensor operations and recent security events must be rendered from the backend control state.'
);
assertVisualization(
    str_contains($css, '--oha-accent: var(--symc-accent);')
        && str_contains($css, '--oha-bg: var(--symc-background);')
        && str_contains($css, 'html.oha-ipsview {')
        && str_contains($css, '--oha-accent: var(--ipsview-role-accent);')
        && str_contains($css, '--oha-bg: var(--ipsview-role-view-background);'),
    'The native tile must consume shared Symcon tokens while IPSView consumes the universal style tokens.'
);
assertVisualization(str_contains($javascript, 'function ohaRenderSummary(state)'), 'Visualization must render the system overview from the control state.');
assertVisualization(
    str_contains($javascript, 'function ohaUpdateOperationsLayout()')
        && str_contains($javascript, 'grid.dataset.visiblePanels = String(visiblePanels);')
        && str_contains($css, '.oha-operations-grid[data-visible-panels="1"]'),
    'Visualization must expand a single operation panel to the available width.'
);
assertVisualization(
    str_contains($css, '@media (min-width: 900px) and (max-width: 1250px)')
        && str_contains($css, '.oha-shell[data-codepad-visible="true"] .oha-hero-meta'),
    'Visualization must reflow the hero while the inline code pad narrows the content area.'
);
assertVisualization(
    str_contains($css, 'container-type: inline-size;')
        && str_contains($css, 'font-size: clamp(1.65rem, 5cqi, 2.75rem);')
        && str_contains($css, 'font-size: clamp(2.1rem, 7cqi, 4.1rem);'),
    'Visualization must scale the hero typography against the available hero width.'
);
assertVisualization(
    str_contains($css, '.oha-topbar-actions {')
        && str_contains($css, 'position: sticky;')
        && str_contains($css, 'box-shadow: 0 0 0 14px var(--oha-bg);'),
    'Visualization must shield scrolled content below the sticky Symcon tile title.'
);
assertVisualization(str_contains($javascript, 'panel.hidden = !memoryActive || alarmActive;'), 'Alarm memory must only be shown when contextually relevant.');
assertVisualization(str_contains($javascript, 'panel.hidden = !state.Faults?.Active;'), 'System faults must only be shown when active.');
assertVisualization(!str_contains($html, 'oha-notice'), 'Legacy permanently sized notice panels must not remain in the dashboard.');

$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Acknowledge',
    'Restore all',
    'Temporarily bypassed',
    'Restore',
    'Bypass once',
    'Sensor management',
    'System log',
    'Recent activity',
    'Silence alarm',
    'Alarm triggered',
    'Reset alarm output',
    'Alarm output reset',
    'System disarmed',
    'Sensor bypassed',
    'Alarm memory acknowledged',
    'System fault detected',
    'System fault cleared'
] as $translationKey) {
    assertVisualization(
        isset($translations[$translationKey]),
        'Missing German visualization translation for ' . $translationKey . '.'
    );
}

fwrite(STDOUT, "OpenHomeAlarm visualization foundation checks passed.\n");
