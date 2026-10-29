# Symcon-9.x-Abnahme

Dieses Dokument beschreibt die Freigabeprüfung für OpenHomeAlarm auf realen
Symcon-9.x-Installationen. Die automatisierten Repository-Tests bleiben
Voraussetzung, ersetzen aber nicht die Prüfung der Symcon-Laufzeit, der
Visualisierungen und der nativen Aktionen. Die Mindestversion 9.0 und der
aktuelle Entwicklungsstand 9.1 wurden praktisch geprüft.

Der verbindliche Funktionsumfang, die Kompatibilitätsgrenzen und die
Release-Gates stehen im [Release-Zielbild](RELEASE_SCOPE.md).

Die aktuelle zusammengefasste Praxisabnahme ist im
[Abnahmeergebnis vom 23.10.2026](acceptance-results/2026-10-23-symcon-9-field-acceptance.md)
dokumentiert. Sie fasst die wiederholten Tests des Maintainers und der
Beta-Tester unter Symcon 9.0 und 9.1 zusammen. Die detaillierte interne Abnahme
der Erweiterungen H-01 bis H-16 steht im
[Abnahmeergebnis vom 04.09.2026](acceptance-results/2026-09-04-symcon-9.1-h.md),
die historische Basisabnahme im
[Abnahmeergebnis vom 24.08.2026](acceptance-results/2026-08-24-symcon-9.1.md).
Die nachfolgende Matrix bleibt als wiederverwendbare Vorlage auf **Offen**;
konkrete Ausführungen werden ausschließlich in datierten Ergebnisprotokollen
festgehalten.

## Freigabestatus

| Bereich | Status | Freigabebedingung |
| --- | --- | --- |
| Automatisierte Tests | Bestanden | `php tests/run.php` endet ohne Fehler |
| PHP-Syntax | Bestanden | Alle PHP-Dateien bestehen `php -l` |
| JSON-Format | Bestanden | `php .style/json-check.php` endet ohne Fehler |
| Historische Basisabnahme | Bestanden (49/49) | Der freigegebene Basisumfang wurde am 24.08.2026 auf Symcon 9.1 bestanden |
| Bisheriger Funktionsumfang A-01 bis H-46 | Bestanden | Wiederholt durch Maintainer und Beta-Tester unter Symcon 9.0 und 9.1 geprüft; zusammengefasster Nachweis vom 23.10.2026 |
| Bereichsstatusvariablen H-47 bis H-49 | Offen | Vor der nächsten Freigabe auf der maßgeblichen Symcon-Version praktisch prüfen |
| Bereichs-Countdown H-50 | Offen | Kachel und IPSView auf Desktop, Mobilgerät und mit reduzierter Bewegung praktisch prüfen |
| HTML-SDK-Kachel | Bestanden | Bedienung und Darstellung sind auf Desktop und Mobilgerät bestanden |
| IPSView | Bestanden | WebContent, WebHook und Token-Prüfung sind bestanden |
| Update/Migration | Bestanden | Bestehende Instanz wird ohne Konfigurations- oder Zustandsverlust aktualisiert |
| Release Candidate | Nicht freigegeben | Die neuen Bereichsprüfungen H-47 bis H-50 sind noch praktisch durchzuführen; anschließend sind der endgültige Commit und dessen zentrale CI festzuhalten |

`Bestanden` bezeichnet hier den zuletzt lokal geprüften Repository-Stand. Vor
jedem Release Candidate müssen die automatisierten Prüfungen erneut ausgeführt
und die Ergebnisse in einem datierten Ergebnisprotokoll festgehalten werden.

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
| G-07 | Bestehende Instanz von der definierten Update-Baseline aktualisieren; ab dem zweiten Release den letzten freigegebenen Stand verwenden | Konfiguration, Modus, Zustand, Bypässe, Historie und Darstellungen bleiben kompatibel | Offen | |
| G-08 | Vorherige Sicherung in separater Testinstanz wiederherstellen | Instanz ist wieder bedienbar und enthält den erwarteten gesicherten Zustand | Offen | |

### H. Erweiterungen des aktuellen Release-Kandidaten

