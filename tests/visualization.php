<?php

declare(strict_types=1);

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

assertVisualization(is_string($module), 'module.php must be readable.');
assertVisualization(is_string($html) && $html !== '', 'Visualization HTML must be present.');
assertVisualization(is_string($css) && $css !== '', 'Visualization CSS must be present.');
assertVisualization(is_string($javascript) && $javascript !== '', 'Visualization JavaScript must be present.');

assertVisualization(
    str_contains($module, "require_once __DIR__ . '/../libs/helper/VisualizationAssetHelper.php';"),
    'VisualizationAssetHelper must be required.'
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

assertVisualization(str_contains($html, '{{OHA_STYLE}}'), 'HTML must contain the CSS asset placeholder.');
assertVisualization(str_contains($html, '{{OHA_SCRIPT}}'), 'HTML must contain the JavaScript asset placeholder.');
assertVisualization(str_contains($html, '{{OHA_INITIAL_STATE}}'), 'HTML must contain the initial-state placeholder.');
assertVisualization(str_contains($javascript, 'function handleMessage(data)'), 'HTML-SDK handleMessage must be implemented.');
assertVisualization(str_contains($javascript, "ohaRequestAction('Arm'"), 'Visualization must arm through RequestAction.');
assertVisualization(str_contains($javascript, "ohaRequestAction('Disarm'"), 'Visualization must disarm through RequestAction.');
assertVisualization(str_contains($html, "onclick=\"ohaRequestAction('RefreshVisualization', true)\""), 'Visualization refresh action must be available.');

assertVisualization(str_contains($html, 'class="oha-hero"'), 'Visualization must use a state-focused hero area.');
assertVisualization(str_contains($html, 'id="armingSection"'), 'Visualization must provide a dedicated arming section.');
assertVisualization(str_contains($html, 'id="contextGrid"'), 'Visualization must provide contextual status cards.');
assertVisualization(str_contains($javascript, 'function ohaRenderHero(state)'), 'Visualization must render the main state contextually.');
assertVisualization(
    str_contains($html, 'class="oha-mode-button"')
        && str_contains($html, 'onclick="ohaHandleModeButton(this)"')
        && str_contains($javascript, "button.dataset.canArm = modeState.CanArm ? 'true' : 'false';")
        && !str_contains($javascript, 'button.disabled = !modeState.CanArm;')
        && str_contains($javascript, 'button.dataset.active = !isDisarmed'),
    'Arming modes must be rendered as direct full-width controls without relying on native disabled buttons.'
);
assertVisualization(
    strpos($html, 'id="statusHero"') < strpos($html, 'id="armingSection"')
        && strpos($html, 'id="armingSection"') < strpos($html, 'id="statusGrid"'),
    'Arming-mode controls must sit directly below the security-status hero and before secondary status information.'
);
assertVisualization(str_contains($html, 'id="statusGrid"'), 'Visualization must provide a compact always-visible system overview.');
assertVisualization(str_contains($javascript, 'function ohaRenderSummary(state)'), 'Visualization must render the system overview from the control state.');
assertVisualization(str_contains($javascript, 'panel.hidden = !memoryActive || alarmActive;'), 'Alarm memory must only be shown when contextually relevant.');
assertVisualization(str_contains($javascript, 'panel.hidden = !state.Faults?.Active;'), 'System faults must only be shown when active.');
assertVisualization(!str_contains($html, 'oha-notice'), 'Legacy permanently sized notice panels must not remain in the dashboard.');

fwrite(STDOUT, "OpenHomeAlarm visualization foundation checks passed.\n");
