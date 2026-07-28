<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

class IPSModuleStrict
{
    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,bool> */
    private array $creationResults;

    /** @var array<string,array<string,mixed>> */
    private array $registeredVariables = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

    /**
     * @param array<string,bool> $creationResults
     */
    public function __construct(array $creationResults = [])
    {
        $this->creationResults = $creationResults;
    }

    public function Create(): void
    {
    }

    public function Destroy(): void
    {
    }

    public function ApplyChanges(): void
    {
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function TestRegisteredVariables(): array
    {
        return $this->registeredVariables;
    }

    /**
     * @return array<string,mixed>
     */
    public function TestWrittenValues(): array
    {
        return $this->writtenValues;
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
    }

    protected function RegisterPropertyInteger(string $name, int $default): void
    {
    }

    protected function RegisterAttributeInteger(string $name, int $default): void
    {
    }

    protected function RegisterAttributeString(string $name, string $default): void
    {
        if (!array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $default;
        }
    }
    protected function ReadAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function WriteAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function RegisterTimer(string $name, int $interval, string $script): bool
    {
        return true;
    }

    protected function RegisterVariableInteger(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'integer',
            'name'         => $name,
            'presentation' => $presentation,
            'position'     => $position
        ];

        return $this->creationResults[$ident] ?? true;
    }

    protected function RegisterVariableBoolean(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'boolean',
            'name'         => $name,
            'presentation' => $presentation,
            'position'     => $position
        ];

        return $this->creationResults[$ident] ?? true;
    }

    protected function RegisterVariableString(string $ident, string $name, array $presentation, int $position): bool
    {
        $this->registeredVariables[$ident] = [
            'type'         => 'string',
            'name'         => $name,
            'presentation' => $presentation,
            'position'     => $position
        ];

        return $this->creationResults[$ident] ?? true;
    }

    protected function SetValue(string $ident, mixed $value): void
    {
        $this->writtenValues[$ident] = $value;
    }

    protected function Translate(string $text): string
    {
        return $text;
    }
}

