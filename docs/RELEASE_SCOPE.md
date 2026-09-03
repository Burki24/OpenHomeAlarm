# Release-Zielbild

Dieses Dokument legt den verbindlichen Umfang und die Freigabekriterien für
produktive Releases von OpenHomeAlarm fest. Die endgültige
Versionsnummer und der Git-Tag werden erst im Release-Engineering vergeben.

## Produktziel

OpenHomeAlarm stellt eine hersteller- und protokollunabhängige Alarm- und
Sicherheitslogik für Symcon 9.x bereit. Bestehende Symcon-Variablen bilden die
Sensor-, Auslöser- und Störungsebene. Das Modul übernimmt Zustandsführung,
Bedienung, Wiederanlauf, Ereignisaufzeichnung und die Anbindung frei
konfigurierbarer Symcon-Aktionen.

Der erste produktive Release richtet sich an Anwender, die eine
Hausautomationslösung mit nachvollziehbarem Alarmzustand benötigen. Er ist
ausdrücklich keine zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage.

## Enthaltener Funktionsumfang

Der Release umfasst verbindlich:

1. die Scharfmodi Zuhause, Abwesend und Nacht,
2. die Zustände Unscharf, Ausgangsverzögerung, Scharf, Eingangsverzögerung und Alarm,
3. Boolean-, Integer-, Float- und Stringvariablen als typgerecht ausgewertete Sensoren,
4. modusabhängige Sensoren, Ausgangswegsensoren, eingangsverzögerte Sensoren und 24/7-Sensoren,
5. modusabhängige Bereitschaft, Blockierlisten und sicherheitsgerichtete Behandlung nicht verfügbarer Sensoren,
6. temporäre Sensorüberbrückungen für genau einen Scharfschaltzyklus,
7. Manipulations- und technische Störungseingänge mit konfigurierbarer Blockade oder Alarmauslösung,
8. konfigurierbare Symcon-Aktionen für Alarm, Alarmrücksetzung, Unscharfschaltung nach Alarm sowie Auftreten und Behebung einer Störung,
9. manuelle und zeitgesteuerte Rücksetzung des Alarmausgangs,
10. optionalen Unscharfschaltcode mit Fehlversuchszähler und wiederanlaufsicherer Sperrzeit,
11. Alarmgedächtnis und begrenzte persistente Sicherheits-Ereignishistorie,
12. Wiederherstellung relevanter Zustände und Fristen nach `ApplyChanges()` und Symcon-Neustart,
13. die dokumentierte öffentliche `OHA_*`-Bedien-API und den versionierten Bedienzustand,
14. die responsive native Symcon-HTML-SDK-Kachel,
15. die vollständig bedienbare IPSView-WebContent-Seite einschließlich token-geschütztem WebHook,
16. deutsche und englische Modultexte.
17. unabhängig adressierbare Alarmbereiche mit eigenen Laufzeit- und Alarmzuständen,
18. mehrere benannte Benutzer mit eigenen Unscharfschaltcodes,
19. wöchentliche Zeitpläne zur automatischen Scharfschaltung,
20. eine optionale Aktion für die Countdown-Ausgabe,
21. zeitgesteuerte Alarm-Eskalationsstufen,
22. JSON- und CSV-Export der Ereignishistorie,
23. Sensor- und Störungsdiagnose in API, Kachel und IPSView einschließlich Export,
24. versionierte Konfigurationssicherungen und deren validierte Wiederherstellung.

Eine Funktion gilt nur dann als Bestandteil des freigegebenen Releases, wenn
ihre zugehörigen automatisierten Prüfungen und Praxisfälle der
[Symcon-9.0-Abnahmematrix](SYMCON_9_ACCEPTANCE.md) bestanden sind.

## Muss-Kriterien

### Funktion

- Jeder angeforderte Scharfmodus berücksichtigt genau seine relevanten Sensoren sowie alle 24/7-Sensoren.
- Ein ausgelöster oder nicht auswertbarer relevanter Sensor verhindert eine unsichere Scharfschaltung.
- Ein Sofortalarm darf durch parallel laufende Verzögerungen nicht unterdrückt werden.
- Alarmaktionen werden pro vorgesehenem Zustandsübergang höchstens einmal ausgeführt.
- Unscharfschalten beendet laufende Verzögerungen und setzt einen aktiven Alarmausgang kontrolliert zurück.
- Bypässe sind nur unscharf zulässig, gelten nicht für 24/7-Sensoren und werden nach einem Scharfschaltzyklus gelöscht.
- Alarmgedächtnis, Ereignishistorie, Sperrzeit und laufende Fristen verhalten sich über `ApplyChanges()` und Neustarts wie dokumentiert.
- Fehlende Sensor- oder Störungsvariablen werden sichtbar und sicherheitsgerichtet behandelt.

