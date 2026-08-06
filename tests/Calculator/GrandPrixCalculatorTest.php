<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle\Tests\Calculator;

use PHPUnit\Framework\TestCase;
use Schachbulle\ContaoGrandPrixBundle\Calculator\GrandPrixCalculator;

/**
 * Tests für den Rechenkern des Grand Prix.
 *
 * Die Tests decken die Turnier-CSV-Auswertung, die Bonuspunkte des
 * Vorjahressiegers, die Beste-x-Wertung, die Feinwertungen samt ihrer
 * Abschaltbarkeit sowie die Platzvergabe bei Punktgleichheit ab.
 */
class GrandPrixCalculatorTest extends TestCase
{
	/**
	 * Erstellt einen Rechner mit den Standardeinstellungen der Tests.
	 *
	 * @param string $rating       Kommagetrennte Wertungspunkte
	 * @param int    $max          Anzahl der besten Turniere
	 * @param bool   $betterPoints 1. Feinwertung aktiv
	 * @param bool   $higherTourns 2. Feinwertung aktiv
	 * @param bool   $viewNull     Nullwertungen anzeigen
	 *
	 * @return GrandPrixCalculator Der konfigurierte Rechner
	 */
	private function calculator(
		string $rating = '20,17,15',
		int $max = 5,
		bool $betterPoints = true,
		bool $higherTourns = true,
		bool $viewNull = true
	): GrandPrixCalculator
	{
		return new GrandPrixCalculator($rating, $max, $betterPoints, $higherTourns, $viewNull);
	}

	/**
	 * Ein einzelnes Turnier ergibt die Wertungspunkte laut Platzierung.
	 */
	public function testEinzelnesTurnier(): void
	{
		$csv = "Platz;Name\n1;Mustermann,Max\n2;Beispiel,Berta\n3;Test,Tina";

		$rows = $this->calculator()->calculate('', 0, array($csv));

		$this->assertCount(3, $rows);
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
		$this->assertSame(20, $rows[0]['punkte']);
		$this->assertSame(17, $rows[1]['punkte']);
		$this->assertSame(15, $rows[2]['punkte']);
		$this->assertSame('1', $rows[0]['platz']);
		$this->assertSame('2', $rows[1]['platz']);
		$this->assertSame('3', $rows[2]['platz']);
	}

	/**
	 * Ergebnisse desselben Spielers werden über mehrere Turniere aufsummiert.
	 */
	public function testSummierungUeberMehrereTurniere(): void
	{
		$csv1 = "Platz;Name\n1;Mustermann,Max\n2;Beispiel,Berta";
		$csv2 = "Platz;Name\n1;Beispiel,Berta\n2;Mustermann,Max";

		$rows = $this->calculator()->calculate('', 0, array($csv1, $csv2));

		// Beide haben 20 + 17 = 37 Punkte und sind punktgleich
		$this->assertSame(37, $rows[0]['punkte']);
		$this->assertSame(37, $rows[1]['punkte']);
		$this->assertSame(2, $rows[0]['anzahl']);
		$this->assertSame('1', $rows[0]['platz']);
		$this->assertSame('', $rows[1]['platz']); // punktgleich => leere Platzspalte
	}

	/**
	 * Der Vorjahressieger startet mit seinen Bonuspunkten.
	 */
	public function testBonuspunkteDesVorjahressiegers(): void
	{
		$csv = "Platz;Name\n1;Beispiel,Berta\n2;Mustermann,Max";

		$rows = $this->calculator()->calculate('Mustermann,Max', 20, array($csv));

		// Mustermann: 20 Bonus + 17 aus dem Turnier = 37, Beispiel: 20
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
		$this->assertSame(37, $rows[0]['punkte']);
		$this->assertSame(20, $rows[0]['bonus']);
		$this->assertSame(2, $rows[0]['anzahl']); // Bonus zählt als Ergebnis
		$this->assertSame('Beispiel,Berta', $rows[1]['name']);
	}

	/**
	 * Ein leerer Name des Vorjahressiegers erzeugt keine Bonuszeile.
	 */
	public function testKeineBonuszeileOhneNamen(): void
	{
		$csv = "Platz;Name\n1;Mustermann,Max";

		$rows = $this->calculator()->calculate('  ', 20, array($csv));

		$this->assertCount(1, $rows);
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
	}