| ID | Prüfschritt | Soll-Ergebnis | Status | Nachweis |
| --- | --- | --- | --- | --- |
| H-01 | Zwei Alarmbereiche mit jeweils eigenen Sensoren konfigurieren und getrennt scharf-/unscharfschalten | Zustände, Bereitschaft, Alarm und Bedienung bleiben auf den gewählten Bereich begrenzt | Offen | |
| H-02 | Einen Alarm in einem Bereich auslösen und Ausgang sowie Gedächtnis bereichsbezogen zurücksetzen | Andere Bereiche und deren Alarmdaten bleiben unverändert | Offen | |
| H-03 | Mehrere Unscharfschaltbenutzer konfigurieren und jeden gültigen Code verwenden | Jeder aktive Benutzer kann unscharfschalten; Codes werden nirgends ausgegeben | Offen | |
| H-04 | Ungültige Benutzercodes bis zur Sperre eingeben und Symcon neu starten | Gemeinsame Fehlversuche und Rest-Sperrzeit bleiben erhalten; korrekte Codes wirken erst danach | Offen | |
| H-05 | Fälligen Wochenzeitplan mit bereiten Sensoren ausführen | Der konfigurierte Bereich wird genau einmal im vorgesehenen Modus scharfgeschaltet | Offen | |
| H-06 | Fälligen Zeitplan bei blockierter Bereitschaft oder bereits aktiver Anlage ausführen | Keine unsichere Scharfschaltung und kein unerwünschter Moduswechsel finden statt | Offen | |
| H-07 | Eingangs- und Ausgangsverzögerung mit aktivierter Sekundenaktion durchlaufen | Die Aktion erhält jeden positiven Restwert einmal und kann Quelle, Modus und Zustand lesen | Offen | |
| H-08 | Mehrere Alarm-Eskalationsstufen mit kurzen Abständen auslösen | Jede aktive Stufe läuft einmal in der vorgesehenen Reihenfolge; deaktivierte Stufen bleiben aus | Offen | |
| H-09 | Symcon zwischen zwei Eskalationsstufen neu starten | Bereits fällige Schritte werden nicht doppelt ausgeführt und verbleibende Schritte laufen korrekt weiter | Offen | |
| H-10 | Ereignishistorie als JSON und CSV exportieren | Inhalt und Reihenfolge stimmen mit der Ansicht überein; Codes und andere Geheimnisse fehlen | Offen | |
| H-11 | Diagnose mit bereiten, ausgelösten, fehlenden, unlesbaren und deaktivierten Eingängen prüfen | API, Kachel und IPSView zeigen konsistente Zustände, Bereiche, Zeitpunkte und Problemanzahl | Offen | |
| H-12 | Diagnose als JSON und CSV exportieren | Beide Exporte enthalten die sichtbaren Diagnosedaten vollständig und maschinenlesbar | Offen | |
| H-13 | Konfigurationssicherung exportieren und vertrauliche Kennzeichnung prüfen | Format, Version, Modul-ID und Kennzeichnung sind vorhanden; Laufzeitzustand und Historie fehlen | Offen | |
| H-14 | Eine geänderte Konfiguration im vollständig unscharfen Zustand aus der Sicherung wiederherstellen | Alle gesicherten Eigenschaften und Datentypen werden exakt wiederhergestellt | Offen | |
| H-15 | Wiederherstellung bei scharfem Bereich sowie mit fremder Modul-ID, unbekannter Eigenschaft und falschem Datentyp versuchen | Jeder Versuch wird ohne Konfigurationsänderung verständlich abgewiesen | Offen | |
| H-16 | Eine Sicherung ohne eine erst in einer neueren Modulversion eingeführte Eigenschaft einspielen | Die fehlende Eigenschaft behält ihren aktuellen Standard- beziehungsweise Konfigurationswert | Offen | |
| H-17 | Einen mehrwertigen Integer-Sensor auf „Abweichung vom Normalwert“ konfigurieren und als normalen sowie als 24/7-Sensor prüfen | Der Normalwert ist bereit; jeder andere gültige Wert blockiert die Scharfschaltung beziehungsweise löst im überwachten Zustand Alarm aus; fehlende oder nicht auswertbare Werte bleiben sicherheitsgerichtet gestört | Offen | |
| H-18 | Einen ausgelösten Türsensor als Ausgangsweg für Zuhause, Abwesend und Nacht konfigurieren und die drei Scharfmodi nacheinander anfordern | Zuhause und Nacht werden sofort mit sichtbarem Blocker abgewiesen; nur Abwesend darf die Ausgangsverzögerung starten und wird bei weiterhin ausgelöstem Kontakt am Countdown-Ende abgebrochen | Offen | |
| H-19 | Zwei Störungen gleichzeitig aktivieren, anschließend zuerst eine und danach die letzte Störung beheben | Jede neue Störung wird einzeln gemeldet; die Aktion nach Behebung aller Störungen bleibt beim ersten Teilübergang aus und läuft erst nach Behebung der letzten Störung genau einmal | Offen | |
| H-20 | Einen dafür freigegebenen Türsensor in einem scharfen Bereich per API zeitlich begrenzt freigeben, öffnen und wieder schließen | Der Bereich bleibt durchgehend scharf; nur dieser Sensor wird für genau einen Durchgang unterdrückt und ist danach wieder vollständig überwacht | Offen | |
| H-21 | Während einer Durchgangsfreigabe einen anderen Sensor auslösen sowie die Frist einmal mit weiterhin geöffneter Tür ablaufen lassen | Der andere Sensor löst unverändert Alarm aus; die bei Fristablauf noch offene freigegebene Tür löst unmittelbar Alarm aus | Offen | |
| H-22 | Eine wartende und eine bereits ausgelöste Durchgangsfreigabe jeweils über ApplyChanges und Symcon-Neustart prüfen; außerdem unfreigegebene, nicht verfügbare und 24/7-Sensoren anfordern | Gültige Freigabe und Restfrist werden ohne Doppelereignis wiederhergestellt; unzulässige Anforderungen werden abgewiesen und umgehen keine Schutzregel | Offen | |
| H-23 | Bei mindestens drei getrennten Bereichen genau zwei Bereiche gemeinsam scharf- und danach wieder unscharfschalten | Nur die ausgewählten Bereiche wechseln ihren Zustand; der dritte Bereich bleibt unverändert | Offen | |
| H-24 | Zwei Bereiche gemeinsam scharfschalten, während einer davon einen nicht überbrückbaren Blocker enthält | Der Befehl wird vollständig abgewiesen; keiner der beiden Bereiche ändert seinen Zustand | Offen | |
| H-25 | Denselben freigegebenen Sensor in zwei gleichzeitig scharfen Bereichen gemeinsam freigeben; anschließend einen dritten, unzulässigen Bereich in denselben Befehl aufnehmen | Die gültige Zweierfreigabe wirkt in beiden Bereichen; der ungültige Dreierbefehl wird ohne Teilfreigabe abgewiesen | Offen | |
| H-26 | Eingangs- und Ausgangsverzögerung jeweils regulär beenden und jeweils kontrolliert abbrechen, während Sekunden- und Endaktion konfiguriert sind; einen laufenden Countdown zusätzlich über ApplyChanges/Neustart wiederherstellen | Jeder positive Countdown-Schritt läuft höchstens einmal; die Endaktion läuft nach jedem begonnenen Countdown genau einmal und wird weder ausgelassen noch doppelt ausgeführt | Offen | |
| H-27 | Eingangs- und Ausgangsverzögerung in der iOS-Kachel und in IPSView beobachten; die Ansicht während des Countdowns kurz in den Hintergrund und wieder nach vorn bringen | Der sichtbare Countdown läuft anhand der Backend-Deadline ohne Sprünge durch verzögert zugestellte Einzelupdates weiter und zeigt nach der Rückkehr sofort die korrekte Restzeit; der fachliche Zustandswechsel bleibt backendgesteuert | Offen | |
| H-28 | Zwei einzelne Bereiche scharfschalten, während `main` selbst unscharf bleibt; anschließend `main` auswählen und mit aktiviertem Codeschutz deaktivieren | Die Oberfläche bietet „Alle Bereiche deaktivieren“ an; eine einzige gültige Code-Eingabe schaltet alle Bereiche unscharf, eine ungültige Eingabe verändert keinen Bereich | Offen | |
| H-29 | Einen ausgelösten Sensor mehreren Bereichen zuordnen und in der unscharfen Gesamtansicht `main` als Schaltziel wählen | Der Sensor wird in jeder betroffenen Bereichsprüfung berücksichtigt, erscheint in der zusammengefassten Blockerliste jedoch nur einmal | Offen | |
| H-30 | In der nativen Kachel einen wegen eines Blockers abgewiesenen Schaltversuch auslösen, ohne danach eine weitere Aktion auszuführen | Der neue Eintrag „Scharfschaltung abgelehnt“ erscheint sofort im Systemprotokoll | Offen | |
| H-31 | Je einen Schaltvorgang im Hauptbereich und in einem benannten Zusatzbereich ausführen | Der Hauptbereich bleibt kompakt beschriftet; beim Zusatzbereich beginnt der Protokolleintrag mit dessen benutzerdefiniertem Namen | Offen | |
| H-32 | Systemprotokoll und Systemdiagnose in einer schmalen mobilen Kachel sowie IPSView öffnen | Diagnosezähler, JSON- und CSV-Schaltflächen bleiben vollständig sichtbar, bedienbar und ohne horizontales Abschneiden | Offen | |
| H-33 | Ausgangs- und Eingangsverzögerung für einen einzelnen Zusatzbereich sowie eine gemeinsam gewählte Bereichsmenge mit Start-, Sekunden- und Endaktion durchlaufen; einen Lauf kontrolliert abbrechen und einen laufenden Countdown über ApplyChanges/Neustart wiederherstellen | Die Startaktion läuft je gemeinsam begonnenem Countdown genau einmal, die Sekundenaktion je positivem Restwert höchstens einmal und die Endaktion bei Ablauf oder Abbruch genau einmal; gemeinsam gestartete Bereiche vervielfachen keine Aktion | Offen | |
| H-34 | Die Aktion nach vollständigem Unscharfschalten konfigurieren; eine bereits unscharfe Anlage, einen einzelnen von mehreren aktiven Bereichen, den letzten aktiven Bereich und mehrere Bereiche gemeinsam unscharfschalten | Die Aktion bleibt bei bereits vollständig unscharfer Anlage und solange ein anderer Bereich aktiv bleibt aus; beim Übergang des letzten aktiven Bereichs beziehungsweise beim gemeinsamen Gesamtübergang läuft sie genau einmal | Offen | |
| H-35 | Einen Sensor `main` und einem Zusatzbereich zuordnen, den Zusatzbereich anschließend löschen und den Sensor erneut bearbeiten | Die verwaiste Zuordnung bleibt als markierte, entfernbare Checkbox sichtbar; nach dem Abwählen lässt sich die Konfiguration ohne erneutes Anlegen des Bereichs speichern | Offen | |
| H-36 | Vier Alarm-Eskalationsaktionen jeweils nur für Zuhause, Abwesend, Nacht beziehungsweise 24/7 konfigurieren; die drei Scharfmodi und einen 24/7-Sensor nacheinander sowie zwei unterschiedliche Kategorien gleichzeitig auslösen; Symcon während eines verzögerten Alarmzyklus neu starten | Nur Aktionen mit passender Auslösekategorie und Alarmierungsart laufen; ein 24/7-Auslöser bleibt unabhängig vom aktuellen Scharfmodus der Kategorie 24/7 zugeordnet; neu hinzukommende Kategorien starten noch nicht ausgeführte passende Aktionen, ohne andere zu wiederholen; Zustand und Fristen bleiben nach dem Neustart erhalten | Offen | |
| H-37 | In einem großen Objektbaum Sensoren sowie Manipulations-/Störungseingänge über die jeweilige Mehrfachsuche suchen, mehrere Treffer auswählen, gemeinsam hinzufügen und eine neue Zeile vor dem Speichern bearbeiten | Die Suche erreicht Treffer unterhalb tiefer Kategorien, weist beim Erreichen einer Grenze darauf hin, schließt bereits in derselben Liste konfigurierte Variablen aus und erzeugt nachbearbeitbare Entwurfszeilen mit den gewählten gemeinsamen Einstellungen; Sensoren heißen `Variablenname (Parentname)`, der gewählte Auslöse-/Störwert ist im sofort geöffneten Zeileneditor vorhanden und das Sensor-Suchfenster schließt nach erfolgreicher Übernahme | Offen | |
| H-38 | Dieselbe positive Variablen-ID zweimal als Störungseingang anlegen und die Konfiguration anwenden | Es entsteht kein PHP-Fatal-Error; die Instanz wechselt kontrolliert in den Status für eine ungültige Sensor-/Störungskonfiguration und das Duplikat bleibt zur Korrektur im Formular sichtbar | Offen | |
| H-39 | Dieselbe positive Variablen-ID zunächst in zwei funktional identischen Sensorzeilen und anschließend in zwei Zeilen mit unterschiedlicher Verzögerungs- oder Bereichskonfiguration anlegen | Identische Zeilen führen ohne PHP-Fatal-Error kontrolliert zum Status für eine ungültige Sensor-/Störungskonfiguration; unterschiedlich konfigurierte Verwendungen derselben Variable bleiben zulässig und werden entsprechend ihrer jeweiligen Einstellungen ausgewertet | Offen | |
| H-40 | Mindestens sechs Alarmbereiche in einer schmalen mobilen Kachel und in IPSView anzeigen, mehrere Einzelbereiche als gemeinsames Schaltziel markieren und die Mehrfachauswahl wieder aufheben | Die Bereichsbuttons brechen ohne horizontales Scrollen auf mehrere Zeilen um; der Alarmbereichsrahmen umfasst jederzeit alle Buttonzeilen und die ein- oder ausgeblendete Mehrfachauswahl; die Schalter für Alarmierungsart und automatische Überbrückung bleiben rechts ausgerichtet und vergrößern die Optionszeilen nicht unnötig | Offen | |
| H-41 | Nacheinander einen ungültigen Alarmbereich, eine doppelte Sensor-/Störungskonfiguration, einen aktivierten Scharfschaltzeitplan ohne Wochentag und einen namentlich bezeichneten aktivierten Signalgeber mit dem Rücksetzverhalten „Keine Rücksetzung“ anwenden | Die Instanz bleibt kontrolliert inaktiv und meldet jeweils den passenden Status 201, 202, 203 beziehungsweise 204; insbesondere nennt Status 204 den Namen der fehlerhaften Alarmaktionszeile, die widersprüchlichen Einstellungen „Signalgeber“ und „Keine Rücksetzung“ sowie beide zulässigen Korrekturen „Boolean automatisch umkehren“ und „Eigene Rücksetzaktion verwenden“ | Offen | |
| H-42 | Einen String-Störungseingang zuerst mit exaktem Störwert und anschließend mit „Abweichung vom Normalwert“ konfigurieren; beide Regeln über ApplyChanges und Neustart prüfen | Bestehende exakte Regeln bleiben kompatibel; beim Normalwert ist nur der konfigurierte Wert fehlerfrei und jede andere gültige Ausprägung aktiviert die Störung | Offen | |
| H-43 | Eine Konfigurationssicherung im vollständig unscharfen Zustand über das Instanzformular auswählen, bestätigen und wiederherstellen; denselben Vorgang bei einem scharfen Bereich sowie mit beschädigter Datei versuchen | Die gültige Sicherung wird nach doppelter Bestätigung angewandt; unzulässige Versuche ändern keine Einstellung und melden den konkreten Grund | Offen | |
| H-44 | Die Gesamtanlage scharfschalten und anschließend aus einer vertrauenswürdigen Testautomation mit `OHA_DisarmWithSource()` sowie mit leerer, überlanger und steuerzeichenhaltiger Quelle unscharfschalten | Nur die gültige Quellenangabe schaltet unscharf und erscheint im Ereignisprotokoll; ungültige Quellen verändern den Scharfzustand nicht | Offen | |
| H-45 | Einen Manipulations- oder Störungseingang einem benannten Zusatzbereich zuordnen, über die Mehrfachsuche einen weiteren Entwurf ergänzen und beide Zeilen vor sowie nach „Änderungen übernehmen“ prüfen | Die Übersicht zeigt jeweils den frei vergebenen Bereichsnamen statt der technischen ID; Bearbeitung, Speicherung und Laufzeitauswertung verwenden weiterhin die unveränderte eindeutige Bereichs-ID | Offen | |
| H-46 | Einen Probealarm mit Bereichs- und Sensornamen auslösen, deren daraus gebildeter Push-Titel beziehungsweise Meldungstext die Symcon-Grenzen überschreitet und mehrbyteige UTF-8-Zeichen enthält | Die native Push-Nachricht wird versendet; Titel und Text bleiben gültiges UTF-8, enden bei tatsächlicher Kürzung mit einem Auslassungszeichen und überschreiten 32 beziehungsweise 256 Bytes nicht | Offen | |
| H-47 | Einen Zusatzbereich anlegen, dessen Ausgangsverzögerung starten, bis Scharf weiterlaufen lassen, eine Eingangsverzögerung und einen Alarm auslösen, unscharf schalten und dieselben Übergänge nach einem Symcon-Neustart wiederholen | Die vier flachen Bereichsvariablen zeigen jeweils Modus, Zustand, sekündlich verbleibende Verzögerung und Verzögerungsquelle des Zusatzbereichs; die vier Gesamtanlagenvariablen bleiben dem Hauptbereich zugeordnet; ApplyChanges und Neustart erzeugen keine Duplikate und verlieren keinen Zustand | Offen | |
| H-48 | Nur den sichtbaren Namen eines Zusatzbereichs ändern und übernehmen; danach seine technische Bereichs-ID ändern | Beim Ändern des sichtbaren Namens bleiben alle vier Objekt-IDs und technischen Idents erhalten und nur die Namen ändern sich. Eine neue technische ID erzeugt ein neues Variablenpaket; das Paket der alten ID bleibt neutralisiert zur bestätigten Bereinigung erhalten | Offen | |
| H-49 | Einen Zusatzbereich löschen und übernehmen, die angebotene Bereinigung zunächst abbrechen, dieselbe technische ID erneut anlegen, anschließend wieder entfernen und die Bereinigung bestätigen | Ohne Bestätigung wird keine Variable gelöscht; die vier alten Variablen sind neutral. Eine vor der Bereinigung erneut angelegte identische ID verwendet dieselben Objekte weiter. Erst die bestätigte Bereinigung entfernt genau die vier verwaisten Variablen und warnt vor Verweisen aus Skripten, Links oder Visualisierungen | Offen | |
| H-50 | In mindestens zwei zusätzlichen Alarmbereichen gleichzeitig unterschiedlich lange Ein- beziehungsweise Ausgangsverzögerungen starten und die Kachel sowie IPSView auf Desktop und Mobilgerät beobachten; anschließend die Betriebssystemoption für reduzierte Bewegung aktivieren und den Ablauf wiederholen | Jeder zusätzliche Bereichsbutton zeigt unabhängig eine kleine, aus der Backend-Frist lokal fortgeschriebene Restzeit. Ein gelbes Lauflicht kennzeichnet den laufenden Countdown, ohne Auswahlrahmen, Schaltziel oder Alarmstatus zu verdecken. Nach Ablauf verschwindet es. Bei reduzierter Bewegung bleibt stattdessen ein statischer Warnrahmen; der große Countdown des angezeigten Bereichs und sämtliche Backend-Zustandswechsel bleiben unverändert | Offen | |
| H-51 | Systemprotokoll, Systemdiagnose, Auswahl der Alarmierungsart und automatische Überbrückung nacheinander im Instanzformular ausblenden und Kachel sowie IPSView jeweils neu öffnen; anschließend mit einem ausgelösten, für automatische Überbrückung freigegebenen Sensor scharfschalten | Die gewählten Elemente sind in beiden Ausgaben nicht sichtbar. Ohne sichtbare Alarmierungsart gilt ausschließlich die konfigurierte Bereichsvorgabe; ohne sichtbare Überbrückung bleibt der ausgelöste Sensor blockierend und keine zuvor gewählte Bedienoption wirkt unsichtbar weiter | Offen | |
| H-52 | Auf zwei getrennten Bediengeräten unterschiedliche Zusatzbereiche derselben Instanz auswählen, beide Ansichten schließen und erneut öffnen; anschließend einen der Bereiche deaktivieren oder löschen | Jedes Gerät öffnet wieder seinen eigenen zuletzt gewählten Bereich, ohne die Auswahl des anderen Geräts zu verändern. Ein nicht mehr verfügbarer gespeicherter Bereich führt sicher zu `main`; Codes und übriger Bedienzustand werden nicht im Browser gespeichert | Offen | |
| H-53 | Einen Zusatzbereich über einen benutzerbezogenen Code und über `OHA_DisarmWithSource()` unscharfschalten; danach abwechselnd `main` und den Zusatzbereich anzeigen | Die Ereignisquelle ist jeweils ausdrücklich als **Quelle** gekennzeichnet. `main` zeigt die neuesten Ereignisse der gesamten Anlage, der Zusatzbereich nur seine eigenen Einträge. Ein erneuter Unscharfschaltaufruf im bereits vollständig unscharfen Zustand erzeugt keinen zusätzlichen Eintrag | Offen | |

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
2. alle für den aktuellen Funktionsumfang einschlägigen Pflichtfälle A-01 bis
   H-50 auf mindestens einer repräsentativen Symcon-9.x-Installation bestanden
   oder durch die dokumentierte wiederholte Praxisabnahme unter Symcon 9.0 und
   9.1 bestätigt sind,
