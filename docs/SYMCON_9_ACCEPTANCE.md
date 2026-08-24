# Symcon-9.0-Abnahme

Dieses Dokument beschreibt die Freigabeprüfung für OpenHomeAlarm auf einer
realen Symcon-9.0-Installation. Die automatisierten Repository-Tests bleiben
Voraussetzung, ersetzen aber nicht die Prüfung der Symcon-Laufzeit, der
Visualisierungen und der nativen Aktionen.

## Freigabestatus

| Bereich | Status | Freigabebedingung |
| --- | --- | --- |
| Automatisierte Tests | Bestanden | `php tests/run.php` endet ohne Fehler |
| PHP-Syntax | Bestanden | Alle PHP-Dateien bestehen `php -l` |
| JSON-Format | Bestanden | `php .style/json-check.php` endet ohne Fehler |
| Symcon-9.0-Laufzeit | Offen | Alle Pflichtfälle dieser Matrix sind auf Symcon 9.0 bestanden |
| HTML-SDK-Kachel | Offen | Bedienung und Darstellung sind auf Desktop und Mobilgerät bestanden |
| IPSView | Offen | WebContent, WebHook und Token-Prüfung sind bestanden |
| Update/Migration | Offen | Bestehende Instanz wird ohne Konfigurations- oder Zustandsverlust aktualisiert |
| Release Candidate | Blockiert | Erst möglich, wenn kein offener Pflichtfall und kein kritischer Befund verbleibt |

`Bestanden` bezeichnet hier den zuletzt lokal geprüften Repository-Stand. Vor
jedem Release Candidate müssen die automatisierten Prüfungen erneut ausgeführt
und die Ergebnisse unten protokolliert werden.

## Testumgebung

Vor Beginn für jede getestete Umgebung ausfüllen:

| Angabe | Wert |
| --- | --- |
| Datum | |
| Prüfer | |
| Git-Commit | |
| Library-Version | |
| Symcon-Version einschließlich Build | |
| Betriebssystem und Version | |
| PHP-Version der Symcon-Laufzeit | |
| Visualisierung und Client-Version | |
| IPSView-Version, falls verwendet | |
| Installationsart | Neuinstallation / Update |
| Bemerkungen | |

## Statuswerte

- **Offen:** noch nicht ausgeführt
- **Bestanden:** Soll-Ergebnis vollständig erreicht
- **Fehlgeschlagen:** Soll-Ergebnis nicht erreicht; Befund dokumentieren
- **Blockiert:** Prüfung wegen einer nachvollziehbaren Abhängigkeit nicht möglich
- **Nicht zutreffend:** nur bei optionalen Fällen und mit Begründung zulässig

Ein Pflichtfall darf nicht als **Nicht zutreffend** markiert werden. Nach einer
Fehlerbehebung wird der betroffene Fall vollständig wiederholt; ein bloßer
Verweis auf automatisierte Tests genügt nicht.

## Vorbereitung

1. Einen eigenen Symcon-9.0-Testserver verwenden, nicht die produktive Alarmanlage.
2. Vor einem Update eine Symcon-Sicherung und einen Export der Instanzkonfiguration erstellen.
3. Testvariablen für Boolean, Integer, Float und String mit diskreten Darstellungen anlegen.
4. Je eine Testaktion für Alarm, Alarmrücksetzung, Unscharfschaltung, neue Störung und behobene Störung anlegen. Jede Aktion schreibt Zeitpunkt und Aufrufparameter in ein separates Testprotokoll.
5. Sensoren für Zuhause, Abwesend, Nacht, 24/7, Ausgangsweg und Eingangsverzögerung konfigurieren.
6. Störungseingänge für blockierende, alarmierende und rein informative Störungen konfigurieren.
7. Ausgangs- und Eingangsverzögerung für die Prüfung kurz, aber beobachtbar einstellen, beispielsweise auf zehn Sekunden.

## Pflichtmatrix

In der Spalte **Nachweis** einen Screenshot, einen Protokollauszug oder eine
kurze Beobachtung mit Zeitstempel eintragen.

### A. Installation und Konfiguration

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| A-01 | Library auf Symcon 9.0 installieren und OpenHomeAlarm-Instanz anlegen | Instanz wird ohne Fehler angelegt und ist aktiv | Offen | |
| A-02 | Konfigurationsformular öffnen | Alle Abschnitte, Sensorlisten, Aktionen und IPSView-Einstellungen werden vollständig dargestellt | Offen | |
| A-03 | Boolean-, Integer-, Float- und Stringvariable als Sensor auswählen | Diskrete Zustände werden mit den Symcon-Beschriftungen angeboten und korrekt gespeichert | Offen | |
| A-04 | Unvollständigen Listeneintrag mit Variablen-ID 0 speichern | Eintrag verursacht weder Alarm noch Störung und blockiert keine Scharfschaltung | Offen | |
| A-05 | Konfiguration übernehmen und Instanz erneut öffnen | Alle Werte bleiben erhalten; Statusvariablen und Timer sind vorhanden | Offen | |
| A-06 | Modul auf derselben Version erneut laden und `ApplyChanges()` auslösen | Betriebszustand und persistente Daten werden nicht unbeabsichtigt zurückgesetzt | Offen | |

