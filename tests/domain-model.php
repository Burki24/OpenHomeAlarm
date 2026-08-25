<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmActionExecutor;
use Burki24\OpenHomeAlarm\AlarmCodeProtection;
use Burki24\OpenHomeAlarm\AlarmConfigurationNormalizer;
use Burki24\OpenHomeAlarm\AlarmControlStateAdapter;
use Burki24\OpenHomeAlarm\AlarmDisarmUserRegistry;
use Burki24\OpenHomeAlarm\AlarmEventHistory;
use Burki24\OpenHomeAlarm\AlarmFaultMonitor;
use Burki24\OpenHomeAlarm\AlarmSensorMonitor;
use Burki24\OpenHomeAlarm\AlarmStateMachine;
use Burki24\OpenHomeAlarm\AlarmTimerSchedule;
use Burki24\OpenHomeAlarm\AlarmTriggerValue;
use Burki24\OpenHomeAlarm\AlarmVisualizationAdapter;

require_once __DIR__ . '/../libs/AlarmCodeProtection.php';
require_once __DIR__ . '/../libs/AlarmConfigurationNormalizer.php';
require_once __DIR__ . '/../libs/AlarmControlStateAdapter.php';
require_once __DIR__ . '/../libs/AlarmDisarmUserRegistry.php';
require_once __DIR__ . '/../libs/AlarmEventHistory.php';
require_once __DIR__ . '/../libs/AlarmActionExecutor.php';
require_once __DIR__ . '/../libs/AlarmFaultMonitor.php';
require_once __DIR__ . '/../libs/AlarmSensorMonitor.php';
require_once __DIR__ . '/../libs/AlarmStateMachine.php';
require_once __DIR__ . '/../libs/AlarmTimerSchedule.php';
require_once __DIR__ . '/../libs/AlarmTriggerValue.php';
require_once __DIR__ . '/../libs/AlarmVisualizationAdapter.php';

function assertDomainSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s Expected %s, got %s.',
            $message,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

function assertDomainThrows(callable $operation, string $expectedMessage): void
{
    try {
        $operation();
    } catch (UnexpectedValueException $exception) {
        assertDomainSame($expectedMessage, $exception->getMessage(), 'Unexpected validation message.');

        return;
    }

    throw new RuntimeException(sprintf('Expected validation exception: %s', $expectedMessage));
}

assertDomainSame([0, 1, 2, 3], AlarmStateMachine::modes(), 'Alarm modes must retain their public values.');
assertDomainSame([0, 1, 2, 3, 4], AlarmStateMachine::states(), 'Alarm states must retain their public values.');
assertDomainSame(true, AlarmStateMachine::isValidMode(3), 'Night mode must be valid.');
assertDomainSame(false, AlarmStateMachine::isValidMode(99), 'Unknown modes must be invalid.');
assertDomainSame(true, AlarmStateMachine::isValidState(4), 'Alarm state must be valid.');
assertDomainSame(false, AlarmStateMachine::isValidState(99), 'Unknown states must be invalid.');
assertDomainSame(true, AlarmStateMachine::isArmingMode(1), 'Home must be an arming mode.');
assertDomainSame(false, AlarmStateMachine::isArmingMode(0), 'None must not be an arming mode.');
assertDomainSame(true, AlarmStateMachine::canArm(0, 2), 'A disarmed system must accept a valid arming target.');
assertDomainSame(false, AlarmStateMachine::canArm(2, 2), 'An armed system must reject another arming transition.');
assertDomainSame(false, AlarmStateMachine::canArm(0, 0), 'Arming without an active mode must be rejected.');
assertDomainSame(true, AlarmStateMachine::canCompleteExitDelay(1, 3), 'A valid exit delay must be completable.');
assertDomainSame(false, AlarmStateMachine::canCompleteExitDelay(1, 0), 'An exit delay without an active mode must be rejected.');
assertDomainSame(true, AlarmStateMachine::monitorsArmedSensors(2), 'Armed sensors must be monitored.');
assertDomainSame(true, AlarmStateMachine::monitorsArmedSensors(3), 'Sensors must remain monitored during entry delay.');
assertDomainSame(false, AlarmStateMachine::monitorsArmedSensors(0), 'Disarmed mode-specific sensors must not be monitored.');
assertDomainSame(true, AlarmStateMachine::canStartEntryDelay(2), 'Entry delay must start from armed state.');
assertDomainSame(false, AlarmStateMachine::canStartEntryDelay(3), 'A running entry delay must not restart.');
assertDomainSame(true, AlarmStateMachine::canEnterAlarm(0), 'A 24/7 sensor must be able to alarm while disarmed.');
assertDomainSame(false, AlarmStateMachine::canEnterAlarm(4), 'An active alarm must not be entered twice.');
assertDomainSame(false, AlarmStateMachine::canEnterAlarm(99), 'An unknown state must not enter alarm.');
assertDomainSame(1, AlarmStateMachine::armingModeFromName(' Home '), 'Control mode parsing must remain stable.');
assertDomainSame(null, AlarmStateMachine::armingModeFromName('none'), 'The public arming API must reject none.');
assertDomainSame('away', AlarmStateMachine::modeName(2), 'Mode names must remain stable.');
assertDomainSame('entry_delay', AlarmStateMachine::stateName(3), 'State names must remain stable.');
assertDomainSame('unknown', AlarmStateMachine::modeName(99), 'Unknown mode names must remain explicit.');
assertDomainSame('unknown', AlarmStateMachine::stateName(99), 'Unknown state names must remain explicit.');

