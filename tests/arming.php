<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    2001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    2002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    2003 => ['VariableType' => 3, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    2004 => ['VariableType' => 1, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    2001 => false,
    2002 => false,
    2003 => 'IDLE',
    2004 => 0
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

    /** @var array<string,int> */
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

function assertArming(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function armingSensor(
    int $variableID,
    string $triggerValue,
    bool $enabled = true,
    bool $armHome = false,
    bool $armAway = false,
    bool $armNight = false
): array {
    return [
        'Enabled'      => $enabled,
        'Name'         => 'Test ' . $variableID,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => $triggerValue,
        'ArmHome'      => $armHome,
        'ArmAway'      => $armAway,
        'ArmNight'     => $armNight,
        'EntryDelay'   => false
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestClearWrittenValues();

$sensors = [
    armingSensor(2001, 'true', armHome: true),
    armingSensor(2002, 'true', armAway: true),
    armingSensor(2003, 'ALARM', armNight: true),
    armingSensor(2004, '1', enabled: false, armAway: true),
    armingSensor(0, 'true', armAway: true)
];
$instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));

// An inactive Home sensor permits immediate arming in Home mode.
assertArming($instance->ArmHome() === true, 'Home arming must succeed when all Home sensors are inactive.');
assertArming(
    $instance->TestWrittenValues() === [
        'ReadyToArm' => true,
        'Mode'       => 1,
        'State'      => 2
    ],
    'Successful Home arming must select Home mode and enter the Armed state.'
);

// A triggered Away-only sensor must block Away, but must not block Home.
global $testValues;
$testValues[2002] = true;
$instance->TestClearWrittenValues();
assertArming($instance->ArmAway() === false, 'A triggered Away sensor must block Away arming.');
assertArming(
    $instance->TestWrittenValues() === ['ReadyToArm' => false],
    'A failed arming attempt must not change Mode or State.'
);

$instance->TestClearWrittenValues();
assertArming(
    $instance->ArmHome() === true,
    'A sensor assigned only to Away must not block Home arming.'
);
assertArming(
    ($instance->TestWrittenValues()['Mode'] ?? null) === 1
    && ($instance->TestWrittenValues()['State'] ?? null) === 2,
    'Mode-specific readiness must allow Home even when a different mode is globally not ready.'
);

// String trigger values are respected for target-mode readiness.
$testValues[2002] = false;
$testValues[2003] = 'ALARM';
$instance->TestClearWrittenValues();
assertArming($instance->ArmNight() === false, 'A matching String trigger must block Night arming.');
assertArming(
    !array_key_exists('Mode', $instance->TestWrittenValues())
    && !array_key_exists('State', $instance->TestWrittenValues()),
    'Blocked Night arming must preserve the operational mode and state.'
);

$testValues[2003] = 'IDLE';
$instance->TestClearWrittenValues();
assertArming($instance->ArmNight() === true, 'Night arming must succeed after the Night sensor clears.');
assertArming(
    ($instance->TestWrittenValues()['Mode'] ?? null) === 3
    && ($instance->TestWrittenValues()['State'] ?? null) === 2,
    'Successful Night arming must enter Night/Armed.'
);

// Missing configured variables fail safe for the mode they are assigned to.
$missingSensors = [armingSensor(9999, 'true', armAway: true)];
$instance->TestSetPropertyString('Sensors', json_encode($missingSensors, JSON_THROW_ON_ERROR));
$instance->TestClearWrittenValues();
assertArming($instance->ArmAway() === false, 'A missing Away sensor variable must block Away arming.');
assertArming(
    $instance->TestWrittenValues() === ['ReadyToArm' => false],
    'A missing sensor must fail safe without changing Mode or State.'
);

// Incomplete rows remain ignored, matching A3 behavior.
$instance->TestSetPropertyString('Sensors', json_encode([armingSensor(0, 'true', armAway: true)], JSON_THROW_ON_ERROR));
$instance->TestClearWrittenValues();
assertArming($instance->ArmAway() === true, 'An incomplete row with VariableID 0 must not block arming.');
assertArming(
    ($instance->TestWrittenValues()['Mode'] ?? null) === 2
    && ($instance->TestWrittenValues()['State'] ?? null) === 2,
    'Away arming must succeed when only incomplete rows exist.'
);

// Disarm always returns the state machine to None/Disarmed and does not depend on sensor readiness.
$instance->TestSetPropertyString('Sensors', '{invalid');
$instance->TestClearWrittenValues();
assertArming($instance->Disarm() === true, 'Disarm must report success.');
assertArming(
    $instance->TestWrittenValues() === [
        'State' => 0,
        'Mode'  => 0
    ],
    'Disarm must clear the selected mode and enter Disarmed without evaluating sensors.'
);

$armMode = new ReflectionMethod(OpenHomeAlarm::class, 'ArmMode');
try {
    $armMode->invoke($instance, 0);
    throw new RuntimeException('MODE_NONE must not be accepted as an arming target.');
} catch (InvalidArgumentException) {
}

fwrite(STDOUT, "OpenHomeAlarm arming checks passed.\n");
