<?php

declare(strict_types=1);

use Burki24\OpenHomeAlarm\AlarmCodeProtection;
use Burki24\OpenHomeAlarm\AlarmConfigurationNormalizer;
use Burki24\OpenHomeAlarm\AlarmEventHistory;
use Burki24\OpenHomeAlarm\AlarmTriggerValue;

require_once __DIR__ . '/../libs/AlarmCodeProtection.php';
require_once __DIR__ . '/../libs/AlarmConfigurationNormalizer.php';
require_once __DIR__ . '/../libs/AlarmEventHistory.php';
require_once __DIR__ . '/../libs/AlarmTriggerValue.php';

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
assertDomainSame([$validEntry], $history, 'Invalid event history entries must be discarded.');

$newEntry = [
    'Time'   => 102,
    'Event'  => 'disarmed',
    'Mode'   => 0,
    'State'  => 0,
    'Source' => ''
];
assertDomainSame(
    [$newEntry],
    AlarmEventHistory::prepend($history, $newEntry, 1),
    'The newest event must be prepended and the history limit enforced.'
);

echo "Extracted alarm domain model checks passed.\n";
