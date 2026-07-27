<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    2101 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    2102 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    2103 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    2101 => false,
    2102 => false,
    2103 => false
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

class IPSModuleStrict
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,array{interval:int,script:string}> */
    private array $timers = [];

    /** @var array<string,mixed> */
    private array $currentValues = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

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

    /** @return array<string,array{interval:int,script:string}> */
    public function TestTimers(): array
    {
        return $this->timers;
    }

    protected function SetVisualizationType(int $type): bool
    {
        return true;
    }

    protected function UpdateVisualizationValue(mixed $data): bool
    {
        return true;
    }

    protected function SendDebug(string $message, string $data, int $format): void
    {
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

    protected function ReadPropertyString(string $name): string
    {
        return $this->properties[$name] ?? '';
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
}

function assertExitRoute(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function exitRouteSensor(
    int $variableID,
    bool $exitDelay,
    bool $alwaysActive = false,
    string $name = ''
): array {
    return [
        'Enabled'      => true,
        'Name'         => $name !== '' ? $name : 'Test ' . $variableID,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'AlwaysActive' => $alwaysActive,
        'ExitDelay'    => $exitDelay,
        'EntryDelay'   => false
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 10);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 5);

// An open sensor explicitly marked as exit route may be active when arming starts.
global $testValues;
$testValues[2101] = true;
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([exitRouteSensor(2101, true, name: 'Haustür')], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
assertExitRoute(
    $instance->ArmAway() === true,
    'An open exit-route sensor must allow arming to start when an exit delay is configured.'
);
assertExitRoute(
    ($instance->TestWrittenValues()['ReadyAway'] ?? null) === true
    && ($instance->TestWrittenValues()['BlockingAwaySensors'] ?? null) === ''
    && ($instance->TestWrittenValues()['Mode'] ?? null) === 2
    && ($instance->TestWrittenValues()['State'] ?? null) === 1,
    'Exit-route sensors must not block the initial Away readiness while the exit delay can absorb them.'
);
assertExitRoute(
    ($instance->TestTimers()['ExitDelay']['interval'] ?? null) === 10000,
    'The configured exit delay must still run for an open exit-route sensor.'
);

// The same sensor must be ready when the exit countdown ends.
$instance->TestClearWrittenValues();
$instance->CompleteExitDelay();
assertExitRoute(
    ($instance->TestWrittenValues()['State'] ?? null) === 0
    && ($instance->TestWrittenValues()['Mode'] ?? null) === 0,
    'An exit-route sensor that is still open at countdown end must cancel arming.'
);

// Closing the exit-route sensor before countdown completion allows arming to finish.
$testValues[2101] = true;
$instance->TestClearWrittenValues();
assertExitRoute($instance->ArmAway() === true, 'A second arming attempt must start normally.');
$testValues[2101] = false;
$instance->TestClearWrittenValues();
$instance->CompleteExitDelay();
assertExitRoute(
    ($instance->TestWrittenValues()['State'] ?? null) === 2,
    'A cleared exit-route sensor must allow the exit delay to complete into Armed.'
);

// A regular open Away sensor continues to block immediately.
$instance->Disarm();
$testValues[2102] = true;
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([exitRouteSensor(2102, false, name: 'Küchenfenster')], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
assertExitRoute(
    $instance->ArmAway() === false,
    'A regular open Away sensor must still reject arming before the exit delay starts.'
);
assertExitRoute(
    ($instance->TestWrittenValues()['ReadyAway'] ?? null) === false
    && ($instance->TestWrittenValues()['BlockingAwaySensors'] ?? null) === 'Küchenfenster',
    'Regular blocking sensors must remain visible in mode readiness diagnostics.'
);

// Exit-route semantics are disabled when no exit delay exists.
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$testValues[2101] = true;
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([exitRouteSensor(2101, true, name: 'Haustür')], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
assertExitRoute(
    $instance->ArmAway() === false,
    'An open exit-route sensor must block immediate arming when ExitDelaySeconds is 0.'
);

// A missing or unreadable exit-route sensor must fail safe instead of being waived.
$instance->TestSetPropertyInteger('ExitDelaySeconds', 10);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([exitRouteSensor(9999, true, name: 'Fehlender Ausgangssensor')], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
assertExitRoute(
    $instance->ArmAway() === false,
    'A missing exit-route variable must block arming even when an exit delay is configured.'
);

// 24/7 sensors never receive the exit-route exception.
$testValues[2103] = true;
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([exitRouteSensor(2103, true, true, 'Rauchmelder')], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();
assertExitRoute(
    $instance->ArmAway() === false,
    'A triggered 24/7 sensor must block arming even if ExitDelay is set on its row.'
);

fwrite(STDOUT, "OpenHomeAlarm exit-route checks passed.\n");