assertDomainSame(
    ['Deadline' => 1060, 'IntervalMilliseconds' => 60000],
    AlarmTimerSchedule::start(1000, 60),
    'A timer start must persist its absolute deadline and interval.'
);
assertDomainSame(
    ['Expired' => false, 'RemainingSeconds' => 30, 'IntervalMilliseconds' => 30000],
    AlarmTimerSchedule::restore(1060, 1030),
    'A future deadline must restore only its remaining interval.'
);
assertDomainSame(
    ['Expired' => true, 'RemainingSeconds' => 0, 'IntervalMilliseconds' => 0],
    AlarmTimerSchedule::restore(1060, 1060),
    'A deadline must expire exactly when it is reached.'
);
assertDomainSame(
    ['Expired' => true, 'RemainingSeconds' => 0, 'IntervalMilliseconds' => 0],
    AlarmTimerSchedule::restore(0, 1000),
    'A missing persisted deadline must be treated as expired.'
);

$monitorSensors = [
    ['Enabled' => true, 'VariableID' => 10, 'AlwaysActive' => false, 'ArmHome' => true, 'ArmAway' => false, 'ArmNight' => false],
    ['Enabled' => true, 'VariableID' => 20, 'AlwaysActive' => true, 'ArmHome' => false, 'ArmAway' => false, 'ArmNight' => false],
    ['Enabled' => false, 'VariableID' => 30, 'AlwaysActive' => false, 'ArmHome' => true, 'ArmAway' => false, 'ArmNight' => false]
];
assertDomainSame(true, AlarmSensorMonitor::containsVariable(10, $monitorSensors), 'An enabled assigned sensor must be monitored.');
assertDomainSame(true, AlarmSensorMonitor::containsVariable(20, $monitorSensors), 'An enabled 24/7 sensor must be monitored.');
assertDomainSame(false, AlarmSensorMonitor::containsVariable(30, $monitorSensors), 'A disabled sensor must not be monitored.');
$sensorTransitions = AlarmSensorMonitor::availabilityTransitions(
    $monitorSensors,
    [20, 40],
    static fn (array $sensor): ?bool => $sensor['VariableID'] === 10 ? null : false
);
assertDomainSame([10], $sensorTransitions['UnavailableIDs'], 'Only unreadable monitored sensors must be unavailable.');
assertDomainSame([10], $sensorTransitions['NewUnavailableIDs'], 'New sensor loss must be detected once.');
assertDomainSame([20, 40], $sensorTransitions['RestoredIDs'], 'Recovered or removed sensors must be reported as restored.');

