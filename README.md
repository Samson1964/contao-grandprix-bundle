# Berliner Schnellschach-Grand-Prix

Contao-Erweiterung zur Berechnung und Anzeige des Gesamtstands einer
Grand-Prix-Serie über mehrere Turniere hinweg (Berliner Schachverband).
Portierung der gleichnamigen Contao-3-Erweiterung `grandprix`.

**Frank Hoppe**

## Voraussetzungen

* PHP 8.1 oder neuer
* Contao 4.13 oder Contao 5

## Installation

```
composer require schachbulle/contao-grandprix-bundle
```

Anschließend die Datenbank über den Contao-Manager oder
`vendor/bin/contao-console contao:migrate` aktualisieren.

## Funktionsweise

Im Backend-Bereich **Berliner Schachverband → Grand Prix** werden Saisons
angelegt. Jede Saison enthält:

* die **Wertungspunkte** je Turnier für Platz 1 bis x (kommagetrennt,
  z.B. `20,17,15,14,...`),
* die Anzahl der **besten x Turniere**, die in die Gesamtsumme eingehen,
* zwei zuschaltbare **Feinwertungen** bei Punktgleichheit
  (bessere Einzelergebnisse, mehr gespielte Turniere),
* optional den **Vorjahressieger**, der mit Bonuspunkten in die Wertung startet,
* die Option, Teilnehmer ohne Wertungspunkte auszublenden.

Zu jeder Saison werden die Einzelturniere mit ihrer Ergebnistabelle im
CSV-Format erfasst (Semikolon als Spaltentrenner). Die erste Zeile ist die
Kopfzeile; erkannt werden eine Platzspalte (`Platz`, `Pl.`, `No.`, `Nr.`,
`Br.`) und eine Namensspalte (`Spieler`, `Spielerin`, `Name`, `Teilnehmer`,
`Teilnehmerin`, `Weiß`, `Weiss`, `Schwarz`). FIDE-Titel in den Namen werden
automatisch entfernt, damit derselbe Spieler über alle Turniere erkannt wird.

Das Inhaltselement **Berliner Schnellschach-Grand-Prix** (Gruppe
Schach-Elemente) zeigt den Gesamtstand einer Saison im Frontend an, wahlweise
nach einer einstellbaren Anzahl von Turnieren.

## Tests

```
vendor/bin/phpunit
```

Die Wertungslogik liegt Contao-unabhängig in `src/Calculator/GrandPrixCalculator.php`
und ist vollständig durch Unit-Tests abgedeckt.
