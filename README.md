# OpenHomeAlarm

OpenHomeAlarm ist eine herstellerunabhängige Alarm- und Sicherheitszentrale für die Hausautomation auf Basis von Symcon.

Vorhandene Symcon-Variablen können unabhängig von Hersteller und Protokoll als Sensoren, 24/7-Auslöser oder technische Störungseingänge verwendet werden. Das Modul stellt unabhängige Alarmbereiche, wiederanlaufsichere Verzögerungen mit optionaler Countdown-Ausgabe, Sensorüberbrückungen, wöchentliche automatische Scharfschaltung, Alarm-Eskalationsstufen, benutzerbezogene Unscharfschaltcodes, Alarmgedächtnis, Ereignis- und Diagnoseexporte sowie eine versionierte Konfigurationssicherung bereit. Bedient wird es über die öffentliche API, eine responsive HTML-SDK-Kachel oder die IPSView-WebContent-Seite.

> [!WARNING]
> OpenHomeAlarm ist keine zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage nach EN-50131. Funktion und Verfügbarkeit hängen von Symcon, dem Hostsystem, dem Netzwerk, den eingebundenen Geräten und den konfigurierten Aktionen ab. Für normativ oder versicherungsrechtlich geforderte Schutzaufgaben ist geeignete zertifizierte Sicherheitstechnik erforderlich.

## Module

- **OpenHomeAlarm** – zentrale Alarm- und Sicherheitslogik ([Dokumentation](OpenHomeAlarm))

## Voraussetzungen

- Symcon ab Version 9.0

## Alarmbereiche – Kurzstart

Beispiel: Eine Garage soll unabhängig vom Hauptbereich geschaltet werden.

1. Unter **Alarmbereiche** einen aktiven Eintrag mit der ID `garage` und dem
   Namen `Garage` anlegen. Den vorhandenen Hauptbereich als einzigen
   **Standardbereich** markiert lassen.
2. **Änderungen übernehmen**.
3. Die gewünschten Sensoren bearbeiten und im Feld **Alarmbereich** den Eintrag
   **Garage** auswählen.
4. Zum Schalten folgende Befehle in eigenen Symcon-Skripten verwenden:

```php
// Nur die Garage im Abwesend-Modus scharfschalten
OHA_ArmPartition(12345, 'garage', 'away');

// Nur die Garage unscharf schalten
OHA_DisarmPartition(12345, 'garage');
```

`12345` durch die Objekt-ID der OpenHomeAlarm-Instanz ersetzen. Zulässige Modi
sind `home`, `away` und `night`. Andere Alarmbereiche werden durch diese Befehle
nicht verändert.

**Aktiv** bedeutet nur, dass ein Bereich verwendet werden kann; es schaltet ihn
nicht scharf. Kachel und IPSView bedienen derzeit den Standardbereich.

Die Bereichs-ID muss mit einem Kleinbuchstaben beginnen. Zulässig sind insgesamt
1 bis 32 Kleinbuchstaben, Ziffern, `_` oder `-`, beispielsweise `main`, `garage`
oder `bereich_1`. Der Anzeigename ist frei wählbar. Weitere Erläuterungen stehen
in der [vollständigen Anleitung](OpenHomeAlarm#alarmbereiche).

## Sicherheit

Hinweise zum Melden von Schwachstellen und zum Umgang mit dem Unscharfschaltcode stehen in [SECURITY.md](SECURITY.md).

## Entwicklung

Das Repository verwendet die zentralen Actions aus `Symcon_ModuleCI v1.0.0`.
Die einheitlichen Status-Checks heißen:

- `tests`
- `style`

Die offiziellen Symcon-Quellen werden als Git-Submodule eingebunden:

- `.style` → `symcon/StylePHP`
- `tests/stubs` → `symcon/SymconStubs`

Der lokale Test-Einstiegspunkt lautet:

```text
php tests/run.php
```

Der verbindliche Umfang und die Freigabekriterien stehen im
[Release-Zielbild](docs/RELEASE_SCOPE.md).

Installation und Aktualisierung sind in der
[Installationsanleitung](docs/INSTALLATION.md) beschrieben. Änderungen stehen
im [Changelog](CHANGELOG.md); die Freigabe wird nach dem
[Release-Prozess](docs/RELEASE_PROCESS.md) erstellt.

## Lizenz

Dieses Projekt steht unter der
[PolyForm Noncommercial License 1.0.0](LICENSE) mit dem Required Notice
`Copyright 2026 Burkhard Kneiseler. OpenHomeAlarm.` Kommerzielle Nutzung ist
damit nicht gestattet, sofern keine gesonderte Erlaubnis erteilt wurde.
