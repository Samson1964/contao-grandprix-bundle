<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle\Calculator;

/**
 * Rechenkern des Berliner Schnellschach-Grand-Prix.
 *
 * Die Klasse hat bewusst keine Contao-Abhängigkeit, damit die komplette
 * Wertungslogik ohne Framework-Bootstrap mit PHPUnit getestet werden kann.
 * Sie erhält die Stammdaten des Grand Prix im Konstruktor und wandelt in
 * calculate() die CSV-Tabellen der Einzelturniere in den sortierten
 * Gesamtstand um.
 */
final class GrandPrixCalculator
{
	/**
	 * Erlaubte Spaltenüberschriften für die Platzspalte der Turnier-CSV.
	 */
	private const PLATZ_SPALTEN = ['Platz', 'Pl.', 'No.', 'Nr.', 'Br.'];

	/**
	 * Erlaubte Spaltenüberschriften für die Namensspalte der Turnier-CSV.
	 */
	private const NAMEN_SPALTEN = ['Spieler', 'Spielerin', 'Name', 'Teilnehmer', 'Teilnehmerin', 'Weiß', 'Weiss', 'Schwarz'];

	/**
	 * FIDE-Titel, die beim Vereinheitlichen der Namen entfernt werden.
	 */
	private const FIDE_TITEL = ['FM', 'IM', 'GM', 'CM', 'WGM', 'WIM', 'WFM', 'WCM'];

	/**
	 * Wertungspunkte je Platz, 1-basiert (Index 1 = Punkte für Platz 1).
	 *
	 * @var array<int, int>
	 */
	private array $wertung = [];

	/**
	 * Legt die Wertungsregeln des Grand Prix fest.
	 *
	 * @param string $rating       Kommagetrennte Wertungspunkte für Platz 1 bis x,
	 *                             z. B. "20,17,15,...". Leere oder nicht numerische
	 *                             Einträge zählen als 0 Punkte
	 * @param int    $max          Anzahl der besten Turniere, die in die Summe
	 *                             eingehen; Werte unter 1 werden wie 1 behandelt
	 * @param bool   $betterPoints 1. Feinwertung bei Punktgleichheit: bessere
	 *                             Einzelergebnisse (höchste Wertungen zuerst verglichen)
	 * @param bool   $higherTourns 2. Feinwertung bei Punktgleichheit: höhere Anzahl
	 *                             der insgesamt gewerteten Turniere
	 * @param bool   $viewNull     true, wenn Teilnehmer ohne Wertungspunkte in der
	 *                             Tabelle bleiben sollen; false blendet sie aus
	 */
	public function __construct(
		string $rating,
		private int $max,
		private bool $betterPoints,
		private bool $higherTourns,
		private bool $viewNull
	)
	{
		// Wertungspunkte von 0-x nach 1-x verschieben, damit der Platz aus der
		// CSV direkt als Index dienen kann
		foreach (explode(',', $rating) as $index => $punkte)
		{
			$this->wertung[$index + 1] = (int) trim($punkte);
		}

		$this->max = max(1, $this->max);
	}

	/**
	 * Berechnet den Gesamtstand aus den CSV-Tabellen der Einzelturniere.
	 *
	 * Jede CSV wird zeilenweise mit Semikolon als Spaltentrenner gelesen. Die
	 * erste Zeile ist immer die Kopfzeile; aus ihr werden Platz- und
	 * Namensspalte anhand der erlaubten Überschriften ermittelt. Fehlt eine der
	 * beiden Spalten, wird das Turnier übersprungen (es zählt dann auch nicht
	 * als gespieltes Turnier). Leere Zeilen und Zeilen ohne Namen werden
	 * ignoriert.
	 *
	 * @param string        $bonusName   Name des Vorjahressiegers im Format
	 *                                   "Nachname,Vorname"; leer, wenn es keinen
	 *                                   Bonusteilnehmer gibt
	 * @param int           $bonusPoints Bonuspunkte des Vorjahressiegers; sie
	 *                                   zählen wie ein zusätzliches Turnierergebnis
	 * @param array<string> $arrCsv      CSV-Inhalte der Turniere in Reihenfolge der
	 *                                   Austragung (Index 0 = erstes Turnier)
	 *
	 * @return array<int, array<string, mixed>> Sortierte Tabellenzeilen mit den
	 *                                          Schlüsseln name, bonus (int|null),
	 *                                          turniere (Turniernummer => Punkte),
	 *                                          punkte, anzahl und platz. platz ist
	 *                                          ein leerer String, wenn die Zeile
	 *                                          mit der vorhergehenden punktgleich
	 *                                          ist. Leeres Array, wenn keine
	 *                                          Teilnehmer gefunden wurden
	 */
	public function calculate(string $bonusName, int $bonusPoints, array $arrCsv): array
	{
		$arrRows = [];
		$arrIndex = []; // Namensregister: Name => Index in $arrRows

		// Vorjahressieger mit seinen Bonuspunkten als ersten Teilnehmer
		// eintragen; ein Bonus von 0 zählt nicht als Turnierergebnis
		$bonusName = trim($bonusName);

		if ('' !== $bonusName)
		{
			$arrIndex[$bonusName] = 0;
			$arrRows[] = $this->neueZeile($bonusName, $bonusPoints > 0 ? $bonusPoints : null);
		}

		// Turniere der Reihe nach auswerten
		$turnier = 0;

		foreach ($arrCsv as $strCsv)
		{
			++$turnier;
			$this->turnierAuswerten((string) $strCsv, $turnier, $arrRows, $arrIndex);
		}

		// Summen und Feinwertungen berechnen
		foreach ($arrRows as &$arrRow)
		{
			$this->zeileSummieren($arrRow);
		}

		unset($arrRow);

		// Teilnehmer ohne Wertungspunkte ausblenden, wenn gewünscht
		if (!$this->viewNull)
		{
			$arrRows = array_values(array_filter($arrRows, static fn (array $arrRow): bool => $arrRow['punkte'] > 0));
		}

		$this->sortieren($arrRows);
		$this->plaetzeVergeben($arrRows);

		return $arrRows;
	}