### Bedienung

- Öffentliche API, HTML-SDK-Kachel und IPSView verwenden dieselbe zentrale Alarm- und Berechtigungslogik.
- Alle sichtbaren Befehle liefern einen eindeutigen Erfolg oder eine verständliche Ablehnung, ohne einen unzulässigen Zwischenzustand zu hinterlassen.
- Die HTML-SDK-Kachel bleibt auf Desktop- und kleinen mobilen Darstellungen vollständig bedienbar.
- IPSView akzeptiert ausschließlich den vorgesehenen WebHook, gültige Token, POST und freigegebene Aktionen.

### Sicherheit und Datenschutz

- Der Unscharfschaltcode erscheint weder in Statusvariablen noch in Bedienzustand, Debug-Ausgaben oder Ereignishistorie.
- Ein falscher oder während einer Sperre eingegebener Code verändert den Alarmzustand nicht.
- Deaktivierte IPSView-Ausgabe darf keinen weiter nutzbaren Bedien-WebHook hinterlassen.
- Das Modul baut selbst keine ausgehenden Netzwerkverbindungen auf.
- Sicherheitsgrenzen und die nicht zertifizierte Einsatzart bleiben prominent dokumentiert.

### Qualität und Betrieb

- Die Repository-Checks `tests` und `style` bestehen für exakt den freizugebenden Commit.
- Installation, Update, Neustart und Wiederherstellung sind auf einer realen Symcon-9.x-Testinstanz protokolliert.
- Eine Aktualisierung erhält kompatible Konfiguration, Betriebszustände und persistente Sicherheitsdaten.
- Für kritische Zustandsübergänge gibt es automatisierte Tests und einen zugeordneten Praxisfall.
- Es bestehen keine offenen kritischen oder hohen Befunde.

## Kompatibilitätsrahmen

### Symcon

- Ziel und unterstützte Hauptversion ist **Symcon 9.x**.
- Die technische Mindestversion in `library.json` bleibt **9.0**.
- Neuere Symcon-Hauptversionen sind ohne eigene Abnahme nicht automatisch Bestandteil dieses Releaseversprechens.
- Die konkret geprüfte Symcon-Version einschließlich Build wird im Abnahmeprotokoll festgehalten.

### Hostsystem

Der Modulcode enthält keine beabsichtigte Bindung an ein bestimmtes
Hostbetriebssystem oder eine Prozessorarchitektur. Praktisch verifiziert ist
jedoch nur die im Abnahmeprotokoll benannte Kombination aus Symcon-Version,
Hostbetriebssystem, Architektur und Client. Weitere Kombinationen gelten als
erwartet kompatibel, aber nicht als für diesen Release nachgewiesen.

### Sensoren und Aktoren

- Unterstützt werden vorhandene Symcon-Variablen mit lesbaren Boolean-, Integer-, Float- oder Stringwerten.
- Hersteller, Funkstandard und Geräteprotokoll sind für den Alarmkern unerheblich.
- Externe Wirkungen werden ausschließlich über ausdrücklich konfigurierte native Symcon-Aktionen ausgelöst.
- Geräteverfügbarkeit kann generisch nur erkannt werden, wenn die Variable fehlt oder nicht lesbar ist. Herstellerabhängige Aktualitäts- oder Kommunikationswerte müssen als eigene Störungseingänge konfiguriert werden.

### Öffentliche Schnittstellen

- Die dokumentierten `OHA_*`-Funktionen und `ApiVersion` 1 bilden den öffentlichen Vertrag des ersten Releases.
- Änderungen an Parametern, Rückgabewerten, maschinenlesbaren Namen oder gespeicherten Eigenschaften benötigen eine dokumentierte Migration oder eine neue API-Hauptversion.
- Bestehende Instanzkonfigurationen dürfen durch ein kompatibles Update nicht stillschweigend umgedeutet werden.

## Nichtziele des ersten Releases

Nicht Bestandteil sind:

