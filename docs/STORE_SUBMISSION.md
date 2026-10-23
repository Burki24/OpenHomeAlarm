# Symcon Module Store – Einreichungsdaten

Dieses Dokument bündelt die vor einer Stable-Einreichung zu kontrollierenden
Store-Angaben. Maßgeblich bleibt die aktuelle Vorlage im Symcon-Entwicklerkonto.

## Im Testsystem bestätigter Store-Stand

Die lokale Store-Schnittstelle einer Symcon-9.1-Testinstallation wurde am
23.10.2026 lesend geprüft.

| Feld | Bestätigter Wert |
| --- | --- |
| Bundle-ID | `de.burki24.openhomealarm` |
| Name | OpenHomeAlarm |
| Aktueller Kanal | Beta |
| Store-Release | 19305 |
| Sprache der sichtbaren Beschreibung | Deutsch |
| Kompatible Symcon-Hauptversion | 9.x |

Die vorhandene Beschreibung nennt Sensoren und Störungseingänge, mehrere
Alarmbereiche und Scharfmodi, Verzögerungen, Codeschutz, Überbrückungen,
automatische Scharfschaltung, Alarmeskalation, Ereignishistorie,
Systemdiagnose, Kachel, IPSView sowie Konfigurationssicherung und
Wiederherstellung. Sie deckt damit den aktuellen wesentlichen Funktionsumfang
ab.

Die im Beta-Eintrag sichtbaren Versionsinformationen enden dagegen mit dem
Stand vom 13.09.2026 und bilden die danach hinzugekommenen Funktionen und
Korrekturen nicht vollständig ab. Sie werden für Stable deshalb nicht
unverändert übernommen, sondern aus dem finalen Changelog neu erstellt.

## Checkliste für die Stable-Vorlage

- **Kanal:** Stable.
- **GIT-URL:** `https://github.com/Burki24/OpenHomeAlarm`.
- **GIT-Commit:** der unveränderte, nach `main` übernommene und dort durch
  `tests` und `style` bestätigte Release-Commit.
- **Name:** OpenHomeAlarm.
- **Deutsche Beschreibung:** vorhandene Beta-Beschreibung übernehmen und vor
  dem Einreichen noch einmal gegen den aktuellen Funktionsumfang prüfen.
- **Versionsinformation:** aus dem final zugeordneten Abschnitt der
  `CHANGELOG.md` zusammenfassen.
- **Dokumentationslink:** auf die Modul-Anleitung des finalen `main`-Stands
  verweisen.
- **Kategorie:** im Entwicklerkonto mindestens eine passende Kategorie
  auswählen und sichtbar kontrollieren.
- **Funktionale Änderungen:** nicht als rein nichtfunktionale Aktualisierung
  kennzeichnen.
- **Niedrigere Kanäle:** bewusst entscheiden, ob Beta und Testing nach
  erfolgreichem Stable-Review geschlossen werden sollen.

Die lokale Store-Schnittstelle liefert weder die hinterlegte Kategorie noch
den Dokumentationslink der Einreichung. Diese beiden Angaben können deshalb
nicht aus dem Testsystem bestätigt werden und werden unmittelbar vor dem
Einreichen im Entwicklerkonto geprüft.
