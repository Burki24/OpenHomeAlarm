# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Funktionsstatus:** OpenHomeAlarm stellt eine vollständige, praktisch auf Symcon 9.x geprüfte Alarmsteuerung bereit. Dazu gehören unabhängige Alarmbereiche, mehrere Scharfmodi, wiederanlaufsichere Verzögerungen, 24/7-Sensoren, Störungsüberwachung, Sensorüberbrückungen, Benutzer-Codeschutz, automatische Scharfschaltung, Alarmaktionen und Eskalationsstufen, Alarmgedächtnis, Ereignis- und Diagnoseexporte sowie eine versionierte Konfigurationssicherung. Die HTML-SDK-Kachel und die IPSView-WebContent-Seite verwenden denselben Bedienzustand und Funktionsumfang.

> **Sicherheitshinweis:** OpenHomeAlarm ist keine zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage. Die Verfügbarkeit hängt von Symcon, Hostsystem, Netzwerk, Sensoren und konfigurierten Aktionen ab. Für normativ oder versicherungsrechtlich geforderte Schutzaufgaben ist geeignete zertifizierte Sicherheitstechnik erforderlich. Weitere Hinweise enthält die [Sicherheitsrichtlinie](../SECURITY.md).

## Schnellstart für neue Anwender

1. Installieren Sie die Library über die Symcon-Modulverwaltung und legen Sie eine
   **OpenHomeAlarm**-Instanz an.
2. Prüfen Sie unter **Alarmbereiche**, dass `main` aktiv ist. `main` ist fest
   die Gesamtanlage; weitere Bereiche wie `garage` können später einzeln
   bedient werden.
3. Öffnen Sie **Sensoren und Auslöser**, klicken Sie auf **Hinzufügen**, wählen
   Sie eine Symcon-Variable und ordnen Sie den Sensor mindestens einem Bereich
   und einem Scharfmodus zu. Ein Sensor darf mehreren Bereichen zugeordnet werden.
4. Übernehmen Sie die Konfiguration und öffnen Sie die HTML-SDK-Kachel. Im
   unscharfen Zustand wählen Sie **Zuhause**, **Abwesend** oder **Nacht**.
   Wählen Sie `main` für alle aktiven Bereiche oder einen anderen Bereich für
   eine Einzelbedienung.
5. Testen Sie anschließend Unscharfschaltung, Sensorblockade und – falls
   eingerichtet – die Alarmaktion. Erst danach sollten automatische Pläne oder
   Eskalationsstufen aktiviert werden.

