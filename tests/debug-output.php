<?php

declare(strict_types=1);

// Reuse the real module and established alarm/action fixtures, including their regression checks.
require_once __DIR__ . '/alarm-actions.php';

final class DebugOutputAlarm extends OpenHomeAlarm
{
    public array $debugEntries = [];
    public array $visualizationPayloads = [];
    public bool $failDebug = false;
    public string $pushoverResponse = '{"status":1}';

    public function EmitLegacyDebug(): void
    {
        $this->SendDebug('Asset failure', 'Visualization asset could not be loaded: token=private-helper-token pin=817263', 0);
    }

    protected function LoadConfigurationForm(): array
    {
        // The production helper resolves the concrete class directory; this test subclass lives in tests/.
        return json_decode((string) file_get_contents(dirname(__DIR__) . '/OpenHomeAlarm/form.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function VisualizationAssetDirectory(): string
    {
        // The production helper resolves the concrete class directory; this test subclass lives in tests/.
        return dirname(__DIR__) . '/OpenHomeAlarm/visualization';
    }

    protected function SendDebug(string $message, string $data, int $format): bool
    {
        if (!str_starts_with($message, 'OpenHomeAlarm | ')) {
            return parent::SendDebug($message, $data, $format);
        }
        if ($this->failDebug) {
            throw new RuntimeException('Debug transport unavailable.');
        }
        $this->debugEntries[] = ['category' => $message, 'data' => json_decode($data, true, 512, JSON_THROW_ON_ERROR)];
        return parent::SendDebug($message, $data, $format);
    }

    protected function PerformPushoverRequest(string $url, array $parameters): string
    {
        return $this->pushoverResponse;
    }

    protected function UpdateVisualizationValue(mixed $data): bool
    {
        $this->visualizationPayloads[] = $data;
        return true;
    }
}

function assertDebugOutput(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function debugOutputText(DebugOutputAlarm $alarm): string
{
    return json_encode($alarm->debugEntries, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$debugAlarm = new DebugOutputAlarm();
$debugAlarm->InstanceID = 12345;
$debugAlarm->Create();
$debugAlarm->TestSetPropertyInteger('ExitDelaySeconds', 0);
$debugAlarm->TestSetPropertyString('Sensors', json_encode([alarmActionSensor(4001, false)], JSON_THROW_ON_ERROR));
$testValues[4001] = false;
$debugAlarm->debugEntries = [];
$actionsBefore = count($testActions);
assertDebugOutput($debugAlarm->ArmAway() === true, 'Tracing must preserve successful arming.');
assertDebugOutput(count($testActions) === $actionsBefore, 'Tracing must not invent external actions.');
$entries = $debugAlarm->debugEntries;
$rootOperation = $entries[0]['data']['Vorgang'];
assertDebugOutput($rootOperation !== '', 'Each operation needs a correlation ID.');
assertDebugOutput(count(array_unique(array_column(array_column($entries, 'data'), 'Vorgang'))) === 1, 'Nested calls must share the root operation.');
$calls = [];
$sourceLines = file(dirname(__DIR__) . '/OpenHomeAlarm/module.php');
foreach ($entries as $entry) {
    assertDebugOutput($entry['data']['Instanz'] === 12345, 'Every entry must identify the instance.');
    assertDebugOutput(preg_match('/^OpenHomeAlarm \| (START|ENDE|AKTION|SENDEN|EMPFANG|FEHLER) \| (\w+):(\d+)$/', $entry['category'], $location) === 1, 'Every entry needs a stable category, function and line.');
    $sourceLine = $sourceLines[(int) $location[3] - 1] ?? '';
    assertDebugOutput(
        str_contains($sourceLine, 'AlarmDebug('),
        'The reported line must point at the actual instrumentation: ' . $entry['category'] . ' -> ' . trim($sourceLine)
    );
    if ($location[1] === 'START') {
        $calls[] = [$location[2], $entry['data']['Aufruf']];
    } elseif ($location[1] === 'ENDE') {
        assertDebugOutput(array_pop($calls) === [$location[2], $entry['data']['Aufruf']], 'Function starts and ends must balance in stack order.');
        assertDebugOutput(isset($entry['data']['Bezug']['Ergebnis']), 'Every function end must contain its result.');
    }
}
assertDebugOutput($calls === [], 'No function may remain open after a command.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Bereich scharfgeschaltet.'), 'The actual state transition must be understandable in German.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Statuswert an Symcon übergeben.'), 'Written values must be visible.');
assertDebugOutput(is_string(end($debugAlarm->visualizationPayloads)), 'Visualization tracing must preserve scalar JSON delivery.');

$debugAlarm->debugEntries = [];
$testValues[4001] = true;
$debugAlarm->MessageSink(time(), 4001, VM_UPDATE, [true, true, false]);
$received = array_values(array_filter($debugAlarm->debugEntries, static fn (array $entry): bool => ($entry['data']['Bezug']['Variable'] ?? null) === 4001));
assertDebugOutput($received !== [] && in_array(true, array_column(array_column(array_column($received, 'data'), 'Bezug'), 'Wert'), true), 'Received sensor values must identify source and value.');

$debugAlarm->Disarm();
$debugAlarm->debugEntries = [];
assertDebugOutput($debugAlarm->ArmAway() === false, 'Active sensor must still block arming.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Scharfschaltung durch Sensoren oder Störungen blockiert.'), 'Rejected arming needs its domain cause.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Nein / abgelehnt oder fehlgeschlagen.'), 'Rejected commands must end with a negative result.');
assertDebugOutput($debugAlarm->debugEntries[0]['data']['Vorgang'] !== $rootOperation, 'Independent commands need independent correlation IDs.');

$debugAlarm->debugEntries = [];
try {
    $debugAlarm->RequestAction('UnknownAction', null);
    throw new RuntimeException('Expected unknown action to throw.');
} catch (InvalidArgumentException) {
}
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Durch Ausnahme abgebrochen.'), 'Thrown failures must retain their exception and close the trace.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'InvalidArgumentException'), 'Failures must identify the exception type.');
$debugAlarm->debugEntries = [];
$testValues[4001] = false;
assertDebugOutput($debugAlarm->ArmAway() === true, 'A failed traced operation must not prevent the next command.');
assertDebugOutput($debugAlarm->debugEntries[0]['data']['Aufruf'] === 1, 'Exception recovery must clear the debug stack.');
$debugAlarm->Disarm();

