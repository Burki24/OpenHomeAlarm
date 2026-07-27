# OpenHomeAlarm

OpenHomeAlarm ist eine herstellerunabhängige Alarm- und Sicherheitszentrale für die Hausautomation auf Basis von Symcon.

Das Projekt befindet sich im Neuaufbau. Ziel ist eine offene Alarmanlage, die vorhandene Symcon-Variablen unabhängig vom zugrunde liegenden Hersteller oder Protokoll als Sensoren und Auslöser verwenden kann. Der aktuelle Entwicklungsstand umfasst bereits Sensorüberwachung, modusabhängige Scharfschaltbereitschaft, Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit laufendem Countdown-Status und definiertem Ausgangsweg, konfigurierbare Alarmaktionen mit optionaler Alarmdauer und separater Rücksetzungsaktion, eine 24/7-Systemüberwachung für Manipulation, Batterie-/Stromversorgung, Kommunikation und Gerätestörungen mit optionaler Scharfschaltblockade oder Alarmauslösung, einen optionalen Unscharfschaltcode, ein quittierbares Alarmgedächtnis, ein persistentes Sicherheits-Ereignisprotokoll sowie eine wiederanlaufsichere Auswertung bereits ausgelöster Sensoren nach ApplyChanges oder einem Symcon-Neustart.

Der aktuelle Entwicklungsstand enthält das grundlegende Zustandsmodell, ein persistentes herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Überwachung und typgerechte Auswertung der konfigurierten Sensorvariablen, die globale und modusabhängige Scharfschaltbereitschaft inklusive der jeweils blockierenden Sensoren, die zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen für einen Scharfschaltzyklus, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit Ausgangsweg-Sensoren, optionale native Symcon-Aktionen für Alarmstart, Rücksetzung des Alarmausgangs und Unscharfschalten nach einem Alarm sowie eine konfigurierbare automatische Alarmdauer, eine 24/7-Systemüberwachung mit frei konfigurierbaren Störungseingängen und optionalen Aktionen bei Auftreten bzw. Behebung einer Störung, eine optionale Code-Prüfung für benutzerseitiges Unscharfschalten, ein quittierbares Alarmgedächtnis mit letztem Auslöser und Alarmzeitpunkt, ein auf 100 Einträge begrenztes persistentes Sicherheits-Ereignisprotokoll sowie die wiederanlaufsichere Prüfung bereits aktiver Sensorzustände.

## Module

- **OpenHomeAlarm** – zentrale Alarm- und Sicherheitslogik ([Dokumentation](OpenHomeAlarm))

## Voraussetzungen

- Symcon ab Version 9.0
