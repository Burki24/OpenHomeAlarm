<?php

declare(strict_types=1);

/*
 * Mögliche Bestätigungsregeln:
 * any       = zwei beliebige Erkennungen innerhalb des Zeitfensters
 * same      = zweimal dieselbe Kamera
 * different = zwei unterschiedliche Kameras
 */
$cameraConfirmationMode = 'different';
$cameraConfirmationWindowSeconds = 30;
$cameraConfirmationOutputSeconds = 5;

// Diese drei IDs an die eigene Symcon-Installation anpassen.
$lastDetectionTimestampID = 34567; // Integer: Zeitstempel der letzten Erkennung
$lastCameraVariableID = 45678;     // Integer: Objekt-ID der letzten Kameravariable
$confirmedDetectionID = 56789;     // Boolean: bestätigte Kameraerkennung für OpenHomeAlarm

$semaphoreName = 'CameraPreAlarm-' . $confirmedDetectionID;
if (!IPS_SemaphoreEnter($semaphoreName, 1000)) {
    IPS_LogMessage(
        'Kamera-Voralarm',
        'Die Erkennung wurde nicht ausgewertet, weil bereits ein anderes Kameraereignis verarbeitet wird.'
    );

    return;
}

try {
    // Der Skript-Timer beendet nur den kurzen Ausgangsimpuls. Ein bereits von
    // OpenHomeAlarm registrierter Alarm wird dadurch nicht quittiert.
    if (($_IPS['SENDER'] ?? '') === 'TimerEvent') {
        SetValueBoolean($confirmedDetectionID, false);
        IPS_SetScriptTimer((int) $_IPS['SELF'], 0);

        return;
    }

    // Nur die positive Flanke einer Boolean-Kameravariable auswerten.
    if (($_IPS['SENDER'] ?? '') !== 'Variable' || ($_IPS['VALUE'] ?? null) !== true) {
        return;
    }

    if (!in_array($cameraConfirmationMode, ['any', 'same', 'different'], true)) {
        IPS_LogMessage(
            'Kamera-Voralarm',
            sprintf('Die Bestätigungsregel "%s" ist ungültig.', $cameraConfirmationMode)
        );

        return;
    }

    $currentCameraVariableID = (int) ($_IPS['VARIABLE'] ?? 0);
    if ($currentCameraVariableID <= 0 || !IPS_VariableExists($currentCameraVariableID)) {
        IPS_LogMessage('Kamera-Voralarm', 'Die auslösende Kameravariable ist nicht mehr verfügbar.');

        return;
    }

    $currentTimestamp = time();
    $lastTimestamp = GetValueInteger($lastDetectionTimestampID);
    $lastCameraID = GetValueInteger($lastCameraVariableID);
    $elapsedSeconds = $currentTimestamp - $lastTimestamp;
    $insideTimeWindow = $lastTimestamp > 0
        && $elapsedSeconds >= 0
        && $elapsedSeconds <= $cameraConfirmationWindowSeconds;

    $cameraConditionMatches = match ($cameraConfirmationMode) {
        'any'       => true,
        'same'      => $lastCameraID === $currentCameraVariableID,
        'different' => $lastCameraID !== $currentCameraVariableID
    };

    if ($insideTimeWindow && $cameraConditionMatches) {
        $firstCameraName = IPS_VariableExists($lastCameraID)
            ? IPS_GetName($lastCameraID)
            : 'Unbekannte Kamera';
        $secondCameraName = IPS_GetName($currentCameraVariableID);

        IPS_LogMessage(
            'Kamera-Voralarm',
            sprintf(
                'Bestätigte Erkennung: %s -> %s innerhalb von %d Sekunden.',
                $firstCameraName,
                $secondCameraName,
                $cameraConfirmationWindowSeconds
            )
        );

        SetValueBoolean($confirmedDetectionID, true);
        SetValueInteger($lastDetectionTimestampID, 0);
        SetValueInteger($lastCameraVariableID, 0);
        IPS_SetScriptTimer((int) $_IPS['SELF'], $cameraConfirmationOutputSeconds);

        return;
    }

    // Eine nicht passende oder zu späte Erkennung bildet den neuen Beginn des
    // Zeitfensters. So kann die nächste Meldung wieder eine gültige Folge bilden.
    SetValueInteger($lastDetectionTimestampID, $currentTimestamp);
    SetValueInteger($lastCameraVariableID, $currentCameraVariableID);

    IPS_LogMessage(
        'Kamera-Voralarm',
        sprintf(
            'Erste Erkennung durch %s gespeichert; Zeitfenster: %d Sekunden.',
            IPS_GetName($currentCameraVariableID),
            $cameraConfirmationWindowSeconds
        )
    );
} finally {
    IPS_SemaphoreLeave($semaphoreName);
}