	/**
	 * Nur die besten x Turniere gehen in die Summe ein.
	 */
	public function testNurBesteTurniereZaehlen(): void
	{
		// Drei Turniere, aber nur die besten 2 zählen
		$csv1 = "Platz;Name\n1;Mustermann,Max"; // 20
		$csv2 = "Platz;Name\n2;Mustermann,Max"; // 17
		$csv3 = "Platz;Name\n3;Mustermann,Max"; // 15

		$rows = $this->calculator('20,17,15', 2)->calculate('', 0, array($csv1, $csv2, $csv3));

		$this->assertSame(37, $rows[0]['punkte']); // 20 + 17, das schlechteste fällt weg
		$this->assertSame(3, $rows[0]['anzahl']); // gespielt hat er trotzdem 3
	}

	/**
	 * FIDE-Titel und Leerzeichen werden aus den Namen entfernt,
	 * damit derselbe Spieler über alle Turniere erkannt wird.
	 */
	public function testNamenWerdenVereinheitlicht(): void
	{
		$csv1 = "Platz;Name\n1;Mustermann , Max , FM";
		$csv2 = "Platz;Name\n1;Mustermann,Max";

		$rows = $this->calculator()->calculate('', 0, array($csv1, $csv2));

		$this->assertCount(1, $rows);
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
		$this->assertSame(40, $rows[0]['punkte']);
	}

	/**
	 * Alternative Spaltenüberschriften (Nr., Teilnehmer) werden erkannt,
	 * ein Punkt hinter der Platzziffer stört nicht.
	 */
	public function testAlternativeSpaltenkoepfe(): void
	{
		$csv = "Verein;Nr.;Teilnehmer\nSC Beispiel;1.;Mustermann,Max";

		$rows = $this->calculator()->calculate('', 0, array($csv));

		$this->assertCount(1, $rows);
		$this->assertSame(20, $rows[0]['punkte']);
	}

	/**
	 * Leerzeilen und Zeilen ohne Namen werden übersprungen,
	 * Plätze außerhalb der Wertungsliste ergeben 0 Punkte.
	 */
	public function testLeerzeilenUndUnbekanntePlaetze(): void
	{
		$csv = "Platz;Name\n1;Mustermann,Max\n\n4;Beispiel,Berta\n;\n";

		$rows = $this->calculator('20,17,15')->calculate('', 0, array($csv));

		$this->assertCount(2, $rows);
		$this->assertSame(20, $rows[0]['punkte']);
		$this->assertSame(0, $rows[1]['punkte']); // Platz 4 bei nur 3 Wertungsstufen
	}

	/**
	 * Eine CSV ohne Platz- oder Namensspalte wird komplett übersprungen.
	 */
	public function testUnbrauchbareKopfzeile(): void
	{
		$csv1 = "Verein;Punkte\nSC Beispiel;5"; // weder Platz- noch Namensspalte
		$csv2 = "Platz;Name\n1;Mustermann,Max";

		$rows = $this->calculator()->calculate('', 0, array($csv1, $csv2));

		$this->assertCount(1, $rows);
		// Das unbrauchbare Turnier 1 taucht nicht als Ergebnis auf
		$this->assertArrayNotHasKey(1, $rows[0]['turniere']);
		$this->assertSame(20, $rows[0]['turniere'][2]);
	}

	/**
	 * Die 1. Feinwertung entscheidet bei Punktgleichheit über die Reihenfolge:
	 * bessere Einzelergebnisse gewinnen.
	 */
	public function testErsteFeinwertung(): void
	{
		// Identische Einzelergebnisse (beide 20 und 10) => punktgleich
		$csv1 = "Platz;Name\n1;A,A\n2;B,B"; // A 20, B 10
		$csv2 = "Platz;Name\n2;A,A\n1;B,B"; // A 10, B 20

		$rows = $this->calculator('20,10')->calculate('', 0, array($csv1, $csv2));

		$this->assertSame('', $rows[1]['platz']);

		// Gleiche Summe (je 25), aber A holt 20+5 und B holt 15+10:
		// A gewinnt die Feinwertung, weil 20 > 15 im besten Turnier
		$csv1 = "Platz;Name\n1;A,A\n2;B,B"; // A 20, B 15
		$csv2 = "Platz;Name\n4;A,A\n3;B,B"; // A 5, B 10

		$rows = $this->calculator('20,15,10,5')->calculate('', 0, array($csv1, $csv2));

		$this->assertSame('A,A', $rows[0]['name']);
		$this->assertSame('1', $rows[0]['platz']);
		$this->assertSame('2', $rows[1]['platz']);
	}