	/**
	 * Liest eine Turnier-CSV ein und trägt die Ergebnisse in die Teilnehmerliste ein.
	 *
	 * @param string                                $strCsv   CSV-Inhalt des Turniers
	 * @param int                                   $turnier  Laufende Turniernummer, 1-basiert
	 * @param array<int, array<string, mixed>>      $arrRows  Teilnehmerliste, wird ergänzt
	 * @param array<string, int>                    $arrIndex Namensregister, wird ergänzt
	 */
	private function turnierAuswerten(string $strCsv, int $turnier, array &$arrRows, array &$arrIndex): void
	{
		$colPlatz = null;
		$colName = null;

		foreach (explode("\n", $strCsv) as $row => $strZeile)
		{
			$arrSpalten = explode(';', $strZeile);

			// Kopfzeile: Platz- und Namensspalte anhand der Überschriften suchen
			if (0 === $row)
			{
				foreach ($arrSpalten as $col => $strTitel)
				{
					$strTitel = trim($strTitel);

					if (null === $colPlatz && \in_array($strTitel, self::PLATZ_SPALTEN, true))
					{
						$colPlatz = $col;
					}
					elseif (null === $colName && \in_array($strTitel, self::NAMEN_SPALTEN, true))
					{
						$colName = $col;
					}
				}

				// Ohne Platz- oder Namensspalte ist die CSV nicht auswertbar
				if (null === $colPlatz || null === $colName)
				{
					return;
				}

				continue;
			}

			// Datenzeile: Leerzeilen und Zeilen ohne Namen überspringen
			$strName = $this->nameKonvertieren($arrSpalten[$colName] ?? '');

			if ('' === $strName)
			{
				continue;
			}

			// Wertungspunkte aus dem Platz ermitteln; Plätze außerhalb der
			// Wertungsliste erhalten 0 Punkte
			$platz = (int) trim($arrSpalten[$colPlatz] ?? '');
			$punkte = $this->wertung[$platz] ?? 0;

			if (isset($arrIndex[$strName]))
			{
				$arrRows[$arrIndex[$strName]]['turniere'][$turnier] = $punkte;
			}
			else
			{
				$arrIndex[$strName] = \count($arrRows);
				$arrRows[] = $this->neueZeile($strName, null, [$turnier => $punkte]);
			}
		}
	}

	/**
	 * Berechnet Punktsumme, Turnieranzahl und Feinwertung einer Teilnehmerzeile.
	 *
	 * Die Bonuspunkte zählen wie ein Turnierergebnis. Von allen Ergebnissen
	 * gehen nur die besten x (Einstellung "max") in die Summe ein; die absteigend
	 * sortierte und auf x Einträge aufgefüllte Ergebnisliste wird als Schlüssel
	 * "beste" für die 1. Feinwertung hinterlegt.
	 *
	 * @param array<string, mixed> $arrRow Teilnehmerzeile, wird direkt verändert
	 */
	private function zeileSummieren(array &$arrRow): void
	{
		$arrErgebnisse = array_values($arrRow['turniere']);

		if (null !== $arrRow['bonus'])
		{
			$arrErgebnisse[] = $arrRow['bonus'];
		}

		$arrRow['anzahl'] = \count($arrErgebnisse);

		// Beste x Turniere ermitteln; mit Nullen auffüllen, damit alle
		// Teilnehmer gleich lange Feinwertungslisten haben
		rsort($arrErgebnisse, SORT_NUMERIC);
		$arrBeste = \array_slice(array_pad($arrErgebnisse, $this->max, 0), 0, $this->max);

		$arrRow['punkte'] = array_sum($arrBeste);
		$arrRow['beste'] = $arrBeste;
	}

