<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

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

    private mixed $visualizationValue = null;

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

    public function TestSetAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function TestSetAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function TestAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    public function TestSetCurrentValue(string $ident, mixed $value): void
    {
        $this->currentValues[$ident] = $value;
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

    public function TestVisualizationValue(): mixed
    {
        return $this->visualizationValue;
    }

    protected function SetVisualizationType(int $type): bool
    {
        return true;
    }

    protected function UpdateVisualizationValue(mixed $data): bool
    {
        $this->visualizationValue = $data;

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

function assertEventHistory(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function eventHistorySensor(int $variableID, string $name): array
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
        'AlwaysActive' => false,
        'EntryDelay'   => false
    ];
}

/** @return list<array<string,mixed>> */
function readEventHistory(OpenHomeAlarm $instance): array
{
    $history = json_decode($instance->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($history) || !array_is_list($history)) {
        throw new RuntimeException('Event history must decode to a JSON list.');
    }

    return $history;
}

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestSetPropertyString('DisarmCode', '1234');
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);

assertEventHistory(readEventHistory($instance) === [], 'A new instance must start with an empty event history.');
assertEventHistory($instance->ArmAway() === true, 'Event-history test must arm successfully.');
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'armed', 'Arming must be written to the event history.');
assertEventHistory(($history[0]['Mode'] ?? null) === 2, 'The armed event must retain the active Away mode.');
assertEventHistory(($history[0]['State'] ?? null) === 2, 'The armed event must retain the Armed state.');
assertEventHistory(($history[0]['Source'] ?? null) === '', 'Arming must not invent an event source.');
assertEventHistory(is_int($history[0]['Time'] ?? null), 'Every event must contain an integer Unix timestamp.');
$historyBeforeApplyChanges = $instance->GetEventHistory();
$instance->ApplyChanges();
assertEventHistory(
    $instance->GetEventHistory() === $historyBeforeApplyChanges,
    'ApplyChanges must preserve the persistent event history without creating duplicate events.'
);

global $testValues;
$testValues[5001] = true;
$instance->MessageSink(1, 5001, VM_UPDATE, [true, true, false]);
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'alarm', 'An alarm must be written to the event history.');
assertEventHistory(($history[0]['Source'] ?? null) === 'Haustür', 'Alarm events must retain the triggering sensor name.');
assertEventHistory(($history[0]['Mode'] ?? null) === 2 && ($history[0]['State'] ?? null) === 4, 'Alarm events must retain mode and Alarm state.');

assertEventHistory($instance->Disarm() === true, 'Disarming after an alarm must succeed.');
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'disarmed', 'Disarming must be written to the event history.');
assertEventHistory(($history[0]['Mode'] ?? null) === 0 && ($history[0]['State'] ?? null) === 0, 'Disarmed events must describe the resulting state.');

$testValues[5001] = false;
assertEventHistory($instance->ArmAway() === true, 'Trusted-source disarm history test must arm successfully.');
assertEventHistory($instance->DisarmWithSource('') === false, 'An empty trusted source must be rejected.');
assertEventHistory($instance->DisarmWithSource("Zutritt\nHaustür") === false, 'Control characters in a trusted source must be rejected.');
assertEventHistory($instance->DisarmWithSource("\nZutrittskontrolle") === false, 'Leading control characters in a trusted source must not be trimmed into validity.');
assertEventHistory($instance->DisarmWithSource(str_repeat('x', 81)) === false, 'An overlong trusted source must be rejected.');
$history = readEventHistory($instance);
assertEventHistory(
    ($history[0]['Event'] ?? null) === 'armed',
    'Rejected source labels must leave the armed state and its event history unchanged.'
);
assertEventHistory($instance->DisarmWithSource('Zutrittskontrolle Haustür') === true, 'A trusted automation must disarm with its authenticated source.');
$history = readEventHistory($instance);
assertEventHistory(
    ($history[0]['Event'] ?? null) === 'disarmed' && ($history[0]['Source'] ?? null) === 'Zutrittskontrolle Haustür',
    'Trusted-source disarming must retain its source in the security event history.'
);

