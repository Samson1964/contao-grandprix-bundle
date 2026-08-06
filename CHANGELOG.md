# Berliner Schnellschach-Grand-Prix Changelog

## Version 2.0.0 (2026-08-06)

Portierung der Contao-3-Erweiterung `grandprix` auf **PHP 8.1+** sowie
**Contao 4.13 und Contao 5** als Bundle `schachbulle/contao-grandprix-bundle`.
Die Datenbankstruktur (`tl_grandprix`, `tl_grandprix_tournaments`, Felder in
`tl_content`) bleibt unverändert - vorhandene Daten werden ohne Migration
übernommen.

### Architektur

* Add: Symfony-Bundle-Struktur mit `ContaoManager\Plugin`,
  `DependencyInjection`-Extension und `services.yaml`
  (`autowire`/`autoconfigure`) statt `config/autoload.php` und `autoload.ini`
* Add: Neue Klasse `Calculator\GrandPrixCalculator` - die komplette
  Wertungslogik ohne Contao-Abhängigkeit und damit ohne Framework-Bootstrap
  testbar
* Add: 16 Unit-Tests in `tests/` samt `phpunit.xml.dist` und Bootstrap, der
  auch ohne eigenes `vendor/`-Verzeichnis funktioniert
* Change: `ContentElements\GrandPrix` lädt nur noch die Daten und delegiert
  die Berechnung; das Element zeigt im Backend jetzt einen Platzhalter statt
  der kompletten Tabelle
* Change: Die HTML-Tabelle wird nicht mehr im PHP-Code zusammengebaut, sondern
  im Template `ce_grandprix.html5` gerendert (`block_searchable`, `thead`/`tbody`,
  maskierte Teilnehmernamen); per `customTpl` ist ein eigenes Template wählbar

### In Contao 5 entfernte APIs ersetzt

* Fix: `dataContainer => 'Table'` → `DC_Table::class`
* Fix: `array_insert()` (entfernt) → `ArrayUtil::arrayInsert()` beim Anlegen
  des Backend-Bereichs "bsv"
* Fix: Toggle-Operation der beiden Tabellen von `button_callback` mit
  `toggleIcon()`/`toggleVisibility()` (nutzte `contao/main.php`, `TL_ERROR`
  und `$this->Input`) auf die Core-Toggle-Operation
  (`act=toggle&field=published`, `'toggle' => true`) umgestellt
* Fix: Edit-Link im Inhaltselement: `contao/main.php` und die Konstante
  `REQUEST_TOKEN` (entfernt) durch die Router-Route `contao_backend` und
  `contao.csrf.token_manager` ersetzt
* Fix: `TL_ROOT`-Wächter (`if (!defined('TL_ROOT')) die(...)`) entfernt -
  die Konstante existiert in Contao 5 nicht mehr, die Dateien hätten beim
  Laden sofort abgebrochen
* Fix: Globale Klassen ohne Namespace (`\ContentElement`, `\Database`,
  `\Backend`, `Image` usw.) auf `Contao\...` umgestellt, `specialchars()` →
  `StringUtil::specialchars()`
* Fix: Backend-Ausgabe über den Scope-Matcher statt `TL_MODE`
* Fix: Operations-Icons von `.gif` auf `.svg` umgestellt - Contao 5 liefert
  nur noch SVG-Icons aus
* Fix: Konstruktoren mit `$this->import('BackendUser', 'User')` entfernt -
  der globale Alias existiert in Contao 5 nicht mehr und `$this->User` wurde
  nirgends benutzt

### Behobene Fehler

* Fix: Das Inhaltselement las die Turnieranzahl aus dem falschen Feldnamen
  (`grandprix_tourncount` statt `grandprix_tourcount`) - die Einstellung
  "Anzahl Turniere" wirkt jetzt; 0 bedeutet "alle Turniere"
* Fix: **Undefined variable** `$content`, wenn ein Grand Prix ohne
  veröffentlichte Turniere angezeigt wurde
* Fix: **Undefined variable** `$colPlatz`/`$colName` (ab PHP 8 Fatal Error),
  wenn die CSV-Kopfzeile keine Platz- oder Namensspalte enthielt - solche
  Turniere werden jetzt übersprungen
* Fix: Leerzeilen und Zeilen ohne Namen in der Turnier-CSV erzeugten
  Geisterteilnehmer bzw. PHP-Warnungen
* Fix: Die Optionen **"1./2. Feinwertung bei Punktgleichheit"**
  (`better_points`, `higher_tourns`) waren im DCA definiert, wurden aber nie
  ausgewertet - abgeschaltete Feinwertungen greifen jetzt weder bei der
  Sortierung noch bei der Platzvergabe
* Fix: Die Option **"Nullwertungen anzeigen"** (`viewnull`) war im DCA
  definiert, wurde aber nie ausgewertet - ist sie deaktiviert, werden
  Teilnehmer ohne Wertungspunkte jetzt ausgeblendet
* Fix: Die 1. Feinwertung verglich zusammengesetzte, auf 2 Zeichen
  abgeschnittene Strings; Bonuspunkte wurden dabei mit Leerzeichen statt
  Nullen aufgefüllt und dadurch zu niedrig einsortiert - verglichen werden
  jetzt die Wertungslisten selbst
* Fix: Die Begrenzung auf 14 Turniere je Saison ist entfallen (die Ergebnisse
  liegen jetzt in einem dynamischen Array statt in den festen Spalten t1-t14)
* Fix: Palette `grandprix` in `tl_content` enthielt die Felder `guest` und
  `space`, die es in Contao 4.13/5 nicht mehr gibt; dafür ist jetzt
  `customTpl` enthalten
* Fix: Doppelte Definition von `tl_grandprix_tournaments.edit` und diverse
  Tippfehler in den Sprachdateien; kaputte Umlaute (Encoding) berichtigt
* Fix: Fehlendes Sprachlabel für den Edit-Link des Inhaltselements
  (`tl_content.editalias` stammte aus dem Core und passte nicht)

### Bereinigung

* Delete: Toter Code entfernt - `NameDrehen()`, `getTemplates()`
  (verwies auf nie existierende Templates `mod_grandprixlists_*`),
  auskommentierte Frontend-Modul-Registrierung, leere
  `buttons_callback`/`__selector__`/`subpalettes`-Blöcke und die Sprachdatei
  `tl_module.php` (Übersetzungen eines anderen Moduls)
* Change: `declare(strict_types=1)` in allen Klassendateien,
  `DataContainer::MODE_*`/`SORT_*`-Konstanten statt Zahlen
* Change: Eingabefelder mit `rgxp`-Validierung (`natural`) für Bonuspunkte,
  beste x Turniere und Turnieranzahl; "Beste x Turniere" erlaubt jetzt
  zweistellige Werte
