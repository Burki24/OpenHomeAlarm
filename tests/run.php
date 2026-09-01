<?php

declare(strict_types=1);

$root = dirname(__DIR__);
if (!chdir($root)) {
    fwrite(STDERR, "Unable to switch to the repository root.\n");
    exit(1);
}

/**
 * Runs one repository-specific test command.
 *
 * @param string $label   Human-readable test label
 * @param string $command Command line to execute
 */
function runTestCommand(string $label, string $command): void
{
    echo $label . "...\n";
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, $label . ' failed with exit code ' . $exitCode . ".\n");
        exit($exitCode);
    }
}

$commands = [
    ['Verify vendored helper integrity', 'python3 tests/helper_integrity.py'],
    ['Validate repository structure', 'python3 tests/validate_structure.py'],
    ['Check Symcon Strict foundation', 'php tests/symcon-strict.php'],
    ['Check extracted alarm domain model', 'php tests/domain-model.php'],
    ['Check alarm state model', 'php tests/state-model.php'],
    ['Check sensor and trigger model', 'php tests/sensor-model.php'],
    ['Check active sensor monitoring', 'php tests/sensor-monitoring.php'],
    ['Check mode-specific arming readiness and blockers', 'php tests/readiness.php'],
    ['Check arming and disarming logic', 'php tests/arming.php'],
    ['Check entry and exit delays with countdown status', 'php tests/delays.php'],
    ['Check exit-route arming behavior', 'php tests/exit-route.php'],
    ['Check alarm actions', 'php tests/alarm-actions.php'],
    ['Check alarm escalation plan', 'php tests/alarm-escalation-plan.php'],
    ['Check alarm duration and reset handling', 'php tests/alarm-duration.php'],
    ['Check disarm code protection', 'php tests/disarm-code.php'],
    ['Check automatic arming schedules', 'php tests/arming-schedule.php'],
    ['Check alarm partition registry', 'php tests/partitions.php'],
    ['Check independent alarm partition runtime', 'php tests/partition-runtime.php'],
    ['Check partition alarm output and memory aggregation', 'php tests/partition-alarm-registry.php'],
    ['Check alarm memory', 'php tests/alarm-memory.php'],
    ['Check 24/7 sensors', 'php tests/always-active.php'],
    ['Check temporary sensor bypasses', 'php tests/bypass.php'],
    ['Check restart-safe armed sensor recovery', 'php tests/restart-recovery.php'],
    ['Check persistent security event history', 'php tests/event-history.php'],
    ['Check event history export', 'php tests/event-history-export.php'],
    ['Check tamper and fault monitoring', 'php tests/fault-monitoring.php'],
    ['Check stable public control API', 'php tests/control-api.php'],
    ['Check HTML-SDK visualization foundation', 'php tests/visualization.php'],
    ['Check visualization disarm codepad', 'php tests/codepad.php'],
    ['Check IPSView WebContent integration', 'php tests/ipsview.php'],
    ['Check IPSView style migration', 'php tests/ipsview-migration.php'],
    ['Check Symcon runtime compatibility', 'php tests/symcon-runtime.php'],
    ['Check visualization JavaScript syntax', 'node --check OpenHomeAlarm/visualization/app.js'],
    ['Test library metadata updater', 'python3 tests/test_update_library_metadata.py'],
    ['Test reproducible release artifact', 'python3 tests/test_build_release_artifact.py'],
];

foreach ($commands as [$label, $command]) {
    runTestCommand($label, $command);
}

echo "All OpenHomeAlarm tests passed.\n";
