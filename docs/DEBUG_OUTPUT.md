# Debug-Ausgaben des Pilotmoduls

OpenHomeAlarm setzt den gemeinsamen Standard aus `SymconDevelopment/standards/DEBUG_OUTPUT.md` um. Die Ausgabe erfolgt im nativen Symcon-Debug-Fenster der Instanz; die Oberflächensprache ändert die deutschen Debug-Meldungen nicht.

## Aufbau lesen

Die Kategorie lautet beispielsweise `OpenHomeAlarm | SENDEN | SetAlarmState:1234`. Die Zeile ist im tatsächlichen Modul automatisch bestimmt; `1234` ist hier nur ein Schema-Beispiel. Die Nutzlast enthält:

| Feld | Bedeutung |
| --- | --- |
| `Instanz` | Instanz-ID des Moduls |
| `Vorgang` | Kennung eines äußeren Modulaufrufs und seiner verschachtelten Aufrufe |
| `Aufruf` | Laufende Nummer des jeweiligen Funktionsaufrufs innerhalb dieses Vorgangs |
| `Meldung` | Deutsche Beschreibung des Ereignisses |
| `Bezug` | Bereich, Variable, Aktion, Wert, Schnittstelle, Ergebnis oder Fehlerursache |

`START` und `ENDE` umschließen die öffentlichen Modul-Einstiege einschließlich Lebenszyklus, Timer, Formularaktionen und Exporte. Hinzu kommen interne Grenzen für Laufzeitinitialisierung, Visualisierungsaktionen und -ausgaben, konfigurierte Aktionen einschließlich Rücksetzung, Symcon-Benachrichtigungen und Pushover. Kleine reine interne Berechnungen erhalten keine eigenen Start-/Endpaare. Bei einer Ausnahme wird der Abbruch erfasst; die ursprüngliche Ausnahme wird unverändert weitergereicht oder vom bisherigen fachlichen Fehlerpfad behandelt.

Eine neue Sensormeldung oder ein späterer Timeraufruf beginnt einen eigenen Vorgang. Über Bereich und Variablen-ID lassen sich diese Folgeereignisse zuordnen; eine über mehrere Symcon-Aufrufe hinweg persistierte Vorgangskennung wird nicht behauptet.

## Typischer Ablauf

1. `START`: `ArmAway` beginnt, einschließlich gewünschter Verzögerung und Optionen.
2. Weitere `START`-Meldungen desselben Vorgangs zeigen die aufgerufenen Modul-Einstiege.
3. `EMPFANG`: Ein bereits für die Entscheidung gelesener Sensorwert erscheint mit Variablen-ID und Wert.
4. `AKTION`: Die Scharfschaltung wird mit Quelle/Blockern abgelehnt oder der Bereich wird scharfgeschaltet.
5. `SENDEN`: Geschriebene Statuswerte nennen Identifier und Wert. Eine konfigurierte Aktion nennt `IPS_RunAction`, Aktionskennung und bekanntes Ziel; beliebige Aktionsparameter bleiben geschützt.
6. `EMPFANG` oder `FEHLER`: Eine Schnittstelle bestätigt die Annahme oder meldet eine Ablehnung. Annahme eines Aktionsstarts oder einer Benachrichtigung beweist noch keine Wirkung am Gerät oder Anzeige beim Empfänger.
7. `ENDE`: Jeder erfasste Aufruf endet mit Rückgabeergebnis, Beschreibung eines geschützten Text-/Strukturergebnisses oder Ausnahmeabbruch.

Bei `MessageSink` wird die Nachricht einer überwachten Variable mit ihrem Meldungswert protokolliert. Separat gelesene Quellwerte werden als gelesen bezeichnet. Entfernte Quellen erzeugen keine erfundenen Empfangswerte. Symcon-Benachrichtigungen, HTML-SDK-Zustandsübergaben, IPSView-Antwortstatus sowie Pushover-Anfragen, HTTP-Status und fachliche Bestätigungen sind eigene Schnittstellenereignisse.

## Geschützte Inhalte

Der unveränderte zentrale `DebugHelper` ist als Abhängigkeit abonniert und über das vorhandene Bundle-Verfahren lokal eingebunden. Es wurden keine anderen Helper aktualisiert. Seine Maskierung und Begrenzung werden durch die fachliche Datenauswahl ergänzt: Keine Unscharfschaltcodes, Benutzer-Codes, Pushover-Zugangsdaten, IPSView-Token, Empfangskennungen, Sicherungsinhalte, vollständigen Visualisierungspayloads oder unkontrollierten externen Fehlertexte.

JSON-/Text-Rückgaben werden nur mit ihrer Länge, Strukturen mit ihrer Eintragsanzahl beschrieben. Aktionsparameter bleiben auch bei numerischem Inhalt geschützt, da sie Codes enthalten können. Bekannte Fehler werden auf deutsche Ursachen abgebildet; unbekannte Ausnahmen werden anhand von Funktion, Fehlerklasse und Fehlerzeile eingegrenzt. Eine Nutzlast ist auf 4096 Bytes begrenzt. Die Ausgabe erzeugt keine zusätzlichen Netzwerkanfragen, Wertabfragen oder Aktionsausführungen. Fehler des Debug-Kanals werden abgefangen.

## Nachweise und reale Abnahme

`php tests/debug-output.php` verwendet die vorhandenen Alarm-/Aktionsfixtures. Geprüft werden erfolgreiche und blockierte Scharfschaltung, Sensorwerte, Statusausgaben, verschachtelte Aufrufe, Quellzeilen, Fehler und anschließende Wiederverwendung, Codeschutz, Pushover-Bestätigung und -Ablehnung, vertrauliche Fehlerantworten, unveränderte Aktionsparameter und ein ausfallender Debug-Kanal. Der Gesamteinstieg bleibt `php tests/run.php`.

Die folgende Abnahme bleibt auf einer ausdrücklich freigegebenen Symcon-Testinstanz auszuführen. Diese Änderung installiert oder aktiviert nichts:

| Fall | Erwartete Beobachtung |
| --- | --- |
| Scharfschalten ohne Blocker | Start bis Ende verfolgt; Bereich, Zustand und positives Ergebnis erkennbar. |
| Scharfschalten bei offenem Sensor | Variablen-ID, gelesener Wert und Blocker nachvollziehbar; negatives Ergebnis. |
| Sensorauslösung mit Verzögerung | Empfang, Eingangsverzögerung und anschließender Timer-/Alarmvorgang zuordenbar. |
| Unscharfschalten mit falschem/richtigem Code | Ablehnung beziehungsweise Zustandswechsel verständlich; kein Code im gesamten Export. |
| Konfigurierte Aktion und Rücksetz-Aktion | Ziel und Annahme/Ablehnung sichtbar; reale Wirkung gesondert prüfen. |
| Symcon-Push und Pushover | Übergabe, HTTP-/Transportergebnis und fachliche Bestätigung unterscheidbar; keine Zugangsdaten. |
| IPSView und HTML-SDK | Bedienaktion und Zustandstransport erkennbar; keine Token oder exportierten Sicherungen. |
| Fehlerhafte Konfiguration und Wiederanlauf | Deutsche Ursache, betroffene Prüfung und erhaltene Status-/Wiederanlauffunktion. |
| Ruhender und häufig aktualisierter Betrieb | Datenmenge, Lesbarkeit und Laufzeit mit aktiviertem und deaktiviertem Debug prüfen. |

Die reale Abnahme und ein Performancenachweis auf der Zielinstallation sind durch lokale Tests nicht erbracht.
