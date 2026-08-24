# Installation und Aktualisierung

## Voraussetzungen

- Symcon 9.0 oder neuer innerhalb der Hauptversion 9.x
- Zugriff auf die Symcon-Verwaltungskonsole
- vorhandene Symcon-Variablen für Sensoren und Störungseingänge

## Neuinstallation über Module Control

1. In Symcon unter **Kern Instanzen** die Instanz **Modules** öffnen.
2. Über **+** das Repository
   `https://github.com/Burki24/OpenHomeAlarm.git` hinzufügen.
3. Für einen produktiven Stand den freigegebenen Branch beziehungsweise Tag
   wählen, nicht den Entwicklungsbranch `dev`.
4. Über **Instanz hinzufügen** eine Instanz **OpenHomeAlarm** anlegen.
5. Sensoren, Störungseingänge, Verzögerungen und optionale Aktionen
   konfigurieren und **Änderungen übernehmen**.
6. Bereitschaft aller verwendeten Modi und sämtliche Alarmwege mit
   Testauslösungen kontrollieren.

## Aktualisierung

1. Eine aktuelle Symcon-Sicherung erstellen und den funktionierenden
   Ausgangsstand dokumentieren.
2. Die Alarmanlage unscharf schalten und aktive Verzögerungen oder Alarme
   kontrolliert beenden.
3. In **Modules** für OpenHomeAlarm **Auf Aktualisierung prüfen** und das
   angebotene Update installieren.
4. Instanzstatus, Sensorkonfiguration, Modusbereitschaft, Bypässe,
   Ereignishistorie und Visualisierungen prüfen.
5. Einen vollständigen Funktionstest aller verwendeten Scharfmodi, Sensoren,
   Störungseingänge und externen Alarmaktionen durchführen.

Konfiguration und persistente Betriebsdaten bleiben bei kompatiblen Updates
erhalten. Ein Update ersetzt dennoch keine Sicherung oder Funktionsprüfung.

## Rückkehr zum vorherigen Stand

Bei einem fehlgeschlagenen Update keine unkontrollierten Änderungen an der
Alarmkonfiguration vornehmen. Symcon stoppen, die zuvor erstellte Sicherung
nach dem dokumentierten Symcon-Verfahren wiederherstellen, den Dienst starten
und den erwarteten Zustand vollständig prüfen.

## Sicherheitsgrenze

OpenHomeAlarm ist keine zertifizierte Alarm- oder Gefahrenmeldeanlage. Hinweise
zu Code-Schutz, IPSView, Fernzugriff und Einsatzgrenzen stehen in
[SECURITY.md](../SECURITY.md).
