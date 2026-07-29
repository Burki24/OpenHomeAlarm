<?php

declare(strict_types=1);

require_once __DIR__ . '/symcon-runtime.php';

const VARIABLE_PRESENTATION_VALUE_PRESENTATION = '{3319437D-7CDE-699D-750A-3C6A3841FA75}';

class IPSModuleStrict
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /** @var array<string,int|string> */
    private array $attributes = [];

    /** @var array<string,mixed> */
    private array $currentValues = [];

    /** @var array<string,mixed> */
    private array $writtenValues = [];

    /** @var list<string> */
    private array $debugMessages = [];

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

    public function TestReadAttributeInteger(string $name): int
    {
        $value = $this->attributes[$name] ?? 0;

        return is_int($value) ? $value : 0;
    }

    public function TestSetCurrentValue(string $ident, mixed $value): void
    {
        $this->currentValues[$ident] = $value;
    }

    public function TestClearWrittenValues(): void
    {
        $this->writtenValues = [];
    }

    /** @return array<string,mixed> */
    public function TestWrittenValues(): array
    {
        return $this->writtenValues;
    }

    /** @return list<string> */
    public function TestDebugMessages(): array
    {
        return $this->debugMessages;
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
        $this->attributes[$name] = $this->attributes[$name] ?? $default;
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

    protected function WriteAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    protected function WriteAttributeString(string $name, string $value): void
    {
        $this->attributes[$name] = $value;
    }
    protected function ReadAttributeString(string $name): string
    {
        $value = $this->attributes[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    protected function RegisterTimer(string $name, int $interval, string $script): bool
    {
        return true;
    }

    protected function SetTimerInterval(string $name, int $interval): bool
    {
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

    protected function SendDebug(string $message, string $data, int $format): void
    {
        $this->debugMessages[] = $message . ': ' . $data;
    }
}

function assertDisarmCode(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once dirname(__DIR__) . '/OpenHomeAlarm/module.php';

$instance = new OpenHomeAlarm();
$instance->Create();
$instance->TestSetPropertyString('DisarmCode', '1234');
$instance->TestSetCurrentValue('Mode', 2);
$instance->TestSetCurrentValue('State', 2);
$instance->TestClearWrittenValues();

assertDisarmCode(
    $instance->DisarmWithCode('9999') === false,
    'A wrong disarm code must be rejected.'
);
assertDisarmCode(
    $instance->TestWrittenValues() === [],
    'A wrong disarm code must not change Mode or State.'
);
assertDisarmCode(
    !str_contains(implode("\n", $instance->TestDebugMessages()), '9999'),
    'The submitted disarm code must never be written to debug output.'
);
assertDisarmCode(
    $instance->TestReadAttributeInteger('DisarmFailedAttempts') === 1,
    'A wrong disarm code must increment the persistent failed-attempt counter.'
);

assertDisarmCode(
    $instance->DisarmWithCode('1234') === true,
    'The configured disarm code must be accepted.'
);
assertDisarmCode(
    $instance->TestWrittenValues() === ['State' => 0, 'Mode' => 0],
    'A valid disarm code must disarm the system and clear the arming mode.'
);
assertDisarmCode(
    $instance->TestReadAttributeInteger('DisarmFailedAttempts') === 0
        && $instance->TestReadAttributeInteger('DisarmLockoutUntil') === 0,
    'A successful disarm must reset the code-protection state.'
);

$lockedInstance = new OpenHomeAlarm();
$lockedInstance->Create();
$lockedInstance->TestSetPropertyString('DisarmCode', '2468');
$lockedInstance->TestSetPropertyInteger('DisarmMaxAttempts', 3);
$lockedInstance->TestSetPropertyInteger('DisarmLockoutSeconds', 120);
$lockedInstance->TestSetCurrentValue('Mode', 2);
$lockedInstance->TestSetCurrentValue('State', 2);
$lockedInstance->TestClearWrittenValues();
assertDisarmCode(
    $lockedInstance->DisarmWithCode('0000') === false
        && $lockedInstance->DisarmWithCode('1111') === false
        && $lockedInstance->DisarmWithCode('2222') === false,
    'The configured number of wrong codes must be rejected.'
);
$lockoutUntil = $lockedInstance->TestReadAttributeInteger('DisarmLockoutUntil');
assertDisarmCode(
    $lockedInstance->TestReadAttributeInteger('DisarmFailedAttempts') === 3
        && $lockoutUntil >= time() + 118,
    'The final configured failed attempt must persist a temporary lockout.'
);
assertDisarmCode(
    $lockedInstance->DisarmWithCode('2468') === false
        && $lockedInstance->TestWrittenValues() === [],
    'Even the correct code must be rejected while code entry is locked.'
);
$lockedControlState = json_decode($lockedInstance->GetControlState(), true, 512, JSON_THROW_ON_ERROR);
assertDisarmCode(
    ($lockedControlState['CodeProtection']['Locked'] ?? null) === true
        && ($lockedControlState['CodeProtection']['RemainingAttempts'] ?? null) === 0
        && ($lockedControlState['CodeProtection']['LockoutRemaining'] ?? 0) > 0,
    'The control API must expose the active lockout without exposing a code.'
);
assertDisarmCode(
    !str_contains(json_encode($lockedControlState, JSON_THROW_ON_ERROR), '2468'),
    'The control API must never expose the configured disarm code.'
);
$lockedEvents = json_decode($lockedInstance->GetEventHistory(), true, 512, JSON_THROW_ON_ERROR);
$lockedEventNames = array_column($lockedEvents, 'Event');
assertDisarmCode(
    count(array_filter(
        $lockedEventNames,
        static fn (mixed $event): bool => $event === 'disarm_code_rejected'
    )) === 3
        && in_array('disarm_code_locked', $lockedEventNames, true),
    'Rejected codes and the resulting lockout must be recorded without their values.'
);
assertDisarmCode(
    !str_contains(json_encode($lockedEvents, JSON_THROW_ON_ERROR), '0000')
        && !str_contains(json_encode($lockedEvents, JSON_THROW_ON_ERROR), '1111')
        && !str_contains(json_encode($lockedEvents, JSON_THROW_ON_ERROR), '2222'),
    'Submitted disarm codes must not be stored in the event history.'
);

$lockedInstance->TestSetAttributeInteger('DisarmLockoutUntil', time() - 1);
assertDisarmCode(
    $lockedInstance->DisarmWithCode('2468') === true,
    'The correct code must be accepted after the temporary lockout expires.'
);
assertDisarmCode(
    $lockedInstance->TestReadAttributeInteger('DisarmFailedAttempts') === 0
        && $lockedInstance->TestReadAttributeInteger('DisarmLockoutUntil') === 0,
    'An expired lockout must be cleared persistently.'
);

$disabledInstance = new OpenHomeAlarm();
$disabledInstance->Create();
$disabledInstance->TestSetCurrentValue('Mode', 3);
$disabledInstance->TestSetCurrentValue('State', 2);
$disabledInstance->TestClearWrittenValues();
assertDisarmCode(
    $disabledInstance->DisarmWithCode('anything') === true,
    'An empty configured code must keep code validation disabled.'
);
assertDisarmCode(
    $disabledInstance->TestWrittenValues() === ['State' => 0, 'Mode' => 0],
    'Disabled code validation must still use the normal disarm path.'
);

$invalidInstance = new OpenHomeAlarm();
$invalidInstance->Create();
$invalidInstance->TestSetPropertyString('DisarmCode', '12ab');
$invalidInstance->TestSetCurrentValue('Mode', 1);
$invalidInstance->TestSetCurrentValue('State', 2);
$invalidInstance->TestClearWrittenValues();
assertDisarmCode(
    $invalidInstance->DisarmWithCode('12ab') === false,
    'An invalid configured code must fail closed.'
);
assertDisarmCode(
    $invalidInstance->TestWrittenValues() === [],
    'An invalid configured code must not disarm the system.'
);

$form = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$codePanel = null;
foreach ($form['elements'] ?? [] as $element) {
    if (($element['type'] ?? null) === 'ExpansionPanel' && ($element['caption'] ?? null) === 'Code protection') {
        $codePanel = $element;
        break;
    }
}
assertDisarmCode(is_array($codePanel), 'The configuration form must contain the Code protection panel.');

$codeField = null;
$maximumAttemptsField = null;
$lockoutSecondsField = null;
foreach ($codePanel['items'] ?? [] as $item) {
    if (($item['name'] ?? null) === 'DisarmCode') {
        $codeField = $item;
    }
    foreach ($item['items'] ?? [] as $nestedItem) {
        if (($nestedItem['name'] ?? null) === 'DisarmMaxAttempts') {
            $maximumAttemptsField = $nestedItem;
        } elseif (($nestedItem['name'] ?? null) === 'DisarmLockoutSeconds') {
            $lockoutSecondsField = $nestedItem;
        }
    }
}
assertDisarmCode(
    is_array($codeField) && ($codeField['type'] ?? null) === 'PasswordTextBox',
    'DisarmCode must use a PasswordTextBox.'
);
assertDisarmCode(
    ($codeField['validate'] ?? null) === '^(?:[0-9]{4,8})?$',
    'DisarmCode must accept only an empty value or 4 to 8 digits.'
);
assertDisarmCode(
    ($maximumAttemptsField['minimum'] ?? null) === 1
        && ($maximumAttemptsField['maximum'] ?? null) === 20,
    'The failed-attempt limit must be bounded in the configuration form.'
);
assertDisarmCode(
    ($lockoutSecondsField['minimum'] ?? null) === 1
        && ($lockoutSecondsField['maximum'] ?? null) === 3600,
    'The temporary lockout duration must be bounded in the configuration form.'
);

$locale = json_decode(
    (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/locale.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$translations = $locale['translations']['de'] ?? [];
foreach ([
    'Code protection',
    'Configure an optional numeric code for disarming from user-facing controls.',
    'Disarm code (4-8 digits)',
    'Maximum failed attempts',
    'Temporary lockout (seconds)',
    'Leave empty to allow disarming without a code.',
    'After too many failed attempts, code entry is blocked temporarily. Trusted automations can still use Disarm().',
    'Code entry is temporarily locked.'
] as $translationKey) {
    assertDisarmCode(
        isset($translations[$translationKey]),
        'Missing German translation for ' . $translationKey . '.'
    );
}

$moduleSource = (string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
assertDisarmCode(
    str_contains($moduleSource, 'hash_equals('),
    'Disarm code comparison must use hash_equals().'
);
assertDisarmCode(
    str_contains($moduleSource, 'public function DisarmWithCode(string $code): bool'),
    'The public DisarmWithCode() API is missing.'
);

fwrite(STDOUT, "OpenHomeAlarm disarm code checks passed.\n");
