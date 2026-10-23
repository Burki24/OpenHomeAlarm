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

Die im Beta-Eintrag sichtbaren Versionsinformationen enden mit dem Stand vom
13.09.2026 und bilden die danach hinzugekommenen Funktionen und Korrekturen
nicht vollständig ab. Für die Stable-Einreichung ist deshalb nachfolgend eine
aktualisierte, direkt übernehmbare Fassung vorbereitet. Sie wird unmittelbar
vor dem Einreichen noch einmal mit dem final zugeordneten Changelog-Abschnitt
des Release-Commits abgeglichen.

## Vorbereitete Versionsinformation für Stable

> OpenHomeAlarm wurde seit dem bisherigen Beta-Stand umfassend erweitert und
> stabilisiert:
>
> - Unabhängige Alarmbereiche können einzeln, als frei gewählte Gruppe oder
>   über den Hauptbereich gemeinsam scharf- und unscharfgeschaltet werden.
> - Sensoren unterstützen mehrere Bereiche, frei wählbare Scharfmodi,
>   Auslösewerte oder Abweichungen vom Normalwert, einmalige Überbrückungen,
>   Ausgangswege sowie zeitlich begrenzte Durchgangsfreigaben.
> - Ein- und Ausgangsverzögerungen sind wiederanlaufsicher. Getrennte Aktionen
>   können einmalig beim Start, während jeder Countdown-Sekunde und am Ende
>   ausgeführt werden.
> - Alarmaktionen lassen sich nach normalem oder stillem Alarm sowie nach den
>   Kategorien Zuhause, Abwesend, Nacht und 24/7 auswählen. Signalgeber,
>   Rücksetzaktionen, Symcon-Push und direkter Pushover-Versand werden
>   unterstützt.
> - Benutzerbezogene Unscharfschaltcodes, protokollierte externe
>   Unscharfschaltquellen, eine optionale Aktion nach vollständigem
>   Unscharfschalten und wöchentliche automatische Scharfschaltungen ergänzen
>   die Steuerung.
> - Kachel und IPSView verwenden denselben Bedienzustand und bieten eine
>   responsive Bereichs-, Status-, Countdown-, Ereignis- und Diagnoseansicht.
>   Die Darstellung wurde insbesondere für mobile Ansichten, iOS-WebViews und
>   große IPSView-Flächen überarbeitet.
> - Das Instanzformular bietet eine Mehrfachsuche für Sensoren und
>   Störungseingänge, verständlichere Validierungsmeldungen sowie kompaktere
>   und besser lesbare Konfigurationslisten.
> - Ereignisprotokoll und Systemdiagnose können als JSON oder CSV exportiert
>   werden. Die vollständige Modulkonfiguration kann versioniert gesichert und
>   nach Validierung bei unscharfer Anlage wiederhergestellt werden.
> - Fehler bei verwaisten oder doppelten Zuordnungen, ungültigen
>   Alarmbereichen, Aktionskonfigurationen, Signalgebern, Push-Nachrichten,
>   Downloads und der Wiederherstellung älterer Sicherheits-Snapshots werden
>   jetzt kontrolliert und mit konkreten Korrekturhinweisen behandelt.
> - Die Anleitung enthält alle öffentlichen `OHA_*`-Funktionen,
>   Konfigurationsschritte, Sicherheitsgrenzen, Screenshots und zusätzliche
>   Beispielskripte, unter anderem für Kamera-Voralarme.
>
> Bestehende Konfigurationen und die öffentliche Bedien-API bleiben
> kompatibel. Vor einem Modulupdate müssen alle Alarmbereiche vollständig
> unscharf sein. OpenHomeAlarm ist eine Automationslösung und keine
> zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage.

## Checkliste für die Stable-Vorlage

- **Kanal:** Stable.
- **GIT-URL:** `https://github.com/Burki24/OpenHomeAlarm`.
- **GIT-Commit:** der unveränderte, nach `main` übernommene und dort durch
  `tests` und `style` bestätigte Release-Commit.
- **Name:** OpenHomeAlarm.
- **Deutsche Beschreibung:** vorhandene Beta-Beschreibung übernehmen und vor
  dem Einreichen noch einmal gegen den aktuellen Funktionsumfang prüfen.
- **Versionsinformation:** den vorbereiteten Stable-Text übernehmen und gegen
  den final zugeordneten Abschnitt der `CHANGELOG.md` abgleichen; nur
  tatsächlich im Release-Commit enthaltene Punkte einreichen.
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
