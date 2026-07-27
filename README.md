# OpenHomeAlarm

OpenHomeAlarm ist eine herstellerunabhängige Alarm- und Sicherheitszentrale für die Hausautomation auf Basis von Symcon.

Das Projekt befindet sich im Neuaufbau. Ziel ist eine offene Alarmanlage, die vorhandene Symcon-Variablen unabhängig vom zugrunde liegenden Hersteller oder Protokoll als Sensoren und Auslöser verwenden kann. Der aktuelle Entwicklungsstand umfasst bereits Sensorüberwachung, modusabhängige Scharfschaltbereitschaft, Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit laufendem Countdown-Status, konfigurierbare Alarmaktionen, einen optionalen Unscharfschaltcode, ein quittierbares Alarmgedächtnis sowie eine wiederanlaufsichere Auswertung bereits ausgelöster Sensoren nach ApplyChanges oder einem Symcon-Neustart.

Der aktuelle Entwicklungsstand enthält das grundlegende Zustandsmodell, ein persistentes herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Überwachung und typgerechte Auswertung der konfigurierten Sensorvariablen, die globale und modusabhängige Scharfschaltbereitschaft inklusive der jeweils blockierenden Sensoren, die zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen für einen Scharfschaltzyklus, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen, optionale native Symcon-Aktionen für Alarmstart und Unscharfschalten nach einem Alarm, eine optionale Code-Prüfung für benutzerseitiges Unscharfschalten, ein quittierbares Alarmgedächtnis mit letztem Auslöser und Alarmzeitpunkt sowie die wiederanlaufsichere Prüfung bereits aktiver Sensorzustände.

## Module

- **OpenHomeAlarm** – zentrale Alarm- und Sicherheitslogik ([Dokumentation](OpenHomeAlarm))

## Voraussetzungen

- Symcon ab Version 9.0
