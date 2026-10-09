# Changelog

- Die erweiterten IPSView-Farbeinstellungen blenden die ausschließlich für native Kalendersteuerungen vorgesehene Farbfamilie in OpenHomeAlarm aus.
- Der zentrale `IPSViewHTMLPageHelper` stellt nun auch in OpenHomeAlarm den Button zur manuellen IPSView-HTML-Neugenerierung bereit und verarbeitet gemeinsame IPSView-Formularaktionen.

Alle wesentlichen Änderungen an OpenHomeAlarm werden in diesem Dokument
festgehalten. Die Library-Version folgt dem Format `Hauptversion.Nebenstand`
aus `library.json`; der dazugehörige Git-Tag ergänzt für SemVer eine Patchstelle,
beispielsweise `v1.109.0`.

## Unreleased

- Beim gemeinsamen Scharfschalten einer Teilmenge folgt die Detailanzeige nun
  einem beteiligten Bereich, sodass Ausgangsverzögerung und Countdown sichtbar
  bleiben. Auch eine einzelne Markierung wird zuverlässig an den tatsächlich
  gewählten statt an den zuvor angezeigten Bereich gesendet.

- Die Mehrbereichsauswahl von Kachel und IPSView steht jetzt direkt unter der
  normalen Bereichsauswahl im gemeinsamen Rahmen **Alarmbereich**.

- Kachel und IPSView zeigen bei einem bereits geschalteten Bereich wieder die
  tatsächliche Sensorbereitschaft statt pauschal „Nicht bereit“. Die nur zum
  Scharfschalten bestimmte Mehrbereichsauswahl wird währenddessen ausgeblendet.

- Kachel und IPSView brechen den Aufbau des Scharfmodus-Bereichs nach der
  Einführung der Mehrbereichsauswahl nicht mehr wegen eines ungültigen
  JavaScript-Hilfsfunktionsaufrufs ab.

- Mehrere frei gewählte Alarmbereiche können über die neue atomare API und die
  gemeinsame Kachel-/IPSView-Auswahl zusammen scharfgeschaltet werden. Ein
  Blocker oder eine ungültige Bereichs-ID verhindert den gesamten Auftrag;
  `main` bleibt die bewusste Auswahl der Gesamtanlage.

- Kachel und IPSView bieten beim Scharfschalten die einmalige automatische
  Überbrückung nur für Sensoren an, die dafür ausdrücklich freigegeben sind.
  Nicht verfügbare und 24/7-Sensoren sowie Störungen bleiben unverändert
  blockierend.

- Ein Sensor kann mit `OHA_GrantPassagePartitions()` atomar in mehreren
  gleichzeitig scharfen Bereichen für denselben Durchgang freigegeben werden.
  Die Anleitung beschreibt zusätzlich die eingeschränkte Verwendung eines
  Nuki-Verriegelungszustands, wenn kein separater Türkontakt vorhanden ist.

- Neben der Aktion für jeden positiven Countdown-Schritt kann eine getrennte
  Abschlussaktion konfiguriert werden. Sie läuft nach regulärem Ende oder
  kontrolliertem Abbruch eines begonnenen Countdowns genau einmal und eignet
  sich zum ausdrücklichen Ausschalten von Ton, Licht oder Statusausgaben.

- Normale Sensoren können einzeln für eine vertrauenswürdige, zeitlich begrenzte
  Durchgangsfreigabe zugelassen werden. Die neuen PHP-Funktionen geben genau
  einen Öffnen-/Schließen-Zyklus frei, lassen den Bereich scharf und beenden
  eine passende Eingangsverzögerung. Andere und 24/7-Sensoren bleiben aktiv;
  eine bei Fristablauf noch offene Freigabe löst Alarm aus. Laufzeit und
  Restfrist sind neustartsicher im Bedienzustand der API-Version 3 enthalten.

- Gleichnamige Sensoren und Störungseingänge bleiben in Blockierungs-, Störungs-
  und Überbrückungslisten intern anhand ihrer Variablen-IDs getrennt. Sichtbar
  bleiben konfigurierte oder aktuelle Symcon-Namen ohne ID-Zusatz.