assertEventHistory($instance->DisarmWithCode('9999') === false, 'A wrong code must remain rejected.');
$encodedHistory = $instance->GetEventHistory();
$history = json_decode($encodedHistory, true, 512, JSON_THROW_ON_ERROR);
assertEventHistory(($history[0]['Event'] ?? null) === 'disarm_code_rejected', 'Rejected code attempts must be auditable.');
assertEventHistory(!str_contains($encodedHistory, '9999'), 'The submitted disarm code must never be stored in the event history.');
assertEventHistory(!str_contains($encodedHistory, '1234'), 'The configured disarm code must never be stored in the event history.');
$alarmExport = json_decode(
    $instance->ExportEventHistory('json', 0, 0, 'alarm'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertEventHistory(
    count($alarmExport) === 1
    && ($alarmExport[0]['Event'] ?? null) === 'alarm'
    && ($alarmExport[0]['Source'] ?? null) === 'Haustür',
    'The public JSON export must filter the persistent history without changing its fields.'
);
$csvExport = $instance->ExportEventHistory('csv', 0, 0, '');
assertEventHistory(str_starts_with($csvExport, "Time,Event,Mode,State,Source,PartitionID\r\n"), 'The public CSV export must expose its stable header.');
assertEventHistory(!str_contains($csvExport, '9999') && !str_contains($csvExport, '1234'), 'Exports must retain the event history code-redaction boundary.');
$instance->RequestAction('ExportEventHistory', 'json');
$visualizationExport = json_decode(
    (string) $instance->TestVisualizationValue(),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertEventHistory(
    ($visualizationExport['Interaction']['Type'] ?? null) === 'event_history_export'
    && ($visualizationExport['Interaction']['Format'] ?? null) === 'json'
    && json_decode($visualizationExport['Interaction']['Content'] ?? '', true, 512, JSON_THROW_ON_ERROR) === $history,
    'The visualization action must return the complete export as a transient interaction.'
);
assertEventHistory(
    preg_match('/^openhomealarm-events-[0-9]{8}-[0-9]{6}\.json$/', $visualizationExport['Interaction']['Filename'] ?? '') === 1,
    'Visualization exports need a deterministic, filesystem-safe download name.'
);

$testValues[5001] = false;
assertEventHistory($instance->BypassSensor(5001) === true, 'A configured arming sensor must remain bypassable while disarmed.');
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'sensor_bypassed', 'Adding a bypass must be logged.');
assertEventHistory(($history[0]['Source'] ?? null) === 'Haustür', 'Bypass events must name the affected sensor.');
assertEventHistory($instance->RemoveSensorBypass(5001) === true, 'The temporary bypass must remain removable.');
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'sensor_bypass_removed', 'Removing a bypass must be logged.');

assertEventHistory($instance->ClearAlarmMemory() === true, 'Stored alarm memory must remain clearable after disarming.');
$history = readEventHistory($instance);
assertEventHistory(($history[0]['Event'] ?? null) === 'alarm_memory_cleared', 'Clearing stored alarm memory must be logged when memory existed.');

assertEventHistory($instance->ClearEventHistory() === true, 'Event history must be clearable explicitly.');
assertEventHistory(readEventHistory($instance) === [], 'ClearEventHistory() must remove every stored event.');

// Rejected arming attempts must retain the concrete blocker and requested target mode.
$testValues[5001] = true;
$rejected = new OpenHomeAlarm();
$rejected->Create();
$rejected->TestSetPropertyInteger('ExitDelaySeconds', 0);
$rejected->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
assertEventHistory($rejected->ArmAway() === false, 'Triggered Away sensor must still reject arming.');
$rejectedHistory = readEventHistory($rejected);
assertEventHistory(($rejectedHistory[0]['Event'] ?? null) === 'arm_rejected', 'Rejected arming must be logged.');
assertEventHistory(($rejectedHistory[0]['Mode'] ?? null) === 2, 'Rejected arming must retain the requested target mode.');
assertEventHistory(($rejectedHistory[0]['Source'] ?? null) === 'Haustür', 'Rejected arming must retain its blocking sensor.');

// A failed final check after exit delay must distinguish cancellation from the later disarm transition.
$testValues[5001] = false;
$exitDelay = new OpenHomeAlarm();
$exitDelay->Create();
$exitDelay->TestSetPropertyInteger('ExitDelaySeconds', 10);
$exitDelay->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
assertEventHistory($exitDelay->ArmAway() === true, 'Ready Away sensor must start exit delay.');
$exitHistory = readEventHistory($exitDelay);
assertEventHistory(($exitHistory[0]['Event'] ?? null) === 'exit_delay_started', 'Starting exit delay must be logged.');
$testValues[5001] = true;
$exitDelay->CompleteExitDelay();
$exitHistory = readEventHistory($exitDelay);
assertEventHistory(($exitHistory[0]['Event'] ?? null) === 'disarmed', 'Cancelled exit delay must end in a disarmed event.');
assertEventHistory(($exitHistory[1]['Event'] ?? null) === 'arm_cancelled', 'Failed final readiness check must be logged as arm_cancelled.');
assertEventHistory(($exitHistory[1]['Source'] ?? null) === 'Haustür', 'Cancelled arming must retain the blocker.');

// Entry-delay events must retain the initiating sensor independently from the later alarm entry.
$testValues[5002] = false;
$entryDelay = new OpenHomeAlarm();
$entryDelay->Create();
$entryDelay->TestSetPropertyInteger('ExitDelaySeconds', 0);
$entryDelay->TestSetPropertyInteger('EntryDelaySeconds', 10);
$entrySensor = eventHistorySensor(5002, 'Terrassentür');
$entrySensor['EntryDelay'] = true;
$entryDelay->TestSetPropertyString('Sensors', json_encode([$entrySensor], JSON_THROW_ON_ERROR));
assertEventHistory($entryDelay->ArmAway() === true, 'Entry-delay history test must arm successfully.');
$testValues[5002] = true;
$entryDelay->MessageSink(2, 5002, VM_UPDATE, [true, true, false]);
$entryHistory = readEventHistory($entryDelay);
assertEventHistory(($entryHistory[0]['Event'] ?? null) === 'entry_delay_started', 'Starting entry delay must be logged.');
assertEventHistory(($entryHistory[0]['Source'] ?? null) === 'Terrassentür', 'Entry-delay events must retain the initiating sensor.');

// A visualization may add a self-declared local operating-device name to events caused by that request.
$testValues[5001] = false;
$clientAttribution = new OpenHomeAlarm();
$clientAttribution->Create();
$clientAttribution->TestSetPropertyInteger('ExitDelaySeconds', 0);
$clientAttribution->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
$clientAttribution->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'main', 'Value' => 'away'],
            'ClientName' => "  Tablet\nFlur  "
        ],
        JSON_THROW_ON_ERROR
    )
);
$clientHistory = readEventHistory($clientAttribution);
assertEventHistory(
    ($clientHistory[0]['Event'] ?? null) === 'armed'
    && ($clientHistory[0]['ClientName'] ?? null) === 'Tablet Flur',
    'A visualization action must store its normalized local operating-device name separately in the event history.'
);
assertEventHistory($clientAttribution->Disarm() === true, 'The attributed visualization arming test must remain disarmable through another transport.');
$clientHistory = readEventHistory($clientAttribution);
assertEventHistory(
    ($clientHistory[0]['Event'] ?? null) === 'disarmed'
    && ($clientHistory[0]['ClientName'] ?? null) === '',
    'The visualization operating-device context must be cleared immediately after its request.'
);
$clientAttribution->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'main', 'Value' => 'away'],
            'ClientName' => str_repeat('x', 100)
        ],
        JSON_THROW_ON_ERROR
    )
);
$clientHistory = readEventHistory($clientAttribution);
assertEventHistory(
    ($clientHistory[0]['ClientName'] ?? null) === str_repeat('x', 64),
    'The self-declared operating-device name must be bounded before it enters persistent history.'
);
assertEventHistory($clientAttribution->Disarm() === true, 'The bounded-name attribution test must remain disarmable.');
$clientAttribution->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'main', 'Value' => 'away'],
            'ClientName' => ['invalid transport metadata']
        ],
        JSON_THROW_ON_ERROR
    )
);
$clientHistory = readEventHistory($clientAttribution);
assertEventHistory(
    ($clientHistory[0]['Event'] ?? null) === 'armed'
    && ($clientHistory[0]['ClientName'] ?? null) === '',
    'Invalid optional client metadata must be ignored without blocking a security action.'
);

