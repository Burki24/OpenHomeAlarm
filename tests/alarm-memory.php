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

    /** @var array<string,int> */
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

    protected function ReadAttributeInteger(string $name): int
    {
        return $this->attributes[$name] ?? 0;
    }

    protected function WriteAttributeInteger(string $name, int $value): void
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

function assertAlarmMemory(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function alarmMemorySensor(int $variableID, string $name, bool $entryDelay): array
{
    return [
        'Enabled'      => true,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'EntryDelay'   => $entryDelay
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmMemorySensor(5001, 'Haustür', false)], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();

assertAlarmMemory($instance->ArmAway() === true, 'Immediate alarm-memory test must arm successfully.');

global $testValues;
$testValues[5001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 5001, VM_UPDATE, [true, true, false]);
$written = $instance->TestWrittenValues();
assertAlarmMemory(($written['State'] ?? null) === 4, 'Immediate trigger must enter Alarm.');
assertAlarmMemory(($written['AlarmMemory'] ?? null) === true, 'An alarm must set AlarmMemory.');
assertAlarmMemory(($written['LastAlarmSource'] ?? null) === 'Haustür', 'The configured sensor name must be remembered.');
assertAlarmMemory(
    is_string($written['LastAlarmTime'] ?? null)
    && preg_match('/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', $written['LastAlarmTime']) === 1,
    'The alarm time must be stored in a readable date/time format.'
);

assertAlarmMemory($instance->ClearAlarmMemory() === false, 'Active alarms must not allow their memory to be cleared.');
$instance->TestClearWrittenValues();
$instance->Disarm();
assertAlarmMemory(
    !array_key_exists('AlarmMemory', $instance->TestWrittenValues())
    && !array_key_exists('LastAlarmSource', $instance->TestWrittenValues())
    && !array_key_exists('LastAlarmTime', $instance->TestWrittenValues()),
    'Disarming must preserve the alarm memory until it is acknowledged explicitly.'
);
$instance->TestClearWrittenValues();
assertAlarmMemory(
    $instance->ClearAlarmMemory() === true,
    'Alarm memory must be acknowledgeable after the active alarm has ended.'
);
assertAlarmMemory(
    $instance->TestWrittenValues() === [
        'AlarmMemory'     => false,
        'LastAlarmSource' => '',
        'LastAlarmTime'   => ''
    ],
    'Acknowledging alarm memory must clear all displayed alarm-memory values.'
);

// A delayed alarm must remember the sensor that started the entry delay, even after it closes again.
$testValues[5002] = false;
$delayed = new OpenHomeAlarm();
$delayed->Create();
$delayed->TestSetPropertyInteger('ExitDelaySeconds', 0);
$delayed->TestSetPropertyInteger('EntryDelaySeconds', 20);
$delayed->TestSetPropertyString(
    'Sensors',
    json_encode([alarmMemorySensor(5002, 'Terrassentür', true)], JSON_THROW_ON_ERROR)
);
$delayed->TestClearWrittenValues();
assertAlarmMemory($delayed->ArmAway() === true, 'Delayed alarm-memory test must arm successfully.');
$testValues[5002] = true;
$delayed->TestClearWrittenValues();
$delayed->MessageSink(2, 5002, VM_UPDATE, [true, true, false]);
assertAlarmMemory(
    ($delayed->TestWrittenValues()['State'] ?? null) === 3
    && !array_key_exists('AlarmMemory', $delayed->TestWrittenValues()),
    'Starting entry delay must not create alarm memory before an alarm actually occurs.'
);

$testValues[5002] = false;
$delayed->TestClearWrittenValues();
$delayed->CompleteEntryDelay();
assertAlarmMemory(
    ($delayed->TestWrittenValues()['State'] ?? null) === 4
    && ($delayed->TestWrittenValues()['AlarmMemory'] ?? null) === true
    && ($delayed->TestWrittenValues()['LastAlarmSource'] ?? null) === 'Terrassentür',
    'Entry-delay expiry must remember the sensor that originally started the delay.'
);

// A blank configured name still produces an identifiable source.
$testValues[5001] = false;
$fallback = new OpenHomeAlarm();
$fallback->Create();
$fallback->TestSetPropertyInteger('ExitDelaySeconds', 0);
$fallback->TestSetPropertyInteger('EntryDelaySeconds', 0);
$fallback->TestSetPropertyString(
    'Sensors',
    json_encode([alarmMemorySensor(5001, '', false)], JSON_THROW_ON_ERROR)
);
$fallback->TestClearWrittenValues();
assertAlarmMemory($fallback->ArmAway() === true, 'Fallback source-name test must arm successfully.');
$testValues[5001] = true;
$fallback->TestClearWrittenValues();
$fallback->MessageSink(3, 5001, VM_UPDATE, [true, true, false]);
assertAlarmMemory(
    ($fallback->TestWrittenValues()['LastAlarmSource'] ?? null) === 'Variable #5001',
    'A sensor without a configured name must fall back to its variable ID.'
);

fwrite(STDOUT, "OpenHomeAlarm alarm memory checks passed.\n");
