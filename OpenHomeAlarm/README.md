# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Entwicklungsstatus:** Das grundlegende Zustandsmodell und das persistente Sensor-/Trigger-Datenmodell sind implementiert. Die aktive Sensorauswertung, Scharfschaltlogik und Code-Eingabe folgen schrittweise.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Sensoren und Auslöser](#6-sensoren-und-auslöser)
7. [Visualisierung](#7-visualisierung)
8. [PHP-Befehlsreferenz](#8-php-befehlsreferenz)

### 1. Funktionsumfang

Der aktuelle Entwicklungsstand stellt das grundlegende Zustandsmodell der Alarmanlage sowie ein herstellerunabhängiges Sensor-/Trigger-Datenmodell bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Symcon-Variablen können als Sensor oder Auslöser hinterlegt und den Scharfmodi Zuhause, Abwesend und Nacht zugeordnet werden. Zusätzlich werden Sensortyp, Auslösewert und die spätere Nutzung einer Eingangsverzögerung gespeichert. Der Auslösewert wird aus den diskreten Zuständen der ausgewählten Symcon-Variable abgeleitet. Boolean-, String- und numerische Zustände werden dabei einheitlich als Auswahlliste mit den in Symcon hinterlegten Beschriftungen angeboten.

Die aktive Sensorauswertung, Scharfschalt-, Alarm- und Code-Logik wird in den folgenden Entwicklungsschritten ergänzt.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Im Konfigurationsformular steht eine Liste **Sensoren und Auslöser** zur Verfügung. Ein Eintrag verweist direkt auf eine vorhandene Symcon-Variable und ist damit unabhängig vom Hersteller oder Protokoll des eigentlichen Geräts.

### 5. Statusvariablen und Darstellungen

OpenHomeAlarm legt derzeit drei schreibgeschützte Statusvariablen an:

| Variable | Bedeutung | Initialwert |
| --- | --- | --- |
| `Mode` | Gewählter Scharfmodus: Kein Scharfmodus, Zuhause, Abwesend oder Nacht | Kein Scharfmodus |
| `State` | Aktuelle Systemphase: Unscharf, Ausgangsverzögerung, Scharf, Eingangsverzögerung oder Alarm | Unscharf |
| `ReadyToArm` | Zeigt an, ob die Anlage scharfschaltbereit ist | Bereit |

Die Variablen verwenden native Symcon-Darstellungen. Bereits vorhandene Betriebszustände werden bei einem Modulupdate nicht auf die Initialwerte zurückgesetzt.

### 6. Sensoren und Auslöser

Jeder konfigurierte Eintrag enthält folgende Daten:

| Feld | Bedeutung |
| --- | --- |
| `Enabled` | Eintrag grundsätzlich aktiviert/deaktiviert |
| `Name` | Frei wählbare Bezeichnung |
| `VariableID` | ID der verwendeten Symcon-Variable |
| `SensorType` | Öffnungskontakt, Bewegungsmelder, Glasbruch-, Rauch- oder Wassermelder, Panikauslöser oder sonstiger Auslöser |
| `TriggerValue` | Rohwert, bei dem der Eintrag später als ausgelöst gilt; diskrete Zustände werden aus der Variablendarstellung bzw. vorhandenen Profil-Assoziationen übernommen |
| `ArmHome` | Im Scharfmodus Zuhause relevant |
| `ArmAway` | Im Scharfmodus Abwesend relevant |
| `ArmNight` | Im Scharfmodus Nacht relevant |
| `EntryDelay` | Soll später eine Eingangsverzögerung statt eines unmittelbaren Alarms starten |

Beim Bearbeiten eines Eintrags liest OpenHomeAlarm die aktuelle Symcon-Variablendarstellung aus. Definierte Optionen einer Boolean- oder String-Wertanzeige, Aufzählungen sowie diskrete numerische Intervalle erscheinen immer als dieselbe Auswahlliste. Für ältere Variablen werden vorhandene Profil-Assoziationen ebenfalls übernommen. Nur wenn eine Variable keine diskreten Zustände bereitstellt, bleibt eine direkte Rohwerteingabe als Fallback sichtbar. Gespeichert wird weiterhin der Rohwert als String, damit das Sensor-Datenmodell stabil bleibt.

Dieser Entwicklungsschritt speichert und validiert das Datenmodell. Es werden noch keine Variablen-Nachrichten registriert und Änderungen an den ausgewählten Sensorvariablen lösen noch keinen Alarm aus.

### 7. Visualisierung

Eine eigene Visualisierung einschließlich der vorgesehenen Code-Eingabe zum Deaktivieren der Alarmanlage wird in einem späteren Entwicklungsschritt umgesetzt.

### 8. PHP-Befehlsreferenz

Im aktuellen Entwicklungsstand stehen noch keine öffentlichen Modulbefehle zur Verfügung.