// A visualization-initiated exit delay must retain its device attribution until
// the final arming result, including across ApplyChanges or a restart.
$delayedClient = new OpenHomeAlarm();
$delayedClient->Create();
$delayedClient->TestSetPropertyInteger('ExitDelaySeconds', 10);
$delayedClient->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
$delayedClient->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'main', 'Value' => 'away'],
            'ClientName' => 'PC Büro'
        ],
        JSON_THROW_ON_ERROR
    )
);
$delayedHistory = readEventHistory($delayedClient);
assertEventHistory(
    ($delayedHistory[0]['Event'] ?? null) === 'exit_delay_started'
    && ($delayedHistory[0]['ClientName'] ?? null) === 'PC Büro',
    'Starting a visualization exit delay must retain its operating device.'
);
$pendingClientNames = json_decode(
    $delayedClient->TestAttributeString('PendingArmingClientNames'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
assertEventHistory(
    ($pendingClientNames['main'] ?? null) === 'PC Büro',
    'A delayed visualization arming must persist its operating device for restart-safe completion.'
);

$restartedDelayedClient = new OpenHomeAlarm();
$restartedDelayedClient->Create();
$restartedDelayedClient->TestSetPropertyInteger('ExitDelaySeconds', 10);
$restartedDelayedClient->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
$restartedDelayedClient->TestSetCurrentValue('Mode', 2);
$restartedDelayedClient->TestSetCurrentValue('State', 1);
$restartedDelayedClient->TestSetAttributeInteger('ExitDelayDeadline', time() + 10);
$restartedDelayedClient->TestSetAttributeString(
    'PendingArmingClientNames',
    $delayedClient->TestAttributeString('PendingArmingClientNames')
);
$restartedDelayedClient->ApplyChanges();
$restartedDelayedClient->CompleteExitDelay();
$restartedHistory = readEventHistory($restartedDelayedClient);
assertEventHistory(
    ($restartedHistory[0]['Event'] ?? null) === 'armed'
    && ($restartedHistory[0]['ClientName'] ?? null) === 'PC Büro',
    'Completing a restored exit delay must attribute the armed event to its initiating operating device.'
);
assertEventHistory(
    json_decode(
        $restartedDelayedClient->TestAttributeString('PendingArmingClientNames'),
        true,
        512,
        JSON_THROW_ON_ERROR
    ) === [],
    'Completing an exit delay must clear its pending operating-device attribution.'
);
assertEventHistory(
    $restartedDelayedClient->Disarm() === true,
    'The restored delayed arming test must remain disarmable.'
);
$restartedHistory = readEventHistory($restartedDelayedClient);
assertEventHistory(
    ($restartedHistory[0]['Event'] ?? null) === 'disarmed'
    && ($restartedHistory[0]['ClientName'] ?? null) === '',
    'A completed delayed attribution must not leak into a later unrelated action.'
);

$partitionClient = new OpenHomeAlarm();
$partitionClient->Create();
$partitionClient->TestSetPropertyInteger('ExitDelaySeconds', 10);
$partitionClient->TestSetPropertyString(
    'Partitions',
    '[{"Enabled":true,"ID":"main","Name":"Gesamtanlage","Default":true},{"Enabled":true,"ID":"garage","Name":"Garage","Default":false}]'
);
$partitionClient->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'garage', 'Value' => 'away'],
            'ClientName' => 'Tablet Garage'
        ],
        JSON_THROW_ON_ERROR
    )
);
$partitionClient->TestSetAttributeString(
    'PartitionRuntime',
    json_encode(
        [
            'garage' => [
                'Mode'            => 2,
                'State'           => 1,
                'Deadline'        => time() - 1,
                'DelaySource'     => '',
                'PendingSourceID' => 0,
                'Silent'          => false
            ]
        ],
        JSON_THROW_ON_ERROR
    )
);
$partitionClient->UpdatePartitionRuntime();
$partitionHistory = readEventHistory($partitionClient);
assertEventHistory(
    ($partitionHistory[0]['Event'] ?? null) === 'armed'
    && ($partitionHistory[0]['PartitionID'] ?? null) === 'garage'
    && ($partitionHistory[0]['ClientName'] ?? null) === 'Tablet Garage',
    'A completed partition exit delay must retain the initiating operating device.'
);
assertEventHistory(
    json_decode(
        $partitionClient->TestAttributeString('PendingArmingClientNames'),
        true,
        512,
        JSON_THROW_ON_ERROR
    ) === [],
    'Completing a partition exit delay must clear its pending operating-device attribution.'
);

