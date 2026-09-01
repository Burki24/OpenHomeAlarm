<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmEscalationPlan;

require_once dirname(__DIR__) . '/libs/AlarmEscalationPlan.php';

function assertEscalationPlan(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$action = ['actionID' => '{NOTIFY}', 'parameters' => ['TEXT' => 'Alarm']];
$steps = AlarmEscalationPlan::steps(json_encode([
    ['Enabled' => true, 'Name' => ' Immediate ', 'DelaySeconds' => 0, 'Action' => $action],
    ['Enabled' => false, 'Name' => '', 'DelaySeconds' => 300, 'Action' => '']
], JSON_THROW_ON_ERROR));

assertEscalationPlan($steps[0]['Name'] === 'Immediate', 'Step names must be trimmed.');
assertEscalationPlan($steps[0]['DelaySeconds'] === 0, 'Immediate escalation must retain a zero delay.');
assertEscalationPlan(
    json_decode($steps[0]['Action'], true, 512, JSON_THROW_ON_ERROR) === $action,
    'A native list action object must be normalized without changing its payload.'
);
assertEscalationPlan($steps[1]['Name'] === 'Step 2', 'Unnamed steps need a stable fallback name.');
assertEscalationPlan($steps[1]['Action'] === '', 'Disabled steps may remain unconfigured.');
assertEscalationPlan(AlarmEscalationPlan::steps('') === [], 'An empty configuration must disable escalation.');

foreach ([
    '{}',
    '[{"Enabled":true,"DelaySeconds":-1,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":86401,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":0,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":0,"Action":{"actionID":"{A}"}}]'
] as $invalidConfiguration) {
    try {
        AlarmEscalationPlan::steps($invalidConfiguration);
        throw new RuntimeException('Invalid escalation configuration must be rejected.');
    } catch (UnexpectedValueException) {
    }
}

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$list = null;
foreach ($form['elements'] ?? [] as $element) {
    foreach ($element['items'] ?? [] as $item) {
        if (($item['name'] ?? null) === 'AlarmEscalationSteps') {
            $list = $item;
        }
    }
}
assertEscalationPlan(is_array($list) && ($list['type'] ?? null) === 'List', 'Escalation steps must be configurable as a list.');
assertEscalationPlan(
    array_column($list['columns'] ?? [], 'name') === ['Enabled', 'Name', 'DelaySeconds', 'Action'],
    'The escalation list must expose the complete step contract.'
);
$actionColumn = ($list['columns'] ?? [])[3] ?? [];
assertEscalationPlan(
    ($actionColumn['edit']['type'] ?? null) === 'SelectAction' && ($actionColumn['edit']['targetID'] ?? null) === -2,
    'Each escalation step must use the native Symcon action selector.'
);

fwrite(STDOUT, "OpenHomeAlarm alarm escalation plan checks passed.\n");