$monitorFaults = [
    ['Enabled' => true, 'VariableID' => 100, 'TriggerAlarm' => true],
    ['Enabled' => true, 'VariableID' => 200, 'TriggerAlarm' => false],
    ['Enabled' => false, 'VariableID' => 300, 'TriggerAlarm' => true]
];
assertDomainSame(true, AlarmFaultMonitor::containsVariable(100, $monitorFaults), 'An enabled fault input must be monitored.');
assertDomainSame(false, AlarmFaultMonitor::containsVariable(300, $monitorFaults), 'A disabled fault input must not be monitored.');
$faultTransitions = AlarmFaultMonitor::transitions(
    $monitorFaults,
    [100, 400],
    static fn (array $fault): ?bool => match ($fault['VariableID']) {
        100 => true, 200 => null, default => false
    }
);
assertDomainSame([100, 200], $faultTransitions['ActiveIDs'], 'Active and unreadable fault inputs must remain fail-safe active.');
assertDomainSame(200, $faultTransitions['NewlyActiveInputs'][0][0]['VariableID'], 'A newly unreadable fault must be detected.');
assertDomainSame(null, $faultTransitions['NewlyActiveInputs'][0][1], 'An unreadable fault must remain distinguishable from a confirmed trigger.');
assertDomainSame([400], $faultTransitions['ClearedIDs'], 'Removed or healthy prior faults must be reported as cleared.');

$executedActions = [];
$actionRunner = static function (string $actionID, array $parameters) use (&$executedActions): bool
{
    $executedActions[] = ['actionID' => $actionID, 'parameters' => $parameters];

    return true;
};
assertDomainSame(
    ['Succeeded' => true, 'Error' => null],
    AlarmActionExecutor::execute(false, '{invalid json', $actionRunner),
    'A disabled optional action must not be parsed or executed.'
);
assertDomainSame(
    ['Succeeded' => true, 'Error' => null],
    AlarmActionExecutor::execute(true, 'false', $actionRunner),
    'An unconfigured native SelectAction value must be successful without execution.'
);
$configuredAction = json_encode(
    ['actionID' => '{ACTION}', 'parameters' => ['VALUE' => true]],
    JSON_THROW_ON_ERROR
);
assertDomainSame(
    ['Succeeded' => true, 'Error' => null],
    AlarmActionExecutor::execute(true, $configuredAction, $actionRunner),
    'A valid action must execute successfully.'
);
assertDomainSame(
    [['actionID' => '{ACTION}', 'parameters' => ['VALUE' => true]]],
    $executedActions,
    'Action ID and parameters must reach the runner unchanged.'
);
$invalidAction = AlarmActionExecutor::execute(true, '{invalid json', $actionRunner);
assertDomainSame(false, $invalidAction['Succeeded'], 'Invalid action JSON must fail safely.');
assertDomainSame(true, str_starts_with($invalidAction['Error'] ?? '', 'Invalid configured action:'), 'Invalid JSON must expose the established diagnostic.');
assertDomainSame(
    ['Succeeded' => false, 'Error' => 'Configured action is missing actionID or parameters.'],
    AlarmActionExecutor::execute(true, '{"actionID":"{ACTION}"}', $actionRunner),
    'Incomplete action payloads must be rejected.'
);
assertDomainSame(
    ['Succeeded' => false, 'Error' => 'Configured action could not be started.'],
    AlarmActionExecutor::execute(true, $configuredAction, static fn (): bool => false),
    'A runner rejection must be reported.'
);
assertDomainSame(
    ['Succeeded' => false, 'Error' => 'Configured action failed: runner failed'],
    AlarmActionExecutor::execute(
        true,
        $configuredAction,
        static function (): bool
        {
            throw new RuntimeException('runner failed');
        }
    ),
    'A runner exception must be contained.'
);

assertDomainSame(
    ['Mode' => ['Value' => 2, 'Name' => 'away'], 'State' => ['Value' => 3, 'Name' => 'entry_delay']],
    AlarmControlStateAdapter::identity(2, 3),
    'The control adapter must expose stable machine-readable mode and state names.'
);

