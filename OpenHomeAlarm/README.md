# OpenHomeAlarm

OpenHomeAlarm ist die zentrale Alarm- und Sicherheitslogik der gleichnamigen Library.

> **Entwicklungsstatus:** Zustandsmodell, Sensor-/Trigger-Datenmodell, aktive und wiederanlaufsichere Sensorüberwachung, modusabhängige Scharfschaltbereitschaft, zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen, 24/7-Sensoren, Ein-/Ausgangsverzögerungen mit Countdown-Status und konfigurierbarem Ausgangsweg, konfigurierbare Alarmaktionen mit Alarmdauer und Rücksetzung des Alarmausgangs, eine 24/7-Systemüberwachung für Manipulation und technische Störungen, Code-Prüfung zum Unscharfschalten, ein quittierbares Alarmgedächtnis, ein persistentes Sicherheits-Ereignisprotokoll sowie eine stabile öffentliche Bedien-API sowie die HTML-SDK-Visualisierung mit Live-Status, Scharfmodus-Auswahl und integriertem Codepad zum geschützten Unscharfschalten sind implementiert. Sensorüberbrückung und weitere Bedienfunktionen werden schrittweise ergänzt.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in Symcon](#4-einrichten-der-instanz-in-symcon)
5. [Statusvariablen und Darstellungen](#5-statusvariablen-und-darstellungen)
6. [Sensoren und Auslöser](#6-sensoren-und-auslöser)
7. [Systemüberwachung](#7-systemüberwachung)
8. [Code-Schutz](#8-code-schutz)
9. [Alarmaktionen](#9-alarmaktionen)
10. [Alarmgedächtnis](#10-alarmgedächtnis)
11. [Ereignisprotokoll](#11-ereignisprotokoll)
12. [Visualisierung](#12-visualisierung)
13. [PHP-Befehlsreferenz](#13-php-befehlsreferenz)

### 1. Funktionsumfang

Der aktuelle Entwicklungsstand stellt das grundlegende Zustandsmodell der Alarmanlage, ein herstellerunabhängiges Sensor-/Trigger-Datenmodell, die aktive Sensorüberwachung, die globale und modusabhängige Scharfschaltbereitschaft inklusive der jeweils blockierenden Sensoren, die zielmodusabhängige Scharf-/Unscharf-Logik, temporäre Sensorüberbrückungen für einen Scharfschaltzyklus, dauerhaft aktive 24/7-Sensoren, timerbasierte Ein-/Ausgangsverzögerungen mit laufendem Countdown-Status und Ausgangsweg-Sensoren, konfigurierbare Alarmaktionen mit optionaler automatischer Alarmdauer und separater Rücksetzungsaktion, eine 24/7-Systemüberwachung für Manipulation, Batterie-/Stromversorgung, Kommunikation und Gerätestörungen mit optionaler Scharfschaltblockade oder Alarmauslösung, eine optionale Code-Prüfung zum Unscharfschalten, ein quittierbares Alarmgedächtnis, ein persistentes Sicherheits-Ereignisprotokoll sowie eine versionierte öffentliche Bedien-API bereit. Betriebsmodus und Systemzustand werden bewusst getrennt geführt, damit beispielsweise ein Alarm weiterhin erkennen lässt, ob zuvor Zuhause-, Abwesend- oder Nachtbetrieb aktiv war.

Symcon-Variablen können als Sensor oder Auslöser hinterlegt und den Scharfmodi Zuhause, Abwesend und Nacht zugeordnet werden. Zusätzlich kann ein Sensor als **24/7 aktiv** markiert werden und löst dann unabhängig vom Scharfmodus sofort aus. Sensortyp, Auslösewert sowie die Nutzung als Ausgangsweg und der Eingangsverzögerung werden ebenfalls gespeichert. Der Auslösewert wird aus den diskreten Zuständen der ausgewählten Symcon-Variable abgeleitet. Boolean-, String- und numerische Zustände werden dabei einheitlich als Auswahlliste mit den in Symcon hinterlegten Beschriftungen angeboten.

Ausgangs- und Eingangsverzögerung sind global in Sekunden konfigurierbar. Der Wert `0` deaktiviert die jeweilige Verzögerung. Sensoren können zusätzlich als **Ausgangsweg** markiert werden. Solche Sensoren dürfen bei aktivierter Ausgangsverzögerung beim Start der Scharfschaltung bereits ausgelöst sein, müssen aber spätestens am Ende des Countdowns wieder bereit sein. Bei deaktivierter Ausgangsverzögerung gelten sie wie normale Sensoren und müssen bereits vor dem Scharfschalten bereit sein. Während eines laufenden Countdowns veröffentlicht `DelayRemaining` die verbleibenden Sekunden. Bei einer Eingangsverzögerung nennt `DelaySource` zusätzlich den Sensor, der den Countdown gestartet hat; beide Statuswerte werden beim Abbruch, Abschluss oder Alarm wieder zurückgesetzt. Zusätzlich können drei native Symcon-Aktionen hinterlegt werden: eine Aktion beim Eintritt in den Alarmzustand, eine Aktion zum Rücksetzen des Alarmausgangs und eine Aktion beim Unscharfschalten eines bereits aktiven Alarms. Die Alarmdauer ist global in Sekunden konfigurierbar. Der Standardwert `0` deaktiviert die automatische Rücksetzung und erhält damit das bisherige Verhalten. Beispielsweise setzt `180` den Alarmausgang nach drei Minuten automatisch zurück, ohne den Alarmzustand oder das Alarmgedächtnis zu löschen. Dadurch lassen sich unter anderem Sirenen, Benachrichtigungen, Skripte oder Ablaufpläne ohne hardwarespezifische Kopplung anbinden. Für benutzerseitiges Unscharfschalten kann optional ein vier- bis achtstelliger Zahlencode hinterlegt werden.

### 2. Voraussetzungen

- Symcon ab Version 9.0

### 3. Software-Installation

Die Library kann über die Modulverwaltung von Symcon aus dem GitHub-Repository `Burki24/OpenHomeAlarm` installiert werden.

### 4. Einrichten der Instanz in Symcon

Unter **Instanz hinzufügen** kann das Modul **OpenHomeAlarm** gefunden und angelegt werden.

Im Konfigurationsformular können die globale **Ausgangsverzögerung** und **Eingangsverzögerung** in Sekunden festgelegt werden. Im Abschnitt **Code-Schutz** kann optional ein vier- bis achtstelliger **Unscharfschaltcode** hinterlegt werden. Im Abschnitt **Alarmaktionen** werden die **Alarmdauer** sowie optional die Aktionen **Bei Alarm**, **Bei Rücksetzung des Alarmausgangs** und **Beim Unscharfschalten nach Alarm** konfiguriert. Jede dieser Aktionen ist standardmäßig deaktiviert und wird erst nach Auswahl **Aktion konfigurieren** und einmaligem Übernehmen eingeblendet und ausgeführt. Im Abschnitt **Systemüberwachung** können zusätzliche 24/7-Eingänge für Manipulation und technische Störungen samt ebenfalls explizit aktivierbaren Aktionen bei Auftreten und Behebung hinterlegt werden. Darunter steht die Liste **Sensoren und Auslöser** zur Verfügung. Dort kann ein Sensor zusätzlich als **Ausgangsweg** markiert werden. Ein Eintrag verweist direkt auf eine vorhandene Symcon-Variable und ist damit unabhängig vom Hersteller oder Protokoll des eigentlichen Geräts.

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
| `SystemFault` | Zeigt an, ob mindestens ein konfigurierter Störungs-/Manipulationseingang aktiv oder nicht auswertbar ist | Keine Systemstörung |
| `ActiveFaults` | Namen aller aktuell aktiven bzw. nicht auswertbaren Störungseingänge | leer |
| `BlockingFaults` | Aktive Störungen, die die Scharfschaltung aller Modi blockieren | leer |
| `LastFaultSource` | Name der zuletzt neu aufgetretenen Störung/Manipulation | leer |
| `LastFaultTime` | Zeitpunkt der zuletzt neu aufgetretenen Störung im Format `TT.MM.JJJJ HH:MM:SS` | leer |

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
| `ExitDelay` | Kennzeichnet einen Sensor des Ausgangswegs; darf bei aktiver Ausgangsverzögerung beim Start der Scharfschaltung ausgelöst sein, muss aber am Countdown-Ende bereit sein |
| `EntryDelay` | Startet bei Auslösung im scharfen Betrieb die konfigurierte Eingangsverzögerung statt unmittelbar den Alarmzustand |

Beim Bearbeiten eines Eintrags liest OpenHomeAlarm die aktuelle Symcon-Variablendarstellung aus. Definierte Optionen einer Boolean- oder String-Wertanzeige, Aufzählungen sowie diskrete numerische Intervalle erscheinen immer als dieselbe Auswahlliste. Für ältere Variablen werden vorhandene Profil-Assoziationen ebenfalls übernommen. Nur wenn eine Variable keine diskreten Zustände bereitstellt, bleibt eine direkte Rohwerteingabe als Fallback sichtbar. Gespeichert wird weiterhin der Rohwert als String, damit das Sensor-Datenmodell stabil bleibt.

Aktive Sensoren werden per `VM_UPDATE` überwacht, sobald sie mindestens einem Scharfmodus zugeordnet oder als **24/7 aktiv** markiert sind. OpenHomeAlarm vergleicht den aktuellen Variablenwert typgerecht mit dem gespeicherten Rohwert. Boolean-, Integer-, Float- und Stringwerte werden entsprechend ihrem tatsächlichen Variablentyp ausgewertet. Nach `ApplyChanges()` oder einem Symcon-Neustart werden die aktuell anliegenden Sensorwerte zusätzlich erneut geprüft. Dadurch wird auch eine Auslösung erkannt, die während eines Neustarts erfolgt ist und deshalb kein neues `VM_UPDATE` mehr erzeugt. Bereits laufende Eingangsverzögerungen werden dabei nicht neu gestartet; ein gleichzeitig aktiver Sofortalarm-Sensor eskaliert weiterhin unmittelbar zum Alarm.

`ReadyToArm` bleibt als konservative Gesamtbereitschaft erhalten. Bei aktivierter Ausgangsverzögerung werden dabei nur Sensoren des ausdrücklich markierten Ausgangswegs vorübergehend von der initialen Bereitschaftsprüfung ausgenommen; alle übrigen ausgelösten oder nicht auswertbaren Sensoren setzen die Bereitschaft weiterhin auf **Nicht bereit**. Zusätzlich zeigen `ReadyHome`, `ReadyAway` und `ReadyNight` die tatsächliche Scharfschaltbereitschaft des jeweiligen Modus. Ein beispielsweise nur für **Abwesend** relevanter offener Kontakt setzt deshalb `ReadyAway` auf **Nicht bereit**, während `ReadyHome` und `ReadyNight` weiterhin **Bereit** bleiben können. Die Variablen `BlockingHomeSensors`, `BlockingAwaySensors` und `BlockingNightSensors` nennen dabei direkt die Sensoren, die den jeweiligen Modus aktuell blockieren. Mehrere Sensoren werden kommasepariert ausgegeben; bei fehlendem Namen wird die Variablen-ID verwendet. 24/7-Sensoren wirken auf alle drei Modi. Sensoren des Ausgangswegs werden bei konfigurierter Ausgangsverzögerung bei der initialen Scharfschaltbereitschaft nicht als blockierend gewertet; die abschließende Prüfung am Ende der Ausgangsverzögerung bleibt jedoch strikt. Nicht mehr vorhandene oder nicht auswertbare relevante Sensorvariablen führen aus Sicherheitsgründen ebenfalls zu **Nicht bereit** und erscheinen ebenfalls in der passenden Blockierliste. Ein noch nicht vollständig konfigurierter Eintrag mit `VariableID = 0` wird ignoriert.

Beim Scharfschalten prüft OpenHomeAlarm die Sensoren, die dem angeforderten Zielmodus zugeordnet sind, sowie alle 24/7 aktiven Sensoren. Ein ausgelöster Sensor, der beispielsweise ausschließlich für **Abwesend** gilt, verhindert deshalb nicht das Scharfschalten von **Zuhause**. Fehlende oder nicht auswertbare Sensoren des angeforderten Modus blockieren die Scharfschaltung aus Sicherheitsgründen. Unvollständige Listeneinträge mit `VariableID = 0` bleiben weiterhin ohne Wirkung.

Nach erfolgreicher Scharfschaltprüfung wechselt OpenHomeAlarm bei einer Ausgangsverzögerung größer als `0` zunächst in **Ausgangsverzögerung**. Erst nach Ablauf des Timers wird erneut geprüft, ob der gewählte Modus scharfschaltbereit ist. Sind dann noch relevante Sensoren ausgelöst oder nicht auswertbar, wird der Scharfschaltvorgang sicher abgebrochen und die Anlage auf **Unscharf** zurückgesetzt. Bei `0` Sekunden wird unmittelbar scharfgeschaltet.

Löst im Zustand **Scharf** ein für den aktiven Modus relevanter Sensor aus, startet ein mit `EntryDelay` markierter Sensor die konfigurierte **Eingangsverzögerung**. Der Countdown wird durch das erneute Schließen des Sensors nicht abgebrochen und bei weiteren verzögerten Sensorereignissen nicht neu gestartet. Ein Sensor ohne Eingangsverzögerung wechselt unmittelbar in den Zustand **Alarm**. Das gilt ebenfalls, wenn während einer laufenden Eingangsverzögerung ein sofort auslösender Sensor anspricht. Nach Ablauf der Eingangsverzögerung wird ebenfalls **Alarm** gesetzt. Beim erstmaligen Eintritt in den Alarmzustand wird die konfigurierte Aktion **Bei Alarm** genau einmal ausgeführt.


Temporäre Sensorüberbrückungen können ausschließlich im Zustand **Unscharf** gesetzt oder entfernt werden. Eine Überbrückung wirkt auf alle Scharfmodi, denen die betreffende Variable zugeordnet ist, und wird bei der Scharfschaltbereitschaft, den Blockierlisten sowie der späteren Alarmauswertung ignoriert. Dadurch kann beispielsweise ein bewusst geöffnetes Fenster für genau einen Scharfschaltzyklus ausgeblendet werden. 24/7 aktive Sensoren können aus Sicherheitsgründen nicht überbrückt werden. Die Überbrückungen werden persistent gespeichert, überstehen daher `ApplyChanges()` und einen Symcon-Neustart, werden aber beim Unscharfschalten nach einem Scharfschaltzyklus automatisch vollständig gelöscht. `BypassedSensors` zeigt die derzeit überbrückten Sensoren an.

24/7 aktive Sensoren sind von den Scharfmodi unabhängig. Sie lösen sowohl im Zustand **Unscharf** als auch während Ausgangsverzögerung, **Scharf** oder Eingangsverzögerung unmittelbar den Zustand **Alarm** aus. Für solche Sensoren werden die Moduszuordnungen sowie `ExitDelay` und `EntryDelay` bewusst ignoriert. Ist ein 24/7-Sensor bei `ApplyChanges()` oder nach einem Symcon-Neustart bereits ausgelöst, wird dieser Zustand unmittelbar erkannt, sodass keine Überwachungslücke bis zur nächsten Variablenänderung entsteht. Typische Anwendungsfälle sind Rauch-, Wasser- oder Panikauslöser; die Aktivierung bleibt jedoch bewusst eine explizite Benutzereinstellung.

Beim Unscharfschalten werden laufende Ein- und Ausgangsverzögerungen immer beendet. Ist der Alarmausgang zu diesem Zeitpunkt noch aktiv, wird er zuerst zurückgesetzt. Wird ein bereits aktiver Alarm unscharf geschaltet, wird anschließend einmalig die konfigurierte Aktion **Beim Unscharfschalten nach Alarm** ausgeführt. Ein Abbruch während Ein- oder Ausgangsverzögerung löst diese Aktion nicht aus. Die Timer für Ein-/Ausgangsverzögerung und Alarmdauer verwenden persistierte Ablaufzeitpunkte und werden nach `ApplyChanges()` bzw. einem Symcon-Neustart mit der verbleibenden Zeit wiederhergestellt.

### 7. Systemüberwachung

Zusätzlich zu den eigentlichen Alarmsensoren können im Abschnitt **Systemüberwachung** unabhängige 24/7-Eingänge für **Manipulation**, **Batterie/Stromversorgung**, **Kommunikation**, **Gerätestörung** oder eine sonstige Störung angelegt werden. Jeder Eintrag verweist wie ein normaler Sensor auf eine Symcon-Variable; der Störwert wird aus deren Variablendarstellung übernommen oder bei Bedarf als Rohwert eingegeben.

Für jeden Störungseingang kann separat festgelegt werden, ob eine aktive Störung die **Scharfschaltung blockiert** und ob sie den normalen Alarmzustand **sofort und 24/7 auslöst**. Dadurch kann beispielsweise ein Sabotagekontakt unmittelbar alarmieren, während eine schwache Batterie lediglich als Systemstörung angezeigt wird. Eine blockierende Störung setzt `ReadyHome`, `ReadyAway`, `ReadyNight` und `ReadyToArm` auf **Nicht bereit** und erscheint zusätzlich in `BlockingFaults`.

Fehlende oder nicht auswertbare konfigurierte Störungsvariablen werden sicherheitsgerichtet als Systemstörung behandelt und können – sofern so konfiguriert – die Scharfschaltung blockieren. Sie lösen den Hauptalarm jedoch nicht allein aufgrund der fehlenden Auswertbarkeit aus; dafür muss der konfigurierte Störwert eindeutig erkannt werden.

Optional können die nativen Symcon-Aktionen **Bei neuer Störung** und **Bei behobener Störung** hinterlegt werden. Auch diese beiden Aktionen müssen zunächst über **Aktion konfigurieren** aktiviert und einmal übernommen werden. Zustandswechsel werden persistent verfolgt, sodass ein `ApplyChanges()` oder Symcon-Neustart eine bereits aktive Störung nicht mehrfach als neu meldet. Neu aufgetretene und behobene Störungen werden zusätzlich als `fault_activated` bzw. `fault_cleared` im Sicherheits-Ereignisprotokoll gespeichert.

### 8. Code-Schutz

Für die spätere benutzerseitige Bedienung kann im Konfigurationsformular ein vier- bis achtstelliger Zahlencode hinterlegt werden. Das Feld wird als Passwortfeld dargestellt. Ein leerer Wert deaktiviert die Code-Prüfung.

`OHA_DisarmWithCode($InstanzID, $Code)` prüft den übergebenen Code und schaltet nur bei Übereinstimmung unscharf. Ein falscher Code verändert weder Modus noch Zustand. Die Prüfung erfolgt ohne Protokollierung des eingegebenen Codes. Standardmäßig wird die Code-Eingabe nach fünf Fehlversuchen für 60 Sekunden gesperrt. Anzahl und Sperrdauer sind im Konfigurationsformular einstellbar. Fehlversuchszähler und Ablaufzeitpunkt der Sperre werden persistent gespeichert, sodass ein `ApplyChanges()` oder Symcon-Neustart die Sperre nicht umgeht. Nach Ablauf oder einem erfolgreichen Unscharfschalten wird der Zähler zurückgesetzt.

Der bestehende Befehl `OHA_Disarm($InstanzID)` bleibt als vertrauenswürdige direkte API für Automationen erhalten und umgeht bewusst Code-Prüfung und Benutzersperre. Der öffentliche Bedienzustand enthält unter `CodeProtection` ausschließlich Sperrstatus, verbleibende Versuche und Sperrdauer; weder der konfigurierte noch der eingegebene Code werden ausgegeben.

### 9. Alarmaktionen

OpenHomeAlarm verwendet für externe Reaktionen die nativen Symcon-Aktionen. Optionale Aktionen sind standardmäßig deaktiviert, damit eine vollständig gültige Konfiguration auch ohne Aktionsauswahl möglich bleibt. Nach Auswahl **Aktion konfigurieren** und einmaligem Übernehmen wird der jeweilige native Aktionsdialog eingeblendet. Solange die Option auf **Keine Aktion** steht, ist das `SelectAction`-Element nicht Bestandteil des Konfigurationsformulars und kann daher andere Änderungen nicht blockieren. Im Feld **Bei Alarm** kann anschließend ein beliebiges Ziel samt passender Aktion gewählt werden. Die Aktion wird genau einmal ausgeführt, wenn das System erstmals in den Zustand **Alarm** wechselt. Gleichzeitig wird `AlarmOutputActive` auf aktiv gesetzt.

Die **Alarmdauer** legt fest, nach wie vielen Sekunden der Alarmausgang automatisch zurückgesetzt wird. Standard ist `0`, sodass ohne bewusste Konfiguration keine automatische Rücksetzung erfolgt; der Alarmausgang bleibt dann aktiv, bis er manuell über `OHA_ResetAlarmOutput()` zurückgesetzt oder die Anlage unscharf geschaltet wird. Beispielsweise entspricht `180` drei Minuten und führt danach automatisch die Rücksetzung aus. Die Rücksetzung beendet ausschließlich den Alarmausgang. `State` bleibt auf **Alarm**, der gewählte Scharfmodus bleibt erhalten und das Alarmgedächtnis bleibt gespeichert.

Für das Abschalten einer Sirene oder eines anderen dauerhaften Alarmgebers kann unter **Bei Rücksetzung des Alarmausgangs** eine eigene Symcon-Aktion hinterlegt werden. Diese Aktion wird pro Alarmzyklus höchstens einmal ausgeführt. Eine automatische oder manuelle Rücksetzung wird zusätzlich im Ereignisprotokoll als `alarm_output_reset` festgehalten.

Optional kann unter **Beim Unscharfschalten nach Alarm** eine weitere Aktion hinterlegt werden. Sie wird nur ausgeführt, wenn tatsächlich ein aktiver Alarm unscharf geschaltet wird, und bleibt bewusst von der Rücksetzung des Alarmausgangs getrennt. So kann beispielsweise die Sirene nach drei Minuten abgeschaltet werden, während beim späteren Unscharfschalten noch eine separate Aufräum- oder Benachrichtigungsaktion läuft.

Die Alarmdauer ist wiederanlaufsicher: Ein laufender Timer wird über einen persistenten Ablaufzeitpunkt nach `ApplyChanges()` oder einem Symcon-Neustart mit der verbleibenden Zeit wiederhergestellt. Ist der Zeitpunkt während des Neustarts bereits abgelaufen, wird die Rücksetzungsaktion unmittelbar nach dem Wiederanlauf ausgeführt.

Da die Zielauswahl Bestandteil der Symcon-Aktion ist, können sowohl einzelne Gerätevariablen als auch Skripte, Ablaufpläne und andere von Symcon angebotene Aktionsziele verwendet werden. Nicht konfigurierte Alarmaktionen haben keine Wirkung auf die Kernlogik. Auch eine fehlerhafte optionale Aktion verhindert nicht den Wechsel des Alarmzustands oder das Unscharfschalten.

### 10. Alarmgedächtnis

Beim tatsächlichen Eintritt in den Zustand **Alarm** speichert OpenHomeAlarm den auslösenden Sensor und den Alarmzeitpunkt. Bei einem Sensor mit Eingangsverzögerung wird dabei der Sensor gemerkt, der den Countdown gestartet hat; auch wenn dieser Sensor vor Ablauf der Verzögerung wieder in den Ruhezustand zurückkehrt, bleibt er die Alarmquelle. Ein Sensor ohne eingetragenen Namen wird ersatzweise über seine Variablen-ID bezeichnet.

Das Alarmgedächtnis bleibt beim Unscharfschalten erhalten. Dadurch ist nach der Rückkehr weiterhin nachvollziehbar, welcher Sensor den letzten Alarm ausgelöst hat. `OHA_ClearAlarmMemory($InstanzID)` quittiert das Alarmgedächtnis und leert Quelle und Zeitpunkt. Während eines noch aktiven Alarms wird die Quittierung abgelehnt.

### 11. Ereignisprotokoll

OpenHomeAlarm führt ein persistentes, auf die letzten 100 Einträge begrenztes Sicherheits-Ereignisprotokoll. Das Protokoll bleibt über `ApplyChanges()` und einen Symcon-Neustart erhalten und wird für die spätere Visualisierung strukturiert als JSON bereitgestellt. Der jeweils neueste Eintrag steht an erster Stelle.

Jeder Eintrag enthält `Time` als Unix-Zeitstempel, `Event` als maschinenlesbaren Ereignistyp, den zum Ereignis gehörenden `Mode` und `State` sowie optional `Source`. Als Quelle werden bei Alarmen, Eingangsverzögerungen und Sensorüberbrückungen die betroffenen Sensornamen gespeichert; bei abgelehnten oder nach der Ausgangsverzögerung abgebrochenen Scharfschaltungen enthält `Source` die blockierenden Sensoren.

Protokolliert werden erfolgreiche und abgelehnte Scharfschaltungen, Start der Ein- und Ausgangsverzögerung, Alarm, Rücksetzungen des Alarmausgangs, Unscharfschalten, temporäre Sensorüberbrückungen, das Löschen des Alarmgedächtnisses, neu aufgetretene bzw. behobene Systemstörungen sowie abgewiesene Code-Eingaben und ausgelöste temporäre Code-Sperren. Weder der konfigurierte Unscharfschaltcode noch ein eingegebener Code werden im Ereignisprotokoll gespeichert.

`OHA_GetEventHistory($InstanzID)` liefert das Protokoll als JSON. Mit `OHA_ClearEventHistory($InstanzID)` kann es gezielt geleert werden. Das Ereignisprotokoll ist ein Bedien- und Diagnoseprotokoll und kein manipulationssicheres Audit-Log.

### 12. Visualisierung

OpenHomeAlarm besitzt eine eigene responsive Objektdarstellung über das native **Symcon HTML-SDK**. Das Dashboard ist zustandsorientiert aufgebaut: **Unscharf**, **Scharf**, **Ein-/Ausgangsverzögerung** und **Alarm** werden als zentraler Hauptzustand dargestellt. Countdown, Alarmgedächtnis, aktive Systemstörungen und temporär überbrückte Sensoren erscheinen nur dann als zusätzliche Hinweise, wenn sie tatsächlich relevant sind. Dadurch bleibt die Normalansicht kompakt und die jeweils wichtigste Information steht im Vordergrund.

Direkt unter dem zentralen Sicherheitsstatus stehen **Zuhause**, **Abwesend** und **Nacht** als vollständige Modus-Schaltflächen. Im unscharfen Zustand lassen sich bereite Modi über die gesamte Schaltfläche aktivieren; während eines laufenden Scharfzustands bleiben sie als deutlich hervorgehobene Statusanzeige sichtbar und folgen der vom Backend veröffentlichten Bedienfreigabe. Sekundäre Angaben zu Überwachung, Systemstatus und Alarmgedächtnis liegen darunter, sodass für Meldungen und Protokolle mehr zusammenhängende Fläche zur Verfügung steht. Auf ausreichend breiten Kacheln ist das Codepad dauerhaft als fester Bestandteil der Alarmzentrale sichtbar. Solange keine Code-Eingabe benötigt wird, bleibt es vollständig sichtbar, aber deaktiviert. Sobald die Anlage mit aktiviertem Code-Schutz deaktiviert werden kann, wird dasselbe Codepad aktiv und bietet unter den Zifferntasten einen eindeutigen **Anlage deaktivieren**-Button. Die Ziffern-, Lösch- und Bestätigungstasten verwenden direkte Klick-Handler der HTML-SDK-Darstellung. Unterhalb einer Kachelbreite von 900 Pixeln wird das feste Codepad ausgeblendet; dort öffnet **Mit Code deaktivieren** weiterhin das kompakte Popup-Codepad. Ist kein Unscharfschaltcode konfiguriert, steht unabhängig von der Breite die direkte Deaktivierung zur Verfügung. Die Code-Eingabe bleibt ausschließlich temporär im JavaScript-Speicher der geöffneten Darstellung, wird nicht in einer Symcon-Variable abgelegt und nach Absenden, Abbrechen oder erfolgreichem Deaktivieren sofort verworfen. Falsche Codes werden direkt am aktiven Codepad gemeldet, ohne den eingegebenen Code anzuzeigen oder zu protokollieren. Während einer temporären Sperre werden beide Codepads deaktiviert und nach Ablauf automatisch wieder aus dem Backend aktualisiert.

Die Darstellung verwendet für die Kommunikation ausschließlich das passwortgeschützte HTML-SDK: Benutzeraktionen werden über `requestAction()` an `RequestAction()` des Moduls gesendet, während Statusänderungen über `UpdateVisualizationValue()` live an geöffnete Kacheln übertragen werden. Die statischen Dateien liegen unter `OpenHomeAlarm/visualization/` und werden über den zentralen `VisualizationAssetHelper` geladen.

Statusquelle bleibt unverändert die öffentliche Bedien-API: `OHA_GetControlState($InstanzID)` liefert einen versionierten JSON-Snapshot mit Modus, Zustand, verfügbaren Bedienmöglichkeiten, Code-Sperrstatus, Scharfschaltbereitschaft, strukturierten Blockierern samt Variablen-ID, temporären Überbrückungen, Verzögerungsstatus, Alarmgedächtnis und Systemstörungen. Die Visualisierung bildet keine Alarmregeln nach.

Die im Snapshot enthaltene `ApiVersion` beginnt mit `1`. Maschinenlesbare Modusnamen sind `none`, `home`, `away`, `night`; Zustandsnamen sind `disarmed`, `exit_delay`, `armed`, `entry_delay` und `alarm`.

### 13. PHP-Befehlsreferenz

Folgende öffentliche Modulbefehle stehen zur Verfügung:

| PHP-Befehl | Rückgabe | Bedeutung |
| --- | --- | --- |
| `OHA_GetControlState($InstanzID)` | `string` | Liefert den versionierten, strukturierten Bedienzustand als JSON; vorgesehen als einzige Statusquelle der eigenen Visualisierung |
| `OHA_Arm($InstanzID, $Modus)` | `bool` | Schaltet über die stabile Bedien-API mit `home`, `away` oder `night` scharf; andere Werte werden sicher abgelehnt |
| `OHA_ArmHome($InstanzID)` | `bool` | Kompatibilitäts-/Komfortbefehl für **Zuhause**; verwendet intern dieselbe Bedien-API |
| `OHA_ArmAway($InstanzID)` | `bool` | Kompatibilitäts-/Komfortbefehl für **Abwesend**; verwendet intern dieselbe Bedien-API |
| `OHA_ArmNight($InstanzID)` | `bool` | Kompatibilitäts-/Komfortbefehl für **Nacht**; verwendet intern dieselbe Bedien-API |
| `OHA_BypassSensor($InstanzID, $VariableID)` | `bool` | Überbrückt einen normalen konfigurierten Scharfsensor temporär; nur im Zustand **Unscharf** möglich |
| `OHA_RemoveSensorBypass($InstanzID, $VariableID)` | `bool` | Entfernt eine einzelne temporäre Sensorüberbrückung; nur im Zustand **Unscharf** möglich |
| `OHA_ClearSensorBypasses($InstanzID)` | `bool` | Entfernt alle temporären Sensorüberbrückungen; nur im Zustand **Unscharf** möglich |
| `OHA_Disarm($InstanzID)` | `bool` | Schaltet die Anlage als vertrauenswürdige direkte API ohne Code-Prüfung unscharf und setzt den Scharfmodus zurück |
| `OHA_DisarmWithCode($InstanzID, $Code)` | `bool` | Prüft den optionalen Unscharfschaltcode und schaltet bei Erfolg unscharf |
| `OHA_ResetAlarmOutput($InstanzID)` | `bool` | Setzt während eines aktiven Alarms nur den Alarmausgang zurück; Alarmzustand und Alarmgedächtnis bleiben erhalten |
| `OHA_ClearAlarmMemory($InstanzID)` | `bool` | Quittiert das gespeicherte Alarmgedächtnis; während eines aktiven Alarms wird `false` zurückgegeben |
| `OHA_GetEventHistory($InstanzID)` | `string` | Liefert das persistente Sicherheits-Ereignisprotokoll als JSON, neuester Eintrag zuerst |
| `OHA_ClearEventHistory($InstanzID)` | `bool` | Leert das persistente Sicherheits-Ereignisprotokoll |

Die Scharfschaltbefehle liefern `false`, wenn das System nicht **Unscharf** ist oder mindestens ein für den Zielmodus relevanter Sensor bzw. eine blockierende Systemstörung die Scharfschaltung verhindert. In diesem Fall bleiben `Mode` und `State` unverändert. Für neue benutzerseitige Oberflächen ist `OHA_Arm()` die bevorzugte Schnittstelle; die drei modusspezifischen Befehle bleiben kompatibel erhalten.
