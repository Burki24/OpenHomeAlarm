<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';
const VM_UPDATE = 10603;

/** @var array<int,array<string,mixed>> */
$testVariables = [
    4001 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => ''],
    4002 => ['VariableType' => 0, 'VariableCustomProfile' => '', 'VariableProfile' => '']
];

/** @var array<int,mixed> */
$testValues = [
    4001 => false,
    4002 => false
];

/** @var list<array{actionID:string,parameters:array<string,mixed>}> */
$testActions = [];

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
    global $testActions;

    $testActions[] = [
        'actionID'   => $actionID,
        'parameters' => $parameters
    ];

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

function assertAlarmAction(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

/** @return array<string,mixed> */
function alarmActionSensor(int $variableID, bool $entryDelay): array
{
    return [
        'Enabled'      => true,
        'Name'         => 'Test ' . $variableID,
        'VariableID'   => $variableID,
        'SensorType'   => 0,
        'TriggerValue' => 'true',
        'ArmHome'      => false,
        'ArmAway'      => true,
        'ArmNight'     => false,
        'EntryDelay'   => $entryDelay
    ];
}

/**
 * @param list<array<string,mixed>> $elements
 *
 * @return array<string,mixed>|null
 */
function findAlarmActionFormField(array $elements, string $name): ?array
{
    foreach ($elements as $element) {
        if (!is_array($element)) {
            continue;
        }
        if (($element['name'] ?? null) === $name) {
            return $element;
        }
        if (isset($element['items']) && is_array($element['items'])) {
            $found = findAlarmActionFormField($element['items'], $name);
            if ($found !== null) {
                return $found;
            }
        }
    }

    return null;
}

$alarmAction = json_encode([
    'actionID'   => '{11111111-1111-1111-1111-111111111111}',
    'parameters' => [
        'TARGET'      => 5001,
        'ENVIRONMENT' => 'Default',
        'PARENT'      => 6001,
        'VALUE'       => true
    ]
], JSON_THROW_ON_ERROR);
$disarmAction = json_encode([
    'actionID'   => '{22222222-2222-2222-2222-222222222222}',
    'parameters' => [
        'TARGET'      => 5001,
        'ENVIRONMENT' => 'Default',
        'PARENT'      => 6001,
        'VALUE'       => false
    ]
], JSON_THROW_ON_ERROR);

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$instance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$instance->TestSetPropertyString('AlarmAction', $alarmAction);
$instance->TestSetPropertyString('DisarmAfterAlarmAction', $disarmAction);
$instance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
$instance->TestClearWrittenValues();

assertAlarmAction($instance->ArmAway() === true, 'Away arming must succeed before testing alarm actions.');

global $testValues, $testActions;
$testValues[4001] = true;
$instance->TestClearWrittenValues();
$instance->MessageSink(1, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    ($instance->TestWrittenValues()['State'] ?? null) === 4,
    'An immediate sensor must enter Alarm before its configured action is executed.'
);
assertAlarmAction(count($testActions) === 1, 'Alarm action must run exactly once when Alarm starts.');
assertAlarmAction(
    $testActions[0]['actionID'] === '{11111111-1111-1111-1111-111111111111}'
    && ($testActions[0]['parameters']['VALUE'] ?? null) === true,
    'Alarm action must preserve the action ID and parameters selected by Symcon.'
);

$instance->MessageSink(2, 4001, VM_UPDATE, [true, true, true]);
assertAlarmAction(count($testActions) === 1, 'Further sensor updates during Alarm must not rerun the alarm action.');

$instance->Disarm();
assertAlarmAction(count($testActions) === 2, 'Disarming an active Alarm must run the reset action once.');
assertAlarmAction(
    $testActions[1]['actionID'] === '{22222222-2222-2222-2222-222222222222}'
    && ($testActions[1]['parameters']['VALUE'] ?? null) === false,
    'Reset action must preserve the action ID and parameters selected by Symcon.'
);
assertAlarmAction(
    ($instance->TestWrittenValues()['State'] ?? null) === 0
    && ($instance->TestWrittenValues()['Mode'] ?? null) === 0,
    'Disarming must remain successful independently of the configured reset action.'
);

$instance->Disarm();
assertAlarmAction(count($testActions) === 2, 'Disarming an already disarmed system must not rerun the reset action.');

// The native SelectAction false value represents a valid, deliberately unconfigured optional action.
$testActions = [];
$testValues[4001] = false;
$noActionInstance = new OpenHomeAlarm();
$noActionInstance->Create();
$noActionInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$noActionInstance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$noActionInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
assertAlarmAction($noActionInstance->ArmAway() === true, 'Unconfigured optional-action test must arm successfully.');
$testValues[4001] = true;
$noActionInstance->MessageSink(20, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction($testActions === [], 'A native SelectAction false value must behave as no configured action.');

// Entry-delay completion must use the same central Alarm transition and therefore run the alarm action.
$testActions = [];
$testValues[4001] = false;
$testValues[4002] = false;
$delayedInstance = new OpenHomeAlarm();
$delayedInstance->Create();
$delayedInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$delayedInstance->TestSetPropertyInteger('EntryDelaySeconds', 10);
$delayedInstance->TestSetPropertyString('AlarmAction', $alarmAction);
$delayedInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4002, true)], JSON_THROW_ON_ERROR)
);
$delayedInstance->TestClearWrittenValues();
assertAlarmAction($delayedInstance->ArmAway() === true, 'Delayed alarm action test must arm successfully.');
$testValues[4002] = true;
$delayedInstance->MessageSink(3, 4002, VM_UPDATE, [true, true, false]);
assertAlarmAction(count($testActions) === 0, 'Entry-delay start must not run the alarm action early.');
$delayedInstance->CompleteEntryDelay();
assertAlarmAction(count($testActions) === 1, 'Entry-delay expiry must run the alarm action exactly once.');

