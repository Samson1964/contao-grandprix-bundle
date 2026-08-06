<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle\ContentElements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\Database;
use Contao\System;
use Schachbulle\ContaoGrandPrixBundle\Calculator\GrandPrixCalculator;

/**
 * Inhaltselement "Berliner Schnellschach-Grand-Prix".
 *
 * Das Element lädt lediglich die Stammdaten und die Turnier-CSVs aus der
 * Datenbank; die eigentliche Berechnung des Gesamtstands übernimmt der
 * GrandPrixCalculator.
 *
 * Die folgenden Eigenschaften stammen aus der Tabelle tl_content und werden von
 * Contao über __get() bereitgestellt; sie sind hier aufgeführt, damit statische
 * Codeprüfungen sie kennen.
 *
 * @property string $grandprix_list      ID des anzuzeigenden Grand Prix
 * @property string $grandprix_tourcount Gesamtstand nach so vielen Turnieren; 0 = alle
 */
class GrandPrix extends ContentElement
{
	/**
	 * Template.
	 *
	 * @var string
	 */
	protected $strTemplate = 'ce_grandprix';

	/**
	 * Erzeugt die Ausgabe des Elements.
	 *
	 * Im Backend erscheint nur ein Platzhalter, damit die Artikelansicht nicht
	 * die komplette Tabelle rendert; im Frontend übernimmt compile().
	 *
	 * @return string Das gerenderte Element
	 */
	public function generate()
	{
		if ($this->isBackendRequest())
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . ($GLOBALS['TL_LANG']['CTE']['grandprix'][0] ?? 'GRAND PRIX') . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Stellt den Gesamtstand des gewählten Grand Prix für das Template zusammen.
	 *
	 * Geladen werden nur veröffentlichte Grand Prix und veröffentlichte
	 * Turniere. Ist eine Turnieranzahl eingestellt, gehen nur die ersten x
	 * Turniere (nach Austragungsdatum) in die Wertung ein. Findet sich kein
	 * veröffentlichter Grand Prix oder kein Turnier, bleibt die Zeilenliste
	 * leer und das Template zeigt den Hinweistext.
	 */
	protected function compile()
	{
		$intGrandPrix = (int) $this->grandprix_list;
		$intCount = (int) $this->grandprix_tourcount;

		$arrRows = [];
		$intTurniere = 0;
		$objDatabase = Database::getInstance();

		// Infos zum gewünschten Grand Prix laden
		$objGrandPrix = $objDatabase
			->prepare('SELECT * FROM tl_grandprix WHERE published=? AND id=?')
			->limit(1)
			->execute('1', $intGrandPrix);

		if ($objGrandPrix->numRows > 0)
		{
			// Turniere in Reihenfolge der Austragung laden, bei eingestellter
			// Anzahl nur die ersten x Turniere
			$objStatement = $objDatabase
				->prepare('SELECT csv FROM tl_grandprix_tournaments WHERE published=? AND pid=? ORDER BY date ASC');

			if ($intCount > 0)
			{
				$objStatement->limit($intCount);
			}

			$objTournaments = $objStatement->execute('1', $intGrandPrix);
			$intTurniere = $objTournaments->numRows;

			if ($intTurniere > 0)
			{
				$calculator = new GrandPrixCalculator(
					(string) $objGrandPrix->rating,
					(int) $objGrandPrix->max,
					(bool) $objGrandPrix->better_points,
					(bool) $objGrandPrix->higher_tourns,
					(bool) $objGrandPrix->viewnull
				);

				$arrRows = $calculator->calculate(
					(string) $objGrandPrix->name,
					(int) $objGrandPrix->points,
					$objTournaments->fetchEach('csv')
				);
			}
		}

		$this->Template->rows = $arrRows;
		$this->Template->anzahlTurniere = $intTurniere;
		$this->Template->titel = $objGrandPrix->numRows > 0 ? $objGrandPrix->title : '';
		$this->Template->leer = $GLOBALS['TL_LANG']['MSC']['grandprix_empty'] ?? 'Noch kein Gesamtstand verfügbar!';
	}

	/**
	 * Prüft, ob das Element im Backend gerendert wird.
	 *
	 * TL_MODE existiert in Contao 5 nicht mehr, deshalb wird der Scope-Matcher
	 * verwendet - dieser ist in Contao 4.13 und 5 gleichermaßen verfügbar.
	 *
	 * @return bool true bei einer Backend-Anfrage, sonst false
	 */
	private function isBackendRequest(): bool
	{
		$container = System::getContainer();

		if (null === $container || !$container->has('request_stack'))
		{
			return false;
		}

		$request = $container->get('request_stack')->getCurrentRequest();

		return null !== $request && $container->get('contao.routing.scope_matcher')->isBackendRequest($request);
	}
}