3. Desktop- und Mobilbedienung der HTML-SDK-Kachel geprüft wurden,
4. alle IPSView-Fälle bestanden sind,
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

## Zentrale Mausradbehandlung am 22.10.2026

Die Ausgabe verwendet die kanonische Interaktionsressource aus `VisualizationAssetHelper`, die `IPSViewHTMLPageHelper` automatisch vor dem Modulskript einbindet. Generische Scroll-/Delta-/Pointer-/Frame-Mechanik wird nicht mehr modulweise neu implementiert. Die verbindliche Prüfregel einschließlich getrenntem Webclient-/App-Nachweis steht in `SymconDevelopment/standards/TESTING.md`.

Einordnung des Praxisbefunds: Die gemeinsame Interaktionsunterstützung wurde in
den OpenHomeAlarm-Ansichten mit überlaufenden Inhalten, verschachtelten Frames
und unterschiedlichen Ausgabegrößen geprüft. Webclient und native IPSView-App
wurden dabei getrennt betrachtet. Die wiederholte Praxisabnahme durch Maintainer
und Beta-Tester bestätigt die aktuelle Bedienung unter Symcon 9.0 und 9.1;
synthetische Regressionen sichern ergänzend umadressierte Ziele mit `(0, 0)`,
korrigierte Zeigerposition, Delta-Modi, Ctrl-/Meta-Ausnahmen und skalierte
direkte Frame-Weitergabe ab.

