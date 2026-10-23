<?php

declare(strict_types=1);

// Diese beiden IDs an die eigene Symcon-Installation anpassen.
$tileVisualizationID = 12345;
$targetObjectID = 23456;

// Das Skript wird durch ein Ereignis "Bei Variablenänderung" an einer
// Boolean-Kameravariable aufgerufen. Nur die positive Flanke erzeugt eine
// Nachricht; das automatische Zurückfallen der Kamera auf false bleibt ohne
// weitere Aktion.
if (($_IPS['SENDER'] ?? '') !== 'Variable' || ($_IPS['VALUE'] ?? null) !== true) {
    return;
}

$cameraVariableID = (int) ($_IPS['VARIABLE'] ?? 0);
if ($cameraVariableID <= 0 || !IPS_VariableExists($cameraVariableID)) {
    IPS_LogMessage('Kamera-Voralarm', 'Die auslösende Kameravariable ist nicht mehr verfügbar.');

    return;
}

$shorten = static function (string $text, int $maximumLength): string {
    if ($maximumLength <= 0) {
        return '';
    }

    if (strlen($text) <= $maximumLength) {
        return $text;
    }

    if (function_exists('mb_strcut')) {
        return mb_strcut($text, 0, $maximumLength, 'UTF-8');
    }

    $prefix = substr($text, 0, $maximumLength);
    while ($prefix !== '' && preg_match('//u', $prefix) !== 1) {
        $prefix = substr($prefix, 0, -1);
    }

    return $prefix;
};

$cameraName = trim(IPS_GetName($cameraVariableID));
$cameraName = $cameraName !== '' ? $cameraName : 'Unbekannte Kamera';
$message = $shorten(sprintf('Die Kamera "%s" hat eine Person erkannt.', $cameraName), 256);

$notificationID = VISU_PostNotificationEx(
    $tileVisualizationID,
    'Kamera-Voralarm',
    $message,
    'Alert',
    'bell',
    $targetObjectID
);

if ($notificationID === false) {
    IPS_LogMessage('Kamera-Voralarm', 'Die Push-Nachricht konnte nicht versendet werden.');
}
