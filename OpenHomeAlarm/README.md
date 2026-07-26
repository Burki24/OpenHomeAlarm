# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Entwicklungsstatus:** Das Modul befindet sich im Neuaufbau. Das grundlegende Zustandsmodell ist implementiert; Sensoren, Scharfschaltlogik und Code-Eingabe folgen schrittweise.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Visualisierung](#6-visualisierung)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

Der aktuelle Entwicklungsstand stellt das grundlegende Zustandsmodell der Alarmanlage bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Die eigentliche Sensor-, Scharfschalt-, Alarm- und Code-Logik wird in den folgenden Entwicklungsschritten ergänzt.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Die Alarmkonfiguration wird in den folgenden Entwicklungsschritten ergänzt.

### 5. Statusvariablen und Darstellungen

OpenHomeAlarm legt derzeit drei schreibgeschützte Statusvariablen an:

| Variable | Bedeutung | Initialwert |
| --- | --- | --- |
| `Mode` | Gewählter Scharfmodus: Kein Scharfmodus, Zuhause, Abwesend oder Nacht | Kein Scharfmodus |
| `State` | Aktuelle Systemphase: Unscharf, Ausgangsverzögerung, Scharf, Eingangsverzögerung oder Alarm | Unscharf |
| `ReadyToArm` | Zeigt an, ob die Anlage scharfschaltbereit ist | Bereit |

Die Variablen verwenden native Symcon-Darstellungen. Bereits vorhandene Betriebszustände werden bei einem Modulupdate nicht auf die Initialwerte zurückgesetzt.

### 6. Visualisierung

Eine eigene Visualisierung einschließlich der vorgesehenen Code-Eingabe zum Deaktivieren der Alarmanlage wird in einem späteren Entwicklungsschritt umgesetzt.

### 7. PHP-Befehlsreferenz

Im aktuellen Entwicklungsstand stehen noch keine öffentlichen Modulbefehle zur Verfügung.