// Broken optional action configuration must never prevent the core alarm state transition.
$testActions = [];
$testValues[4001] = false;
$brokenInstance = new OpenHomeAlarm();
$brokenInstance->Create();
$brokenInstance->TestSetPropertyInteger('ExitDelaySeconds', 0);
$brokenInstance->TestSetPropertyInteger('EntryDelaySeconds', 0);
$brokenInstance->TestSetPropertyString('AlarmAction', '{invalid json');
$brokenInstance->TestSetPropertyString(
    'Sensors',
    json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR)
);
$brokenInstance->TestClearWrittenValues();
assertAlarmAction($brokenInstance->ArmAway() === true, 'Broken optional alarm action must not block arming.');
$testValues[4001] = true;
$brokenInstance->MessageSink(4, 4001, VM_UPDATE, [true, true, false]);
assertAlarmAction(
    ($brokenInstance->TestWrittenValues()['State'] ?? null) === 4,
    'Broken optional action configuration must not prevent the Alarm state.'
);
assertAlarmAction($testActions === [], 'Invalid action configuration must not call IPS_RunAction.');

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$alarmPanel = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'ExpansionPanel' && ($element['caption'] ?? null) === 'Alarm actions') {
        $alarmPanel = $element;
        break;
    }
}
assertAlarmAction(is_array($alarmPanel), 'A6 configuration form must contain an Alarm actions panel.');
$actionFields = [];
foreach ($alarmPanel['items'] ?? [] as $item) {
    if (($item['type'] ?? null) === 'SelectAction') {
        $actionFields[$item['name'] ?? ''] = $item;
    }
}
assertAlarmAction(
    isset($actionFields['AlarmAction'], $actionFields['DisarmAfterAlarmAction']),
    'A6 must offer SelectAction fields for alarm start and alarm reset.'
);
assertAlarmAction(
    ($actionFields['AlarmAction']['targetID'] ?? null) === -2
    && ($actionFields['DisarmAfterAlarmAction']['targetID'] ?? null) === -2,
    'A6 SelectAction fields must let the user choose their target.'
);
assertAlarmAction(
    ($actionFields['AlarmAction']['value'] ?? null) === false
    && ($actionFields['DisarmAfterAlarmAction']['value'] ?? null) === false,
    'Optional SelectAction fields must use native false as the no-action value.'
);
assertAlarmAction(
    !array_key_exists('enabled', $actionFields['AlarmAction'])
    && !array_key_exists('visible', $actionFields['AlarmAction']),
    'Optional SelectAction fields must remain native, visible selectors without wrapper switches.'
);

$dynamicFormInstance = new OpenHomeAlarm();
$dynamicFormInstance->Create();
$dynamicForm = json_decode($dynamicFormInstance->GetConfigurationForm(), true, 512, JSON_THROW_ON_ERROR);
$dynamicAlarmAction = findAlarmActionFormField($dynamicForm['elements'] ?? [], 'AlarmAction');
assertAlarmAction(
    is_array($dynamicAlarmAction)
    && ($dynamicAlarmAction['type'] ?? null) === 'SelectAction'
    && ($dynamicAlarmAction['value'] ?? null) === false,
    'GetConfigurationForm must preserve the native optional SelectAction no-action value.'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach (['Alarm actions', 'On alarm', 'On disarm after alarm'] as $translationKey) {
    assertAlarmAction(isset($translations[$translationKey]), 'Missing German translation for ' . $translationKey . '.');
}

fwrite(STDOUT, "OpenHomeAlarm alarm action checks passed.\n");
