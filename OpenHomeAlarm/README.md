# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Entwicklungsstatus:** Zustandsmodell, Sensor-/Trigger-Datenmodell, aktive und wiederanlaufsichere Sensorüberwachung, modusabhängige Scharfschaltbereitschaft, zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen, 24/7-Sensoren, Ein-/Ausgangsverzögerungen mit Countdown-Status, konfigurierbare Alarmaktionen, Code-Prüfung zum Unscharfschalten sowie ein quittierbares Alarmgedächtnis sind implementiert. Die eigene Visualisierung folgt schrittweise.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Sensoren und Auslöser](#6-sensoren-und-auslöser)
7. [Code-Schutz](#7-code-schutz)
8. [Alarmaktionen](#8-alarmaktionen)
9. [Alarmgedächtnis](#9-alarmgedächtnis)
10. [Visualisierung](#10-visualisierung)
11. [PHP-Befehlsreferenz](#11-php-befehlsreferenz)

### 1. Funktionsumfang

Der aktuelle Entwicklungsstand stellt das grundlegende Zustandsmodell der Alarmanlage, ein herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Sensorüberwachung, die globale und modusabhängige Scharfschaltbereitschaft inklusive der jeweils blockierenden Sensoren, die zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen für einen Scharfschaltzyklus, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit laufendem Countdown-Status, konfigurierbare Alarmaktionen, eine optionale Code-Prüfung zum Unscharfschalten sowie ein quittierbares Alarmgedächtnis bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Symcon-Variablen können als Sensor oder Auslöser hinterlegt und den Scharfmodi Zuhause, Abwesend und Nacht zugeordnet werden. Zusätzlich kann ein Sensor als **24/7 aktiv** markiert werden und löst dann unabhängig vom Scharfmodus sofort aus. Sensortyp, Auslösewert und die Nutzung der Eingangsverzögerung werden ebenfalls gespeichert. Der Auslösewert wird aus den diskreten Zuständen der ausgewählten Symcon-Variable abgeleitet. Boolean-, String- und numerische Zustände werden dabei einheitlich als Auswahlliste mit den in Symcon hinterlegten Beschriftungen angeboten.

Ausgangs- und Eingangsverzögerung sind global in Sekunden konfigurierbar. Der Wert `0` deaktiviert die jeweilige Verzögerung. Während eines laufenden Countdowns veröffentlicht `DelayRemaining` die verbleibenden Sekunden. Bei einer Eingangsverzögerung nennt `DelaySource` zusätzlich den Sensor, der den Countdown gestartet hat; beide Statuswerte werden beim Abbruch, Abschluss oder Alarm wieder zurückgesetzt. Zusätzlich können zwei native Symcon-Aktionen hinterlegt werden: eine Aktion beim Eintritt in den Alarmzustand und eine Aktion beim Unscharfschalten eines bereits aktiven Alarms. Dadurch lassen sich unter anderem Sirenen, Benachrichtigungen, Skripte oder Ablaufpläne ohne hardwarespezifische Kopplung anbinden. Für benutzerseitiges Unscharfschalten kann optional ein vier- bis achtstelliger Zahlencode hinterlegt werden.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Im Konfigurationsformular können die globale **Ausgangsverzögerung** und **Eingangsverzögerung** in Sekunden festgelegt werden. Im Abschnitt **Code-Schutz** kann optional ein vier- bis achtstelliger **Unscharfschaltcode** hinterlegt werden. Im Abschnitt **Alarmaktionen** können optional eine Aktion **Bei Alarm** und eine Aktion **Beim Unscharfschalten nach Alarm** gewählt werden. Darunter steht die Liste **Sensoren und Auslöser** zur Verfügung. Ein Eintrag verweist direkt auf eine vorhandene Symcon-Variable und ist damit unabhängig vom Hersteller oder Protokoll des eigentlichen Geräts.

### 5. Statusvariablen und Darstellungen

OpenHomeAlarm legt folgende schreibgeschützte Statusvariablen an:

| Variable | Bedeutung | Initialwert |
| --- | --- | --- |
| `Mode` | Gewählter Scharfmodus: Kein Scharfmodus, Zuhause, Abwesend oder Nacht | Kein Scharfmodus |
| `State` | Aktuelle Systemphase: Unscharf, Ausgangsverzögerung, Scharf, Eingangsverzögerung oder Alarm | Unscharf |
| `DelayRemaining` | Verbleibende Sekunden einer laufenden Ein- oder Ausgangsverzögerung | 0 s |
| `DelaySource` | Sensor, der die aktuelle Eingangsverzögerung gestartet hat; bei Ausgangsverzögerung leer | leer |
| `ReadyToArm` | Konservative Gesamtbereitschaft über alle überwachten Sensoren | Bereit |
| `ReadyHome` | Scharfschaltbereitschaft für Zuhause | Bereit |
| `ReadyAway` | Scharfschaltbereitschaft für Abwesend | Bereit |
| `ReadyNight` | Scharfschaltbereitschaft für Nacht | Bereit |
| `BlockingHomeSensors` | Namen der Sensoren, die Zuhause aktuell blockieren | leer |
| `BlockingAwaySensors` | Namen der Sensoren, die Abwesend aktuell blockieren | leer |
| `BlockingNightSensors` | Namen der Sensoren, die Nacht aktuell blockieren | leer |
| `BypassedSensors` | Namen der aktuell temporär überbrückten Sensoren | leer |
| `AlarmMemory` | Zeigt an, ob seit der letzten Quittierung ein Alarm gespeichert ist | Kein Alarm gespeichert |
| `LastAlarmSource` | Name des Sensors, der den letzten Alarm ausgelöst hat | leer |
| `LastAlarmTime` | Zeitpunkt des letzten Alarms im Format `TT.MM.JJJJ HH:MM:SS` | leer |

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
| `AlwaysActive` | 24/7 aktiv; löst unabhängig vom Scharfmodus sofort aus |
| `EntryDelay` | Startet bei Auslösung im scharfen Betrieb die konfigurierte Eingangsverzögerung statt unmittelbar den Alarmzustand |

Beim Bearbeiten eines Eintrags liest OpenHomeAlarm die aktuelle Symcon-Variablendarstellung aus. Definierte Optionen einer Boolean- oder String-Wertanzeige, Aufzählungen sowie diskrete numerische Intervalle erscheinen immer als dieselbe Auswahlliste. Für ältere Variablen werden vorhandene Profil-Assoziationen ebenfalls übernommen. Nur wenn eine Variable keine diskreten Zustände bereitstellt, bleibt eine direkte Rohwerteingabe als Fallback sichtbar. Gespeichert wird weiterhin der Rohwert als String, damit das Sensor-Datenmodell stabil bleibt.

Aktive Sensoren werden per `VM_UPDATE` überwacht, sobald sie mindestens einem Scharfmodus zugeordnet oder als **24/7 aktiv** markiert sind. OpenHomeAlarm vergleicht den aktuellen Variablenwert typgerecht mit dem gespeicherten Rohwert. Boolean-, Integer-, Float- und Stringwerte werden entsprechend ihrem tatsächlichen Variablentyp ausgewertet. Nach `ApplyChanges()` oder einem Symcon-Neustart werden die aktuell anliegenden Sensorwerte zusätzlich erneut geprüft. Dadurch wird auch eine Auslösung erkannt, die während eines Neustarts erfolgt ist und deshalb kein neues `VM_UPDATE` mehr erzeugt. Bereits laufende Eingangsverzögerungen werden dabei nicht neu gestartet; ein gleichzeitig aktiver Sofortalarm-Sensor eskaliert weiterhin unmittelbar zum Alarm.

`ReadyToArm` bleibt als konservative Gesamtbereitschaft erhalten: Sie ist nur dann **Bereit**, wenn kein aktivierter, einem Scharfmodus zugeordneter oder 24/7 aktiver Sensor ausgelöst ist. Zusätzlich zeigen `ReadyHome`, `ReadyAway` und `ReadyNight` die tatsächliche Scharfschaltbereitschaft des jeweiligen Modus. Ein beispielsweise nur für **Abwesend** relevanter offener Kontakt setzt deshalb `ReadyAway` auf **Nicht bereit**, während `ReadyHome` und `ReadyNight` weiterhin **Bereit** bleiben können. Die Variablen `BlockingHomeSensors`, `BlockingAwaySensors` und `BlockingNightSensors` nennen dabei direkt die Sensoren, die den jeweiligen Modus aktuell blockieren. Mehrere Sensoren werden kommasepariert ausgegeben; bei fehlendem Namen wird die Variablen-ID verwendet. 24/7-Sensoren wirken auf alle drei Modi. Nicht mehr vorhandene oder nicht auswertbare relevante Sensorvariablen führen aus Sicherheitsgründen ebenfalls zu **Nicht bereit** und erscheinen ebenfalls in der passenden Blockierliste. Ein noch nicht vollständig konfigurierter Eintrag mit `VariableID = 0` wird ignoriert.

Beim Scharfschalten prüft OpenHomeAlarm die Sensoren, die dem angeforderten Zielmodus zugeordnet sind, sowie alle 24/7 aktiven Sensoren. Ein ausgelöster Sensor, der beispielsweise ausschließlich für **Abwesend** gilt, verhindert deshalb nicht das Scharfschalten von **Zuhause**. Fehlende oder nicht auswertbare Sensoren des angeforderten Modus blockieren die Scharfschaltung aus Sicherheitsgründen. Unvollständige Listeneinträge mit `VariableID = 0` bleiben weiterhin ohne Wirkung.

Nach erfolgreicher Scharfschaltprüfung wechselt OpenHomeAlarm bei einer Ausgangsverzögerung größer als `0` zunächst in **Ausgangsverzögerung**. Erst nach Ablauf des Timers wird erneut geprüft, ob der gewählte Modus scharfschaltbereit ist. Sind dann noch relevante Sensoren ausgelöst oder nicht auswertbar, wird der Scharfschaltvorgang sicher abgebrochen und die Anlage auf **Unscharf** zurückgesetzt. Bei `0` Sekunden wird unmittelbar scharfgeschaltet.

Löst im Zustand **Scharf** ein für den aktiven Modus relevanter Sensor aus, startet ein mit `EntryDelay` markierter Sensor die konfigurierte **Eingangsverzögerung**. Der Countdown wird durch das erneute Schließen des Sensors nicht abgebrochen und bei weiteren verzögerten Sensorereignissen nicht neu gestartet. Ein Sensor ohne Eingangsverzögerung wechselt unmittelbar in den Zustand **Alarm**. Das gilt ebenfalls, wenn während einer laufenden Eingangsverzögerung ein sofort auslösender Sensor anspricht. Nach Ablauf der Eingangsverzögerung wird ebenfalls **Alarm** gesetzt. Beim erstmaligen Eintritt in den Alarmzustand wird die konfigurierte Aktion **Bei Alarm** genau einmal ausgeführt.


Temporäre Sensorüberbrückungen können ausschließlich im Zustand **Unscharf** gesetzt oder entfernt werden. Eine Überbrückung wirkt auf alle Scharfmodi, denen die betreffende Variable zugeordnet ist, und wird bei der Scharfschaltbereitschaft, den Blockierlisten sowie der späteren Alarmauswertung ignoriert. Dadurch kann beispielsweise ein bewusst geöffnetes Fenster für genau einen Scharfschaltzyklus ausgeblendet werden. 24/7 aktive Sensoren können aus Sicherheitsgründen nicht überbrückt werden. Die Überbrückungen werden persistent gespeichert, überstehen daher `ApplyChanges()` und einen Symcon-Neustart, werden aber beim Unscharfschalten nach einem Scharfschaltzyklus automatisch vollständig gelöscht. `BypassedSensors` zeigt die derzeit überbrückten Sensoren an.

24/7 aktive Sensoren sind von den Scharfmodi unabhängig. Sie lösen sowohl im Zustand **Unscharf** als auch während Ausgangsverzögerung, **Scharf** oder Eingangsverzögerung unmittelbar den Zustand **Alarm** aus. Für solche Sensoren werden die Moduszuordnungen sowie `EntryDelay` bewusst ignoriert. Ist ein 24/7-Sensor bei `ApplyChanges()` oder nach einem Symcon-Neustart bereits ausgelöst, wird dieser Zustand unmittelbar erkannt, sodass keine Überwachungslücke bis zur nächsten Variablenänderung entsteht. Typische Anwendungsfälle sind Rauch-, Wasser- oder Panikauslöser; die Aktivierung bleibt jedoch bewusst eine explizite Benutzereinstellung.

Beim Unscharfschalten werden laufende Ein- und Ausgangsverzögerungen immer beendet. Wird ein bereits aktiver Alarm unscharf geschaltet, wird anschließend einmalig die konfigurierte Aktion **Beim Unscharfschalten nach Alarm** ausgeführt. Ein Abbruch während Ein- oder Ausgangsverzögerung löst diese Rücksetzaktion nicht aus. Die Timer verwenden persistierte Ablaufzeitpunkte und werden nach `ApplyChanges()` bzw. einem Symcon-Neustart mit der verbleibenden Zeit wiederhergestellt.

### 7. Code-Schutz

Für die spätere benutzerseitige Bedienung kann im Konfigurationsformular ein vier- bis achtstelliger Zahlencode hinterlegt werden. Das Feld wird als Passwortfeld dargestellt. Ein leerer Wert deaktiviert die Code-Prüfung.

`OHA_DisarmWithCode($InstanzID, $Code)` prüft den übergebenen Code und schaltet nur bei Übereinstimmung unscharf. Ein falscher Code verändert weder Modus noch Zustand. Die Prüfung erfolgt ohne Protokollierung des eingegebenen Codes. Der bestehende Befehl `OHA_Disarm($InstanzID)` bleibt als vertrauenswürdige direkte API für Automationen erhalten und umgeht bewusst die Code-Prüfung.

### 8. Alarmaktionen

OpenHomeAlarm verwendet für externe Reaktionen die nativen Symcon-Aktionen. Im Feld **Bei Alarm** kann ein beliebiges Ziel samt passender Aktion gewählt werden. Die Aktion wird genau einmal ausgeführt, wenn das System erstmals in den Zustand **Alarm** wechselt.

Optional kann unter **Beim Unscharfschalten nach Alarm** eine zweite Aktion hinterlegt werden. Diese wird nur ausgeführt, wenn tatsächlich ein aktiver Alarm unscharf geschaltet wird. Ein normales Unscharfschalten oder das Abbrechen einer Ein-/Ausgangsverzögerung führt die Rücksetzaktion nicht aus.

Da die Zielauswahl Bestandteil der Symcon-Aktion ist, können sowohl einzelne Gerätevariablen als auch Skripte, Ablaufpläne und andere von Symcon angebotene Aktionsziele verwendet werden. Nicht konfigurierte Alarmaktionen haben keine Wirkung auf die Kernlogik. Auch eine fehlerhafte optionale Aktion verhindert nicht den Wechsel des Alarmzustands oder das Unscharfschalten.

### 9. Alarmgedächtnis

Beim tatsächlichen Eintritt in den Zustand **Alarm** speichert OpenHomeAlarm den auslösenden Sensor und den Alarmzeitpunkt. Bei einem Sensor mit Eingangsverzögerung wird dabei der Sensor gemerkt, der den Countdown gestartet hat; auch wenn dieser Sensor vor Ablauf der Verzögerung wieder in den Ruhezustand zurückkehrt, bleibt er die Alarmquelle. Ein Sensor ohne eingetragenen Namen wird ersatzweise über seine Variablen-ID bezeichnet.

Das Alarmgedächtnis bleibt beim Unscharfschalten erhalten. Dadurch ist nach der Rückkehr weiterhin nachvollziehbar, welcher Sensor den letzten Alarm ausgelöst hat. `OHA_ClearAlarmMemory($InstanzID)` quittiert das Alarmgedächtnis und leert Quelle und Zeitpunkt. Während eines noch aktiven Alarms wird die Quittierung abgelehnt.

### 10. Visualisierung

Eine eigene Visualisierung mit Code-Eingabe zum Unscharfschalten wird in einem späteren Entwicklungsschritt umgesetzt. Sie verwendet dafür die bereits vorhandene Code-Prüfung und das Alarmgedächtnis des Moduls.

### 11. PHP-Befehlsreferenz

Folgende öffentliche Modulbefehle stehen zur Verfügung:

| PHP-Befehl | Rückgabe | Bedeutung |
| --- | --- | --- |
| `OHA_ArmHome($InstanzID)` | `bool` | Prüft die für **Zuhause** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_ArmAway($InstanzID)` | `bool` | Prüft die für **Abwesend** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_ArmNight($InstanzID)` | `bool` | Prüft die für **Nacht** relevanten Sensoren und startet bei Erfolg die Scharfschaltung |
| `OHA_BypassSensor($InstanzID, $VariableID)` | `bool` | Überbrückt einen normalen konfigurierten Scharfsensor temporär; nur im Zustand **Unscharf** möglich |
| `OHA_RemoveSensorBypass($InstanzID, $VariableID)` | `bool` | Entfernt eine einzelne temporäre Sensorüberbrückung; nur im Zustand **Unscharf** möglich |
| `OHA_ClearSensorBypasses($InstanzID)` | `bool` | Entfernt alle temporären Sensorüberbrückungen; nur im Zustand **Unscharf** möglich |
| `OHA_Disarm($InstanzID)` | `bool` | Schaltet die Anlage als vertrauenswürdige direkte API ohne Code-Prüfung unscharf und setzt den Scharfmodus zurück |
| `OHA_DisarmWithCode($InstanzID, $Code)` | `bool` | Prüft den optionalen Unscharfschaltcode und schaltet bei Erfolg unscharf |
| `OHA_ClearAlarmMemory($InstanzID)` | `bool` | Quittiert das gespeicherte Alarmgedächtnis; während eines aktiven Alarms wird `false` zurückgegeben |

Die drei Scharfschaltbefehle liefern `false`, wenn mindestens ein für den Zielmodus relevanter Sensor ausgelöst, nicht vorhanden oder nicht auswertbar ist. In diesem Fall bleiben `Mode` und `State` unverändert.