$debugAlarm->TestSetPropertyString('DisarmCode', '817263');
$debugAlarm->debugEntries = [];
assertDebugOutput($debugAlarm->DisarmWithCode('926374') === false, 'Wrong codes must remain rejected.');
$debugText = debugOutputText($debugAlarm);
assertDebugOutput(!str_contains($debugText, '817263') && !str_contains($debugText, '926374'), 'Neither stored nor submitted codes may enter debug output.');
assertDebugOutput(str_contains($debugText, 'Der eingegebene Code wurde abgelehnt'), 'Code rejection must be explained in German.');

$debugAlarm->TestSetPropertyString('PushoverApplicationToken', str_repeat('t', 30));
$debugAlarm->TestSetPropertyString('PushoverUserKey', str_repeat('u', 30));
$debugAlarm->debugEntries = [];
assertDebugOutput($debugAlarm->TestPushover() === true, 'A confirmed Pushover request must remain successful.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Pushover hat die Anfrage bestätigt.'), 'Transport acceptance and application confirmation must be distinguishable.');
$debugAlarm->pushoverResponse = '{"status":0,"errors":["token=' . str_repeat('t', 30) . ' pin=817263"]}';
$debugAlarm->debugEntries = [];
assertDebugOutput($debugAlarm->TestPushover() === false, 'Rejected Pushover responses must remain failures.');
$debugText = debugOutputText($debugAlarm);
assertDebugOutput(str_contains($debugText, 'Pushover hat die Anfrage fachlich abgelehnt.'), 'Remote rejection needs a German cause.');
assertDebugOutput(!str_contains($debugText, str_repeat('t', 30)) && !str_contains($debugText, str_repeat('u', 30)) && !str_contains($debugText, '817263'), 'Remote response bodies and credentials must not leak through exceptions.');

$debugAlarm->TestSetPropertyString('FullyDisarmedAction', json_encode(['actionID' => '{TEST-ACTION}', 'parameters' => ['TARGET' => 45678, 'VALUE' => 817263, 'token' => 'private-token']], JSON_THROW_ON_ERROR));
$runner = new ReflectionMethod(OpenHomeAlarm::class, 'RunConfiguredAction');
$debugAlarm->debugEntries = [];
$actionsBefore = count($testActions);
assertDebugOutput($runner->invoke($debugAlarm, 'FullyDisarmedAction') === true, 'Action execution must preserve its result.');
assertDebugOutput(count($testActions) === $actionsBefore + 1 && end($testActions)['parameters']['VALUE'] === 817263, 'Logging must neither repeat the action nor modify its parameters.');
$debugText = debugOutputText($debugAlarm);
assertDebugOutput(str_contains($debugText, '45678') && str_contains($debugText, 'IPS_RunAction'), 'Actions need an identifiable destination and interface.');
assertDebugOutput(!str_contains($debugText, '817263') && !str_contains($debugText, 'private-token'), 'Opaque action parameters may contain credentials and must remain protected.');
$debugAlarm->TestSetPropertyString('FullyDisarmedAction', '{invalid-json');
$debugAlarm->debugEntries = [];
assertDebugOutput($runner->invoke($debugAlarm, 'FullyDisarmedAction') === false, 'Invalid actions must remain rejected.');
assertDebugOutput(str_contains(debugOutputText($debugAlarm), 'Die Aktion enthält ungültiges JSON.'), 'Action validation needs an actionable German cause.');

$debugAlarm->failDebug = true;
$debugAlarm->TestSetPropertyString('DisarmCode', '');
assertDebugOutput($debugAlarm->ArmAway() === true, 'Failure of the debug channel must not prevent arming.');
assertDebugOutput($debugAlarm->Disarm() === true, 'Failure of the debug channel must not prevent disarming.');

$debugAlarm->failDebug = false;
$debugAlarm->debugEntries = [];
$debugAlarm->EmitLegacyDebug();
$debugText = debugOutputText($debugAlarm);
assertDebugOutput(str_contains($debugText, 'Eine benötigte Visualisierungsdatei konnte nicht geladen werden.'), 'Legacy helper diagnostics must use the German envelope too.');
assertDebugOutput(!str_contains($debugText, 'private-helper-token') && !str_contains($debugText, '817263'), 'Legacy helper payloads must not bypass privacy filtering.');

fwrite(STDOUT, "OpenHomeAlarm debug-output checks passed.\n");
