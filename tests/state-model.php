<?php

declare(strict_types=1);

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

class IPSModuleStrict
{
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
assertStateModel(array_keys($variables) === ['Mode', 'State', 'ReadyToArm'], 'Unexpected A1 status variables.');
assertStateModel($variables['Mode']['type'] === 'integer', 'Mode must be an integer variable.');
assertStateModel($variables['State']['type'] === 'integer', 'State must be an integer variable.');
assertStateModel($variables['ReadyToArm']['type'] === 'boolean', 'ReadyToArm must be a boolean variable.');
assertStateModel($variables['Mode']['position'] === 10, 'Mode must use position 10.');
assertStateModel($variables['State']['position'] === 20, 'State must use position 20.');
assertStateModel($variables['ReadyToArm']['position'] === 30, 'ReadyToArm must use position 30.');

$initialValues = $newInstance->TestWrittenValues();
assertStateModel(
    $initialValues === [
        'Mode'       => 0,
        'State'      => 0,
        'ReadyToArm' => true
    ],
    'A new instance must start without an arming mode, disarmed and ready to arm.'
);

$modePresentation = $variables['Mode']['presentation'];
$statePresentation = $variables['State']['presentation'];
$readyPresentation = $variables['ReadyToArm']['presentation'];

assertStateModel(
    ($modePresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'Mode must use the native value presentation.'
);
assertStateModel(
    ($statePresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'State must use the native value presentation.'
);
assertStateModel(
    ($readyPresentation['PRESENTATION'] ?? null) === VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'ReadyToArm must use the native value presentation.'
);
assertStateModel(($modePresentation['INTERVALS_ACTIVE'] ?? false) === true, 'Mode intervals must be active.');
assertStateModel(($statePresentation['INTERVALS_ACTIVE'] ?? false) === true, 'State intervals must be active.');

$modeIntervals = json_decode((string) $modePresentation['INTERVALS'], true, 512, JSON_THROW_ON_ERROR);
$stateIntervals = json_decode((string) $statePresentation['INTERVALS'], true, 512, JSON_THROW_ON_ERROR);
$readyOptions = json_decode((string) $readyPresentation['OPTIONS'], true, 512, JSON_THROW_ON_ERROR);

assertStateModel(
    array_column($modeIntervals, 'ConstantValue') === ['No arming mode', 'Home', 'Away', 'Night'],
    'Mode presentation captions are incomplete.'
);
assertStateModel(
    array_column($stateIntervals, 'ConstantValue') === ['Disarmed', 'Exit delay', 'Armed', 'Entry delay', 'Alarm'],
    'State presentation captions are incomplete.'
);
assertStateModel(
    array_column($readyOptions, 'Caption') === ['Not ready', 'Ready'],
    'ReadyToArm presentation captions are incomplete.'
);
foreach ([...$modeIntervals, ...$stateIntervals] as $interval) {
    assertStateModel(
        array_key_exists('ColorValue', $interval),
        'Every value-presentation interval must contain ColorValue.'
    );
}
foreach ($readyOptions as $option) {
    foreach (['Value', 'Caption', 'IconActive', 'IconValue', 'ColorActive', 'ColorValue'] as $requiredKey) {
        assertStateModel(
            array_key_exists($requiredKey, $option),
            'ReadyToArm option is missing required key ' . $requiredKey . '.'
        );
    }
}

$existingInstance = new OpenHomeAlarm([
    'Mode'       => false,
    'State'      => false,
    'ReadyToArm' => false
]);
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
    'Ready to arm',
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
    'Not ready'
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
