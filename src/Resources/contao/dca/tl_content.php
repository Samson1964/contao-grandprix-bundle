<?php

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

/**
 * Palette
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['grandprix'] = '{type_legend},type,headline;{grandprix_legend},grandprix_list,grandprix_tourcount;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

/**
 * Felder
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['grandprix_list'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['grandprix_list'],
	'exclude'                 => true,
	'options_callback'        => array('tl_content_grandprixlist', 'getGrandPrixLists'),
	'inputType'               => 'select',
	'eval'                    => array
	(
		'mandatory'           => true,
		'multiple'            => false,
		'chosen'              => true,
		'submitOnChange'      => true,
		'tl_class'            => 'w50 wizard'
	),
	'wizard'                  => array
	(
		array('tl_content_grandprixlist', 'editListe')
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['grandprix_tourcount'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['grandprix_tourcount'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array
	(
		'rgxp'                => 'natural',
		'tl_class'            => 'w50',
		'maxlength'           => 2,
	),
	'sql'                     => "int(2) unsigned NOT NULL default '0'"
);

/**
 * Stellt Hilfsmethoden für das Data-Container-Array bereit.
 */
class tl_content_grandprixlist extends Backend
{
	/**
	 * Erzeugt den Link zum Bearbeiten des gewählten Grand Prix.
	 *
	 * @param DataContainer $dc Der Data-Container des Felds; value enthält die
	 *                          ID des gewählten Grand Prix
	 *
	 * @return string Der Link als HTML; leer, wenn noch kein Grand Prix gewählt ist
	 */
	public function editListe(DataContainer $dc)
	{
		if ((int) $dc->value < 1)
		{
			return '';
		}

		$title = sprintf($GLOBALS['TL_LANG']['tl_content']['grandprix_edit'] ?? 'Grand Prix ID %s bearbeiten', $dc->value);

		// Backend-URL und Request-Token über die Services erzeugen
		// (contao/main.php und die Konstante REQUEST_TOKEN existieren in Contao 5 nicht mehr)
		$container = System::getContainer();
		$strToken = $container->get('contao.csrf.token_manager')->getDefaultTokenValue();
		$strUrl = $container->get('router')->generate('contao_backend', array
		(
			'do'    => 'grandprix',
			'act'   => 'edit',
			'id'    => $dc->value,
			'popup' => 1,
			'rt'    => $strToken,
		));

		return ' <a href="' . StringUtil::specialchars($strUrl) . '" title="' . StringUtil::specialchars($title) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.svg', $title) . '</a>';
	}

	/**
	 * Gibt alle Grand-Prix-Serien als Auswahlliste zurück.
	 *
	 * Aufgelistet werden auch unveröffentlichte Serien, damit ein Element schon
	 * vor der Freischaltung der Saison angelegt werden kann.
	 *
	 * @return array ID => "Saison - Titel", absteigend nach Saison sortiert
	 */
	public function getGrandPrixLists()
	{
		$arrOptions = array();

		$objListe = Database::getInstance()
			->prepare('SELECT id, saison, title FROM tl_grandprix ORDER BY saison DESC, title ASC')
			->execute();

		while ($objListe->next())
		{
			$arrOptions[$objListe->id] = $objListe->saison . ' - ' . $objListe->title;
		}

		return $arrOptions;
	}
}