### B. Zustände und Scharfschaltung

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| B-01 | Zuhause, Abwesend und Nacht jeweils mit bereiten Sensoren scharfschalten | Gewählter Modus und Zustandsfolge sind korrekt | Offen | |
| B-02 | Einen nur für Abwesend relevanten Sensor auslösen und Zuhause scharfschalten | Zuhause bleibt bereit; Abwesend wird blockiert und nennt den Sensor | Offen | |
| B-03 | Relevanten Sensor vor dem Scharfschalten auslösen | Scharfschaltung wird abgewiesen; Bereitschaft und Blockierliste stimmen | Offen | |
| B-04 | Fehlende relevante Sensorvariable simulieren | Alle betroffenen Modi sind nicht bereit; Systemstörung und Sensorname/ID werden angezeigt | Offen | |
| B-05 | Anlage ohne Code unscharfschalten | Verzögerungen enden, Modus wird zurückgesetzt und Bypässe werden nach einem Scharfzyklus gelöscht | Offen | |
| B-06 | Ungültigen Modus über die öffentliche API anfordern | Anfrage wird ohne Zustandsänderung abgewiesen | Offen | |

### C. Verzögerungen und Ausgangsweg

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| C-01 | Mit aktivierter Ausgangsverzögerung scharfschalten | Zustand wechselt auf Ausgangsverzögerung; Countdown läuft bis Scharf | Offen | |
| C-02 | Ausgangswegsensor beim Start offen lassen und vor Ablauf schließen | Scharfschaltung wird nach Ablauf erfolgreich abgeschlossen | Offen | |
| C-03 | Ausgangswegsensor bis zum Ablauf offen lassen | Scharfschaltung wird sicher abgebrochen und Anlage wird unscharf | Offen | |
| C-04 | Eingangsverzögerten Sensor im scharfen Zustand auslösen | Eingangsverzögerung startet einmal; Quelle und Countdown stimmen | Offen | |
| C-05 | Verzögerten Sensor während des Countdowns wieder schließen | Countdown wird nicht abgebrochen oder neu gestartet | Offen | |
| C-06 | Sofortalarm-Sensor während der Eingangsverzögerung auslösen | Anlage wechselt unmittelbar in Alarm | Offen | |

### D. Alarm, Aktionen und Gedächtnis

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| D-01 | Relevanten Sofortalarm-Sensor auslösen | Zustand ist Alarm; Quelle, Zeitpunkt und Alarmgedächtnis werden gesetzt | Offen | |
| D-02 | Während desselben Alarms weitere Sensorereignisse erzeugen | Alarmaktion wird genau einmal ausgeführt | Offen | |
| D-03 | Alarmausgang manuell zurücksetzen | Rücksetzungsaktion wird einmal ausgeführt; Alarmzustand und Gedächtnis bleiben erhalten | Offen | |
| D-04 | Automatische Alarmdauer ablaufen lassen | Alarmausgang wird zurückgesetzt; Alarmzustand und Gedächtnis bleiben erhalten | Offen | |
| D-05 | Aktiven Alarm unscharfschalten | Alarmausgang wird zurückgesetzt und Aktion „Unscharf nach Alarm“ läuft genau einmal | Offen | |
| D-06 | Alarmgedächtnis nach dem Alarm quittieren | Quittierung ist erst außerhalb des Alarmzustands möglich und löscht die letzten Alarmdaten wie dokumentiert | Offen | |
| D-07 | Ereignishistorie prüfen und löschen | Ereignisse sind chronologisch, enthalten keine Codes und lassen sich kontrolliert löschen | Offen | |

### E. 24/7-Sensoren, Bypass und Störungen

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| E-01 | 24/7-Sensor im unscharfen Zustand auslösen | Alarm wird unmittelbar und unabhängig vom Modus ausgelöst | Offen | |
| E-02 | Bypass im unscharfen Zustand setzen und scharfschalten | Sensor wird in Bereitschaft und Alarmauswertung ignoriert; Status zeigt den Bypass | Offen | |
| E-03 | Bypass im scharfen Zustand oder für 24/7-Sensor anfordern | Anfrage wird ohne Änderung abgewiesen | Offen | |
| E-04 | Blockierende Störung aktivieren | Systemstörung wird angezeigt und alle konfigurierten Modi werden blockiert | Offen | |
| E-05 | Alarmierende Störung aktivieren | Alarm wird unabhängig vom Scharfmodus ausgelöst | Offen | |
| E-06 | Informative Störung aktivieren und beheben | Störungsstatus und Aktionen für Auftreten/Behebung sind korrekt; Scharfschaltung bleibt entsprechend der Konfiguration möglich | Offen | |
| E-07 | Störungsvariable löschen oder unlesbar machen | Eingang wird sicherheitsgerichtet als Störung behandelt | Offen | |

