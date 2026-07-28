# Security Policy

## Schwachstellen melden

Bitte sicherheitsrelevante Probleme nicht zuerst in einem öffentlichen Issue veröffentlichen. Verwende nach Möglichkeit eine private GitHub Security Advisory für dieses Repository oder kontaktiere den Repository-Inhaber direkt. Beschreibe die betroffene Version, die notwendigen Voraussetzungen, die Auswirkung und einen reproduzierbaren Testfall. Zugangsdaten, Unscharfschaltcodes und private Symcon-Adressen dürfen nicht mitgesendet werden.

## Sicherheitsgrenzen

OpenHomeAlarm verarbeitet Sensorzustände und Konfiguration ausschließlich innerhalb der jeweiligen Symcon-Installation. Das Modul baut selbst keine externen Netzwerkverbindungen auf. Externe Wirkungen entstehen nur durch ausdrücklich konfigurierte native Symcon-Aktionen.

Der optionale Unscharfschaltcode wird als lokale Instanzeigenschaft gespeichert und im Konfigurationsformular verdeckt dargestellt. Er wird weder in Statusvariablen noch im Bedienzustand, Debug-Log oder Ereignisprotokoll ausgegeben. Der Zugriff auf die Symcon-Konfiguration, Sicherungen und JSON-RPC-Schnittstellen muss trotzdem angemessen geschützt werden. `OHA_Disarm()` ist bewusst eine vertrauenswürdige Automationsschnittstelle ohne Code-Prüfung; benutzerseitige Oberflächen müssen `OHA_DisarmWithCode()` beziehungsweise die Kachelaktion verwenden.

Das Ereignisprotokoll dient Bedienung und Diagnose. Es ist kein manipulationssicheres Audit-Log.

## Einsatzgrenze

OpenHomeAlarm ist eine Automationslösung und keine zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage. Für Personen- oder Sachschutz mit normativen, behördlichen oder versicherungsrechtlichen Anforderungen ist geeignete zertifizierte Sicherheitstechnik einzusetzen.