$disarmUsers = AlarmDisarmUserRegistry::users('[{"Enabled":true,"Name":" Alice ","Code":"1234"},{"Enabled":false,"Name":"Bob","Code":"5678"}]');
assertDomainSame('Alice', $disarmUsers[0]['Name'], 'Disarm user names must be normalized.');
assertDomainSame(true, AlarmDisarmUserRegistry::hasEnabledCode($disarmUsers), 'An enabled user code must enable code protection.');
assertDomainSame('Alice', AlarmDisarmUserRegistry::matchingUser('1234', $disarmUsers), 'A user code must resolve to its name.');
assertDomainSame(null, AlarmDisarmUserRegistry::matchingUser('5678', $disarmUsers), 'Disabled user codes must be rejected.');
assertDomainThrows(
    static fn (): array => AlarmDisarmUserRegistry::users('[{"Code":"1234"},{"Code":"1234"}]'),
    'Enabled disarm user codes must be unique.'
);
assertDomainSame(
    [
        'CodeRequired'        => true,
        'CanDisarm'           => true,
        'CanManageBypasses'   => false,
        'CanResetAlarmOutput' => true,
        'CanClearAlarmMemory' => false
    ],
    AlarmControlStateAdapter::capabilities(2, 4, true, true, true),
    'Alarm capabilities must be derived independently from visualization transports.'
);
assertDomainSame(
    ['ApiVersion' => 2, 'Interaction' => ['Type' => 'test']],
    AlarmControlStateAdapter::withInteraction(['ApiVersion' => 2], ['Type' => 'test']),
    'Visualization interactions must be added without rebuilding control state.'
);
assertDomainSame(
    '{"Name":"Tür"}',
    AlarmControlStateAdapter::encode(['Name' => 'Tür']),
    'Control JSON must remain Unicode- and slash-safe.'
);
assertDomainSame(
    ['Action' => 'Arm', 'Value' => 'away'],
    AlarmVisualizationAdapter::command('Arm', 'away'),
    'Visualization arming commands must retain their mode.'
);
assertDomainSame(
    ['Action' => 'BypassSensor', 'Value' => 42],
    AlarmVisualizationAdapter::command('BypassSensor', '42'),
    'Visualization variable IDs must be normalized to positive integers.'
);
try {
    AlarmVisualizationAdapter::command('Arm', 2);
    throw new RuntimeException('A non-string visualization mode must be rejected.');
} catch (InvalidArgumentException $exception) {
    assertDomainSame('Arm action requires a mode string.', $exception->getMessage(), 'The established action diagnostic must remain stable.');
}
try {
    AlarmVisualizationAdapter::command('Unknown', null);
    throw new RuntimeException('An unknown visualization command must be rejected.');
} catch (InvalidArgumentException $exception) {
    assertDomainSame('Unknown visualization action.', $exception->getMessage(), 'Unknown actions must retain their diagnostic.');
}

$codeStatus = AlarmCodeProtection::status(true, 0, 0, 3, 1000);
assertDomainSame(3, $codeStatus['RemainingAttempts'], 'A new code-protection state must expose every attempt.');
$codeStatus = AlarmCodeProtection::registerFailure($codeStatus, 60, 1000);
assertDomainSame(1, $codeStatus['FailedAttempts'], 'A rejected code must increment the failed-attempt counter.');
$codeStatus = AlarmCodeProtection::registerFailure($codeStatus, 60, 1000);
$codeStatus = AlarmCodeProtection::registerFailure($codeStatus, 60, 1000);
assertDomainSame(true, $codeStatus['Locked'], 'The configured number of rejected codes must start a lockout.');
assertDomainSame(1060, $codeStatus['LockoutUntil'], 'The lockout deadline must use the configured duration.');
assertDomainSame(
    60,
    $codeStatus['LockoutRemaining'],
    'The lockout state must expose its remaining duration.'
);
$codeStatus = AlarmCodeProtection::status(true, 3, 1060, 3, 1061);
assertDomainSame(false, $codeStatus['Locked'], 'An expired code lockout must be released.');
assertDomainSame(0, $codeStatus['FailedAttempts'], 'An expired code lockout must reset failed attempts.');