	/**
	 * Sortiert die Teilnehmerliste nach Punkten und den aktivierten Feinwertungen.
	 *
	 * Sortiert wird immer absteigend nach der Punktsumme. Bei Punktgleichheit
	 * entscheidet die 1. Feinwertung (bessere Einzelergebnisse, elementweise
	 * verglichen) und danach die 2. Feinwertung (mehr gewertete Turniere) -
	 * jeweils nur, wenn die Einstellung aktiviert ist.
	 *
	 * @param array<int, array<string, mixed>> $arrRows Teilnehmerliste, wird direkt sortiert
	 */
	private function sortieren(array &$arrRows): void
	{
		usort($arrRows, function (array $a, array $b): int {
			$result = $b['punkte'] <=> $a['punkte'];

			if (0 === $result && $this->betterPoints)
			{
				$result = $b['beste'] <=> $a['beste'];
			}

			if (0 === $result && $this->higherTourns)
			{
				$result = $b['anzahl'] <=> $a['anzahl'];
			}

			return $result;
		});
	}

	/**
	 * Trägt die Platznummern in die sortierte Teilnehmerliste ein.
	 *
	 * Zeilen, die nach allen aktivierten Wertungen mit der vorhergehenden Zeile
	 * gleichauf liegen, erhalten einen leeren String statt einer Nummer - die
	 * Platzspalte bleibt dann in der Ausgabe leer. Die Zählung läuft trotzdem
	 * weiter, der nächste unterschiedliche Teilnehmer bekommt also seine
	 * laufende Nummer (Beispiel: 1, leer, 3).
	 *
	 * @param array<int, array<string, mixed>> $arrRows Sortierte Teilnehmerliste, wird direkt verändert
	 */
	private function plaetzeVergeben(array &$arrRows): void
	{
		$arrVorher = null;

		foreach ($arrRows as $index => &$arrRow)
		{
			// Vergleichsschlüssel nur aus den aktivierten Wertungen aufbauen
			$arrSchluessel = [$arrRow['punkte']];

			if ($this->betterPoints)
			{
				$arrSchluessel[] = $arrRow['beste'];
			}

			if ($this->higherTourns)
			{
				$arrSchluessel[] = $arrRow['anzahl'];
			}

			$arrRow['platz'] = $arrSchluessel === $arrVorher ? '' : (string) ($index + 1);
			$arrVorher = $arrSchluessel;

			// Interner Feinwertungsschlüssel gehört nicht in die Ausgabe
			unset($arrRow['beste']);
		}

		unset($arrRow);
	}

	/**
	 * Erstellt eine leere Teilnehmerzeile.
	 *
	 * @param string          $strName  Teilnehmername im Format "Nachname,Vorname"
	 * @param int|null        $bonus    Bonuspunkte oder null, wenn der Teilnehmer
	 *                                  keinen Bonus erhält
	 * @param array<int, int> $turniere Bereits bekannte Turnierergebnisse
	 *                                  (Turniernummer => Punkte)
	 *
	 * @return array<string, mixed> Die initialisierte Zeile
	 */
	private function neueZeile(string $strName, ?int $bonus, array $turniere = []): array
	{
		return [
			'name' => $strName,
			'bonus' => $bonus,
			'turniere' => $turniere,
			'punkte' => 0,
			'anzahl' => 0,
			'platz' => '',
		];
	}

	/**
	 * Vereinheitlicht einen Teilnehmernamen aus der CSV.
	 *
	 * Aus "Nachname , Vorname , Titel" wird "Nachname,Vorname": Leerzeichen um
	 * die Kommas werden entfernt und FIDE-Titel (FM, IM, GM usw.) gestrichen,
	 * damit derselbe Spieler über alle Turniere hinweg erkannt wird, auch wenn
	 * die Tabellen ihn unterschiedlich führen.
	 *
	 * @param string $strName Der Rohwert aus der Namensspalte
	 *
	 * @return string Der bereinigte Name; leer, wenn die Spalte leer war
	 */
	private function nameKonvertieren(string $strName): string
	{
		$arrTeile = array_map('trim', explode(',', $strName));

		$arrTeile = array_filter($arrTeile, static function (string $strTeil, int $index): bool {
			// Der erste Teil (Nachname) bleibt immer erhalten, dahinter fliegen
			// leere Teile und FIDE-Titel heraus
			return 0 === $index || ('' !== $strTeil && !\in_array(strtoupper($strTeil), self::FIDE_TITEL, true));
		}, ARRAY_FILTER_USE_BOTH);

		return implode(',', $arrTeile);
	}
}