	/**
	 * Die 2. Feinwertung zieht bei gleicher Punktzahl und gleicher
	 * 1. Feinwertung: mehr gespielte Turniere gewinnen.
	 */
	public function testZweiteFeinwertung(): void
	{
		// A: 20 aus einem Turnier; B: 20 aus einem Turnier + 0-Wertung aus einem zweiten
		$csv1 = "Platz;Name\n1;A,A\n2;B,B"; // A 20, B 0 (nur Platz 1 gewertet)
		$csv2 = "Platz;Name\n1;B,B"; // B 20

		$rows = $this->calculator('20')->calculate('', 0, array($csv1, $csv2));

		// Beide 20 Punkte, beste Listen identisch, aber B hat 2 Turniere gespielt
		$this->assertSame('B,B', $rows[0]['name']);
		$this->assertSame('1', $rows[0]['platz']);
		$this->assertSame('2', $rows[1]['platz']);
	}

	/**
	 * Abgeschaltete Feinwertungen ändern weder Reihenfolge noch Platzvergabe:
	 * Zeilen gelten dann schon bei gleicher Punktsumme als gleichauf.
	 */
	public function testAbgeschalteteFeinwertungen(): void
	{
		$csv1 = "Platz;Name\n1;A,A\n2;B,B";
		$csv2 = "Platz;Name\n1;B,B";

		$rows = $this->calculator('20', 5, false, false)->calculate('', 0, array($csv1, $csv2));

		// Ohne Feinwertungen bleibt die Eintragsreihenfolge erhalten (A zuerst)
		$this->assertSame('A,A', $rows[0]['name']);
		$this->assertSame('1', $rows[0]['platz']);
		$this->assertSame('', $rows[1]['platz']); // gleichauf trotz weniger Turnieren
	}

	/**
	 * Teilnehmer ohne Wertungspunkte verschwinden, wenn Nullwertungen
	 * ausgeblendet werden.
	 */
	public function testNullwertungenAusblenden(): void
	{
		$csv = "Platz;Name\n1;Mustermann,Max\n5;Beispiel,Berta";

		$rows = $this->calculator('20,17,15', 5, true, true, false)->calculate('', 0, array($csv));

		$this->assertCount(1, $rows);
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
	}

	/**
	 * Ohne Turniere und ohne Vorjahressieger bleibt die Tabelle leer.
	 */
	public function testLeereEingabe(): void
	{
		$this->assertSame(array(), $this->calculator()->calculate('', 0, array()));
	}

	/**
	 * Leere oder nicht numerische Wertungspunkte (z.B. "20,,x,5") führen
	 * nicht zu Fehlern, sondern zählen als 0 Punkte.
	 */
	public function testKaputteWertungspunkte(): void
	{
		$csv = "Platz;Name\n1;A,A\n2;B,B\n3;C,C\n4;D,D";

		$rows = $this->calculator('20,,x,5')->calculate('', 0, array($csv));

		$this->assertSame(20, $rows[0]['punkte']); // Platz 1
		$this->assertSame(5, $rows[1]['punkte']); // Platz 4
		$this->assertSame(0, $rows[2]['punkte']); // Platz 2 (leerer Eintrag)
		$this->assertSame(0, $rows[3]['punkte']); // Platz 3 (nicht numerisch)
	}

	/**
	 * Windows-Zeilenenden (\r\n) in der CSV stören die Auswertung nicht.
	 */
	public function testWindowsZeilenenden(): void
	{
		$csv = "Platz;Name\r\n1;Mustermann,Max\r\n2;Beispiel,Berta\r\n";

		$rows = $this->calculator()->calculate('', 0, array($csv));

		$this->assertCount(2, $rows);
		$this->assertSame('Mustermann,Max', $rows[0]['name']);
		$this->assertSame(20, $rows[0]['punkte']);
	}
}
