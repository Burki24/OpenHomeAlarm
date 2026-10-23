# Zusammengefasste Praxisabnahme Symcon 9.0 und 9.1

Dieses Protokoll hält die bis zum 23.10.2026 wiederholt erfolgte praktische
Abnahme des aktuellen OpenHomeAlarm-Funktionsumfangs durch den Maintainer und
die Beta-Tester fest. Es ergänzt die detaillierten internen Protokolle vom
24.08.2026 und 04.09.2026 um die seitdem im Beta-Betrieb geprüften
Erweiterungen und Fehlerkorrekturen.

## Referenzstand

| Angabe | Wert |
| --- | --- |
| Zeitraum | August bis 23.10.2026 |
| Prüfer | Maintainer und Beta-Tester |
| Funktionaler Referenz-Commit | `907ed73b0d958de18743000d72de2d4d1296f102` |
| Library-Version | 1.399 |
| Symcon-Versionen | 9.0 und 9.1 |
| Aktuell verbundene Testumgebung | Symcon 9.1, Revision `rust-2b3c9295995acc6734252298bbea89f3dfe0e161` |
| Installationsarten | Neuinstallation und wiederholte Aktualisierung des Beta-/Entwicklungsstands |
| Bedienoberflächen | HTML-SDK-Kachel auf Desktop und Mobilgeräten sowie IPSView-WebContent |

Die einzelnen Rückmeldungen entstanden auf mehreren Installationen. Nicht jede
Installation verwendete denselben Kernel-Build oder dieselbe Clientversion;
deshalb werden nur die sicher bestätigten Hauptversionen gemeinsam angegeben.
Der funktionale Referenz-Commit bezeichnet den vor dieser reinen
Dokumentationsaktualisierung geprüften Programmstand.

## Ergebnis

| Prüfbereich | Ergebnis | Zusammengefasster Praxisnachweis |
| --- | --- | --- |
| Installation und Instanzerstellung | Bestanden | Instanzen wurden unter Symcon 9.0 und 9.1 angelegt und konfiguriert. |
| Sensoren, Störungen und Alarmbereiche | Bestanden | Einzelne, mehrere und die Gesamtanlage wurden mit unterschiedlichen Sensorzuordnungen und Blockierern geprüft. |
| Scharf-/Unscharfschaltung und Codeschutz | Bestanden | Direkte, codegeschützte und bereichsbezogene Bedienwege sowie Fehlversuche und Sperrzeiten wurden im laufenden Betrieb verwendet. |
| Verzögerungen und Countdown-Aktionen | Bestanden | Ein- und Ausgangsverzögerung sowie Start-, Sekunden- und Endaktionen wurden für einzelne und mehrere Bereiche geprüft. |
| Überbrückung und Durchgangsfreigabe | Bestanden | Einmalige Überbrückungen und zeitlich begrenzte Durchgänge wurden mit mehreren Bereichszuordnungen geprüft. |
| Alarm, Eskalation und Benachrichtigung | Bestanden | Alarmaktionen, Rücksetzungen, Symcon-Push und direkte Pushover-Benachrichtigungen wurden praktisch eingesetzt. |
| Kachel und IPSView | Bestanden | Desktop-, Mobil- und IPSView-Darstellung sowie Bereichsauswahl, Codefeld, Protokoll und Diagnose wurden wiederholt geprüft. |
| Neustart und Update | Bestanden | Konfiguration, Sicherheitszustände und Bedienung blieben bei kontrollierten Aktualisierungen und Neustarts erhalten. |
| Sicherung und Wiederherstellung | Bestanden | Export, Validierung, bewusste Wiederherstellung und Ablehnung unzulässiger Wiederherstellungen wurden geprüft. |
| Erweiterungen H-01 bis H-46 | Bestanden | Die in der Beta-Phase gemeldeten Fälle wurden korrigiert und anschließend von Maintainer oder Beta-Testern erneut bestätigt. |

## Verbleibende Release-Schritte

Die fachliche Praxisabnahme ist abgeschlossen. Nach dieser
Dokumentationsaktualisierung sind für den endgültigen Stable-Kandidaten noch
der finale Commit, die kontrollierte Übernahme nach `main` und die Pflichtchecks
`tests` und `style` für exakt diesen unveränderten `main`-Commit festzuhalten.
Das ist Release-Engineering und kein offener funktionaler Praxisbefund.

Updates bleiben ausschließlich bei vollständig unscharfer Anlage zulässig.
Die direkte Pushover-Anbindung ist optional und stellt nur bei ausdrücklicher
Aktivierung eine externe HTTPS-Verbindung her.