- Sensoren können weiterhin auf einen einzelnen Auslösewert oder neu auf jede
  Abweichung von einem festgelegten Normalwert reagieren. Dadurch lassen sich
  mehrwertige Zustandsvariablen etwa von Fenstergriffen, Türschlössern oder
  Wassermeldern ohne mehrfachen Sensoreintrag auswerten.

- Die Kachel- und IPSView-Oberfläche zeigen den Sicherheitsstatus zuerst. Die
  Bereichsauswahl erhält einen eigenen Rahmen; Scharfmodi-Überschrift,
  Alarmierungsart und Moduskarten stehen gemeinsam in einem zweiten Rahmen.
  Kacheln bemessen die Rasterzeilen nach Inhalt, damit sich die Rahmen nicht
  überlappen.

- 24/7-Sensoren lösen nun stets einen normalen Alarm aus, auch in still
  geschalteten Bereichen. Scharfmodus- und Verzögerungsfelder werden bei 24/7
  deaktiviert; vorhandene Werte dafür bleiben ohne Wirkung.

- Symcon-Push und Pushover können unabhängig voneinander nur bei normalen,
  nur bei stillen oder bei beiden Alarmierungsarten benachrichtigen. Bestehende
  Konfigurationen bleiben bei beiden Arten aktiv.

- Die Eskalationsmaske benennt die Alarmierungsarten eindeutig als **Nur normal**,
  **Nur still** und **Normal und still**. Bei Signalgebern wird die redundante
  Auswahl ausgeblendet und ihre Beschränkung auf normale Alarme erklärt;
  bestehende Einstellungen bleiben wirksam.

- Die Alarmierungsart wird in Kachel und IPSView über einen gestalteten Switch
  statt über ein im eingebetteten Browser unzuverlässiges Dropdown gewählt.

- Stiller Alarm je Bereich: konfigurierbare Vorgabe und einmalige Auswahl beim
  Scharfschalten per API, Kachel oder IPSView. Eskalationsaktionen können für
  normale, stille oder beide Alarmierungsarten ausgeführt werden; Signalgeber
  bleiben bei ausschließlich stillen Alarmbereichen aus.

### Fixed

- Die konfigurierte Entstörungsaktion wird bei mehreren gleichzeitigen
  Störungen erst ausgeführt, nachdem die letzte Störung behoben wurde. Einzelne
  Behebungen bleiben im Ereignisprotokoll sichtbar, lösen aber keine vorzeitige
  Entwarnung mehr aus.
- Das Ziel nativer Symcon-Push-Nachrichten wird als Kachel-Visualisierung direkt
  aus dem Instanzbaum gewählt, statt eine fehleranfällige numerische ID zu
  verlangen.
- Die Ausnahme für aktive Ausgangswegsensoren gilt nur noch beim Scharfmodus
  **Abwesend**. Bei **Zuhause** und **Nacht** werden dieselben Sensoren sofort als
  Blocker angezeigt und verhindern die Scharfschaltung.
- Das Konfigurationsformular bleibt bei einem fehlenden oder deaktivierten
  Hauptbereich `main` erreichbar, damit die ungültige Bereichskonfiguration
  direkt korrigiert werden kann.
- Drei gleichzeitig sichtbare Detailbereiche bleiben in Kachel und IPSView bis
  zum direkten Wechsel auf die einspaltige Mobilansicht gleich breit.
- Auf schmalen IPSView-Flächen scrollt die vollständige Seite statt einzelner
  Detaillisten, damit insbesondere die Systemdiagnose jederzeit wieder bis zu
  ihrem Kopfbereich zurückgescrollt werden kann.
