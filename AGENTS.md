# Projektregeln für OpenHomeAlarm

Lies zuerst `../SymconDevelopment/AGENTS.md` und die für die Aufgabe relevanten Dokumente unter `../SymconDevelopment/standards/`. Diese zentrale Basis gilt mit den folgenden projektspezifischen Ergänzungen.

## Projektkontext

- OpenHomeAlarm ist eine Symcon-9.x-Alarmsteuerung mit sicherheitsrelevanten Zustands-, Schalt-, Code-, Alarm- und Wiederanlauffunktionen. Änderungen an diesen Grenzen benötigen positive, negative und Wiederherstellungstests.
- Zielplattform ist PHP 8.5 unter IP-Symcon 9.x; `library.json` erklärt die Mindestkompatibilität mit Symcon 9.0.
- Das Repository enthält genau ein Modul unter `OpenHomeAlarm/`. Gemeinsame fachliche Bausteine liegen unter `libs/`, Tests unter `tests/`.
- Dateien unter `libs/helper` sind synchronisierte Kopien aus `Symcon_ModuleHelper`. Ändere sie nicht lokal und lege keine Helper-Duplikate an; Änderungen erfolgen in der zentralen Quelle und kommen über den konfigurierten Helper-Sync.
- `.style` und `tests/stubs` sind offizielle Symcon-Git-Submodule. Ändere deren Inhalte oder Zeiger nur auf ausdrücklichen Auftrag.

## Vor jeder Änderung

1. Lies `README.md`, `OpenHomeAlarm/README.md`, `library.json`, `OpenHomeAlarm/module.json` und die betroffenen Quellen und Tests.
2. Lies bei Sicherheits- oder Freigabefragen zusätzlich `SECURITY.md`, `docs/RELEASE_SCOPE.md`, `docs/SYMCON_9_ACCEPTANCE.md` und `docs/RELEASE_PROCESS.md`.
3. Prüfe öffentliche `OHA_*`-Funktionen, `ApiVersion`, Properties, Identifiers, Statuswerte, persistierte Konfiguration und Wiederanlaufverhalten auf Kompatibilität.
4. Prüfe bei Visualisierungsänderungen die native HTML-SDK-Kachel und die IPSView-Ausgabe gemeinsam; beide verwenden denselben Bedienzustand und gemeinsame Assets.
5. Bewahre fremde Änderungen und löse keine Releases, Tags, Helper-Syncs oder anderen repositoryübergreifenden Aktionen ohne ausdrücklichen Auftrag aus.

## Umsetzung und Dokumentation

- Erhalte bestehende Konfigurationen, Alarmbereiche, Sensorzuordnungen, Codeschutz, Alarmgedächtnis, Historie und öffentliche Steuerungs-APIs, sofern keine ausdrücklich geplante Migration vorliegt.
- Konfiguration, `ApplyChanges()` und Wiederherstellung bleiben idempotent und neustartsicher.
- Neue oder geänderte Sicherheitslogik behandelt ungültige Eingaben, verweigerte Aktionen, Teilfehler und Wiederanlauf ausdrücklich; ein stiller Fallback darf Schutzregeln nicht umgehen.
- Sichtbares Verhalten wird im selben Arbeitspaket in `OpenHomeAlarm/README.md` und bei Bedarf in der Root-README, `CHANGELOG.md`, `form.json` und `locale.json` dokumentiert.
- Änderungen an Release-Umfang oder Praxisabnahme aktualisieren vor der Implementierung die entsprechenden Dokumente und Abnahmefälle.

## Qualität und CI

- Der lokale Gesamteinstieg ist `php tests/run.php`. Er umfasst Helper-Integrität, `validate_structure`, Fachlogik, öffentliche Verträge, Visualisierung, Laufzeitkompatibilität, Metadaten und das reproduzierbare Release-Artefakt.
- Verbindliche CI-Checks sind `tests` und `style` aus `Symcon_ModuleCI` v1.0.0. Sie müssen für den exakten Commit erfolgreich sein.
- Die maßgebliche Plattformprüfung läuft mit PHP 8.5. Ein lokaler Lauf unter einer älteren PHP-Version ist nur ergänzende Evidenz.
- Stub- und Strukturtests ersetzen nicht die reale Symcon-Abnahme. Änderungen an Schaltung, Alarmwegen, Visualisierung, Neustart, Update oder Wiederherstellung benötigen zusätzlich die betroffenen Fälle aus `docs/SYMCON_9_ACCEPTANCE.md`.
- Erzeugte ZIP-Dateien unter `dist/` dienen nur der Reproduzierbarkeitsprüfung und werden nicht als zusätzliches Release-Artefakt eingecheckt oder angehängt.

## Branch- und Release-Modell

- `dev` ist der dauerhafte Entwicklungs- und Integrationsbranch. `main` enthält ausschließlich kontrolliert freigegebene Produktstände.
- Laufende Änderungen und Helper-Sync-PRs zielen auf `dev`; `dev` wird nach einem Merge nicht gelöscht.
- Der Metadaten-Bot aktualisiert auf `dev` Version, Build und Datum in `library.json`. Pflege diese generierten Werte nicht manuell, außer die konkrete Metadaten- oder Release-Aufgabe verlangt es.
- Eine Übernahme von `dev` nach `main` ist eine Produktfreigabe und erfolgt nur nach ausdrücklichem Auftrag sowie vollständiger Erfüllung aller dokumentierten Release-Gates.
- Tags und veröffentlichte Releases werden nie verschoben oder überschrieben; Korrekturen erhalten eine neue Version.