- Zertifizierung oder Konformität nach EN 50131 oder anderen Alarmanlagen-Normen,
- garantierte Verfügbarkeit bei Ausfall von Symcon, Host, Stromversorgung, Netzwerk oder Geräten,
- direkte Hardware-, Funkprotokoll- oder Herstellerintegration,
- Rollen- oder Rechteverwaltung außerhalb der konfigurierten Unscharfschaltbenutzer,
- geofencing-basierte automatische Scharfschaltung,
- Leitstellenaufschaltung oder garantierte Alarmübertragung,
- manipulationssicheres oder revisionssicheres Audit-Logging,
- Cloud-Dienst, Fernzugriff oder eigener Benachrichtigungsdienst,
- automatische Erkennung veralteter Sensorwerte ohne ein geeignetes externes Kommunikationssignal,
- Unterstützung von Symcon-Versionen vor 9.0,
- verbindliche Unterstützung einer späteren Symcon-Hauptversion ohne erneute Abnahme.

Diese Nichtziele dürfen nach dem ersten Release separat geplant werden, ändern
aber nicht die Freigabekriterien des aktuellen Zielumfangs.

## Sicherheits- und Betriebsgrenzen

OpenHomeAlarm ist eine Automationslösung. Für normativ, behördlich,
versicherungsrechtlich oder lebenssicherheitsrelevant geforderten Schutz ist
zertifizierte Technik erforderlich. Rauch-, Wasser-, Panik- oder
Manipulationssensoren können als 24/7-Auslöser verwendet werden; daraus entsteht
keine zugesicherte Eignung für Personen- oder Sachschutz.

Der Betreiber ist verantwortlich für:

- Schutz der Symcon-Administration, Sicherungen und JSON-RPC-Zugänge,
- vertrauliche Aufbewahrung exportierter Modulkonfigurationen, da diese Codes
  und Zugriffstoken enthalten können,
- verschlüsselte Verbindung bei Zugriff außerhalb eines vertrauenswürdigen Netzes,
- zuverlässige Strom-, Netzwerk- und Geräteversorgung,
- regelmäßige Funktionsprüfungen aller Sensoren, Aktoren und Alarmwege,
- sichere Konfiguration der vertrauenswürdigen `OHA_Disarm()`-Automationsschnittstelle,
- Sicherung und kontrollierte Wiederherstellung der Symcon-Installation.

Weitere Einzelheiten stehen in der [Sicherheitsrichtlinie](../SECURITY.md).

## Release-Gates

Der aktuelle `dev`-Stand darf erst als Release Candidate behandelt werden,
wenn alle folgenden Gates erfüllt sind:

| Gate | Bedingung | Nachweis |
| --- | --- | --- |
| G1 – Umfang | Enthaltener Funktionsumfang und Nichtziele sind dokumentiert und unverändert oder bewusst neu beschlossen | Dieses Dokument |
| G2 – Automatisierung | Repository-Tests, PHP-Syntax, JSON-Format und CI-Style bestehen für denselben Commit | Lokales Protokoll und CI-Lauf |
| G3 – Symcon-Laufzeit | Alle Pflichtfälle der Abnahmematrix sind auf einer realen Symcon-9.x-Instanz bestanden | Ausgefüllte Abnahmematrix |
| G4 – Bedienung | HTML-SDK auf Desktop/Mobil und IPSView einschließlich negativer WebHook-Fälle sind bestanden | Abnahmefälle F-05 bis F-09 |
| G5 – Wiederanlauf | Neustart-, Update- und Wiederherstellungsfälle sind bestanden | Abnahmefälle G-01 bis G-08 |
| G6 – Befunde | Keine kritischen oder hohen Befunde offen; mittlere Befunde entschieden | Befundprotokoll |
| G7 – Release | Changelog, Version, Tag, Installations-/Updatehinweise und reproduzierbares Artefakt sind vorbereitet | Release-Unterlagen |

Ein fehlendes Gate darf nicht durch eine Annahme oder ausschließlich durch
Stub-Tests ersetzt werden.

## Änderungssteuerung

Während der Release-Vorbereitung sind Fehlerkorrekturen, Dokumentation,
Abnahmetests und zur Abnahme notwendige Diagnoseverbesserungen zulässig. Neue
Produktfunktionen werden nur aufgenommen, wenn Zielumfang und Abnahmematrix vor
der Implementierung ausdrücklich angepasst werden. Jede Änderung nach
bestandener Praxisabnahme erfordert mindestens die Wiederholung der betroffenen
Fälle und aller automatisierten Checks.