function assertStateModel(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

$newInstance = new OpenHomeAlarm();
$newInstance->Create();

$variables = $newInstance->TestRegisteredVariables();
assertStateModel(
    array_keys($variables) === ['Mode', 'State', 'DelayRemaining', 'DelaySource', 'AlarmOutputActive', 'ReadyToArm', 'ReadyHome', 'ReadyAway', 'ReadyNight', 'BlockingHomeSensors', 'BlockingAwaySensors', 'BlockingNightSensors', 'BypassedSensors', 'AlarmMemory', 'LastAlarmSource', 'LastAlarmTime', 'SystemFault', 'ActiveFaults', 'BlockingFaults', 'LastFaultSource', 'LastFaultTime'],
    'Unexpected status variables.'
);
assertStateModel($variables['Mode']['type'] === 'integer', 'Mode must be an integer variable.');
assertStateModel($variables['State']['type'] === 'integer', 'State must be an integer variable.');
assertStateModel($variables['DelayRemaining']['type'] === 'integer', 'DelayRemaining must be an integer variable.');
assertStateModel($variables['DelaySource']['type'] === 'string', 'DelaySource must be a string variable.');
assertStateModel($variables['AlarmOutputActive']['type'] === 'boolean', 'AlarmOutputActive must be a boolean variable.');
assertStateModel($variables['ReadyToArm']['type'] === 'boolean', 'ReadyToArm must be a boolean variable.');
assertStateModel($variables['ReadyHome']['type'] === 'boolean', 'ReadyHome must be a boolean variable.');
assertStateModel($variables['ReadyAway']['type'] === 'boolean', 'ReadyAway must be a boolean variable.');
assertStateModel($variables['ReadyNight']['type'] === 'boolean', 'ReadyNight must be a boolean variable.');
assertStateModel($variables['BlockingHomeSensors']['type'] === 'string', 'BlockingHomeSensors must be a string variable.');
assertStateModel($variables['BlockingAwaySensors']['type'] === 'string', 'BlockingAwaySensors must be a string variable.');
assertStateModel($variables['BlockingNightSensors']['type'] === 'string', 'BlockingNightSensors must be a string variable.');
assertStateModel($variables['BypassedSensors']['type'] === 'string', 'BypassedSensors must be a string variable.');
assertStateModel($variables['AlarmMemory']['type'] === 'boolean', 'AlarmMemory must be a boolean variable.');
assertStateModel($variables['LastAlarmSource']['type'] === 'string', 'LastAlarmSource must be a string variable.');
assertStateModel($variables['LastAlarmTime']['type'] === 'string', 'LastAlarmTime must be a string variable.');
assertStateModel($variables['SystemFault']['type'] === 'boolean', 'SystemFault must be a boolean variable.');
assertStateModel($variables['ActiveFaults']['type'] === 'string', 'ActiveFaults must be a string variable.');
assertStateModel($variables['BlockingFaults']['type'] === 'string', 'BlockingFaults must be a string variable.');
assertStateModel($variables['LastFaultSource']['type'] === 'string', 'LastFaultSource must be a string variable.');
assertStateModel($variables['LastFaultTime']['type'] === 'string', 'LastFaultTime must be a string variable.');
assertStateModel($variables['Mode']['position'] === 10, 'Mode must use position 10.');
assertStateModel($variables['State']['position'] === 20, 'State must use position 20.');
assertStateModel($variables['DelayRemaining']['position'] === 21, 'DelayRemaining must use position 21.');
assertStateModel($variables['DelaySource']['position'] === 22, 'DelaySource must use position 22.');
assertStateModel($variables['AlarmOutputActive']['position'] === 23, 'AlarmOutputActive must use position 23.');
assertStateModel($variables['ReadyToArm']['position'] === 30, 'ReadyToArm must use position 30.');
assertStateModel($variables['ReadyHome']['position'] === 31, 'ReadyHome must use position 31.');
assertStateModel($variables['ReadyAway']['position'] === 32, 'ReadyAway must use position 32.');
assertStateModel($variables['ReadyNight']['position'] === 33, 'ReadyNight must use position 33.');
assertStateModel($variables['BlockingHomeSensors']['position'] === 34, 'BlockingHomeSensors must use position 34.');
assertStateModel($variables['BlockingAwaySensors']['position'] === 35, 'BlockingAwaySensors must use position 35.');
assertStateModel($variables['BlockingNightSensors']['position'] === 36, 'BlockingNightSensors must use position 36.');
assertStateModel($variables['BypassedSensors']['position'] === 37, 'BypassedSensors must use position 37.');
assertStateModel($variables['AlarmMemory']['position'] === 40, 'AlarmMemory must use position 40.');
assertStateModel($variables['LastAlarmSource']['position'] === 50, 'LastAlarmSource must use position 50.');
assertStateModel($variables['LastAlarmTime']['position'] === 60, 'LastAlarmTime must use position 60.');
assertStateModel($variables['SystemFault']['position'] === 70, 'SystemFault must use position 70.');
assertStateModel($variables['ActiveFaults']['position'] === 71, 'ActiveFaults must use position 71.');
assertStateModel($variables['BlockingFaults']['position'] === 72, 'BlockingFaults must use position 72.');
assertStateModel($variables['LastFaultSource']['position'] === 73, 'LastFaultSource must use position 73.');
assertStateModel($variables['LastFaultTime']['position'] === 74, 'LastFaultTime must use position 74.');

$initialValues = $newInstance->TestWrittenValues();
assertStateModel(
    $initialValues === [
        'Mode'                 => 0,
        'State'                => 0,
        'DelayRemaining'       => 0,
        'DelaySource'          => '',
        'AlarmOutputActive'    => false,
        'ReadyToArm'           => true,
        'ReadyHome'            => true,
        'ReadyAway'            => true,
        'ReadyNight'           => true,
        'BlockingHomeSensors'  => '',
        'BlockingAwaySensors'  => '',
        'BlockingNightSensors' => '',
        'BypassedSensors'      => '',
        'AlarmMemory'          => false,
        'LastAlarmSource'      => '',
        'LastAlarmTime'        => '',
        'SystemFault'          => false,
        'ActiveFaults'         => '',
        'BlockingFaults'       => '',
        'LastFaultSource'      => '',
        'LastFaultTime'        => ''
    ],
    'A new instance must start without an arming mode, disarmed and ready to arm.'
);

$modePresentation = $variables['Mode']['presentation'];
$statePresentation = $variables['State']['presentation'];
$delayRemainingPresentation = $variables['DelayRemaining']['presentation'];
$alarmOutputPresentation = $variables['AlarmOutputActive']['presentation'];
$readyPresentation = $variables['ReadyToArm']['presentation'];
$readyHomePresentation = $variables['ReadyHome']['presentation'];
$readyAwayPresentation = $variables['ReadyAway']['presentation'];
$readyNightPresentation = $variables['ReadyNight']['presentation'];
$alarmMemoryPresentation = $variables['AlarmMemory']['presentation'];
$systemFaultPresentation = $variables['SystemFault']['presentation'];

assertStateModel(
    ($modePresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'Mode must use the native value presentation.'
);
assertStateModel(
    ($statePresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'State must use the native value presentation.'
);
assertStateModel(
    ($delayRemainingPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION
    && ($delayRemainingPresentation['SUFFIX'] ?? null) === ' s',
    'DelayRemaining must use a native seconds value presentation.'
);
assertStateModel(
    ($alarmOutputPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'AlarmOutputActive must use the native value presentation.'
);
assertStateModel(
    ($readyPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'ReadyToArm must use the native value presentation.'
);
foreach ([$readyHomePresentation, $readyAwayPresentation, $readyNightPresentation] as $modeReadinessPresentation) {
    assertStateModel(
        ($modeReadinessPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
        'Mode-specific readiness variables must use the native value presentation.'
    );
}
assertStateModel(
    ($alarmMemoryPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'AlarmMemory must use the native value presentation.'
);
assertStateModel(
    ($systemFaultPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'SystemFault must use the native value presentation.'
);
assertStateModel(($modePresentation['INTERVALS_ACTIVE'] ?? false) === true, 'Mode intervals must be active.');
assertStateModel(($statePresentation['INTERVALS_ACTIVE'] ?? false) === true, 'State intervals must be active.');

$modeIntervals = json_decode((string) $modePresentation['INTERVALS'], true, 512, JSON_THROW_ON_ERROR);
$stateIntervals = json_decode((string) $statePresentation['INTERVALS'], true, 512, JSON_THROW_ON_ERROR);
$alarmOutputOptions = json_decode((string) $alarmOutputPresentation['OPTIONS'], true, 512, JSON_THROW_ON_ERROR);
$readyOptions = json_decode((string) $readyPresentation['OPTIONS'], true, 512, JSON_THROW_ON_ERROR);
$alarmMemoryOptions = json_decode((string) $alarmMemoryPresentation['OPTIONS'], true, 512, JSON_THROW_ON_ERROR);
$systemFaultOptions = json_decode((string) $systemFaultPresentation['OPTIONS'], true, 512, JSON_THROW_ON_ERROR);

assertStateModel(
    array_column($modeIntervals, 'ConstantValue') === ['No arming mode', 'Home', 'Away', 'Night'],
    'Mode presentation captions are incomplete.'
);
assertStateModel(
    array_column($stateIntervals, 'ConstantValue') === ['Disarmed', 'Exit delay', 'Armed', 'Entry delay', 'Alarm'],
    'State presentation captions are incomplete.'
);
assertStateModel(
    array_column($alarmOutputOptions, 'Caption') === ['Alarm output inactive', 'Alarm output active'],
    'AlarmOutputActive presentation captions are incomplete.'
);
assertStateModel(
    array_column($readyOptions, 'Caption') === ['Not ready', 'Ready'],
    'ReadyToArm presentation captions are incomplete.'
);
assertStateModel(
    array_column($alarmMemoryOptions, 'Caption') === ['No alarm stored', 'Alarm stored'],
    'AlarmMemory presentation captions are incomplete.'
);
assertStateModel(
    array_column($systemFaultOptions, 'Caption') === ['No system fault', 'System fault active'],
    'SystemFault presentation captions are incomplete.'
);
foreach ([...$modeIntervals, ...$stateIntervals] as $interval) {
    assertStateModel(
        array_key_exists('ColorValue', $interval),
        'Every value-presentation interval must contain ColorValue.'
    );
}
foreach ([...$alarmOutputOptions, ...$readyOptions, ...$alarmMemoryOptions, ...$systemFaultOptions] as $option) {
    foreach (['Value', 'Caption', 'IconActive', 'IconValue', 'ColorActive', 'ColorValue'] as $requiredKey) {
        assertStateModel(
            array_key_exists($requiredKey, $option),
            'ReadyToArm option is missing required key ' . $requiredKey . '.'
        );
    }
}

$existingInstance = new OpenHomeAlarm(
    [
        'Mode'                 => false,
        'State'                => false,
        'DelayRemaining'       => false,
        'DelaySource'          => false,
        'AlarmOutputActive'    => false,
        'ReadyToArm'           => false,
        'ReadyHome'            => false,
        'ReadyAway'            => false,
        'ReadyNight'           => false,
        'BlockingHomeSensors'  => false,
        'BlockingAwaySensors'  => false,
        'BlockingNightSensors' => false,
        'BypassedSensors'      => false,
        'AlarmMemory'          => false,
        'LastAlarmSource'      => false,
        'LastAlarmTime'        => false,
        'SystemFault'          => false,
        'ActiveFaults'         => false,
        'BlockingFaults'       => false,
        'LastFaultSource'      => false,
        'LastFaultTime'        => false
    ]
);
$existingInstance->Create();
assertStateModel(
    $existingInstance->TestWrittenValues() === [],
    'Existing operational state must not be reset during Create().'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Mode',
    'State',
    'Delay remaining',
    'Delay source',
    'Alarm output active',
    'Alarm output inactive',
    'Ready to arm',
    'Ready Home',
    'Ready Away',
    'Ready Night',
    'Blocking sensors Home',
    'Blocking sensors Away',
    'Blocking sensors Night',
    'Bypassed sensors',
    'No arming mode',
    'Home',
    'Away',
    'Night',
    'Disarmed',
    'Exit delay',
    'Armed',
    'Entry delay',
    'Alarm',
    'Ready',
    'Not ready',
    'Alarm memory',
    'No alarm stored',
    'Alarm stored',
    'Last alarm source',
    'Last alarm time',
    'System fault',
    'No system fault',
    'System fault active',
    'Active faults',
    'Blocking faults',
    'Last fault source',
    'Last fault time'
] as $translationKey) {
    assertStateModel(
        isset($translations[$translationKey]),
        'Missing German translation for ' . $translationKey . '.'
    );
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertStateModel(!str_contains($moduleSource, 'EnableAction('), 'A1 status variables must remain read-only.');
assertStateModel(str_contains($moduleSource, 'MODE_NONE = 0'), 'MODE_NONE is missing.');
assertStateModel(str_contains($moduleSource, 'MODE_HOME = 1'), 'MODE_HOME is missing.');
assertStateModel(str_contains($moduleSource, 'MODE_AWAY = 2'), 'MODE_AWAY is missing.');
assertStateModel(str_contains($moduleSource, 'MODE_NIGHT = 3'), 'MODE_NIGHT is missing.');
assertStateModel(str_contains($moduleSource, 'STATE_DISARMED = 0'), 'STATE_DISARMED is missing.');
assertStateModel(str_contains($moduleSource, 'STATE_EXIT_DELAY = 1'), 'STATE_EXIT_DELAY is missing.');
assertStateModel(str_contains($moduleSource, 'STATE_ARMED = 2'), 'STATE_ARMED is missing.');
assertStateModel(str_contains($moduleSource, 'STATE_ENTRY_DELAY = 3'), 'STATE_ENTRY_DELAY is missing.');
assertStateModel(str_contains($moduleSource, 'STATE_ALARM = 4'), 'STATE_ALARM is missing.');

$setMode = new ReflectionMethod(OpenHomeAlarm::class, 'SetAlarmMode');
$setState = new ReflectionMethod(OpenHomeAlarm::class, 'SetAlarmState');

try {
    $setMode->invoke($newInstance, 99);
    throw new RuntimeException('Invalid alarm modes must be rejected.');
} catch (InvalidArgumentException) {
}

try {
    $setState->invoke($newInstance, 99);
    throw new RuntimeException('Invalid alarm states must be rejected.');
} catch (InvalidArgumentException) {
}

fwrite(STDOUT, "OpenHomeAlarm state model checks passed.\n");
