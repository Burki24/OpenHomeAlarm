# Release-Prozess

Dieses Verfahren erstellt den ersten produktiven Release und alle späteren
Versionen aus einem eindeutig geprüften Commit.

## Versionsschema

- Die Symcon-Library-Version ist der Wert aus `library.json`, beispielsweise
  `1.109`.
- Der Git-Tag ergänzt die SemVer-Patchstelle: `v1.109.0`.
- Tag, Release-Titel, Changelog und Artefakt müssen dieselbe Version nennen.
- Der Metadaten-Bot auf `dev` muss seine Aktualisierung abgeschlossen haben,
  bevor der endgültige Kandidaten-Commit gewählt wird.

## Kandidat vorbereiten

1. Abnahmematrix und jüngstes Ergebnis auf vollständig bestanden prüfen.
2. `CHANGELOG.md` von **Unreleased** auf die endgültige Version und das
   Veröffentlichungsdatum umstellen.
3. Änderungen mit einem zulässigen Betreff committen und den automatischen
   Metadatencommit abwarten.
4. `tests` und `style` für exakt den resultierenden Commit prüfen.
5. Den geprüften `dev`-Stand kontrolliert nach `main` übernehmen.
6. Auf dem unveränderten `main`-Commit erneut beide Pflichtchecks prüfen.

## Reproduzierbares Artefakt

Aus dem endgültigen Commit zweimal ausführen:

```text
python .github/scripts/build_release_artifact.py
```

Beide Läufe müssen denselben ausgegebenen SHA-256-Hash liefern. Das ZIP enthält
nur die für Installation und Betrieb vorgesehenen Dateien; Entwicklungs-,
Test- und Workflowdateien sind ausgeschlossen.

## Veröffentlichung

1. Den signierten oder annotierten Tag `v<Library-Version>.0` exakt auf dem
   geprüften `main`-Commit erstellen.
2. Tag pushen und einen GitHub Release mit dem entsprechenden Changelog-Text
   anlegen.
3. Das erzeugte ZIP und seinen SHA-256-Hash am Release veröffentlichen.
4. Installation und Update über den veröffentlichten Stand stichprobenartig
   prüfen.
5. Falls der Symcon Module Store verwendet wird, exakt diesen Commit für den
   vorgesehenen Kanal einreichen.

Kein Tag darf verschoben und kein veröffentlichtes Artefakt überschrieben
werden. Eine Korrektur erhält eine neue Library-Version und einen neuen Tag.
