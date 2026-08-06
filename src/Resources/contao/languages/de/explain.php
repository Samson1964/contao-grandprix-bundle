<?php

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

/*
 * Hilfetext für das CSV-Feld der Turniere
 */
$GLOBALS['TL_LANG']['XPL']['grandprix_csv'] = array
(
	array('colspan', 'Geben Sie hier die Daten der Tabelle im CSV-Format ein. Zeilen müssen durch einen Zeilenumbruch, Spalten durch ein Semikolon getrennt sein.<br><br>Die erste Zeile wird <b>immer</b> als Kopfzeile interpretiert, wobei die Spaltennamen wichtig für die Zuordnung der Spaltenart sind. Die nachfolgenden Spaltenarten sind Pflicht:'),
	array('Spaltenkopf für Platzfeld', 'Erlaubt sind: Platz, Pl., No., Nr., Br.'),
	array('Spaltenkopf für Namensfeld', 'Erlaubt sind: Spieler, Spielerin, Name, Teilnehmer, Teilnehmerin, Weiß, Weiss, Schwarz'),
	array('colspan', 'Die Spalten müssen wie folgt formatiert werden:'),
	array('Spalte für Platz', 'Zahl mit oder ohne Punkt'),
	array('Spalte für Name', 'Nachname,Vorname oder Nachname,Vorname,Titel'),
	array('colspan', 'Weitere Spalten sind möglich, werden aber nicht ausgewertet.'),
);