**Wichtig:** „Aktiv“ bei einem Bereich oder Sensor bedeutet nur verfügbar – es
schaltet noch nichts scharf. Ein Scharfschaltversuch über `main` wird vollständig
abgelehnt, sobald ein aktiver Bereich nicht bereit ist; es gibt keine teilweise
aktivierte Anlage.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Sensoren und Auslöser](#6-sensoren-und-auslöser)
7. [Systemüberwachung](#7-systemüberwachung)
8. [Code-Schutz](#8-code-schutz)
9. [Automatische Scharfschaltung](#9-automatische-scharfschaltung)
10. [Alarmaktionen](#10-alarmaktionen)
11. [Alarmgedächtnis](#11-alarmgedächtnis)
12. [Ereignisprotokoll und Diagnose](#12-ereignisprotokoll-und-diagnose)
13. [Konfigurationssicherung](#13-konfigurationssicherung)
14. [Visualisierung](#14-visualisierung)
15. [PHP-Befehlsreferenz](#15-php-befehlsreferenz)

### 1. Funktionsumfang

Das Modul stellt das grundlegende Zustandsmodell der Alarmanlage, ein herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Sensorüberwachung, die globale und modusabhängige Scharfschaltbereitschaft inklusive der jeweils blockierenden Sensoren, die zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen für einen Scharfschaltzyklus, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit laufendem Countdown-Status, optionaler Countdown-Aktion und Ausgangsweg-Sensoren, konfigurierbare Alarm-Eskalationsaktionen mit optionaler automatischer Alarmdauer und separatem Stoppen von Signalgebern, eine 24/7-Systemüberwachung für Manipulation, Batterie-/Stromversorgung, Kommunikation und Gerätestörungen mit optionaler Scharfschaltblockade oder Alarmauslösung, eine optionale Code-Prüfung zum Unscharfschalten, ein quittierbares Alarmgedächtnis, ein persistentes Sicherheits-Ereignisprotokoll sowie eine versionierte öffentliche Bedien-API bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Symcon-Variablen können als Sensor oder Auslöser hinterlegt und den Scharfmodi Zuhause, Abwesend und Nacht zugeordnet werden. Zusätzlich kann ein Sensor als **24/7 aktiv** markiert werden und löst dann unabhängig vom Scharfmodus sofort aus. Sensortyp, Auslösewert sowie die Nutzung als Ausgangsweg und der Eingangsverzögerung werden ebenfalls gespeichert. Der Auslösewert wird aus den diskreten Zuständen der ausgewählten Symcon-Variable abgeleitet. Boolean-, String- und numerische Zustände werden dabei einheitlich als Auswahlliste mit den in Symcon hinterlegten Beschriftungen angeboten. Für Bewegungsmelder oder vergleichbare Sensoren kann zusätzlich **Alarm bei erneuter Auslösung verlängern** aktiviert werden. Löst ein solcher Sensor während eines laufenden Alarms erneut aus, beginnt die Alarmdauer erneut und die Eskalationsaktionen werden noch einmal ausgeführt.

Ausgangs- und Eingangsverzögerung sind global in Sekunden konfigurierbar. Der Wert `0` deaktiviert die jeweilige Verzögerung. Sensoren können zusätzlich als **Ausgangsweg** markiert werden. Bei aktivierter Ausgangsverzögerung dürfen sie beim Start der Scharfschaltung bereits ausgelöst sein. Ein Bewegungsmelder des Ausgangswegs darf auch am Countdown-Ende noch aktiv sein, weil sein Boolean-Wert nach einer Bewegung häufig nachläuft. Nach dem Scharfschalten wird er beim Zurückkehren in den Ruhezustand wieder regulär ausgewertet und löst bei der nächsten Aktivierung Alarm aus. Andere Sensortypen des Ausgangswegs, insbesondere Öffnungskontakte, müssen am Countdown-Ende bereit sein. Bei deaktivierter Ausgangsverzögerung gelten Ausgangswegsensoren wie normale Sensoren und müssen bereits vor dem Scharfschalten bereit sein. Während eines laufenden Countdowns steht die verbleibende Zeit in der öffentlichen Statusabfrage `OHA_GetControlState($InstanzID)` bereit. Bei einer Eingangsverzögerung enthält sie zusätzlich den Sensor, der den Countdown gestartet hat; die Werte werden beim Abbruch, Abschluss oder Alarm zurückgesetzt. Die optionale Countdown-Aktion wird für jeden positiven Wert genau einmal ausgeführt. Ein Aktionsskript kann `OHA_GetControlState($InstanzID)` verwenden, um etwa Restzeit, auslösenden Sensor, Scharfmodus und Zustand für eine Sprachausgabe oder Signaltöne auszuwerten. Alarmreaktionen werden als Eskalationsaktionen konfiguriert. Die Alarmdauer ist global in Sekunden konfigurierbar; der Standardwert `0` lässt den Alarmausgang aktiv, bis er manuell zurückgesetzt oder die Anlage unscharf geschaltet wird. Ist eine Alarmdauer größer als `0`, ist **Nach Ablauf der Alarmdauer automatisch wieder scharf schalten** standardmäßig aktiv: Der betroffene Bereich wird nach dem Zurücksetzen der Alarmaktionen nur im vorherigen Modus wieder scharf, wenn kein relevanter Sensor ausgelöst und keine blockierende Störung aktiv ist. Das Alarmgedächtnis bleibt dabei als Nachweis erhalten. Für einen erkannten Fehlalarm steht in der Kachel zusätzlich **Fehlalarm zurücksetzen** bereit. Diese Schaltfläche setzt Aktionen und Alarmgedächtnis zurück und schaltet den Bereich ebenfalls nur bei voller Bereitschaft wieder scharf. Für benutzerseitiges Unscharfschalten kann optional ein vier- bis achtstelliger Zahlencode hinterlegt werden.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Im Konfigurationsformular können die globale **Ausgangsverzögerung** und **Eingangsverzögerung** in Sekunden festgelegt werden. Unter **Countdown-Aktionen** kann optional eine oder mehrere normale Symcon-Aktionen angelegt werden. Sie laufen bei jeder Sekunde einer aktiven Ein- oder Ausgangsverzögerung und können beispielsweise eine Restzeit ansagen, einen Gong auslösen oder einen Statuswert setzen. Eine leere Liste führt keine Aktion aus. Im Abschnitt **Code-Schutz** kann optional ein vier- bis achtstelliger **Unscharfschaltcode** hinterlegt werden. **Automatische Scharfschaltung** verwaltet wöchentliche Schaltpläne mit Wochentagen, Uhrzeit und Zielmodus. Im Abschnitt **Alarmaktionen** werden die **Alarmdauer** und alle Alarmreaktionen ausschließlich als **Alarm-Eskalationsaktionen** konfiguriert. Im Abschnitt **Systemüberwachung** wird außerdem das Intervall der **Sensor-Integritätsprüfung** festgelegt; dort stehen auch die optionalen Listen **Aktionen bei neuer Störung** und **Aktionen bei behobener Störung** bereit. Die darin angelegten Symcon-Aktionen werden jeweils nur beim Zustandswechsel einmal ausgeführt; leere Listen führen keine Aktion aus. Darunter steht die Liste **Sensoren und Auslöser** zur Verfügung. Dort kann ein Sensor zusätzlich als **Ausgangsweg** markiert werden. Ein Eintrag verweist direkt auf eine vorhandene Symcon-Variable und ist damit unabhängig vom Hersteller oder Protokoll des eigentlichen Geräts.

#### Alarmbereiche

Mit Alarmbereichen können beispielsweise Wohnhaus und Garage unabhängig voneinander scharf- und unscharf geschaltet werden. `main` ist dabei fest die **Gesamtanlage**: Scharf- und Unscharfschalten über ihn wirkt auf alle aktiven Bereiche.

##### Beispiel: Bereich „Garage“ einrichten

**1. Bereich anlegen**

In der Instanzkonfiguration unter **Alarmbereiche** auf **Hinzufügen** klicken und folgende Werte eintragen:

| Feld | Wert im Beispiel | Bedeutung |
| --- | --- | --- |
| Aktiv | Ein | Der Bereich kann verwendet werden. Er ist dadurch noch nicht scharfgeschaltet. |
| Bereichs-ID | `garage` | Technischer Schlüssel für Zuordnungen und PHP-Befehle |
| Name | `Garage` | Frei wählbarer Anzeigename |

Danach **Änderungen übernehmen**. Der vorhandene Bereich `main` muss aktiv bleiben; er wird nicht ausgewählt, sondern ist fest die Gesamtanlage.

**2. Sensoren zuordnen**

Den gewünschten Eintrag unter **Sensoren und Auslöser** bearbeiten. Im Editor werden alle aktiven Alarmbereiche als Checkboxen angezeigt. **Garage** aktivieren und die Änderungen übernehmen. Ein Sensor kann gleichzeitig mehreren Bereichen zugeordnet werden; sein Zustand beeinflusst dann die Bereitschaft jedes zugeordneten Bereichs und löst nur in den jeweils scharfgeschalteten beziehungsweise bei 24/7-Sensoren in allen zugeordneten Bereichen aus. Neue Sensoren sind automatisch `main` zugeordnet. Störungseingänge bleiben einem einzelnen Bereich zugeordnet und verwenden bei neuen Einträgen ebenfalls automatisch `main`.

Temporäre Überbrückungen gelten immer nur für den aktuell ausgewählten Bereich. Ist derselbe Sensor beispielsweise **Haus** und **Garage** zugeordnet, lässt eine Überbrückung in **Garage** seine Überwachung in **Haus** unverändert.

**3. Einzelnen Bereich oder Gesamtanlage schalten**

Ein Symcon-Skript anlegen und folgenden Befehl verwenden:

```php
OHA_ArmPartition(12345, 'garage', 'away');
```

- `12345` durch die Objekt-ID der OpenHomeAlarm-Instanz ersetzen.
- `garage` ist die zuvor eingetragene Bereichs-ID.
- `away` ist der Scharfmodus. Zulässig sind `home`, `away` und `night`.
- Der Befehl verändert keinen anderen Alarmbereich.

Für die Gesamtanlage verwenden Sie `main` beziehungsweise die bestehenden Befehle ohne Bereichs-ID:

```php
// Alle aktiven Bereiche im Abwesend-Modus scharfschalten
OHA_ArmAway(12345);

// Alle aktiven Bereiche unscharf schalten
OHA_Disarm(12345);
```

Vor dem Scharfschalten prüft OpenHomeAlarm alle aktiven Bereiche. Blockiert ein Sensor oder Störungseingang einen Bereich, bleibt die gesamte Anlage unverändert unscharf.

**4. Nur die Garage unscharf schalten**

```php
OHA_DisarmPartition(12345, 'garage');
```

Auch dieser Befehl verändert keinen anderen Alarmbereich.

##### Was bedeuten die Begriffe?

| Begriff | Bedeutung |
| --- | --- |
| Aktiv | Der Bereich steht zur Verfügung und kann Sensoren erhalten. Das ist kein Scharfbefehl. |
| `main` | Feste Gesamtanlage. Die Befehle ohne Bereichsangabe sowie die Auswahl `main` in Kachel und IPSView schalten alle aktiven Bereiche gemeinsam; Kachel und IPSView können weiterhin einzelne Bereiche auswählen |
| Scharfgeschaltet | Laufzeitzustand eines Bereichs; seine zugeordneten Sensoren werden entsprechend dem gewählten Modus überwacht |

Die HTML-SDK-Kachel und die IPSView-Seite zeigen oberhalb des Sicherheitsstatus eine Bereichsauswahl. Scharf-/Unscharfschaltung, Bereitschaft, Diagnose, Alarmgedächtnis und Sensorüberbrückungen beziehen sich auf den dort gewählten Bereich. Die öffentlichen PHP-Funktionen stehen zusätzlich für Automationen zur Verfügung.

##### Regeln für die Bereichs-ID

- 1 bis 32 Zeichen
- beginnt mit einem Kleinbuchstaben
- danach sind Kleinbuchstaben, Ziffern, `_` und `-` zulässig
- keine Leerzeichen, Großbuchstaben oder Umlaute

Gültig sind beispielsweise `main`, `garage`, `erdgeschoss`, `bereich_1` und `aussen-2`. Ungültig sind `1`, `Garage`, `außen` und `mein bereich`. Da die ID in Zuordnungen und Skripten verwendet wird, sollte sie später nicht ohne Anpassung dieser Verwendungen geändert werden.

##### Technischer Hintergrund

Änderungen an Bereichen, Sensoren und Störungseingängen sind nur möglich, wenn alle Bereiche unscharf sind. Die Control API 2 veröffentlicht jeden aktiven Bereich mit eigenem Modus, Zustand, Countdown, Alarmausgang und Alarmgedächtnis unter `Partitions`; `DefaultPartition` ist stets `main`. `OHA_GetPartitions($InstanzID)` liefert die konfigurierten Bereichsmetadaten. Laufzeiten, Fristen und Alarmdaten werden neustartsicher gespeichert. Die bestehenden Instanzvariablen `AlarmOutputActive`, `AlarmMemory`, `LastAlarmSource` und `LastAlarmTime` fassen den Gesamtzustand aller Bereiche zusammen.

### 5. Statusvariablen und Darstellungen

OpenHomeAlarm legt folgende schreibgeschützte Statusvariablen an:

| Variable | Bedeutung | Initialwert |
| --- | --- | --- |
| `Mode` | Gewählter Scharfmodus: Kein Scharfmodus, Zuhause, Abwesend oder Nacht | Kein Scharfmodus |
| `State` | Aktuelle Systemphase: Unscharf, Ausgangsverzögerung, Scharf, Eingangsverzögerung oder Alarm | Unscharf |
| `DelayRemaining` | Verbleibende Sekunden einer laufenden Ein- oder Ausgangsverzögerung | 0 s |
| `DelaySource` | Sensor, der die aktuelle Eingangsverzögerung gestartet hat; bei Ausgangsverzögerung leer | leer |
| `AlarmOutputActive` | Zeigt, ob der Alarmausgang innerhalb eines aktiven Alarms noch aktiv ist | Alarmausgang inaktiv |
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
| `SystemFault` | Zeigt an, ob ein Störungseingang aktiv/nicht auswertbar oder eine genutzte Sensorvariable nicht verfügbar ist | Keine Systemstörung |
| `ActiveFaults` | Namen aller aktiven bzw. nicht auswertbaren Störungseingänge und nicht verfügbaren Sensoren | leer |
| `BlockingFaults` | Aktive Störungen, die die Scharfschaltung aller Modi blockieren | leer |
| `LastFaultSource` | Name der zuletzt neu aufgetretenen Störung/Manipulation | leer |
| `LastFaultTime` | Zeitpunkt der zuletzt neu aufgetretenen Störung im Format `TT.MM.JJJJ HH:MM:SS` | leer |

Die Variablen verwenden native Symcon-Darstellungen. Bereits vorhandene Betriebszustände werden bei einem Modulupdate nicht auf die Initialwerte zurückgesetzt.

### 6. Sensoren und Auslöser

Jeder konfigurierte Eintrag enthält folgende Daten:

| Feld | Bedeutung |
| --- | --- |
| `Enabled` | Eintrag grundsätzlich aktiviert/deaktiviert |
| `PartitionID` | Technische ID des Alarmbereichs; leer wird `main` zugeordnet |
| `Name` | Frei wählbare Bezeichnung |
| `VariableID` | ID der verwendeten Symcon-Variable |
| `SensorType` | Öffnungskontakt, Bewegungsmelder, Glasbruch-, Rauch- oder Wassermelder, Panikauslöser oder sonstiger Auslöser |
| `TriggerValue` | Rohwert, bei dem der Eintrag später als ausgelöst gilt; diskrete Zustände werden aus der Variablendarstellung bzw. vorhandenen Profil-Assoziationen übernommen |
| `ArmHome` | Im Scharfmodus Zuhause relevant |
| `ArmAway` | Im Scharfmodus Abwesend relevant |
| `ArmNight` | Im Scharfmodus Nacht relevant |
| `AlwaysActive` | 24/7 aktiv; löst unabhängig vom Scharfmodus sofort aus |
| `ExitDelay` | Kennzeichnet einen Sensor des Ausgangswegs; darf bei aktiver Ausgangsverzögerung beim Start ausgelöst sein. Bewegungsmelder dürfen wegen ihres nachlaufenden Werts auch am Countdown-Ende aktiv sein |
| `EntryDelay` | Startet bei Auslösung im scharfen Betrieb die konfigurierte Eingangsverzögerung statt unmittelbar den Alarmzustand |

Beim Bearbeiten eines Eintrags liest OpenHomeAlarm die aktuelle Symcon-Variablendarstellung aus. Definierte Optionen einer Boolean- oder String-Wertanzeige, Aufzählungen sowie diskrete numerische Intervalle erscheinen immer als dieselbe Auswahlliste. Für ältere Variablen werden vorhandene Profil-Assoziationen ebenfalls übernommen. Nur wenn eine Variable keine diskreten Zustände bereitstellt, bleibt eine direkte Rohwerteingabe als Fallback sichtbar. Gespeichert wird weiterhin der Rohwert als String, damit das Sensor-Datenmodell stabil bleibt.

Aktive Sensoren werden per `VM_UPDATE` überwacht, sobald sie mindestens einem Scharfmodus zugeordnet oder als **24/7 aktiv** markiert sind. OpenHomeAlarm vergleicht den aktuellen Variablenwert typgerecht mit dem gespeicherten Rohwert. Boolean-, Integer-, Float- und Stringwerte werden entsprechend ihrem tatsächlichen Variablentyp ausgewertet. Nach `ApplyChanges()` oder einem Symcon-Neustart werden die aktuell anliegenden Sensorwerte zusätzlich erneut geprüft. Dadurch wird auch eine Auslösung erkannt, die während eines Neustarts erfolgt ist und deshalb kein neues `VM_UPDATE` mehr erzeugt. Bereits laufende Eingangsverzögerungen werden dabei nicht neu gestartet; ein gleichzeitig aktiver Sofortalarm-Sensor eskaliert weiterhin unmittelbar zum Alarm.

`ReadyToArm` bleibt als konservative Gesamtbereitschaft erhalten. Bei aktivierter Ausgangsverzögerung werden dabei nur erreichbare, ausdrücklich markierte Sensoren des Ausgangswegs von der initialen Bereitschaftsprüfung ausgenommen. Am Ende des Countdowns gilt diese Ausnahme nur noch für Bewegungsmelder, damit deren nachlaufender Auslösewert die Scharfschaltung nicht abbricht. Alle übrigen ausgelösten oder nicht auswertbaren Sensoren setzen die Bereitschaft weiterhin auf **Nicht bereit**. Zusätzlich zeigen `ReadyHome`, `ReadyAway` und `ReadyNight` die tatsächliche Scharfschaltbereitschaft des jeweiligen Modus. Ein beispielsweise nur für **Abwesend** relevanter offener Kontakt setzt deshalb `ReadyAway` auf **Nicht bereit**, während `ReadyHome` und `ReadyNight` weiterhin **Bereit** bleiben können. Die Variablen `BlockingHomeSensors`, `BlockingAwaySensors` und `BlockingNightSensors` nennen dabei direkt die Sensoren, die den jeweiligen Modus aktuell blockieren. Mehrere Sensoren werden kommasepariert ausgegeben; bei fehlendem Namen wird die Variablen-ID verwendet. 24/7-Sensoren wirken auf alle drei Modi. Andere Sensoren und blockierende Störungen werden am Countdown-Ende weiterhin strikt geprüft. Nicht mehr vorhandene oder nicht auswertbare relevante Sensorvariablen führen aus Sicherheitsgründen ebenfalls zu **Nicht bereit**, erscheinen in der passenden Blockierliste und werden zusätzlich als persistente Systemstörung ausgewiesen. Ein noch nicht vollständig konfigurierter Eintrag mit `VariableID = 0` wird ignoriert.

Beim Scharfschalten prüft OpenHomeAlarm die Sensoren, die dem angeforderten Zielmodus zugeordnet sind, sowie alle 24/7 aktiven Sensoren. Ein ausgelöster Sensor, der beispielsweise ausschließlich für **Abwesend** gilt, verhindert deshalb nicht das Scharfschalten von **Zuhause**. Fehlende oder nicht auswertbare Sensoren des angeforderten Modus blockieren die Scharfschaltung aus Sicherheitsgründen. Unvollständige Listeneinträge mit `VariableID = 0` bleiben weiterhin ohne Wirkung.

Nach erfolgreicher Scharfschaltprüfung wechselt OpenHomeAlarm bei einer Ausgangsverzögerung größer als `0` zunächst in **Ausgangsverzögerung**. Erst nach Ablauf des Timers wird erneut geprüft, ob der gewählte Modus scharfschaltbereit ist. Sind dann noch relevante Sensoren ausgelöst oder nicht auswertbar, wird der Scharfschaltvorgang sicher abgebrochen und die Anlage auf **Unscharf** zurückgesetzt. Bei `0` Sekunden wird unmittelbar scharfgeschaltet.

Löst im Zustand **Scharf** ein für den aktiven Modus relevanter Sensor aus, startet ein mit `EntryDelay` markierter Sensor die konfigurierte **Eingangsverzögerung**. Der Countdown wird durch das erneute Schließen des Sensors nicht abgebrochen und bei weiteren verzögerten Sensorereignissen nicht neu gestartet. Ein Sensor ohne Eingangsverzögerung wechselt unmittelbar in den Zustand **Alarm**. Das gilt ebenfalls, wenn während einer laufenden Eingangsverzögerung ein sofort auslösender Sensor anspricht. Nach Ablauf der Eingangsverzögerung wird ebenfalls **Alarm** gesetzt. Beim erstmaligen Eintritt in den Alarmzustand werden die fälligen Alarm-Eskalationsaktionen ausgeführt.


Temporäre Sensorüberbrückungen können ausschließlich im Zustand **Unscharf** gesetzt oder entfernt werden. Eine Überbrückung wirkt auf alle Scharfmodi, denen die betreffende Variable zugeordnet ist, und wird bei der Scharfschaltbereitschaft, den Blockierlisten sowie der späteren Alarmauswertung ignoriert. Dadurch kann beispielsweise ein bewusst geöffnetes Fenster für genau einen Scharfschaltzyklus ausgeblendet werden. 24/7 aktive Sensoren können aus Sicherheitsgründen nicht überbrückt werden. Die Überbrückungen werden persistent gespeichert, überstehen daher `ApplyChanges()` und einen Symcon-Neustart, werden aber beim Unscharfschalten nach einem Scharfschaltzyklus automatisch vollständig gelöscht. `BypassedSensors` zeigt die derzeit überbrückten Sensoren an.

24/7 aktive Sensoren sind von den Scharfmodi unabhängig. Sie lösen sowohl im Zustand **Unscharf** als auch während Ausgangsverzögerung, **Scharf** oder Eingangsverzögerung unmittelbar den Zustand **Alarm** aus. Für solche Sensoren werden die Moduszuordnungen sowie `ExitDelay` und `EntryDelay` bewusst ignoriert. Ist ein 24/7-Sensor bei `ApplyChanges()` oder nach einem Symcon-Neustart bereits ausgelöst, wird dieser Zustand unmittelbar erkannt, sodass keine Überwachungslücke bis zur nächsten Variablenänderung entsteht. Typische Anwendungsfälle sind Rauch-, Wasser- oder Panikauslöser; die Aktivierung bleibt jedoch bewusst eine explizite Benutzereinstellung.

Beim Unscharfschalten werden laufende Ein- und Ausgangsverzögerungen immer beendet. Ist der Alarmausgang zu diesem Zeitpunkt noch aktiv, wird er zuerst zurückgesetzt. Wird ein bereits aktiver Alarm unscharf geschaltet, wird anschließend einmalig die konfigurierte Aktion **Beim Unscharfschalten nach Alarm** ausgeführt. Ein Abbruch während Ein- oder Ausgangsverzögerung löst diese Aktion nicht aus. Die Timer für Ein-/Ausgangsverzögerung und Alarmdauer verwenden persistierte Ablaufzeitpunkte und werden nach `ApplyChanges()` bzw. einem Symcon-Neustart mit der verbleibenden Zeit wiederhergestellt.

### 7. Systemüberwachung

Zusätzlich zu den eigentlichen Alarmsensoren können im Abschnitt **Systemüberwachung** unabhängige 24/7-Eingänge für **Manipulation**, **Batterie/Stromversorgung**, **Kommunikation**, **Gerätestörung** oder eine sonstige Störung angelegt werden. Jeder Eintrag verweist wie ein normaler Sensor auf eine Symcon-Variable; der Störwert wird aus deren Variablendarstellung übernommen oder bei Bedarf als Rohwert eingegeben.

Jeder Sensor und jeder Störungseingang ist über `PartitionID` genau einem aktiven Alarmbereich zugeordnet. Leere Zuordnungen werden auf `main` aufgelöst. Unbekannte oder deaktivierte Zielbereiche werden bereits bei `ApplyChanges()` abgewiesen, damit keine Eingänge unbemerkt außerhalb eines aktiven Bereichs liegen.

Für jeden Störungseingang kann separat festgelegt werden, ob eine aktive Störung die **Scharfschaltung blockiert** und ob sie den normalen Alarmzustand **sofort und 24/7 auslöst**. Dadurch kann beispielsweise ein Sabotagekontakt unmittelbar alarmieren, während eine schwache Batterie lediglich als Systemstörung angezeigt wird. Eine blockierende Störung setzt `ReadyHome`, `ReadyAway`, `ReadyNight` und `ReadyToArm` auf **Nicht bereit** und erscheint zusätzlich in `BlockingFaults`.

Fehlende oder nicht auswertbare konfigurierte Störungsvariablen werden sicherheitsgerichtet als Systemstörung behandelt und können – sofern so konfiguriert – die Scharfschaltung blockieren. Sie lösen den Hauptalarm jedoch nicht allein aufgrund der fehlenden Auswertbarkeit aus; dafür muss der konfigurierte Störwert eindeutig erkannt werden.

Optional können in den nativen Symcon-Aktionslisten **Aktionen bei neuer Störung** und **Aktionen bei behobener Störung** mehrere Aktionen hinterlegt werden. Ohne Zeile wird nichts ausgeführt. **Bei neuer Störung** läuft jede aktive Zeile genau einmal, wenn eine konfigurierte Störung aktiv oder ein überwachter Sensor nicht verfügbar wird – beispielsweise für eine Push-Nachricht, Warnansage oder ein Warnlicht. **Bei behobener Störung** läuft jede aktive Zeile genau einmal, wenn dieselbe Störung wieder in Ordnung ist – beispielsweise für eine Entwarnung oder zum Ausschalten des Warnlichts. Zustandswechsel werden persistent verfolgt, sodass ein `ApplyChanges()` oder Symcon-Neustart eine bereits aktive Störung nicht mehrfach als neu meldet. Neu aufgetretene und behobene Störungen werden zusätzlich als `fault_activated` bzw. `fault_cleared` im Sicherheits-Ereignisprotokoll gespeichert.

Dasselbe Störungsmodell überwacht die Verfügbarkeit aller aktiv genutzten Alarmsensoren. OpenHomeAlarm reagiert unmittelbar auf die Symcon-Meldung, wenn eine konfigurierte Sensorvariable entfernt wird, und prüft alle Sensoren zusätzlich im konfigurierbaren Intervall von standardmäßig 60 Sekunden. Eine fehlende oder nicht lesbare Sensorvariable setzt `SystemFault`, erscheint mit dem Hinweis **Sensor nicht verfügbar** in `ActiveFaults` und in der Kachel und blockiert die zugeordneten Scharfmodi. Der Verlust allein wird nicht als bestätigter Einbruch gewertet und löst deshalb keinen Hauptalarm aus. Auftreten und Behebung verwenden die vorhandenen Störungsaktionen und werden als `fault_activated` beziehungsweise `fault_cleared` protokolliert.

Die generische Prüfung kann erkennen, ob eine Symcon-Variable fehlt oder nicht gelesen werden kann. Bleibt eine Variable vorhanden und liefert lediglich einen veralteten letzten Wert, ist das ohne anbieterspezifisches Kommunikations- oder Zeitkriterium nicht zuverlässig feststellbar. Für solche Geräte sollte ein separater Kommunikations- oder Gerätestörungswert als Störungseingang konfiguriert werden.

### 8. Code-Schutz

Für die benutzerseitige Bedienung können im Konfigurationsformular mehrere aktivierbare Benutzer mit Namen und individuellem vier- bis achtstelligem Zahlencode hinterlegt werden. Doppelte aktive Codes werden abgelehnt. Der bisherige einzelne Unscharfschaltcode bleibt als kompatibler Legacy-Code erhalten; sind weder ein Legacy-Code noch aktive Benutzercodes konfiguriert, ist die Code-Prüfung deaktiviert.

Der Code wird als lokale Symcon-Instanzeigenschaft gespeichert. Das Passwortfeld verhindert die offene Anzeige im Konfigurationsformular, ersetzt aber keinen Schutz der Symcon-Administration, Sicherungen und JSON-RPC-Zugänge. Der Code wird nicht in Statusvariablen, Bedienzustand, Debug-Ausgaben oder Ereignisprotokoll übernommen.

`OHA_DisarmWithCode($InstanzID, $Code)` prüft den übergebenen Code und schaltet nur bei Übereinstimmung unscharf. Bei einem Benutzercode wird ausschließlich der zugehörige Benutzername als Quelle des Unscharfschalt-Ereignisses gespeichert. Ein falscher Code verändert weder Modus noch Zustand. Kein eingegebener oder konfigurierter Code wird protokolliert. Standardmäßig wird die Code-Eingabe benutzerübergreifend nach fünf Fehlversuchen für 60 Sekunden gesperrt. Anzahl und Sperrdauer sind im Konfigurationsformular einstellbar. Fehlversuchszähler und Ablaufzeitpunkt der Sperre werden persistent gespeichert, sodass ein `ApplyChanges()` oder Symcon-Neustart die Sperre nicht umgeht. Nach Ablauf oder einem erfolgreichen Unscharfschalten wird der Zähler zurückgesetzt.

Der bestehende Befehl `OHA_Disarm($InstanzID)` bleibt als vertrauenswürdige direkte API für Automationen erhalten und umgeht bewusst Code-Prüfung und Benutzersperre. Der öffentliche Bedienzustand enthält unter `CodeProtection` ausschließlich Sperrstatus, verbleibende Versuche und Sperrdauer; weder der konfigurierte noch der eingegebene Code werden ausgegeben.

### 9. Automatische Scharfschaltung

Im Abschnitt **Automatische Scharfschaltung** können beliebig viele wöchentliche Zeitpläne aktiviert werden. Jeder Eintrag besitzt einen Namen, die gewünschten Wochentage, eine lokale Uhrzeit im Format `HH:MM` und den Zielmodus **Zuhause**, **Abwesend** oder **Nacht**. Die lokale Zeit und Zeitzone der Symcon-Installation sind maßgeblich.

Ein fälliger Zeitplan verwendet exakt dieselbe Scharfschaltlogik wie `OHA_Arm()`. Ausgelöste oder nicht verfügbare Sensoren und blockierende Störungen verhindern die automatische Scharfschaltung deshalb unverändert. Sensorüberbrückungen werden nicht automatisch angelegt. Ist die Anlage bereits nicht mehr unscharf, wird weder der Modus gewechselt noch unscharf geschaltet.

Jeder Zeitplan wird innerhalb derselben Minute höchstens einmal ausgeführt. Der Ausführungsmarker wird persistent gespeichert, sodass wiederholte Timeraufrufe, `ApplyChanges()` oder ein Symcon-Neustart keine zweite Ausführung in derselben Minute verursachen. War Symcon während der vollständigen Zielminute nicht betriebsbereit, wird der verpasste Zeitplan aus Sicherheitsgründen nicht nachträglich ausgeführt. Erfolg und Ablehnung werden als `automatic_arming_succeeded` beziehungsweise `automatic_arming_rejected` mit dem Zeitplannamen im Ereignisprotokoll gespeichert.

### 10. Alarmaktionen

OpenHomeAlarm verwendet für externe Reaktionen ausschließlich **Alarm-Eskalationsaktionen**. Jede Tabellenzeile entspricht einer Aktion. Mehrere Aktionen mit derselben Verzögerung werden gemeinsam fällig und bilden damit eine Eskalationsstufe. Die Verzögerung wird ab dem Beginn des gemeinsamen Alarmausgangs gemessen; jede aktive Aktion wird in der konfigurierten Reihenfolge genau einmal ausgeführt.

Die **Alarmdauer** legt fest, nach wie vielen Sekunden der jeweilige Bereichsausgang automatisch zurückgesetzt wird. Standard ist `0`; der Alarmausgang bleibt dann aktiv, bis er manuell zurückgesetzt oder der betreffende Bereich unscharf geschaltet wird. Mehrere gleichzeitig ausgelöste Bereiche besitzen getrennte Ablaufzeitpunkte. Die zusammengefasste Instanzausgabe bleibt aktiv, solange mindestens ein Bereichsausgang aktiv ist. Die Rücksetzung beendet den jeweiligen Alarmausgang, lässt jedoch Alarmgedächtnis und Bereichszustand erhalten. Die Alarmdauer ist wiederanlaufsicher.

Da die Zielauswahl Bestandteil der nativen Symcon-Aktion ist, können einzelne Gerätevariablen, Skripte, Ablaufpläne und andere Symcon-Aktionsziele verwendet werden. Eine fehlerhafte Aktion verhindert weder den Alarmzustand noch das Unscharfschalten.

#### Eskalationsaktionen einrichten

1. Öffnen Sie in der Instanzkonfiguration den Abschnitt **Alarmaktionen**.
2. Wählen Sie unter **Alarm-Eskalationsaktionen** die Schaltfläche **Hinzufügen**. Dadurch wird eine neue Aktionszeile angelegt und deren Bearbeitungsdialog geöffnet.
3. Aktivieren Sie die Zeile mit **Aktiv** und vergeben Sie einen verständlichen Namen, beispielsweise `Flurlicht einschalten`, `Sirene einschalten` oder `Benachrichtigung senden`.
4. Tragen Sie unter **Verzögerung (Sekunden)** ein, wie viele Sekunden nach Beginn des gemeinsamen Alarmausgangs die Aktion ausgeführt werden soll. Der Wert `0` führt die Aktion unmittelbar aus.
5. Wählen Sie unter **Aktion** das gewünschte Symcon-Ziel und anschließend die auszuführende native Symcon-Aktion aus.
6. Wählen Sie unter **Rücksetzverhalten** eine der drei Möglichkeiten: **Keine Rücksetzung**, **Boolean automatisch umkehren** oder **Eigene Rücksetzaktion verwenden**.
7. Nur bei **Eigene Rücksetzaktion verwenden** erscheint das Feld **Eigene Rücksetzaktion**. Wählen Sie dort das gewünschte Ziel und den exakten Rückgabewert, beispielsweise `Auf` für einen zuvor auf `Zu` gefahrenen Rollladen. Bei **Keine Rücksetzung** und **Boolean automatisch umkehren** ist dieses Aktionsfeld nicht vorhanden und muss daher auch nicht ausgefüllt werden.
8. Kennzeichnen Sie eine Sirene oder einen anderen akustischen Alarmgeber zusätzlich als **Signalgeber**. Ein Signalgeber benötigt zwingend eine automatische Boolean-Rücksetzung oder eine eigene Rücksetzaktion.
9. Bestätigen Sie den Bearbeitungsdialog. Weitere Aktionen werden jeweils als eigene Tabellenzeile hinzugefügt.
10. Übernehmen Sie abschließend die Änderungen der Instanzkonfiguration.

Eine Eskalationsstufe entsteht durch die eingetragene Verzögerung: Alle aktiven Zeilen mit derselben Verzögerung gehören funktional zur gleichen Stufe und werden beim Erreichen dieses Zeitpunkts nacheinander ausgeführt. Beispielsweise können drei Zeilen mit `0` Sekunden gleichzeitig Licht, Innensirene und Außensirene einschalten. Eine weitere Zeile mit `60` Sekunden kann nach einer Minute eine zusätzliche Benachrichtigung auslösen. Für mehrere Aktionen derselben Stufe muss deshalb keine Unterliste geöffnet werden.

Eine vorhandene Zeile kann über das Zahnrad bearbeitet, über **Aktiv** vorübergehend deaktiviert oder über den Papierkorb gelöscht werden. Deaktivieren behält die Konfiguration der Aktion für eine spätere erneute Aktivierung bei. Löschen entfernt die vollständige Aktionszeile. Änderungen an einer bereits laufenden Alarmeskalation sollten vermieden werden; konfigurieren und testen Sie die Aktionen bei unscharfer Anlage.

#### Signalgeber separat stoppen

Bei einem aktiven Alarm erscheint in der Kachel zusätzlich **Signalgeber stoppen**, sobald mindestens ein als Signalgeber gekennzeichneter Eintrag ausgeführt wurde. Die Schaltfläche führt ausschließlich dessen Rücksetzaktion aus. Der Alarmausgang, Alarmgedächtnis sowie andere Eskalationsaktionen wie Licht oder Rollläden bleiben unverändert aktiv. **Alarmaktionen zurücksetzen** bleibt die bewusste Gesamtaktion und setzt anschließend alle noch nicht zurückgesetzten Eskalationsaktionen zurück.

#### Rücksetzverhalten

Für jede Eskalationsaktion kann das Rücksetzverhalten unabhängig festgelegt werden:

Standardmäßig ist **Keine Rücksetzung** gewählt. Eine Gegenaktion wird erst ausgeführt, wenn sie ausdrücklich aktiviert beziehungsweise konfiguriert wurde.

- **Keine Rücksetzung** führt beim Ende des Alarmausgangs keine Gegenaktion aus. Das eignet sich beispielsweise für Benachrichtigungen.
- **Boolean automatisch umkehren** erzeugt für native boolesche Aktionen vom Typ **Schalte auf Wert** automatisch die Gegenaktion:

  - Aus `An` wird bei der Rücksetzung `Aus`.
  - Aus `Aus` wird bei der Rücksetzung `An`.

- **Eigene Rücksetzaktion verwenden** führt die ausdrücklich konfigurierte native Symcon-Aktion aus. Damit können mehrwertige Geräte eindeutig behandelt werden, beispielsweise Rollladen bei Alarm auf `Zu` und bei Rücksetzung auf `Auf`, ein Dimmer von `100 %` zurück auf `20 %` oder eine Alarm-Szene zurück auf eine Normal-Szene.

Die Gegenaktionen werden ausgeführt, sobald der letzte aktive Alarmausgang endet – unabhängig davon, ob dies durch die konfigurierte Alarmdauer, eine manuelle Rücksetzung oder das Unscharfschalten geschieht. Wurden mehrere Aktionen ausgeführt, setzt OpenHomeAlarm sie in umgekehrter Ausführungsreihenfolge zurück. Noch nicht fällige beziehungsweise nicht ausgeführte Eskalationsaktionen werden nicht zurückgesetzt.

OpenHomeAlarm errät für Integer-, Float- oder Stringwerte keine Gegenwerte. Insbesondere wird bei Zuständen wie `Auf / Stop / Zu` niemals automatisch entschieden, ob `Auf` oder `Zu` die Gegenaktion sein soll. Dafür ist **Eigene Rücksetzaktion verwenden** vorgesehen. Die **Zusätzliche Aktion bei Rücksetzung des Alarmausgangs** bleibt davon unabhängig und wird genau einmal ausgeführt, nachdem auch der letzte Bereichsausgang zurückgesetzt wurde.

Der Ausführungsstand einschließlich der ausgeführten Aktionen und ihrer gewählten Rücksetzwege wird persistent gespeichert. Nach `ApplyChanges()` oder einem Symcon-Neustart werden bereits ausgeführte Aktionen deshalb nicht wiederholt, während noch ausstehende Aktionen mit ihrem ursprünglichen Fälligkeitszeitpunkt fortgesetzt werden. Eine Rücksetzung des letzten aktiven Alarmausgangs führt die vorgesehenen Rücksetzaktionen in umgekehrter Ausführungsreihenfolge aus und beendet anschließend den laufenden Eskalationsplan. Bestehende Einträge mit der bisherigen Option **Automatisch zurücksetzen** werden kompatibel übernommen: aktiviert entspricht **Boolean automatisch umkehren**, deaktiviert entspricht **Keine Rücksetzung**.

### 11. Alarmgedächtnis

Beim tatsächlichen Eintritt in den Zustand **Alarm** speichert OpenHomeAlarm den auslösenden Sensor und den Alarmzeitpunkt. Bei einem Sensor mit Eingangsverzögerung wird dabei der Sensor gemerkt, der den Countdown gestartet hat; auch wenn dieser Sensor vor Ablauf der Verzögerung wieder in den Ruhezustand zurückkehrt, bleibt er die Alarmquelle. Ein Sensor ohne eingetragenen Namen wird ersatzweise über seine Variablen-ID bezeichnet.

Das Alarmgedächtnis bleibt beim Unscharfschalten erhalten. Dadurch ist nach der Rückkehr weiterhin nachvollziehbar, welcher Sensor den letzten Alarm ausgelöst hat. Jeder Bereich besitzt ein eigenes Gedächtnis; die bestehenden Instanzvariablen zeigen zusammengefasst den jüngsten Alarm. `OHA_ClearAlarmMemory($InstanzID)` quittiert das Gedächtnis von `main`. `OHA_ClearAlarmMemoryPartition($InstanzID, $BereichID)` quittiert gezielt einen Bereich. Während dessen Alarmzustand noch aktiv ist, wird die Quittierung abgelehnt.

### 12. Ereignisprotokoll und Diagnose

OpenHomeAlarm führt ein persistentes, auf die letzten 100 Einträge begrenztes Sicherheits-Ereignisprotokoll. Das Protokoll bleibt über `ApplyChanges()` und einen Symcon-Neustart erhalten und wird für die spätere Visualisierung strukturiert als JSON bereitgestellt. Der jeweils neueste Eintrag steht an erster Stelle.

Jeder Eintrag enthält `Time` als Unix-Zeitstempel, `Event` als maschinenlesbaren Ereignistyp, den zum Ereignis gehörenden `Mode` und `State`, die technische `PartitionID` sowie optional `Source`. Ältere gespeicherte Einträge ohne Bereichsangabe bleiben lesbar und erhalten eine leere `PartitionID`. Als Quelle werden bei Alarmen, Eingangsverzögerungen und Sensorüberbrückungen die betroffenen Sensornamen gespeichert; bei abgelehnten oder nach der Ausgangsverzögerung abgebrochenen Scharfschaltungen enthält `Source` die blockierenden Sensoren.

Protokolliert werden erfolgreiche und abgelehnte Scharfschaltungen, Start der Ein- und Ausgangsverzögerung, Alarm, Rücksetzungen des Alarmausgangs, Unscharfschalten, temporäre Sensorüberbrückungen, das Löschen des Alarmgedächtnisses, neu aufgetretene bzw. behobene Systemstörungen sowie abgewiesene Code-Eingaben und ausgelöste temporäre Code-Sperren. Weder der konfigurierte Unscharfschaltcode noch ein eingegebener Code werden im Ereignisprotokoll gespeichert.

`OHA_GetEventHistory($InstanzID)` liefert das Protokoll als JSON. `OHA_ExportEventHistory()` exportiert es als JSON oder CSV und kann den Zeitraum sowie den Ereignistyp filtern. Die Schaltflächen **JSON** und **CSV** im Systemprotokoll laden den vollständig gespeicherten Bestand direkt aus der Kachel oder IPSView herunter. Mit `OHA_ClearEventHistory($InstanzID)` kann das Protokoll gezielt geleert werden. Das Ereignisprotokoll ist ein Bedien- und Diagnoseprotokoll und kein manipulationssicheres Audit-Log.

Die Diagnoseansicht führt alle konfigurierten Sensoren und Störungseingänge mit Alarmbereich, Symcon-Variablen-ID und den Zeitpunkten der letzten Änderung und Aktualisierung auf. Mögliche Zustände sind **bereit**, **ausgelöst**, **fehlend**, **unlesbar** und **deaktiviert**. Die zusammengefasste Problemanzahl zählt fehlende oder unlesbare Eingänge sowie ausgelöste Störungseingänge. Ein ausgelöster normaler Alarmsensor wird angezeigt, erhöht diese technische Problemanzahl aber nicht. Kachel, IPSView und `OHA_GetDiagnostics()` verwenden denselben versionierten Diagnose-Snapshot.

Die Diagnose kann über die Schaltflächen **JSON** und **CSV** oder mit `OHA_ExportDiagnostics()` heruntergeladen werden. JSON enthält den vollständigen Snapshot einschließlich Zusammenfassung und Erstellungszeitpunkt. CSV enthält pro Eingang eine maschinenlesbare Zeile in derselben Reihenfolge wie die Diagnoseansicht. Rohwerte, Unscharfschaltcodes und andere Geheimnisse sind nicht Bestandteil des Diagnoseexports.

### 13. Konfigurationssicherung

`OHA_ExportConfigurationBackup($InstanzID)` exportiert sämtliche registrierten Moduleinstellungen als versioniertes, menschenlesbares JSON. Dazu gehören auch Unscharfschalt- und Benutzercodes. Der Export trägt deshalb die Kennzeichnung `ContainsSecrets: true` und muss vertraulich gespeichert sowie ausschließlich über einen geschützten Übertragungsweg weitergegeben werden. Laufzeitzustände, Timer, Alarmgedächtnis und Ereignishistorie werden nicht gesichert.

`OHA_RestoreConfigurationBackup($InstanzID, $JSON)` stellt eine validierte Sicherung nur wieder her, wenn alle Alarmbereiche vollständig unscharf sind. Format, Sicherungsversion, Modul-ID, Eigenschaftsnamen und Datentypen werden vor jeder Änderung geprüft. Fremde oder beschädigte Sicherungen werden ohne Konfigurationsänderung abgewiesen. Scheitert das Anwenden einer bereits validierten Sicherung, setzt das Modul die vorherige Konfiguration zurück. Fehlt in einer älteren Sicherung eine erst später eingeführte Eigenschaft, behält diese ihren aktuellen Wert.

### 14. Visualisierung

OpenHomeAlarm besitzt eine eigene responsive Objektdarstellung über das native **Symcon HTML-SDK**. Das Dashboard ist zustandsorientiert aufgebaut: **Unscharf**, **Scharf**, **Ein-/Ausgangsverzögerung** und **Alarm** werden als zentraler Hauptzustand dargestellt. Countdown, Alarmgedächtnis, aktive Systemstörungen und temporär überbrückte Sensoren erscheinen nur dann als zusätzliche Hinweise, wenn sie tatsächlich relevant sind. Dadurch bleibt die Normalansicht kompakt und die jeweils wichtigste Information steht im Vordergrund. Farben, Oberflächen, Abstände und Fokusdarstellung stammen aus dem gemeinsamen `VisualizationThemeConfigurationHelper`; standardmäßig folgt die Kachel den nativen Symcon-Farben einschließlich Light-/Dark-Umschaltung. Im Konfigurationsabschnitt **Symcon-Kachel** können Text-, Überschriften-, Hintergrund-, Akzent- und Statusfarben optional überschrieben werden. Nur tatsächlich vom Standard abweichende Farben werden fest vorgegeben; alle unveränderten Rollen passen sich weiterhin dem nativen Symcon-Schema an.

Direkt unter dem zentralen Sicherheitsstatus stehen **Zuhause**, **Abwesend** und **Nacht** als vollständige Modus-Schaltflächen. Im unscharfen Zustand lassen sich bereite Modi über die gesamte Schaltfläche aktivieren; während eines laufenden Scharfzustands bleiben sie als deutlich hervorgehobene Statusanzeige sichtbar und folgen der vom Backend veröffentlichten Bedienfreigabe. Offene oder nicht verfügbare Sensoren können – sofern zulässig – direkt einmalig überbrückt werden. Bestehende Überbrückungen lassen sich einzeln oder gemeinsam aufheben. Ein gespeichertes Alarmgedächtnis kann quittiert und ein aktiver Alarmausgang ohne Unscharfschaltung gestoppt werden. Die letzten sechs Einträge des persistenten Sicherheitsprotokolls werden als kompakte Ereignisliste angezeigt.

Auf ausreichend breiten Kacheln erscheint das Codepad als fester Bestandteil der Alarmzentrale, sobald eine Code-Eingabe tatsächlich zum Unscharfschalten benötigt wird. Im normalen unscharfen Zustand bleibt der Platz für Sensorverwaltung und Ereignisse frei. Unterhalb einer Kachelbreite von 900 Pixeln öffnet **Mit Code deaktivieren** das kompakte Popup-Codepad. Ist kein Unscharfschaltcode konfiguriert, steht unabhängig von der Breite die direkte Deaktivierung zur Verfügung. Die Code-Eingabe bleibt ausschließlich temporär im JavaScript-Speicher der geöffneten Darstellung, wird nicht in einer Symcon-Variable abgelegt und nach Absenden, Abbrechen oder erfolgreichem Deaktivieren sofort verworfen. Falsche Codes werden direkt am aktiven Codepad gemeldet, ohne den eingegebenen Code anzuzeigen oder zu protokollieren. Während einer temporären Sperre werden beide Codepads deaktiviert und nach Ablauf automatisch wieder aus dem Backend aktualisiert.

Die native Symcon-Kachel verwendet für die Kommunikation ausschließlich das HTML-SDK: Benutzeraktionen werden über `requestAction()` an `RequestAction()` des Moduls gesendet, während Statusänderungen über `UpdateVisualizationValue()` live an geöffnete Kacheln übertragen werden. Die statischen Dateien liegen unter `OpenHomeAlarm/visualization/` und werden über den zentralen `VisualizationAssetHelper` geladen. Native Kachel und IPSView-WebContent-Seite werden aus denselben Assets und demselben versionierten Bootstrap-Vertrag durch den `IPSViewHTMLPageHelper` erzeugt. Das gemeinsame Symcon-Design wird durch den vendorten `VisualizationThemeHelper` eingebettet; der vollständige IPSView-Stil mit Farben, Typografie, Rahmen, Schatten, Deckkraft und Verläufen wird zentral durch den `IPSViewStyleConfigurationHelper` erzeugt.

Zusätzlich kann im Konfigurationsabschnitt **IPSView** eine eigenständige WebContent-Variable **IPSView-Alarmanlage** aktiviert werden. Die optionale Ausgabe wird zentral durch den `IPSViewHTMLPageHelper` verwaltet. Sie verwendet dieselbe HTML-, CSS- und JavaScript-Oberfläche wie die native Kachel und ist vollständig bedienbar: Scharfmodi, Deaktivierung mit Code, Sensorüberbrückungen, Alarmgedächtnis und Rücksetzung des Alarmausgangs stehen auch in IPSView zur Verfügung. In IPSView muss die Variable in einer **HTML-Box** mit dem Renderer **Browser des Clients** dargestellt werden, da nur dieser Renderer JavaScript unterstützt.

Wird die IPSView-Ausgabe deaktiviert, bleibt eine bereits angelegte Variable zunächst mit ihrer Objekt-ID, ihrem Inhalt und bestehenden Verknüpfungen erhalten; sie wird lediglich nicht mehr aktualisiert. Im Konfigurationsformular kann der Nutzer anschließend selbst entscheiden, ob sie weiter bestehen bleiben oder nach ausdrücklicher Bestätigung gelöscht werden soll. Bei einer erneuten Aktivierung wird eine vorhandene Variable wiederverwendet.

Für die Darstellung stehen gemeinsame Stilquellen zur Verfügung: **Benutzerdefinierter Stil**, **IPSView-Standardstil**, **Helle Vorgabe**, **Dunkle Vorgabe**, ein validiertes **Style Profile V1** sowie die zentralen Vorgaben **Hell**, **Dunkel**, **Warm**, **Kühl**, **Erdig**, **Wasser** und **Sonnig**. Beim IPSView-Standardstil wird ein in Symcon hinterlegtes `.ipsView`-Medienobjekt ausgewählt; der Helper übernimmt daraus ausschließlich freigegebene globale Stylewerte. Style Profile V1 lädt eine vollständige validierte Gestaltung aus einem Medienobjekt. Änderungen an ausgewählten Medienobjekten aktualisieren die HTML-Seite automatisch. Der benutzerdefinierte Stil erlaubt dieselben universellen Einstellungen für View-, Seiten-, Label-, Bedienelement- und Popupflächen, normale/aktive/inaktive Texte, Icons, Rahmen, Linien, Akzent-, Informations-, positive, warnende und kritische Zustände sowie Typografie, Schatten, Deckkraft und Verlaufsstärke. Zusätzlich enthält die gemeinsame Bearbeitungsmaske einen eingeklappten Expertenbereich für die gruppierten nativen IPSView-Farben. Einzelne native Werte werden erst nach Aktivierung der jeweiligen Überschreibung wirksam und erben andernfalls weiterhin ihre semantische Grundfarbe. OpenHomeAlarm ordnet seine fachlichen Zustände nur noch diesen gemeinsamen Stilrollen zu und definiert keine eigene IPSView-Farbpalette mehr. Der View-Hintergrund bildet die vollständige HTML-Box, der Seitenhintergrund die Informationskarten und Inhaltsbereiche; normale, aktive und inaktive Bedienelemente verwenden ausschließlich die zugehörigen Bedienelementflächen. Primärtext wird ausschließlich für normalen Inhalt und Werte verwendet. Eyebrows, Abschnittskennzeichnungen und Feldnamen nutzen die Label-Schrift, Beschreibungen die sekundäre Schrift, zurückhaltende Hinweise die Faint-Schrift und neutrale Symbole die Iconfarbe. Die aktive beziehungsweise inaktive Schrift wird auch auf den sichtbaren Text des jeweiligen Bedienelements angewendet. Dadurch führt dieselbe Farbeinstellung in allen Modulen zur gleichen semantischen Änderung.

Da IPSView keine HTML-SDK-`requestAction()`-Brücke bereitstellt, kommuniziert die Seite über einen instanzbezogenen Symcon-WebHook. Das Modul erzeugt dafür ein zufälliges, persistentes Zugriffstoken und akzeptiert ausschließlich die fest freigegebenen Visualisierungsaktionen per POST. Der Deaktivierungscode wird nur im Request-Body übertragen, weder in einer URL noch in einer Symcon-Variable gespeichert und nicht protokolliert. Der aktuelle Zustand wird zyklisch vom Modul gelesen; während Ein-/Ausgangsverzögerungen und Code-Sperren erfolgt die Aktualisierung häufiger. Für einen Zugriff außerhalb des eigenen Netzes sollte ausschließlich eine verschlüsselte HTTPS-/Connect-Verbindung verwendet werden.

Statusquelle bleibt unverändert die öffentliche Bedien-API: `OHA_GetControlState($InstanzID)` liefert einen versionierten JSON-Snapshot mit Modus, Zustand, verfügbaren Bedienmöglichkeiten, Code-Sperrstatus, Scharfschaltbereitschaft, strukturierten Blockierern samt Variablen-ID, temporären Überbrückungen, Verzögerungsstatus, Alarmgedächtnis und Systemstörungen. Die Visualisierung bildet keine Alarmregeln nach.

Die partitionsfähige Struktur verwendet `ApiVersion` 2. `DefaultPartition` enthält immer `main`; `Partitions` ist nach den Bereichs-IDs indiziert. Maschinenlesbare Modusnamen sind `none`, `home`, `away`, `night`; Zustandsnamen sind `disarmed`, `exit_delay`, `armed`, `entry_delay` und `alarm`.

### 15. PHP-Befehlsreferenz

Folgende für Anwender und Automationen vorgesehene Modulbefehle stehen zur Verfügung. Weitere öffentliche Methoden dienen ausschließlich Symcon-internen Lebenszyklus-, Timer-, Formular- und Visualisierungsaufrufen.

| PHP-Befehl | Rückgabe | Bedeutung |
| --- | --- | --- |
| `OHA_GetControlState($InstanzID)` | `string` | Liefert den versionierten, strukturierten Bedienzustand als JSON; vorgesehen als einzige Statusquelle der eigenen Visualisierung |
| `OHA_GetPartitions($InstanzID)` | `string` | Liefert die konfigurierten Partitionsmetadaten als JSON |
| `OHA_GetDiagnostics($InstanzID)` | `string` | Liefert einen rein lesenden Diagnose-Snapshot aller konfigurierten Sensoren und Störungseingänge als JSON |
| `OHA_ExportDiagnostics($InstanzID, $Format)` | `string` | Exportiert den aktuellen Diagnose-Snapshot als `json` oder `csv` |
| `OHA_ExportConfigurationBackup($InstanzID)` | `string` | Exportiert sämtliche Moduleinstellungen als versioniertes JSON; das Ergebnis kann Unscharfschalt- und Benutzercodes enthalten und muss vertraulich gespeichert werden |
| `OHA_RestoreConfigurationBackup($InstanzID, $JSON)` | `bool` | Stellt ein validiertes Backup nur bei vollständig unscharfen Alarmbereichen wieder her; bei einem Fehler wird die vorherige Konfiguration zurückgespielt |
| `OHA_ArmPartition($InstanzID, $BereichID, $Modus)` | `bool` | Schaltet einen einzelnen aktiven Alarmbereich mit `home`, `away` oder `night` scharf; bei `main` werden alle aktiven Bereiche gemeinsam geschaltet |
| `OHA_DisarmPartition($InstanzID, $BereichID)` | `bool` | Schaltet einen einzelnen aktiven Alarmbereich unscharf; bei `main` werden alle aktiven Bereiche gemeinsam unscharf geschaltet |
| `OHA_Arm($InstanzID, $Modus, $Verzögerung = null)` | `bool` | Schaltet alle aktiven Bereiche über die stabile Bedien-API mit `home`, `away` oder `night` scharf; eine optionale Verzögerung überschreibt die konfigurierte Ausgangsverzögerung für diesen Aufruf |
| `OHA_ArmHome($InstanzID, $Verzögerung = null)` | `bool` | Komfortbefehl für **Zuhause** für alle aktiven Bereiche; `null` verwendet die konfigurierte, `0` keine und ein positiver Wert die angegebene Ausgangsverzögerung |
| `OHA_ArmAway($InstanzID, $Verzögerung = null)` | `bool` | Komfortbefehl für **Abwesend** für alle aktiven Bereiche; `null` verwendet die konfigurierte, `0` keine und ein positiver Wert die angegebene Ausgangsverzögerung |
| `OHA_ArmNight($InstanzID, $Verzögerung = null)` | `bool` | Komfortbefehl für **Nacht** für alle aktiven Bereiche; `null` verwendet die konfigurierte, `0` keine und ein positiver Wert die angegebene Ausgangsverzögerung |
| `OHA_BypassSensor($InstanzID, $VariableID)` | `bool` | Überbrückt einen normalen konfigurierten Scharfsensor temporär; nur im Zustand **Unscharf** möglich |
| `OHA_RemoveSensorBypass($InstanzID, $VariableID)` | `bool` | Entfernt eine einzelne temporäre Sensorüberbrückung; nur im Zustand **Unscharf** möglich |
| `OHA_BypassSensorPartition($InstanzID, $BereichID, $VariableID)` | `bool` | Überbrückt einen Sensor ausschließlich im angegebenen unscharfen Alarmbereich |
| `OHA_RemoveSensorBypassPartition($InstanzID, $BereichID, $VariableID)` | `bool` | Entfernt die Überbrückung ausschließlich im angegebenen unscharfen Alarmbereich |
| `OHA_ClearSensorBypasses($InstanzID)` | `bool` | Entfernt alle temporären Sensorüberbrückungen; nur im Zustand **Unscharf** möglich |
| `OHA_Disarm($InstanzID)` | `bool` | Schaltet die Anlage als vertrauenswürdige direkte API ohne Code-Prüfung unscharf und setzt den Scharfmodus zurück |
| `OHA_DisarmWithCode($InstanzID, $Code)` | `bool` | Prüft den optionalen Unscharfschaltcode und schaltet bei Erfolg unscharf |
| `OHA_StopSignalGenerator($InstanzID)` | `bool` | Führt während eines aktiven Alarms nur die Rücksetzaktionen der als **Signalgeber** markierten Eskalationsaktionen aus |
| `OHA_ResetAlarmOutput($InstanzID)` | `bool` | Setzt während eines aktiven Alarms nur den Alarmausgang zurück; Alarmzustand und Alarmgedächtnis bleiben erhalten |
| `OHA_ResetAlarmOutputPartition($InstanzID, $BereichID)` | `bool` | Setzt nur den Ausgang eines alarmierenden Bereichs zurück; andere aktive Bereichsausgänge halten die Gesamtausgabe aktiv |
| `OHA_ResetFalseAlarm($InstanzID)` | `bool` | Setzt einen Fehlalarm der Gesamtanlage zurück, löscht das Alarmgedächtnis und schaltet nur bei voller Bereitschaft im vorherigen Modus wieder scharf |
| `OHA_ResetFalseAlarmPartition($InstanzID, $BereichID)` | `bool` | Entspricht `OHA_ResetFalseAlarm()` für einen einzelnen Bereich |
| `OHA_ClearAlarmMemory($InstanzID)` | `bool` | Quittiert das gespeicherte Alarmgedächtnis; während eines aktiven Alarms wird `false` zurückgegeben |
| `OHA_ClearAlarmMemoryPartition($InstanzID, $BereichID)` | `bool` | Quittiert das Alarmgedächtnis eines einzelnen Bereichs nach dessen Unscharfschaltung |
| `OHA_ClearSensorBypassesPartition($InstanzID, $BereichID)` | `bool` | Entfernt alle temporären Sensorüberbrückungen eines unscharfen Bereichs |
| `OHA_GetEventHistory($InstanzID)` | `string` | Liefert das persistente Sicherheits-Ereignisprotokoll als JSON, neuester Eintrag zuerst |
| `OHA_ExportEventHistory($InstanzID, $Format, $VonZeitstempel, $BisZeitstempel, $Ereignistyp)` | `string` | Exportiert die Historie als `json` oder `csv`; Zeitstempel `0` und ein leerer Ereignistyp deaktivieren den jeweiligen Filter |
| `OHA_ClearEventHistory($InstanzID)` | `bool` | Leert das persistente Sicherheits-Ereignisprotokoll |
| `OHA_CheckSensorIntegrity($InstanzID)` | `void` | Prüft konfigurierte Sensor- und Störungsvariablen sofort auf Verfügbarkeit und aktualisiert Systemstörung sowie Scharfschaltbereitschaft |

Die Scharfschaltbefehle liefern `false`, wenn das System nicht **Unscharf** ist oder mindestens ein für den Zielmodus relevanter Sensor bzw. eine blockierende Systemstörung die Scharfschaltung verhindert. In diesem Fall bleiben `Mode` und `State` unverändert. Für neue benutzerseitige Oberflächen ist `OHA_Arm()` die bevorzugte Schnittstelle; die drei modusspezifischen Befehle bleiben kompatibel erhalten.