$sensors = AlarmConfigurationNormalizer::sensors(
    json_encode(
        [
            [
                'Name'       => ' Front door ',
                'VariableID' => 1001,
                'SensorType' => 1,
                'ArmHome'    => true
            ]
        ],
        JSON_THROW_ON_ERROR
    ),
    [0, 1, 2],
    0
);
assertDomainSame(
    [
        [
            'Enabled'      => true,
            'PartitionID'  => '',
            'Name'         => 'Front door',
            'VariableID'   => 1001,
            'SensorType'   => 1,
            'TriggerValue' => '1',
            'ArmHome'      => true,
            'ArmAway'      => true,
            'ArmNight'     => false,
            'AlwaysActive' => false,
            'ExitDelay'    => false,
            'EntryDelay'   => false
        ]
    ],
    $sensors,
    'Sensor configuration must be normalized.'
);
assertDomainThrows(
    static fn (): array => AlarmConfigurationNormalizer::sensors(
        '[{"VariableID":-1}]',
        [0],
        0
    ),
    'Sensor VariableID must not be negative.'
);
assertDomainThrows(
    static fn (): array => AlarmConfigurationNormalizer::sensors(
        '[{"SensorType":9}]',
        [0],
        0
    ),
    'Unsupported sensor type.'
);

assertDomainSame(
    [],
    AlarmConfigurationNormalizer::faultInputs('', [0], 0),
    'An empty fault configuration must remain empty.'
);
$faultInputs = AlarmConfigurationNormalizer::faultInputs(
    '[{"Name":" Tamper ","VariableID":2001,"FaultType":0,"BlockArming":true}]',
    [0, 1],
    0
);
assertDomainSame('Tamper', $faultInputs[0]['Name'], 'Fault input names must be trimmed.');
assertDomainSame('', $faultInputs[0]['PartitionID'], 'Empty fault partition assignments must remain available for default resolution.');
assertDomainSame(false, $faultInputs[0]['TriggerAlarm'], 'Fault input defaults must be retained.');
assertDomainThrows(
    static fn (): array => AlarmConfigurationNormalizer::faultInputs(
        '[{"VariableID":2001},{"VariableID":2001}]',
        [0],
        0
    ),
    'A fault input variable can only be configured once.'
);

assertDomainSame('true', AlarmTriggerValue::toStorageString(1, 0), 'Boolean trigger conversion failed.');
assertDomainSame('12', AlarmTriggerValue::toStorageString(12.8, 1), 'Integer trigger conversion failed.');
assertDomainSame('2.0', AlarmTriggerValue::toStorageString(2, 2), 'Float trigger conversion failed.');
assertDomainSame(null, AlarmTriggerValue::toStorageString([]), 'Unsupported trigger values must be rejected.');
assertDomainSame(true, AlarmTriggerValue::matches(0, '1', true), 'Boolean trigger comparison failed.');
assertDomainSame(false, AlarmTriggerValue::matches(1, '7', 8), 'Integer trigger mismatch failed.');
assertDomainSame(true, AlarmTriggerValue::matches(2, '2.5', 2.5), 'Float trigger comparison failed.');
assertDomainSame(null, AlarmTriggerValue::matches(1, 'invalid', 1), 'Invalid trigger text must be rejected.');

$validEntry = [
    'Time'   => 100,
    'Event'  => 'armed',
    'Mode'   => 1,
    'State'  => 2,
    'Source' => 'Test'
];
$history = AlarmEventHistory::normalize(
    [
        $validEntry,
        ['Time' => 'invalid'],
        [
            'Time'   => 101,
            'Event'  => 'alarm',
            'Mode'   => 9,
            'State'  => 4,
            'Source' => 'Test'
        ]
    ],
    [0, 1, 2, 3],
    [0, 1, 2, 3, 4],
    10
);
$normalizedValidEntry = $validEntry;
$normalizedValidEntry['PartitionID'] = '';
assertDomainSame([$normalizedValidEntry], $history, 'Invalid event history entries must be discarded and legacy entries retained.');

$newEntry = [
    'Time'        => 102,
    'Event'       => 'disarmed',
    'Mode'        => 0,
    'State'       => 0,
    'Source'      => '',
    'PartitionID' => 'main'
];
assertDomainSame(
    [$newEntry],
    AlarmEventHistory::prepend($history, $newEntry, 1),
    'The newest event must be prepended and the history limit enforced.'
);

echo "Extracted alarm domain model checks passed.\n";
