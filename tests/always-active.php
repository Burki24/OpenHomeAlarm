<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    5001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    5002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    5001 => false,
    5002 => false
];

function IPS_VariableExists(int $variableID): bool
{
    global $testVariables;

    return array_key_exists($variableID, $testVariables);
}

/** @return array<string,mixed> */
function IPS_GetVariable(int $variableID): array
{
    global $testVariables;

    if (!array_key_exists($variableID, $testVariables)) {
        throw new RuntimeException('Unknown test variable.');
    }

    return $testVariables[$variableID];
}

/** @return array<string,mixed> */
function IPS_GetVariablePresentation(int $variableID): array
{
    return [];
}

/** @return array<string,mixed> */
function IPS_GetVariableProfile(string $profileName): array
{
    return ['Associations' => []];
}

function GetValue(int $variableID): mixed
{
    global $testValues;

    if (!array_key_exists($variableID, $testValues)) {
        throw new RuntimeException('No test value available.');
    }

    return $testValues[$variableID];
}

/** @param array<string,mixed> $parameters */
function IPS_RunAction(string $actionID, array $parameters): bool
{
    return true;
}

class IPSModuleStrict
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,array{interval:int,script:string}> */
    private array $timers = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

    /** @var array<string,mixed> */
    private array $currentValues = [];

    public function Create(): void
    {
    }

    public function Destroy(): void
    {
    }

    public function ApplyChanges(): void
    {
    }

    public function TestSetPropertyString(string $name, string $value): void
    {
        $this->properties[$name] = $value;
    }

    public function TestSetPropertyInteger(string $name, int $value): void
    {
        $this->properties[$name] = $value;
    }

    /** @return array<string,mixed> */
    public function TestWrittenValues(): array
    {
        return $this->writtenValues;
    }

    public function TestClearWrittenValues(): void
    {
        $this->writtenValues = [];
    }

    /** @return array<int,list<int>> */
    public function TestMessages(): array
    {
        return $this->messages;
    }

    protected function RegisterPropertyString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $default;
        }
    }

    protected function RegisterPropertyInteger(string $name, int $default): void
    {
        if (!array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $default;
        }
    }

    protected function ReadPropertyString(string $name): string
    {
        $value = $this->properties[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function ReadPropertyInteger(string $name): int
    {
        $value = $this->properties[$name] ?? 0;

        return is_int($value) ? $value : 0;
    }

    protected function RegisterAttributeInteger(string $name, int $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }

    protected function RegisterAttributeString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }
    protected function ReadAttributeInteger(string $name): int
    {
        return $this->attributes[$name] ?? 0;
    }

    protected function ReadAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }
    protected function WriteAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function WriteAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }
    protected function RegisterTimer(string $name, int $interval, string $script): bool
    {
        $this->timers[$name] = ['interval' => $interval, 'script' => $script];

        return true;
    }

    protected function SetTimerInterval(string $name, int $interval): bool
    {
        $this->timers[$name]['interval'] = $interval;

        return true;
    }

    protected function RegisterVariableInteger(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function RegisterVariableBoolean(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function RegisterVariableString(string $ident, string $name, array $presentation, int $position): bool
    {
        return true;
    }

    protected function SetValue(string $ident, mixed $value): void
    {
        $this->writtenValues[$ident] = $value;
        $this->currentValues[$ident] = $value;
    }

    protected function GetValue(string $ident): mixed
    {
        return $this->currentValues[$ident] ?? null;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }

    protected function RegisterMessage(int $senderID, int $messageID): bool
    {
        $this->messages[$senderID] ??= [];
        if (!in_array($messageID, $this->messages[$senderID], true)) {
            $this->messages[$senderID][] = $messageID;
        }

        return true;
    }

    protected function UnregisterMessage(int $senderID, int $messageID): bool
    {
        return true;
    }

    /** @return array<int,list<int>> */
    protected function GetMessageList(): array
    {
        return $this->messages;
    }

    protected function UpdateFormField(string $field, string $parameter, mixed $value): bool
    {
        return true;
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        return true;
    }
}

function assertAlwaysActive(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function alwaysActiveSensor(
    int $variableID,
    string $name,
    bool $alwaysActive,
    bool $armAway = false,
    bool $entryDelay = false
): array {
    return [
        'Enabled'      => true,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'SensorType'   => 3,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => $armAway,
        'ArmNight'     => false,
        'AlwaysActive' => $alwaysActive,
        'EntryDelay'   => $entryDelay
    ];
}

// A 24/7 sensor without any arming-mode assignment must still be monitored.
$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 30);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([
        alwaysActiveSensor(5001, 'Rauchmelder Flur', true, entryDelay: true),
        alwaysActiveSensor(5002, 'Nur Abwesend', false, armAway: true)
    ], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
$instance->ApplyChanges();
assertAlwaysActive(
    $instance->TestMessages() === [
        5001 => [VM_UPDATE],
        5002 => [VM_UPDATE]
    ],
    '24/7 sensors must receive VM_UPDATE subscriptions even without a mode assignment.'
);
assertAlwaysActive(
    ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === true,
    'Inactive 24/7 sensors must leave the system ready to arm.'
);

// A mode-dependent sensor must not alarm while the system is disarmed.
global $testValues;
$testValues[5002] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 5002, VM_UPDATE, [true, true, false]);
assertAlwaysActive(
    !array_key_exists('State', $instance->TestWrittenValues())
        && ($instance->TestWrittenValues()['ReadyToArm'] ?? null) === false,
    'A normal arming-mode sensor must not enter Alarm while the system is disarmed.'
);
$testValues[5002] = false;

// A 24/7 sensor must alarm immediately while disarmed. EntryDelay is ignored.
$testValues[5001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(2, 5001, VM_UPDATE, [true, true, false]);
$written = $instance->TestWrittenValues();
assertAlwaysActive(($written['State'] ?? null) === 4, 'A 24/7 trigger must enter Alarm while disarmed.');
assertAlwaysActive(
    !array_key_exists('Mode', $written),
    'A 24/7 alarm while disarmed must not invent an arming mode.'
);
assertAlwaysActive(($written['AlarmMemory'] ?? null) === true, 'A 24/7 alarm must set the alarm memory.');
assertAlwaysActive(
    ($written['LastAlarmSource'] ?? null) === 'Rauchmelder Flur',
    'A 24/7 alarm must store its configured sensor name.'
);

// After the alarm is silenced and the sensor clears, readiness must recover.
$instance->Disarm();
$testValues[5001] = false;
$instance->TestClearWrittenValues();
$instance->MessageSink(3, 5001, VM_UPDATE, [false, true, true]);
assertAlwaysActive(
    $instance->TestWrittenValues() === [
        'ReadyToArm'           => true,
        'ReadyHome'            => true,
        'ReadyAway'            => true,
        'ReadyNight'           => true,
        'BlockingHomeSensors'  => '',
        'BlockingAwaySensors'  => '',
        'BlockingNightSensors' => ''
    ],
    'Clearing a 24/7 sensor must restore readiness after the alarm has been disarmed.'
);

// 24/7 sensors also bypass entry delay while the system is armed.
assertAlwaysActive($instance->ArmAway() === true, 'The system must arm with an inactive 24/7 sensor.');
$testValues[5001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(4, 5001, VM_UPDATE, [true, true, false]);
assertAlwaysActive(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'A 24/7 trigger must bypass entry delay and alarm immediately while armed.'
);

// A pre-existing 24/7 trigger must be detected during ApplyChanges after a restart.
$restart = new OpenHomeAlarm();
$restart->Create();
$restart->TestSetPropertyString(
    'Sensors',
    json_encode([
        alwaysActiveSensor(5001, 'Rauchmelder Flur', true)
    ], JSON_THROW_ON_ERROR)
);
$restart->TestClearWrittenValues();
$restart->ApplyChanges();
$restartWritten = $restart->TestWrittenValues();
assertAlwaysActive(
    ($restartWritten['State'] ?? null) === 4
        && ($restartWritten['AlarmMemory'] ?? null) === true
        && ($restartWritten['LastAlarmSource'] ?? null) === 'Rauchmelder Flur',
    'ApplyChanges must detect a 24/7 sensor that was already triggered before the module restarted.'
);

fwrite(STDOUT, "OpenHomeAlarm 24/7 sensor checks passed.\n");