$testValues[5001] = false;
$cancelledClient = new OpenHomeAlarm();
$cancelledClient->Create();
$cancelledClient->TestSetPropertyInteger('ExitDelaySeconds', 10);
$cancelledClient->TestSetPropertyString(
    'Sensors',
    json_encode([eventHistorySensor(5001, 'Haustür')], JSON_THROW_ON_ERROR)
);
$cancelledClient->RequestAction(
    'ArmPartition',
    json_encode(
        [
            'Transport'  => 'openhomealarm-visualization',
            'Version'    => 1,
            'Value'      => ['PartitionID' => 'main', 'Value' => 'away'],
            'ClientName' => 'Wanddisplay Flur'
        ],
        JSON_THROW_ON_ERROR
    )
);
$testValues[5001] = true;
$cancelledClient->CompleteExitDelay();
$cancelledHistory = readEventHistory($cancelledClient);
assertEventHistory(
    ($cancelledHistory[0]['Event'] ?? null) === 'disarmed'
    && ($cancelledHistory[0]['ClientName'] ?? null) === 'Wanddisplay Flur'
    && ($cancelledHistory[1]['Event'] ?? null) === 'arm_cancelled'
    && ($cancelledHistory[1]['ClientName'] ?? null) === 'Wanddisplay Flur',
    'A failed final readiness check must attribute both cancellation events to the initiating operating device.'
);
assertEventHistory(
    json_decode(
        $cancelledClient->TestAttributeString('PendingArmingClientNames'),
        true,
        512,
        JSON_THROW_ON_ERROR
    ) === [],
    'Cancelling a delayed arming must clear its pending operating-device attribution.'
);
$testValues[5001] = false;

