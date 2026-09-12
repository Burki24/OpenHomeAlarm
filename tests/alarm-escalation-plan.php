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
    json_decode($steps[0]['Actions'][0]['Action'], true, 512, JSON_THROW_ON_ERROR) === $action,
    'A native list action object must be normalized without changing its payload.'
);
assertEscalationPlan(
    $steps[0]['Actions'][0]['ResetMode'] === 0 && $steps[0]['Actions'][0]['ResetAction'] === '',
    'Migrated single actions must not unexpectedly enable automatic reset.'
);
assertEscalationPlan($steps[1]['Name'] === 'Step 2', 'Unnamed steps need a stable fallback name.');
assertEscalationPlan($steps[1]['Actions'] === [], 'Disabled steps may remain unconfigured.');
assertEscalationPlan(AlarmEscalationPlan::steps('') === [], 'An empty configuration must disable escalation.');

$runtime = AlarmEscalationPlan::start(1000);
$firstKey = AlarmEscalationPlan::actionKey($steps[0], 0, $steps[0]['Actions'][0], 0);
assertEscalationPlan(
    $runtime === [
        'StartedAt'                  => 1000,
        'PushNotificationSent'       => false,
        'PushoverNotificationSent'   => false,
        'ExecutedStepKeys'           => [],
        'ResetActionKeys'            => [],
        'ExecutedActions'            => []
    ],
    'A new escalation cycle must persist its absolute start and an empty execution set.'
);
assertEscalationPlan(
    array_column(AlarmEscalationPlan::dueSteps($steps, $runtime, 1000), 'Key') === [$firstKey],
    'Enabled zero-delay steps must become due immediately.'
);
$runtime['ExecutedStepKeys'][] = $firstKey;
assertEscalationPlan(
    AlarmEscalationPlan::dueSteps($steps, $runtime, 2000) === [],
    'Executed and disabled steps must never become due again.'
);

$scheduledSteps = AlarmEscalationPlan::steps(json_encode([
    ['Enabled' => true, 'Name' => 'First', 'DelaySeconds' => 10, 'Action' => $action],
    ['Enabled' => true, 'Name' => 'Second', 'DelaySeconds' => 30, 'Action' => $action]
], JSON_THROW_ON_ERROR));
$scheduledRuntime = AlarmEscalationPlan::start(1000);
assertEscalationPlan(
    AlarmEscalationPlan::nextDeadline($scheduledSteps, $scheduledRuntime) === 1010,
    'The earliest unexecuted escalation deadline must drive the shared timer.'
);
$scheduledRuntime['ExecutedStepKeys'][] = AlarmEscalationPlan::actionKey(
    $scheduledSteps[0],
    0,
    $scheduledSteps[0]['Actions'][0],
    0
);
assertEscalationPlan(
    AlarmEscalationPlan::nextDeadline($scheduledSteps, $scheduledRuntime) === 1030,
    'The next pending step must take over after an earlier step executed.'
);
$scheduledRuntime['ExecutedStepKeys'][] = AlarmEscalationPlan::actionKey(
    $scheduledSteps[1],
    1,
    $scheduledSteps[1]['Actions'][0],
    0
);
assertEscalationPlan(
    AlarmEscalationPlan::nextDeadline($scheduledSteps, $scheduledRuntime) === 0,
    'A completed escalation plan must no longer schedule a timer.'
);
assertEscalationPlan(
    AlarmEscalationPlan::runtime(['StartedAt' => 1000, 'ExecutedStepKeys' => [$firstKey, $firstKey]])
        === [
            'StartedAt'                  => 1000,
            'PushNotificationSent'       => false,
            'PushoverNotificationSent'   => false,
            'ExecutedStepKeys'           => [$firstKey],
            'ResetActionKeys'            => [],
            'ExecutedActions'            => []
        ],
    'Persisted execution keys must be normalized without duplicates.'
);
assertEscalationPlan(AlarmEscalationPlan::runtime([]) === null, 'Missing runtime state must not create a historical escalation cycle.');

