<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    7001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    7002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    7003 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    7001 => true,
    7002 => false,
    7003 => false
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
    private array $values = [];

    /** @var array<int,list<int>> */
    private array $messages = [];

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

    public function TestValue(string $ident): mixed
    {
        return $this->values[$ident] ?? null;
    }

    public function TestAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function SetVisualizationType(int $type): bool
    {
        return true;
    }

    protected function UpdateVisualizationValue(mixed $data): bool
    {
        return true;
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
        $value = $this->attributes[$name] ?? 0;

        return is_int($value) ? $value : 0;
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
        $this->values[$ident] = $value;
    }

    protected function GetValue(string $ident): mixed
    {
        return $this->values[$ident] ?? null;
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
        unset($this->messages[$senderID]);

        return true;
    }

    /** @return array<int,list<int>> */
    protected function GetMessageList(): array
    {
        return $this->messages;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }

    protected function SendDebug(string $message, mixed $data, int $format): void
    {
    }
}

function assertBypass(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function bypassSensor(
    int $variableID,
    string $name,
    bool $armHome = false,
    bool $armAway = false,
    bool $armNight = false,
    bool $alwaysActive = false,
    bool $enabled = true,
    bool $allowAutomaticBypass = false
): array {
    return [
        'Enabled'      => $enabled,
        'Name'         => $name,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => $armHome,
        'ArmAway'      => $armAway,
        'ArmNight'     => $armNight,
        'AlwaysActive' => $alwaysActive,
        'AllowAutomaticBypass' => $allowAutomaticBypass,
        'EntryDelay'   => false
    ];
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);

$sensors = [
    bypassSensor(7001, 'Haustür', armHome: true, armAway: true),
    bypassSensor(7002, 'Rauchmelder', alwaysActive: true),
    bypassSensor(7003, 'Küchenfenster', armAway: true),
    bypassSensor(9999, 'Defekter Nachtkontakt', armNight: true)
];
$instance->TestSetPropertyString('Sensors', json_encode($sensors, JSON_THROW_ON_ERROR));
$instance->ApplyChanges();

assertBypass($instance->TestValue('ReadyHome') === false, 'The triggered Home sensor must initially block Home.');
assertBypass($instance->TestValue('ReadyAway') === false, 'The triggered shared sensor must initially block Away.');
assertBypass($instance->TestValue('ReadyNight') === false, 'A missing Night sensor must fail safe before bypassing.');

assertBypass($instance->BypassSensor(7001) === true, 'A normal arming sensor must be bypassable while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === 'Haustür (main)', 'The bypass status must expose sensor and partition.');
assertBypass($instance->TestValue('ReadyHome') === true, 'Bypassing the only Home blocker must make Home ready.');
assertBypass($instance->TestValue('ReadyAway') === true, 'Bypassing the shared blocker must make Away ready.');
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === ['main:7001'],
    'The bypassed sensor assignment must be stored persistently.'
);

assertBypass($instance->BypassSensor(9999) === true, 'A missing configured arming sensor must remain explicitly bypassable.');
assertBypass($instance->TestValue('ReadyNight') === true, 'Bypassing a missing Night sensor must make Night ready.');
assertBypass(
    $instance->TestValue('BypassedSensors') === 'Haustür (main), Defekter Nachtkontakt (main)',
    'Multiple bypasses must be published in deterministic sensor order.'
);

assertBypass($instance->BypassSensor(7002) === false, 'A 24/7 sensor must never be bypassable.');
assertBypass($instance->ArmHome() === true, 'Home arming must succeed with its blocker bypassed.');
assertBypass($instance->TestValue('State') === 2, 'The system must enter Armed after successful bypass-assisted arming.');
assertBypass($instance->BypassSensor(7003) === false, 'New bypasses must be rejected while armed.');
assertBypass($instance->RemoveSensorBypass(7001) === false, 'Existing bypasses must be immutable while armed.');
assertBypass($instance->ClearSensorBypasses() === false, 'All bypasses must not be clearable while armed.');

$instance->MessageSink(1, 7001, VM_UPDATE, [true, true, 0]);
assertBypass($instance->TestValue('State') === 2, 'A bypassed sensor update must not trigger an alarm while armed.');

assertBypass($instance->Disarm() === true, 'Disarming must always succeed.');
assertBypass($instance->TestValue('State') === 0 && $instance->TestValue('Mode') === 0, 'Disarming must return to None/Disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'Disarming must clear every temporary bypass.');
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'The persistent bypass list must be empty after disarming.'
);
assertBypass($instance->TestValue('ReadyHome') === false, 'After bypass cleanup, the still-triggered Home sensor must block Home again.');
assertBypass($instance->TestValue('ReadyNight') === false, 'After bypass cleanup, the missing Night sensor must block Night again.');

assertBypass($instance->BypassSensor(7003) === true, 'Another normal sensor must be bypassable while disarmed.');
assertBypass($instance->RemoveSensorBypass(7003) === true, 'One bypass must be removable while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'Removing the last bypass must clear the status text.');

assertBypass($instance->BypassSensor(7003) === true, 'The sensor must be bypassable again for the clear-all test.');
assertBypass($instance->ClearSensorBypasses() === true, 'ClearSensorBypasses must succeed while disarmed.');
assertBypass($instance->TestValue('BypassedSensors') === '', 'ClearSensorBypasses must clear the published bypass list.');

$instance->BypassSensor(7003);
$reconfiguredSensors = [
    bypassSensor(7001, 'Haustür', armHome: true, armAway: true),
    bypassSensor(7002, 'Rauchmelder', alwaysActive: true),
    bypassSensor(7003, 'Küchenfenster', armAway: true, alwaysActive: true)
];
$instance->TestSetPropertyString('Sensors', json_encode($reconfiguredSensors, JSON_THROW_ON_ERROR));
$instance->ApplyChanges();
assertBypass(
    json_decode($instance->TestAttributeString('BypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'ApplyChanges must remove a bypass when that sensor becomes 24/7 active.'
);
assertBypass($instance->TestValue('BypassedSensors') === '', 'Stale bypass status must be cleared after reconfiguration.');

$automatic = new OpenHomeAlarm();
$automatic->Create();
$automatic->TestSetPropertyInteger('ExitDelaySeconds', 0);
$automatic->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Bedroom window', armNight: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$automatic->ApplyChanges();
assertBypass($automatic->ArmNight() === false, 'Existing arming calls must still reject an active sensor.');
assertBypass($automatic->ArmNight(null, null, true) === true, 'An opted-in active sensor may be bypassed for one arming call.');
assertBypass(
    json_decode($automatic->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === ['main:7001'],
    'Automatic bypass assignments must be persisted separately from manual bypasses.'
);
$automaticState = json_decode($automatic->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertBypass(($automaticState['BypassedSensors'][0]['Automatic'] ?? false) === true, 'The control API must identify automatic bypasses.');
$automatic->ApplyChanges();
assertBypass($automatic->TestValue('State') === 2, 'An active bypassed sensor must not alarm after ApplyChanges.');
assertBypass($automatic->TestValue('BypassedSensors') !== '', 'An automatic bypass must remain visible after ApplyChanges.');
$testValues[7001] = false;
$automatic->MessageSink(1, 7001, VM_UPDATE, [false, true, 0]);
assertBypass($automatic->TestValue('State') === 2, 'Returning to normal must not itself trigger an alarm.');
assertBypass($automatic->TestValue('BypassedSensors') === '', 'The automatic bypass must end at the first normal value.');
assertBypass(
    json_decode($automatic->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'Restoring monitoring must clear the persisted automatic bypass.'
);
$testValues[7001] = true;
$automatic->MessageSink(1, 7001, VM_UPDATE, [true, false, 0]);
assertBypass($automatic->TestValue('State') === 4, 'A new trigger after restoration must raise the alarm.');
$events = json_decode($automatic->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
assertBypass(in_array('sensor_auto_bypassed', array_column($events, 'Event'), true), 'Automatic bypass creation must be logged.');
assertBypass(in_array('sensor_auto_bypass_restored', array_column($events, 'Event'), true), 'Automatic bypass restoration must be logged.');
$automatic->Disarm();

$testValues[7003] = true;
$notPermitted = new OpenHomeAlarm();
$notPermitted->Create();
$notPermitted->TestSetPropertyInteger('ExitDelaySeconds', 0);
$notPermitted->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Allowed window', armAway: true, allowAutomaticBypass: true),
    bypassSensor(7003, 'Protected door', armAway: true)
], JSON_THROW_ON_ERROR));
$notPermitted->ApplyChanges();
assertBypass($notPermitted->ArmAway(null, null, true) === false, 'An unapproved active sensor must still block arming.');
assertBypass($notPermitted->TestValue('State') === 0, 'Rejected arming must leave the system disarmed.');
assertBypass(
    json_decode($notPermitted->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'A rejected arming attempt must not leave an automatic bypass behind.'
);

$unavailable = new OpenHomeAlarm();
$unavailable->Create();
$unavailable->TestSetPropertyInteger('ExitDelaySeconds', 0);
$unavailable->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(9999, 'Missing window', armAway: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$unavailable->ApplyChanges();
assertBypass($unavailable->ArmAway(null, null, true) === false, 'An unavailable sensor must never be automatically bypassed.');

$testValues[7002] = true;
$blockingFault = new OpenHomeAlarm();
$blockingFault->Create();
$blockingFault->TestSetPropertyInteger('ExitDelaySeconds', 0);
$blockingFault->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Allowed window', armAway: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$blockingFault->TestSetPropertyString('FaultInputs', json_encode([[
    'Enabled' => true, 'Name' => 'Door tamper', 'VariableID' => 7002,
    'FaultType' => 0, 'TriggerValue' => 'true', 'BlockArming' => true, 'TriggerAlarm' => false
]], JSON_THROW_ON_ERROR));
$blockingFault->ApplyChanges();
assertBypass($blockingFault->ArmAway(null, null, true) === false, 'A blocking fault must prevent bypass-assisted arming.');
assertBypass(
    json_decode($blockingFault->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'A blocking fault must not leave a proposed automatic bypass behind.'
);
$testValues[7002] = false;

$scheduled = new OpenHomeAlarm();
$scheduled->Create();
$scheduled->TestSetPropertyInteger('ExitDelaySeconds', 0);
$scheduled->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Scheduled window', armNight: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$scheduledMinute = mktime(2, 35, 0, 9, 26, 2026);
$schedule = [
    'Enabled' => true, 'Name' => 'Night check', 'Time' => '02:35',
    'Mode' => 'night', 'BypassActiveSensors' => true
];
foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $weekday) {
    $schedule[$weekday] = true;
}
$scheduled->TestSetPropertyString('AutomaticArmingSchedules', json_encode([$schedule], JSON_THROW_ON_ERROR));
$scheduled->ApplyChanges();
$executeSchedule = new ReflectionMethod(OpenHomeAlarm::class, 'ExecuteAutomaticArmingAt');
$executeSchedule->invoke($scheduled, $scheduledMinute);
assertBypass($scheduled->TestValue('State') === 2, 'An opted-in weekly schedule must arm with an eligible active sensor.');
assertBypass(
    json_decode($scheduled->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === ['main:7001'],
    'The weekly schedule must use the same persistent automatic-bypass path as the public API.'
);
$scheduled->Disarm();

$delayed = new OpenHomeAlarm();
$delayed->Create();
$delayed->TestSetPropertyInteger('ExitDelaySeconds', 30);
$delayed->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Open window', armNight: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$delayed->ApplyChanges();
assertBypass($delayed->ArmNight(null, null, true), 'An opted-in active sensor may start an exit delay.');
assertBypass($delayed->TestValue('State') === 1, 'The normal exit delay must remain active.');
$delayed->CompleteExitDelay();
assertBypass($delayed->TestValue('State') === 2, 'A still-open approved contact must not cancel arming at the end of the exit delay.');
$testValues[7001] = false;
$delayed->ApplyChanges();
assertBypass($delayed->TestValue('State') === 2, 'A normal contact on restart must keep the system armed.');
assertBypass(
    json_decode($delayed->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'ApplyChanges must restore monitoring if the bypassed contact has returned to normal.'
);
$testValues[7001] = true;
$delayed->MessageSink(1, 7001, VM_UPDATE, [true, false, 0]);
assertBypass($delayed->TestValue('State') === 4, 'A contact re-opened after restart must alarm.');
$delayed->Disarm();

$strictSchedule = new OpenHomeAlarm();
$strictSchedule->Create();
$strictSchedule->TestSetPropertyInteger('ExitDelaySeconds', 0);
$strictSchedule->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Scheduled window', armNight: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$schedule['BypassActiveSensors'] = false;
$strictSchedule->TestSetPropertyString('AutomaticArmingSchedules', json_encode([$schedule], JSON_THROW_ON_ERROR));
$strictSchedule->ApplyChanges();
$executeSchedule->invoke($strictSchedule, $scheduledMinute);
assertBypass($strictSchedule->TestValue('State') === 0, 'A schedule without the option must still reject the active sensor.');

$partitioned = new OpenHomeAlarm();
$partitioned->Create();
$partitioned->TestSetPropertyInteger('ExitDelaySeconds', 0);
$partitioned->TestSetPropertyString('Partitions', '[{"Enabled":true,"ID":"main","Name":"House"},{"Enabled":true,"ID":"garage","Name":"Garage"}]');
$partitioned->TestSetPropertyString('Sensors', json_encode([
    array_merge(bypassSensor(7001, 'Main window', armAway: true, allowAutomaticBypass: true), ['Partition_main' => true]),
    array_merge(bypassSensor(7003, 'Garage door', armAway: true), ['Partition_garage' => true])
], JSON_THROW_ON_ERROR));
$partitioned->ApplyChanges();
assertBypass($partitioned->ArmAway(null, null, true) === false, 'A blocked area must prevent partial global arming.');
assertBypass(
    json_decode($partitioned->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'Global arming failure must not persist bypasses in another area.'
);
assertBypass($partitioned->ArmPartition('main', 'away', 0, null, true) === false, 'The main area must still represent the complete system.');
assertBypass($partitioned->ArmPartition('garage', 'away', 0, null, true) === false, 'A protected garage contact must block its area.');

$partitioned->TestSetPropertyString('Sensors', json_encode([
    array_merge(bypassSensor(7001, 'Main window', armAway: true, allowAutomaticBypass: true), ['Partition_main' => true]),
    array_merge(bypassSensor(7003, 'Garage door', armAway: true, allowAutomaticBypass: true), ['Partition_garage' => true])
], JSON_THROW_ON_ERROR));
$partitioned->ApplyChanges();
assertBypass($partitioned->ArmPartition('garage', 'away', 0, null, true) === true, 'A single area may bypass its own approved contact.');
assertBypass(
    json_decode($partitioned->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === ['garage:7003'],
    'An area-local arming call must not bypass the same or another sensor in main.'
);
$partitionState = json_decode($partitioned->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertBypass(
    ($partitionState['Partitions']['main']['State']['Name'] ?? null) === 'disarmed'
        && ($partitionState['Partitions']['garage']['State']['Name'] ?? null) === 'armed',
    'Arming one area must leave main disarmed.'
);
$testValues[7003] = false;
$partitioned->MessageSink(1, 7003, VM_UPDATE, [false, true, 0]);
assertBypass(
    json_decode($partitioned->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'An area-local automatic bypass must end when its sensor returns to normal.'
);
$testValues[7003] = true;
$partitioned->MessageSink(1, 7003, VM_UPDATE, [true, false, 0]);
$partitionState = json_decode($partitioned->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertBypass(($partitionState['Partitions']['garage']['State']['Name'] ?? null) === 'alarm', 'A re-opened garage contact must alarm its area.');
$partitioned->DisarmPartition('garage');
assertBypass($partitioned->ArmAway(0, null, true) === true, 'Global arming may bypass approved active contacts in every area.');
assertBypass(
    json_decode($partitioned->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === ['garage:7003', 'main:7001'],
    'Global arming must retain separate bypass assignments for every area.'
);
$partitioned->Disarm();
assertBypass(
    json_decode($partitioned->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'Disarming all areas must remove every automatic bypass.'
);

$lostSensor = new OpenHomeAlarm();
$lostSensor->Create();
$lostSensor->TestSetPropertyInteger('ExitDelaySeconds', 0);
$lostSensor->TestSetPropertyString('Sensors', json_encode([
    bypassSensor(7001, 'Lost window', armAway: true, allowAutomaticBypass: true)
], JSON_THROW_ON_ERROR));
$lostSensor->ApplyChanges();
assertBypass($lostSensor->ArmAway(null, null, true), 'The unavailable-sensor test requires an armed bypass.');
$savedVariable = $testVariables[7001];
unset($testVariables[7001]);
$lostSensor->MessageSink(1, 7001, OM_UNREGISTER, []);
assertBypass(
    json_decode($lostSensor->TestAttributeString('AutoBypassedSensorIDs'), true, 512, JSON_THROW_ON_ERROR) === [],
    'A sensor that becomes unavailable must not remain automatically bypassed.'
);
assertBypass($lostSensor->TestValue('SystemFault') === true, 'A lost automatically bypassed sensor must still be reported as a system fault.');
$testVariables[7001] = $savedVariable;

fwrite(STDOUT, "OpenHomeAlarm manual and automatic sensor bypass checks passed.\n");