$staleClientAttribution = new OpenHomeAlarm();
$staleClientAttribution->Create();
$staleClientAttribution->TestSetAttributeString(
    'PendingArmingClientNames',
    json_encode(['main' => 'Veraltetes Gerät'], JSON_THROW_ON_ERROR)
);
$staleClientAttribution->ApplyChanges();
assertEventHistory(
    json_decode(
        $staleClientAttribution->TestAttributeString('PendingArmingClientNames'),
        true,
        512,
        JSON_THROW_ON_ERROR
    ) === [],
    'ApplyChanges must discard stale operating-device attribution when no matching exit delay is active.'
);

for ($attempt = 0; $attempt < 110; ++$attempt) {
    assertEventHistory($instance->DisarmWithCode('0000') === false, 'Wrong code attempts used for history limit testing must remain rejected.');
    $instance->TestSetAttributeInteger('DisarmFailedAttempts', 0);
    $instance->TestSetAttributeInteger('DisarmLockoutUntil', 0);
}
$history = readEventHistory($instance);
assertEventHistory(count($history) === 100, 'The persistent event history must be bounded to 100 entries.');
assertEventHistory(
    array_reduce(
        $history,
        static fn (bool $carry, array $entry): bool => $carry && ($entry['Event'] ?? null) === 'disarm_code_rejected',
        true
    ),
    'History trimming must retain complete, valid event records.'
);

fwrite(STDOUT, "OpenHomeAlarm event history checks passed.\n");
