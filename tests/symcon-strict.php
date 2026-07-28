<?php

declare(strict_types=1);

function assertFoundation(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = dirname(__DIR__);
$library = json_decode(
    (string) file_get_contents($root . '/library.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertFoundation(
    version_compare((string) ($library['compatibility']['version'] ?? '0'), '9.0', '>='),
    'The library must require Symcon 9.0 or newer.'
);

$moduleDirectories = glob($root . '/*/module.json') ?: [];
assertFoundation(count($moduleDirectories) === 1, 'The foundation must contain exactly one module.');

$moduleJsonPath = $root . '/OpenHomeAlarm/module.json';
$moduleSourcePath = $root . '/OpenHomeAlarm/module.php';
assertFoundation(is_file($moduleJsonPath), 'OpenHomeAlarm/module.json is missing.');
assertFoundation(is_file($moduleSourcePath), 'OpenHomeAlarm/module.php is missing.');

$module = json_decode(
    (string) file_get_contents($moduleJsonPath),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assertFoundation(($module['name'] ?? null) === 'OpenHomeAlarm', 'Unexpected module name.');
assertFoundation(($module['type'] ?? null) === 3, 'OpenHomeAlarm must be a device module (type 3).');
assertFoundation(($module['prefix'] ?? null) === 'OHA', 'OpenHomeAlarm must use the OHA prefix.');

$moduleSource = (string) file_get_contents($moduleSourcePath);
assertFoundation(
    preg_match('/class\s+OpenHomeAlarm\s+extends\s+IPSModuleStrict\b/', $moduleSource) === 1,
    'OpenHomeAlarm must extend IPSModuleStrict.'
);

foreach (['Create', 'Destroy', 'ApplyChanges'] as $method) {
    assertFoundation(
        preg_match('/public function ' . $method . '\(\): void/', $moduleSource) === 1,
        $method . '() must declare the void return type.'
    );
}

assertFoundation(
    str_contains($moduleSource, 'RegisterMessage(0, IPS_KERNELSTARTED)')
        && preg_match(
            '/public function ApplyChanges\(\): void[\s\S]*?IPS_GetKernelRunlevel\(\) !== KR_READY[\s\S]*?InitializeRuntime\(\)/',
            $moduleSource
        ) === 1
        && preg_match(
            '/public function MessageSink\([\s\S]*?IPS_KERNELSTARTED[\s\S]*?InitializeRuntime\(\)/',
            $moduleSource
        ) === 1,
    'Runtime sensor access must be deferred until the Symcon kernel is ready.'
);

$workflow = (string) file_get_contents($root . '/.github/workflows/tests.yml');
assertFoundation(
    str_contains($workflow, "php-version: '8.5'"),
    'The test workflow must run on PHP 8.5 to match Symcon 9.0.'
);

fwrite(STDOUT, "OpenHomeAlarm foundation checks passed.\n");