- Verwaiste Sensor- oder Störungszuordnungen zu einem fehlenden bzw. deaktivierten
  Alarmbereich führen beim Anwenden oder Neustart nicht mehr zu einem PHP-Fatal.
  Die Instanz meldet stattdessen einen verständlichen Konfigurationsstatus, und
  betroffene Einträge bleiben im Editor sichtbar und korrigierbar. Erkennt ein
  späteres Modulupdate gleichzeitig eine solche Teilrücksetzung, stellt es die
  zuletzt erfolgreich angewendeten Sicherheitsdaten einschließlich Alarmbereichen
  und Eskalationsstufen wieder her; neu hinzugekommene Einstellungen bleiben erhalten.
- Der Zustand ausgeführter Signalgeber wird über die sichtbare Boolean-Variable
  `SignalGeneratorActive` geführt. Alarmierte Areas erhalten dadurch zuverlässig
  die separate Aktion **Signalgeber stoppen**, ohne andere Alarmaktionen zurückzusetzen.
- Das von der Symcon-Konsole gespeicherte flache Format der Eskalationsaktionen
  übernimmt `ResetMode`, `ResetAction` und `SignalGenerator` nun vollständig;
  zuvor ging insbesondere die Signalgeber-Markierung beim Einlesen verloren.
- Ausgangsweg-Bewegungsmelder dürfen am Ende der Ausgangsverzögerung noch ihren
  nachlaufenden Auslösewert melden, ohne die Scharfschaltung abzubrechen; Kontakte,
  normale, fehlende oder unlesbare Sensoren und blockierende Störungen bleiben strikt.

### Added

- Direkter Pushover-Versand ohne zusätzliches Symcon-Modul mit optionalem
  Empfängergerät, Sound, Priorität und verzögerter Alarm-Eskalation. Notfall-
  Wiederholungen werden beim Ende des Alarmausgangs über den gespeicherten
  Pushover-Beleg beendet.
- Eskalationsaktionen unterstützen neben der automatischen Boolean-Umkehrung
  eine explizite native Rücksetzaktion für Rollläden, Dimmer, Szenen und andere
  mehrwertige Ziele.
- Sensoren können mehreren Alarmbereichen gleichzeitig zugeordnet werden;
  temporäre Überbrückungen bleiben dabei je Sensor und Bereich getrennt.
- Unabhängig bedienbare Alarmbereiche mit bereichsbezogenen Zuständen,
  Alarmgedächtnissen und Ausgängen.
- Benutzerbezogene Unscharfschaltcodes mit gemeinsamer, wiederanlaufsicherer
  Fehlversuchs- und Sperrzeitbehandlung.
- Wöchentliche automatische Scharfschaltung über die regulären
  Bereitschaftsprüfungen.
- Optionale Countdown-Aktion für Ein- und Ausgangsverzögerungen sowie
  zeitgesteuerte Alarm-Eskalationsstufen.
- JSON- und CSV-Export für Ereignishistorie und Systemdiagnose.
- Gemeinsame Diagnoseansicht für HTML-SDK-Kachel und IPSView mit Sensor-,
  Störungs- und Aktualitätsinformationen.
- Versionierter Export und validierte Wiederherstellung der vollständigen
  Modulkonfiguration.

### Changed

- Kachel und IPSView stellen Alarmbereiche, Diagnose und Exportfunktionen über
  denselben Bedienzustand bereit.
- Der zentrale `IPSViewStyleHelper` wurde bis Version 1.6.4 aktualisiert.
- Die IPSView-Konfiguration verwendet nun dieselbe gemeinsame Bearbeitungsmaske
  wie OpenCalendar, einschließlich optionaler gruppierter Überschreibungen für
  native IPSView-Farben.
- Das HTML-Dokument von Kachel und IPSView kennzeichnet die aktive
  Symcon-Sprache nun auch im standardkonformen `lang`-Attribut.

### Fixed

- Die native Auswahl einer eigenen Eskalations-Rücksetzaktion wird nur noch im
  entsprechenden Rücksetzmodus erzeugt und blockiert Boolean-Aktionen nicht
  mehr mit „Keine Aktion ausgewählt“.
- Bereits konfigurierte optionale Aktionen behalten ihren Wert in der
  dynamischen Konfigurationsmaske und blockieren dadurch keine unabhängigen
  Änderungen mehr mit „Keine Aktion ausgewählt“.