Die Einbindung erfolgt ausschließlich über den gemeinsamen Seiten-Helper; Alarmzustände, Codeschutz und Schaltaktionen sind unverändert. Es wurden keine Live-Alarmaktionen für diese Prüfung ausgelöst.

## Snapshot-Migration FullyDisarmedAction am 22.10.2026

Ursache des gemeldeten ApplyChanges-Abbruchs: Ein älterer `v2:`-Snapshot enthielt `FullyDisarmedAction` noch nicht. Die aktuelle Konfiguration enthält dafür den registrierten Standardstring `false`. Der unveränderte exakte Vergleich erkannte deshalb einen Unterschied. Die Migration ergänzt nur diesen historischen Standard vor `DisarmCode`, erhält alle bestehenden Snapshot-Werte und übernimmt keine aktuellen Konfigurationswerte in die gespeicherte Sicherheits-Baseline. Bereits vorhandene Aktionseinträge, unbekannte Snapshot-Versionen und ungültige Daten werden nicht ersetzt. Der Guard und die Sperre noch nicht übernommener Formularänderungen bleiben unverändert.

Regressionen prüfen die Migration, ihre Idempotenz, unveränderte Konfiguration und Attribute, Ablehnung tatsächlich geänderter Sensoren und Post-Disarm-Aktionen sowie fehlende Alarm-/Unscharfschaltaktionen. Die Prüfung mit aktiver Anlage im Testdouble ist ausschließlich ein negativer Sicherheitsnachweis und keine Freigabe für Updates im scharfen Zustand. Updates dürfen nur bei vollständig unscharfer Anlage erfolgen, auch über den Module Store, bei Sammelupdates und Kanalwechseln. Es wird weder eine zuverlässige Store-Updatesperre noch eine automatische Unscharfschaltung behauptet.

Die Snapshot-Migration wurde im Rahmen der wiederholten Praxisabnahme bei
vollständig unscharfer Anlage unter Symcon 9.0 und 9.1 bestätigt. Nach den
Updates blieben Instanzstatus, Konfiguration, IPSView-Ausgabe und Alarmwege
funktionsfähig. Die Sicherheitsregel bleibt unverändert: Aktualisierungen
dürfen ausschließlich bei vollständig unscharfer Anlage erfolgen.
