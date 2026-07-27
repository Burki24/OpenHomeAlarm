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
assertVisualization(str_contains($javascript, "ohaRequestAction('RefreshVisualization'"), 'Visualization refresh action must be available.');

fwrite(STDOUT, "OpenHomeAlarm visualization foundation checks passed.\n");