### Security

- Konfigurationssicherungen sind ausdrücklich als vertraulich gekennzeichnet,
  weil sie Unscharfschaltcodes, Pushover-Zugangsdaten und IPSView-Zugriffstoken
  enthalten können.
- Wiederherstellungen sind nur bei vollständig unscharfer Anlage zulässig und
  weisen fremde, unbekannte oder typwidrige Sicherungsdaten ab.

### Verified

- Die automatisierten Tests und die praktische Einzelprüfung der neuen
  Funktionen wurden während der Entwicklung bestanden.
- Export, Wiederherstellung, Ablehnung einer fremden Modul-ID und unveränderte
  Rückkehr zur Ausgangskonfiguration wurden auf der Symcon-Testinstanz geprüft.
- Die vollständige Release-Abnahme des exakten Kandidaten-Commits steht noch aus.

## 1.122 – 2026-08-24

### Changed

- Der Alarmkern wurde in klar getrennte, unabhängig testbare Komponenten für
  Zustandsübergänge, wiederanlaufsichere Timer, Sensorüberwachung,
  Störungsauswertung und Aktionsausführung aufgeteilt.
- Bedien-API und Visualisierungskommandos werden nun über eigene Adapter
  aufbereitet und validiert, während die öffentliche `OHA_*`-API vollständig
  kompatibel bleibt.
- Der zentrale `IPSViewStyleHelper` wurde auf Version 1.4.2 aktualisiert.

### Verified

- Alle extrahierten Komponenten, die vollständige Repository-Testsuite sowie
  PHP-, JSON- und Release-Reproduzierbarkeitsprüfungen sind bestanden.
- Auf dem Symcon-Testsystem wurden `ApplyChanges()`, Control API, IPSView sowie
  ein vollständiger Scharf-/Unscharf-Zyklus erfolgreich geprüft.
- Alle 33 öffentlichen Modulmethoden des vorherigen Releases bleiben erhalten.

## 1.109 – 2026-08-24

### Added

- Scharfmodi Zuhause, Abwesend und Nacht mit wiederanlaufsicheren Ein- und
  Ausgangsverzögerungen.
- Modusabhängige, verzögerte, 24/7- und Ausgangswegsensoren für Boolean-,
  Integer-, Float- und Stringvariablen.
- Temporäre Sensor-Bypässe, technische Störungen und Manipulationskontakte.
- Alarmaktionen, Alarmdauer, Alarmgedächtnis und persistente Ereignishistorie.
- Optionaler Unscharfschaltcode mit Fehlversuchszähler und Sperrzeit.
- Versionierte öffentliche `OHA_*`-API, responsive HTML-SDK-Kachel und
  token-geschützte IPSView-WebContent-Oberfläche.
- Deutsche und englische Modultexte.

### Fixed

- Fehlende oder gelöschte Sensor- und Störungsvariablen werden sichtbar und
  sicherheitsgerichtet behandelt, ohne ungültige Symcon-Metadatenzugriffe.
- Zustände, Fristen, Sperrzeit und Alarmausgang werden nach `ApplyChanges()`
  und einem Symcon-Neustart korrekt wiederhergestellt.

### Verified

- Alle automatisierten Repository-Prüfungen sind lokal bestanden.
- Die reale Symcon-9.1-Abnahme ist mit 49 von 49 Pflichtfällen bestanden.
- Desktop- und Mobilbedienung, IPSView, Update von Baseline 1.102 sowie
  Sicherungswiederherstellung wurden praktisch geprüft.

### Security

- OpenHomeAlarm ist eine Automationslösung und keine zertifizierte Einbruch-,
  Brand- oder Gefahrenmeldeanlage. Die verbindlichen Einsatzgrenzen stehen in
  [SECURITY.md](SECURITY.md).

### License

- OpenHomeAlarm steht unter der PolyForm Noncommercial License 1.0.0 mit dem
  Required Notice `Copyright 2026 Burkhard Kneiseler. OpenHomeAlarm.`
