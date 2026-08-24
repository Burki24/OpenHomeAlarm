# Changelog

Alle wesentlichen Änderungen an OpenHomeAlarm werden in diesem Dokument
festgehalten. Die Library-Version folgt dem Format `Hauptversion.Nebenstand`
aus `library.json`; der dazugehörige Git-Tag ergänzt für SemVer eine Patchstelle,
beispielsweise `v1.109.0`.

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
