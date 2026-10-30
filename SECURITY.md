# Security Policy

## Schwachstellen melden

Bitte sicherheitsrelevante Probleme nicht zuerst in einem öffentlichen Issue veröffentlichen. Verwende nach Möglichkeit eine private GitHub Security Advisory für dieses Repository oder kontaktiere den Repository-Inhaber direkt. Beschreibe die betroffene Version, die notwendigen Voraussetzungen, die Auswirkung und einen reproduzierbaren Testfall. Zugangsdaten, Unscharfschaltcodes und private Symcon-Adressen dürfen nicht mitgesendet werden.

## Sicherheitsgrenzen

OpenHomeAlarm verarbeitet Sensorzustände und Konfiguration grundsätzlich innerhalb
der jeweiligen Symcon-Installation. Eine externe Netzwerkverbindung baut das
Modul ausgehend ausschließlich auf, wenn die optionale direkte Pushover-Benachrichtigung
ausdrücklich konfiguriert und aktiviert wurde. Dabei werden Anwendungs-Token,
Benutzer- oder Gruppen-Key, optional der Gerätename sowie Titel und Text der
Alarmmeldung per HTTPS an `api.pushover.net` übertragen. Ohne aktivierte direkte
Pushover-Benachrichtigung findet keine modulinterne Kommunikation mit externen
Diensten statt. Weitere externe Wirkungen entstehen nur durch ausdrücklich
konfigurierte native Symcon-Aktionen oder durch autorisierte Bedienbefehle der
bereitgestellten Visualisierungen.

Die optionale externe Alarmereignis-Schnittstelle stellt einen eingehenden,
instanzbezogenen JSON-WebHook bereit. Sie ist standardmäßig deaktiviert und
kann ausschließlich einen bereits scharfen, für die jeweilige Quelle und den
aktiven Modus ausdrücklich freigegebenen Bereich in Alarm versetzen. Scharf-
und Unscharfschaltung, Konfigurationsänderungen, Eingangsverzögerungen und
24/7-Sonderrechte sind über diese Schnittstelle nicht erreichbar. Der WebHook
verlangt einen zufälligen 256-Bit-Zugangsschlüssel und `application/json`.
Quelle, Ereignis-ID, Ereignisart und ein enges Zeitfenster werden validiert;
verarbeitete Ereignis-IDs werden als Hash persistent gespeichert, damit eine
wiederholte Zustellung nach `ApplyChanges()` oder Neustart keine zweite
Alarmaktion ausführt. Ereignisse, die bei unscharfem Bereich oder in einem
nicht freigegebenen Modus eintreffen, werden ebenfalls als verbraucht
markiert und können später nicht nachzünden. Das Token wird nicht
protokolliert, nicht über die öffentliche Informationsabfrage ausgegeben und
nicht in der Konfigurationssicherung gespeichert. Es ist im geschützten
Instanzformular sichtbar und muss bei externer Nutzung ausschließlich über
HTTPS beziehungsweise Symcon Connect übertragen werden. Die lokale Funktion
`OHA_TriggerExternalAlarmEvent()` benötigt keinen WebHook-Schlüssel und ist
deshalb ausschließlich für bereits vertrauenswürdige Symcon-Module und
Skripte bestimmt; alle fachlichen Quellen- und Zustandsprüfungen bleiben auch
dort aktiv.

Der optionale Unscharfschaltcode wird als lokale Instanzeigenschaft gespeichert und im Konfigurationsformular verdeckt dargestellt. Er wird weder in Statusvariablen noch im Bedienzustand, Debug-Log oder Ereignisprotokoll ausgegeben. Der Zugriff auf die Symcon-Konfiguration, Sicherungen und JSON-RPC-Schnittstellen muss trotzdem angemessen geschützt werden. `OHA_Disarm()`, `OHA_DisarmWithSource()`, `OHA_DisarmPartition()` und `OHA_DisarmPartitions()` sind bewusst vertrauenswürdige Automationsschnittstellen ohne Code-Prüfung; benutzerseitige Oberflächen müssen `OHA_DisarmWithCode()` beziehungsweise die codegeschützte Kachelaktion verwenden. Bei `OHA_DisarmWithSource()` dient die Quelle ausschließlich der Protokollzuordnung und stellt keine Authentifizierung dar. Die aufrufende Automation muss die Berechtigung vorher selbst sicher prüfen. Auch `OHA_GrantPassage()`, `OHA_GrantPassagePartition()` und `OHA_GrantPassagePartitions()` sind ausschließlich für vertrauenswürdige Automationen vorgesehen: Die aufrufende Logik muss ein berechtigtes Öffnen sicher erkennen, bevor sie einen dafür ausdrücklich konfigurierten Sensor zeitlich begrenzt freigibt.

Die optionale IPSView-Oberfläche verwendet einen instanzbezogenen WebHook mit einem zufälligen, persistenten Zugriffstoken. Der WebHook akzeptiert ausschließlich eine feste Liste von Visualisierungsaktionen per POST und ist bei deaktivierter IPSView-Ausgabe nicht nutzbar. Das Token ist Bestandteil der WebContent-Seite und deshalb wie die IPSView-/Symcon-Verbindung selbst zu schützen. Der Unscharfschaltcode wird nur im POST-Body übertragen und nicht in URLs oder Statusvariablen geschrieben. Bei Zugriff außerhalb eines vertrauenswürdigen lokalen Netzes ist eine verschlüsselte HTTPS-/Symcon-Connect-Verbindung erforderlich.

Ein IPSView-Client kann sich für die Ereigniszuordnung einen frei gewählten
Bediengerätenamen geben. Dieser Name wird ausschließlich lokal im jeweiligen
Client gespeichert und bei Bedienaktionen als selbst angegebene, nicht
vertrauenswürdige Zusatzinformation übertragen. Er ist weder eine stabile
technische Gerätekennung noch ein Authentifizierungs- oder
Berechtigungsmerkmal und darf Codeschutz oder WebHook-Token niemals ersetzen.
Ein fehlender oder ungültiger Name blockiert keine Bedienaktion.

Das Ereignisprotokoll dient Bedienung und Diagnose. Es ist kein manipulationssicheres Audit-Log.

## Einsatzgrenze

OpenHomeAlarm ist eine Automationslösung und keine zertifizierte Einbruch-, Brand- oder Gefahrenmeldeanlage. Für Personen- oder Sachschutz mit normativen, behördlichen oder versicherungsrechtlichen Anforderungen ist geeignete zertifizierte Sicherheitstechnik einzusetzen.