$booleanAction = ['actionID' => '{SWITCH}', 'parameters' => ['VALUE' => true]];
$flatSignalGenerator = AlarmEscalationPlan::steps(json_encode([[
    'Enabled'         => true,
    'Name'            => 'Siren',
    'DelaySeconds'    => 0,
    'Action'          => $booleanAction,
    'ResetMode'       => 1,
    'ResetAction'     => '',
    'SignalGenerator' => true
]], JSON_THROW_ON_ERROR))[0]['Actions'][0];
assertEscalationPlan(
    $flatSignalGenerator['ResetMode'] === 1
        && $flatSignalGenerator['SignalGenerator'] === true,
    'The flat Symcon list format must retain signal-generator and reset settings.'
);
$multipleActions = AlarmEscalationPlan::steps(json_encode([[
    'Enabled'      => true,
    'Name'         => 'Outputs',
    'DelaySeconds' => 0,
    'Actions'      => [
        ['Enabled' => true, 'Name' => 'Light', 'Action' => $booleanAction, 'ResetEnabled' => true],
        ['Enabled' => true, 'Name' => 'Siren', 'Action' => $action, 'ResetEnabled' => false]
    ]
]], JSON_THROW_ON_ERROR));
assertEscalationPlan(
    count(AlarmEscalationPlan::dueSteps($multipleActions, AlarmEscalationPlan::start(1000), 1000)) === 2,
    'Every enabled action of a due escalation step must execute independently.'
);
$inverse = AlarmEscalationPlan::inverseAction($multipleActions[0]['Actions'][0]['Action']);
assertEscalationPlan(
    json_decode($inverse, true, 512, JSON_THROW_ON_ERROR)['parameters']['VALUE'] === false,
    'Automatic reset must invert the Boolean VALUE of a set-value action.'
);
assertEscalationPlan(
    AlarmEscalationPlan::inverseAction($multipleActions[0]['Actions'][1]['Action']) === '',
    'Actions without a Boolean VALUE must never receive a guessed inverse action.'
);
$shutterCloseAction = ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 2]];
$shutterOpenAction = ['actionID' => '{SHUTTER}', 'parameters' => ['VALUE' => 0]];
$customReset = AlarmEscalationPlan::steps(json_encode([[
    'Enabled' => true,
    'Actions' => [[
        'Enabled'     => true,
        'Name'        => 'Close shutter',
        'Action'      => $shutterCloseAction,
        'ResetMode'   => 2,
        'ResetAction' => $shutterOpenAction
    ]]
]], JSON_THROW_ON_ERROR))[0]['Actions'][0];
assertEscalationPlan(
    json_decode(AlarmEscalationPlan::resetAction($customReset), true, 512, JSON_THROW_ON_ERROR) === $shutterOpenAction,
    'A custom reset must execute the explicitly configured action without guessing an inverse value.'
);

foreach ([
    '{}',
    '[{"Enabled":true,"DelaySeconds":-1,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":86401,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":0,"Action":false}]',
    '[{"Enabled":true,"DelaySeconds":0,"Action":{"actionID":"{A}"}}]',
    '[{"Enabled":true,"DelaySeconds":0,"Actions":[{"Enabled":true,"Action":{"actionID":"{A}","parameters":{"TEXT":"Alarm"}},"ResetMode":1}]}]',
    '[{"Enabled":true,"DelaySeconds":0,"Actions":[{"Enabled":true,"Action":{"actionID":"{A}","parameters":{"VALUE":2}},"ResetMode":2}]}]',
    '[{"Enabled":true,"DelaySeconds":0,"Actions":[{"Enabled":true,"Action":{"actionID":"{A}","parameters":{"VALUE":true}},"SignalGenerator":true}]}]'
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
    array_column($list['columns'] ?? [], 'name') === ['Enabled', 'Name', 'DelaySeconds', 'ResetMode', 'SignalGenerator'],
    'The escalation list must expose one understandable row per action without rendering native action payloads.'
);
assertEscalationPlan(
    (($list['columns'] ?? [])[3]['add'] ?? null) === 0
        && (($list['columns'] ?? [])[4]['add'] ?? null) === false,
    'Every visible escalation column needs a default value so Symcon can add a row.'
);
assertEscalationPlan(
    ($list['form'][0] ?? null) === 'return OHA_GetAlarmEscalationEditForm($id, $AlarmEscalationSteps);',
    'The escalation list must use a dynamic editor so unused reset actions are absent.'
);

fwrite(STDOUT, "OpenHomeAlarm alarm escalation plan checks passed.\n");
