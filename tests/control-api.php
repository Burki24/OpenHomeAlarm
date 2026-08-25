<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

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

    protected function RegisterPropertyBoolean(string $name, bool $default): void
    {
        $this->RegisterPropertyInteger($name, $default ? 1 : 0);
    }

    protected function ReadPropertyBoolean(string $name): bool
    {
        return $this->ReadPropertyInteger($name) === 1;
    }

    protected function RegisterPropertyFloat(string $name, float $default): void
    {
    }

    protected function ReadPropertyFloat(string $name): float
    {
        return 0.0;
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

function assertControlApi(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

$invalidPartitionInstance = new OpenHomeAlarm();
$invalidPartitionInstance->Create();
$invalidPartitionInstance->TestSetPropertyString(
    'Partitions',
    '[{"Enabled":true,"ID":"house","Name":"House","Default":true},{"Enabled":true,"ID":"garage","Name":"Garage","Default":true}]'
);
try {
    $invalidPartitionInstance->ApplyChanges();
    throw new RuntimeException('ApplyChanges must reject ambiguous default partitions.');
} catch (UnexpectedValueException $exception) {
    assertControlApi(
        $exception->getMessage() === 'Exactly one enabled partition must be the default.',
        'ApplyChanges must report the partition validation failure.'
    );
}

$invalidAssignmentInstance = new OpenHomeAlarm();
$invalidAssignmentInstance->Create();
$invalidAssignmentInstance->TestSetPropertyString(
    'Sensors',
    '[{"Enabled":true,"PartitionID":"unknown","VariableID":0,"SensorType":0}]'
);
try {
    $invalidAssignmentInstance->ApplyChanges();
    throw new RuntimeException('ApplyChanges must reject unknown sensor partitions.');
} catch (UnexpectedValueException $exception) {
    assertControlApi(
        $exception->getMessage() === 'Sensor partition references an unknown partition.',
        'ApplyChanges must report an invalid sensor partition assignment.'
    );
}

/** @return array<string,mixed> */
function controlSensor(
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

$sensors = [
    [
        'Enabled'      => true,
        'Name'         => 'Front door',
        'VariableID'   => 2001,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => true,
        'ArmAway'      => false,
        'ArmNight'     => false,
        'AlwaysActive' => false,
        'ExitDelay'    => false,
        'EntryDelay'   => true
    ],
    [
        'Enabled'      => true,
        'Name'         => 'Hall motion',
        'VariableID'   => 2002,
        'SensorType'   => 1,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'AlwaysActive' => false,
        'ExitDelay'    => false,
        'EntryDelay'   => false
    ]
];
$instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));

global $testValues;
$testValues[2001] = true;
$testValues[2002] = false;

$state = json_decode($instance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(($state['ApiVersion'] ?? null) === 2, 'The partition-aware control API must expose schema version 2.');
assertControlApi(($state['DefaultPartition'] ?? null) === 'main', 'The control API must identify the default partition.');
assertControlApi(
    ($state['Partitions']['main']['ID'] ?? null) === 'main'
    && ($state['Partitions']['main']['Name'] ?? null) === 'Main area'
    && ($state['Partitions']['main']['Default'] ?? null) === true,
    'The default partition must expose its stable identity in the control state.'
);
assertControlApi(
    ($state['Partitions']['main']['State']['Name'] ?? null) === 'disarmed',
    'Slice 1 must publish the existing runtime as the default partition state.'
);
$partitionMetadata = json_decode($instance->GetPartitions(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionMetadata[0]['ID'] ?? null) === 'main'
    && ($partitionMetadata[0]['Default'] ?? null) === true,
    'The public partition metadata API must expose the configured default partition.'
);
assertControlApi(
    ($state['Mode']['Name'] ?? null) === 'none' && ($state['State']['Name'] ?? null) === 'disarmed',
    'The initial control state must expose stable machine-readable mode and state names.'
);
assertControlApi(($state['Capabilities']['CodeRequired'] ?? null) === false, 'An empty code must not require a codepad.');
assertControlApi(
    ($state['CodeProtection']['Locked'] ?? null) === false
        && ($state['CodeProtection']['RemainingAttempts'] ?? null) === 5,
    'Disabled code protection must expose a safe, unlocked default state.'
);
assertControlApi(($state['Capabilities']['CanManageBypasses'] ?? null) === true, 'Bypasses must be manageable while disarmed.');
assertControlApi(($state['Modes']['home']['Ready'] ?? null) === false, 'The triggered Home sensor must block Home.');
assertControlApi(($state['Modes']['home']['CanArm'] ?? null) === false, 'A blocked Home mode must not be armable.');
assertControlApi(($state['Modes']['away']['Ready'] ?? null) === true, 'The inactive Away sensor must leave Away ready.');
assertControlApi(($state['Modes']['away']['CanArm'] ?? null) === true, 'A ready mode must be armable while disarmed.');
assertControlApi(
    ($state['Modes']['home']['Blockers'][0] ?? null) === [
        'Kind'       => 'sensor',
        'VariableID' => 2001,
        'Name'       => 'Front door',
        'Reason'     => 'triggered',
        'Bypassable' => true
    ],
    'The control API must expose structured sensor blockers including the variable ID required for bypass controls.'
);

assertControlApi($instance->BypassSensor(2001) === true, 'A user-facing blocker must be bypassable through the existing API.');
$state = json_decode($instance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(($state['Modes']['home']['Ready'] ?? null) === true, 'Bypassing the Home blocker must make Home ready.');
assertControlApi(
    ($state['BypassedSensors'][0] ?? null) === ['VariableID' => 2001, 'Name' => 'Front door'],
    'The control API must expose bypassed sensors as structured data.'
);

assertControlApi($instance->Arm('unsupported') === false, 'Unknown control API mode names must be rejected safely.');
assertControlApi($instance->Arm('away') === true, 'The generic control API must arm a ready Away mode.');
$state = json_decode($instance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($state['Mode']['Name'] ?? null) === 'away' && ($state['State']['Name'] ?? null) === 'armed',
    'Generic Away arming must be reflected by the control state.'
);
assertControlApi(($state['Modes']['home']['CanArm'] ?? null) === false, 'No arming command may be offered while the system is already active.');
assertControlApi(($state['Capabilities']['CanDisarm'] ?? null) === true, 'An armed system must always expose disarming as available.');
assertControlApi($instance->Arm('home') === false, 'The control API must reject re-arming while the system is not disarmed.');
assertControlApi($instance->DisarmWithCode('') === true, 'An empty configured code must allow user-facing disarming without a codepad.');

$instance->TestSetPropertyString('DisarmCode', '2468');
$state = json_decode($instance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(($state['Capabilities']['CodeRequired'] ?? null) === true, 'A configured disarm code must be announced to the visualization.');
assertControlApi(
    ($state['CodeProtection']['Locked'] ?? null) === false
        && ($state['CodeProtection']['MaxAttempts'] ?? null) === 5,
    'Configured code protection must expose its lockout capability.'
);
assertControlApi(!str_contains($instance->GetControlState(), '2468'), 'The configured disarm code must never be exposed by the control API.');

$faults = [
    [
        'Enabled'      => true,
        'Name'         => 'Control cabinet tamper',
        'VariableID'   => 2004,
        'FaultType'    => 0,
        'TriggerValue' => '1',
        'BlockArming'  => true,
        'TriggerAlarm' => false
    ]
];
$testValues[2004] = 1;
$instance->TestSetPropertyString('FaultInputs', json_encode($faults, JSON_THROW_ON_ERROR));
$state = json_decode($instance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(($state['Modes']['away']['Ready'] ?? null) === false, 'A blocking fault must block every arming mode in the control state.');
$awayBlockers = $state['Modes']['away']['Blockers'] ?? [];
assertControlApi(count($awayBlockers) === 1, 'The Away mode must expose the blocking fault exactly once.');
assertControlApi(
    $awayBlockers[0] === [
        'Kind'         => 'fault',
        'VariableID'   => 2004,
        'Name'         => 'Control cabinet tamper',
        'FaultType'    => 0,
        'Reason'       => 'active',
        'BlockArming'  => true,
        'TriggerAlarm' => false,
        'Bypassable'   => false
    ],
    'Fault blockers must be structured and explicitly non-bypassable.'
);
assertControlApi(
    ($state['Faults']['Blocking'][0]['Name'] ?? null) === 'Control cabinet tamper',
    'The control state must expose active blocking faults separately for status views.'
);

$partitionInstance = new OpenHomeAlarm();
$partitionInstance->Create();
$partitionInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$partitionInstance->TestSetPropertyString(
    'Partitions',
    '[{"Enabled":true,"ID":"house","Name":"House","Default":true},{"Enabled":true,"ID":"garage","Name":"Garage","Default":false}]'
);
$partitionSensors = [
    array_merge(controlSensor(2001, 'true', true, true), ['PartitionID' => 'house']),
    array_merge(controlSensor(2002, 'true', true, false, true), ['PartitionID' => 'garage'])
];
$testValues[2001] = false;
$testValues[2002] = false;
$partitionInstance->TestSetPropertyString('Sensors', json_encode($partitionSensors, JSON_THROW_ON_ERROR));
$partitionInstance->ApplyChanges();
assertControlApi(!$partitionInstance->ArmPartition('unknown', 'away'), 'Unknown partitions must be rejected safely.');
assertControlApi($partitionInstance->ArmPartition('house', 'home'), 'The default partition must arm through the partition API.');
assertControlApi($partitionInstance->ArmPartition('garage', 'away'), 'A second partition must arm independently.');
$partitionState = json_decode($partitionInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionState['Partitions']['house']['State']['Name'] ?? null) === 'armed'
        && ($partitionState['Partitions']['garage']['State']['Name'] ?? null) === 'armed',
    'Both partitions must expose their independent armed states.'
);
assertControlApi($partitionInstance->DisarmPartition('house'), 'The default partition must disarm through the partition API.');
$partitionState = json_decode($partitionInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionState['Partitions']['house']['State']['Name'] ?? null) === 'disarmed'
        && ($partitionState['Partitions']['garage']['State']['Name'] ?? null) === 'armed',
    'Disarming one partition must leave the other partition armed.'
);
$testValues[2002] = true;
$partitionInstance->MessageSink(2, 2002, VM_UPDATE, [true, true, false]);
$testValues[2001] = false;
assertControlApi($partitionInstance->ArmPartition('house', 'home'), 'A partition must arm while another partition is alarming.');
$testValues[2001] = true;
$partitionInstance->MessageSink(3, 2001, VM_UPDATE, [true, true, false]);
$partitionState = json_decode($partitionInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionState['Partitions']['house']['Alarm']['OutputActive'] ?? null) === true
        && ($partitionState['Partitions']['garage']['Alarm']['OutputActive'] ?? null) === true
        && ($partitionState['Alarm']['OutputActive'] ?? null) === true,
    'Two partition alarms must contribute to the aggregated alarm output.'
);
$partitionEvents = json_decode($partitionInstance->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionEvents[0]['Event'] ?? null) === 'alarm'
        && ($partitionEvents[0]['PartitionID'] ?? null) === 'house'
        && ($partitionEvents[0]['Mode'] ?? null) === 1
        && ($partitionEvents[0]['State'] ?? null) === 4,
    'Partition alarm events must retain their partition, mode and alarm state.'
);
assertControlApi(
    $partitionInstance->ResetAlarmOutputPartition('house'),
    'One partition alarm output must be resettable independently.'
);
$partitionState = json_decode($partitionInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionState['Partitions']['house']['Alarm']['OutputActive'] ?? null) === false
        && ($partitionState['Partitions']['garage']['Alarm']['OutputActive'] ?? null) === true
        && ($partitionState['Alarm']['OutputActive'] ?? null) === true,
    'Resetting one partition must retain another partition and the aggregated output.'
);
assertControlApi($partitionInstance->DisarmPartition('house'), 'The first alarm partition must disarm independently.');
assertControlApi($partitionInstance->ResetAlarmOutputPartition('garage'), 'The remaining alarm output must be resettable.');
assertControlApi($partitionInstance->DisarmPartition('garage'), 'The second alarm partition must disarm independently.');
assertControlApi($partitionInstance->ClearAlarmMemoryPartition('house'), 'The first partition memory must be acknowledgeable.');
assertControlApi($partitionInstance->ClearAlarmMemoryPartition('garage'), 'The second partition memory must be acknowledgeable.');
$partitionState = json_decode($partitionInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertControlApi(
    ($partitionState['Alarm']['OutputActive'] ?? null) === false
        && ($partitionState['Alarm']['MemoryActive'] ?? null) === false,
    'The aggregate alarm summary must clear after every local output and memory was cleared.'
);

fwrite(STDOUT, "OpenHomeAlarm control API checks passed.\n");
