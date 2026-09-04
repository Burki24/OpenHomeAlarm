# Changelog

Alle wesentlichen Änderungen an OpenHomeAlarm werden in diesem Dokument
festgehalten. Die Library-Version folgt dem Format `Hauptversion.Nebenstand`
aus `library.json`; der dazugehörige Git-Tag ergänzt für SemVer eine Patchstelle,
beispielsweise `v1.109.0`.

## Unreleased

### Added

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

### Security

- Konfigurationssicherungen sind ausdrücklich als vertraulich gekennzeichnet,
  weil sie Unscharfschaltcodes und IPSView-Zugriffstoken enthalten können.
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
