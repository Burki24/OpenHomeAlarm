# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Entwicklungsstatus:** Zustandsmodell, Sensor-/Trigger-Datenmodell, aktive Sensorüberwachung, zielmodusabhängige Scharf-/Unscharf-Logik, Ein-/Ausgangsverzögerungen sowie konfigurierbare Alarmaktionen sind implementiert. Die Code-Eingabe folgt schrittweise.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Sensoren und Auslöser](#6-sensoren-und-auslöser)
7. [Alarmaktionen](#7-alarmaktionen)
8. [Visualisierung](#8-visualisierung)
9. [PHP-Befehlsreferenz](#9-php-befehlsreferenz)

### 1. Funktionsumfang

Der aktuelle Entwicklungsstand stellt das grundlegende Zustandsmodell der Alarmanlage, ein herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Sensorüberwachung, die zielmodusabhängige Scharf-/Unscharf-Logik, timerbasierte Ein-/Ausgangsverzögerungen sowie konfigurierbare Alarmaktionen bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Symcon-Variablen können als Sensor oder Auslöser hinterlegt und den Scharfmodi Zuhause, Abwesend und Nacht zugeordnet werden. Zusätzlich werden Sensortyp, Auslösewert und die Nutzung der Eingangsverzögerung gespeichert. Der Auslösewert wird aus den diskreten Zuständen der ausgewählten Symcon-Variable abgeleitet. Boolean-, String- und numerische Zustände werden dabei einheitlich als Auswahlliste mit den in Symcon hinterlegten Beschriftungen angeboten.

Ausgangs- und Eingangsverzögerung sind global in Sekunden konfigurierbar. Der Wert `0` deaktiviert die jeweilige Verzögerung. Zusätzlich können zwei native Symcon-Aktionen hinterlegt werden: eine Aktion beim Eintritt in den Alarmzustand und eine Aktion beim Unscharfschalten eines bereits aktiven Alarms. Dadurch lassen sich unter anderem Sirenen, Benachrichtigungen, Skripte oder Ablaufpläne ohne hardwarespezifische Kopplung anbinden. Die Code-Logik wird in einem folgenden Entwicklungsschritt ergänzt.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Im Konfigurationsformular können die globale **Ausgangsverzögerung** und **Eingangsverzögerung** in Sekunden festgelegt werden. Im Abschnitt **Alarmaktionen** können optional eine Aktion **Bei Alarm** und eine Aktion **Beim Unscharfschalten nach Alarm** gewählt werden. Darunter steht die Liste **Sensoren und Auslöser** zur Verfügung. Ein Eintrag verweist direkt auf eine vorhandene Symcon-Variable und ist damit unabhängig vom Hersteller oder Protokoll des eigentlichen Geräts.

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
| `EntryDelay` | Startet bei Auslösung im scharfen Betrieb die konfigurierte Eingangsverzögerung statt unmittelbar den Alarmzustand |

Beim Bearbeiten eines Eintrags liest OpenHomeAlarm die aktuelle Symcon-Variablendarstellung aus. Definierte Optionen einer Boolean- oder String-Wertanzeige, Aufzählungen sowie diskrete numerische Intervalle erscheinen immer als dieselbe Auswahlliste. Für ältere Variablen werden vorhandene Profil-Assoziationen ebenfalls übernommen. Nur wenn eine Variable keine diskreten Zustände bereitstellt, bleibt eine direkte Rohwerteingabe als Fallback sichtbar. Gespeichert wird weiterhin der Rohwert als String, damit das Sensor-Datenmodell stabil bleibt.

Aktive, mindestens einem Scharfmodus zugeordnete Sensoren werden per `VM_UPDATE` überwacht. OpenHomeAlarm vergleicht den aktuellen Variablenwert typgerecht mit dem gespeicherten Rohwert. Boolean-, Integer-, Float- und Stringwerte werden entsprechend ihrem tatsächlichen Variablentyp ausgewertet.

`ReadyToArm` ist in diesem Entwicklungsschritt eine globale Bereitschaftsanzeige: Sie ist nur dann **Bereit**, wenn kein aktivierter, einem Scharfmodus zugeordneter Sensor ausgelöst ist. Nicht mehr vorhandene oder nicht auswertbare konfigurierte Sensorvariablen führen aus Sicherheitsgründen ebenfalls zu **Nicht bereit**. Ein noch nicht vollständig konfigurierter Eintrag mit `VariableID = 0` wird ignoriert.

Beim Scharfschalten prüft OpenHomeAlarm nur die Sensoren, die dem angeforderten Zielmodus zugeordnet sind. Ein ausgelöster Sensor, der beispielsweise ausschließlich für **Abwesend** gilt, verhindert deshalb nicht das Scharfschalten von **Zuhause**. Fehlende oder nicht auswertbare Sensoren des angeforderten Modus blockieren die Scharfschaltung aus Sicherheitsgründen. Unvollständige Listeneinträge mit `VariableID = 0` bleiben weiterhin ohne Wirkung.

Nach erfolgreicher Scharfschaltprüfung wechselt OpenHomeAlarm bei einer Ausgangsverzögerung größer als `0` zunächst in **Ausgangsverzögerung**. Erst nach Ablauf des Timers wird erneut geprüft, ob der gewählte Modus scharfschaltbereit ist. Sind dann noch relevante Sensoren ausgelöst oder nicht auswertbar, wird der Scharfschaltvorgang sicher abgebrochen und die Anlage auf **Unscharf** zurückgesetzt. Bei `0` Sekunden wird unmittelbar scharfgeschaltet.

Löst im Zustand **Scharf** ein für den aktiven Modus relevanter Sensor aus, startet ein mit `EntryDelay` markierter Sensor die konfigurierte **Eingangsverzögerung**. Der Countdown wird durch das erneute Schließen des Sensors nicht abgebrochen und bei weiteren verzögerten Sensorereignissen nicht neu gestartet. Ein Sensor ohne Eingangsverzögerung wechselt unmittelbar in den Zustand **Alarm**. Das gilt ebenfalls, wenn während einer laufenden Eingangsverzögerung ein sofort auslösender Sensor anspricht. Nach Ablauf der Eingangsverzögerung wird ebenfalls **Alarm** gesetzt. Beim erstmaligen Eintritt in den Alarmzustand wird die konfigurierte Aktion **Bei Alarm** genau einmal ausgeführt.

Beim Unscharfschalten werden laufende Ein- und Ausgangsverzögerungen immer beendet. Wird ein bereits aktiver Alarm unscharf geschaltet, wird anschließend einmalig die konfigurierte Aktion **Beim Unscharfschalten nach Alarm** ausgeführt. Ein Abbruch während Ein- oder Ausgangsverzögerung löst diese Rücksetzaktion nicht aus. Die Timer verwenden persistierte Ablaufzeitpunkte und werden nach `ApplyChanges()` bzw. einem Symcon-Neustart mit der verbleibenden Zeit wiederhergestellt.

### 7. Alarmaktionen

OpenHomeAlarm verwendet für externe Reaktionen die nativen Symcon-Aktionen. Im Feld **Bei Alarm** kann ein beliebiges Ziel samt passender Aktion gewählt werden. Die Aktion wird genau einmal ausgeführt, wenn das System erstmals in den Zustand **Alarm** wechselt.

Optional kann unter **Beim Unscharfschalten nach Alarm** eine zweite Aktion hinterlegt werden. Diese wird nur ausgeführt, wenn tatsächlich ein aktiver Alarm unscharf geschaltet wird. Ein normales Unscharfschalten oder das Abbrechen einer Ein-/Ausgangsverzögerung führt die Rücksetzaktion nicht aus.

Da die Zielauswahl Bestandteil der Symcon-Aktion ist, können sowohl einzelne Gerätevariablen als auch Skripte, Ablaufpläne und andere von Symcon angebotene Aktionsziele verwendet werden. Nicht konfigurierte Alarmaktionen haben keine Wirkung auf die Kernlogik. Auch eine fehlerhafte optionale Aktion verhindert nicht den Wechsel des Alarmzustands oder das Unscharfschalten.

### 8. Visualisierung

Eine eigene Visualisierung einschließlich der vorgesehenen Code-Eingabe zum Deaktivieren der Alarmanlage wird in einem späteren Entwicklungsschritt umgesetzt.

### 9. PHP-Befehlsreferenz

Folgende öffentliche Modulbefehle stehen zur Verfügung:

| PHP-Befehl | Rückgabe | Bedeutung |
| --- | --- | --- |
| `OHA_ArmHome($InstanzID)` | `bool` | Prüft die für **Zuhause** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_ArmAway($InstanzID)` | `bool` | Prüft die für **Abwesend** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_ArmNight($InstanzID)` | `bool` | Prüft die für **Nacht** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_Disarm($InstanzID)` | `bool` | Schaltet die Anlage unscharf und setzt den Scharfmodus zurück |

Die drei Scharfschaltbefehle liefern `false`, wenn mindestens ein für den Zielmodus relevanter Sensor ausgelöst, nicht vorhanden oder nicht auswertbar ist. In diesem Fall bleiben `Mode` und `State` unverändert.
