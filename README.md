# OpenHomeAlarm

OpenHomeAlarm ist eine herstellerunabhängige Alarm- und Sicherheitszentrale für die Hausautomation auf Basis von Symcon.

Das Projekt befindet sich im Neuaufbau. Ziel ist eine offene Alarmanlage, die vorhandene Symcon-Variablen unabhängig vom zugrunde liegenden Hersteller oder Protokoll als Sensoren und Auslöser verwenden kann. Der aktuelle Entwicklungsstand umfasst bereits Sensorüberwachung, Scharf-/Unscharf-Logik, timerbasierte Ein-/Ausgangsverzögerungen, konfigurierbare Alarmaktionen sowie einen optionalen Unscharfschaltcode.

Der aktuelle Entwicklungsstand enthält das grundlegende Zustandsmodell, ein persistentes herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Überwachung und typgerechte Auswertung der konfigurierten Sensorvariablen, die zielmodusabhängige Scharf-/Unscharf-Logik, timerbasierte Ein-/Ausgangsverzögerungen, optionale native Symcon-Aktionen für Alarmstart und Unscharfschalten nach einem Alarm sowie eine optionale Code-Prüfung für benutzerseitiges Unscharfschalten.

## Module

- **OpenHomeAlarm** – zentrale Alarm- und Sicherheitslogik ([Dokumentation](OpenHomeAlarm))

## Voraussetzungen

- Symcon ab Version 9.0