### F. Code-Schutz und Bedienwege

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| F-01 | Mit richtigem vier- bis achtstelligem Code unscharfschalten | Anlage wird unscharf; Code erscheint in keinem Status- oder Ereignisprotokoll | Offen | |
| F-02 | Wiederholt falschen Code eingeben | Fehlversuche werden gezählt und nach dem Grenzwert wird die Sperrzeit aktiv | Offen | |
| F-03 | Richtigen Code während der Sperrzeit eingeben | Unscharfschaltung bleibt bis zum Ende der Sperrzeit gesperrt | Offen | |
| F-04 | Öffentliche vertrauenswürdige `OHA_Disarm()`-API verwenden | Unscharfschaltung funktioniert ohne Code entsprechend der dokumentierten Sicherheitsgrenze | Offen | |
| F-05 | HTML-SDK-Kachel auf Desktop bedienen | Modi, Bypässe, Codepad, Alarmrücksetzung, Gedächtnis und Ereignisse funktionieren | Offen | |
| F-06 | HTML-SDK-Kachel auf kleinem mobilen Bildschirm bedienen | Keine unbedienbaren oder überlappenden Elemente; alle Pflichtaktionen erreichbar | Offen | |
| F-07 | IPSView-WebContent mit gültigem Token bedienen | Statusaktualisierung und erlaubte POST-Aktionen funktionieren | Offen | |
| F-08 | IPSView-WebHook mit ungültigem Token, GET und unbekannter Aktion aufrufen | Zugriff wird ohne Zustandsänderung abgewiesen | Offen | |
| F-09 | IPSView-Ausgabe deaktivieren und alten WebHook erneut aufrufen | WebHook ist nicht mehr nutzbar | Offen | |

### G. Neustart, Update und Wiederherstellung

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| G-01 | Symcon während der Ausgangsverzögerung neu starten | Countdown wird mit der verbleibenden Zeit fortgesetzt oder bei abgelaufener Frist korrekt abgeschlossen | Offen | |
| G-02 | Symcon während der Eingangsverzögerung neu starten | Countdown und Alarmquelle werden korrekt wiederhergestellt | Offen | |
| G-03 | Symcon während der Alarmdauer neu starten | Alarmausgang wird für die Restdauer korrekt wiederhergestellt | Offen | |
| G-04 | Symcon im scharfen Zustand neu starten und Sensor während des Neustarts auslösen | Anliegende Auslösung wird nach dem Start erkannt | Offen | |
| G-05 | Symcon mit aktivem 24/7-Sensor neu starten | Anliegende Auslösung wird unmittelbar nach Initialisierung erkannt | Offen | |
| G-06 | Symcon während einer Code-Sperrzeit neu starten | Fehlversuche und verbleibende Sperrzeit bleiben erhalten | Offen | |
| G-07 | Bestehende Instanz vom letzten freigegebenen Stand aktualisieren | Konfiguration, Modus, Zustand, Bypässe, Historie und Darstellungen bleiben kompatibel | Offen | |
| G-08 | Vorherige Sicherung in separater Testinstanz wiederherstellen | Instanz ist wieder bedienbar und enthält den erwarteten gesicherten Zustand | Offen | |

## Automatisierte Vorprüfung

Vor Beginn und nach jeder Korrektur ausführen:

```text
php tests/run.php
php .style/json-check.php
```

Die PHP-Syntaxprüfung erfolgt für alle PHP-Dateien unter `OpenHomeAlarm`,
`libs` und `tests`. Der zentrale CI-Workflow muss zusätzlich die Checks
`tests` und `style` für den exakten Release-Commit erfolgreich abschließen.

## Befundprotokoll

| Befund-ID | Testfall | Schweregrad | Beschreibung | Reproduktion | Status/Verweis |
| --- | --- | --- | --- | --- | --- |
| | | kritisch / hoch / mittel / niedrig | | | |

Kritische oder hohe offene Befunde blockieren den Release Candidate. Mittlere
Befunde benötigen eine dokumentierte Entscheidung. Niedrige Befunde dürfen nur
mit Begründung in einen Folgerelease verschoben werden.

## Freigabeentscheidung

Ein Commit darf als Release Candidate markiert werden, wenn:

1. alle automatisierten Checks und beide CI-Checks für exakt diesen Commit bestanden sind,
2. alle Pflichtfälle A-01 bis G-08 auf mindestens einer repräsentativen Symcon-9.0-Installation bestanden sind,
3. Desktop- und Mobilbedienung der HTML-SDK-Kachel geprüft wurden,
4. die IPSView-Fälle bestanden sind oder IPSView ausdrücklich nicht Bestandteil des Releases ist,
5. Update und Wiederherstellung praktisch geprüft wurden,
6. keine kritischen oder hohen Befunde offen sind,
7. Commit, Library-Version, Symcon-Build und Nachweise im Protokoll eingetragen sind.

### Ergebnis

| Entscheidung | Wert |
| --- | --- |
| Freigegeben als Release Candidate | Ja / Nein |
| Commit | |
| Library-Version | |
| Datum | |
| Prüfer | |
| Offene akzeptierte Restrisiken | |
| Verweis auf CI-Lauf und Nachweise | |
