<?php

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\Config;
use Contao\DataContainer;
use Contao\Date;
use Contao\DC_Table;

/**
 * Tabelle tl_grandprix_tournaments
 */
$GLOBALS['TL_DCA']['tl_grandprix_tournaments'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'ptable'                      => 'tl_grandprix',
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                  => 'primary',
				'pid'                 => 'index',
			)
		)
	),

	// Listenansicht
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_PARENT,
			'fields'                  => array('date ASC'),
			'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
			'headerFields'            => array('title'),
			'panelLayout'             => 'sort,filter;search,limit',
			'child_record_callback'   => array('tl_grandprix_tournaments', 'listTournaments'),
			'child_record_class'      => 'no_padding',
			'disableGrouping'         => true,
		),
		'label' => array
		(
			'fields'                  => array('title', 'date'),
			'showColumns'             => false,
			'format'                  => '%s %s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['toggle'],
				'href'                => 'act=toggle&amp;field=published',
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{title_legend},title,date;{csv_legend},csv;{publish_legend},published'
	),

	// Felder
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'maxlength'           => 255,
				'tl_class'            => 'long'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['date'],
			'default'                 => time(),
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => DataContainer::SORT_MONTH_DESC,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'rgxp'                => 'date',
				'doNotCopy'           => true,
				'datepicker'          => true,
				'tl_class'            => 'w50 wizard'
			),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'csv' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['csv'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'explanation'             => 'grandprix_csv',
			'eval'                    => array
			(
				'allowHtml'           => false,
				'class'               => 'monospace',
				'rows'                => 30,
				'rte'                 => 'ace',
				'helpwizard'          => true
			),
			'sql'                     => "mediumtext NULL"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix_tournaments']['published'],
			'toggle'                  => true,
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	)
);

/**
 * Stellt Hilfsmethoden für das Data-Container-Array bereit.
 */
class tl_grandprix_tournaments extends Backend
{
	/**
	 * Erzeugt eine Zeile der Turnierliste als HTML.
	 *
	 * @param array $arrRow Der Datensatz des Turniers aus tl_grandprix_tournaments
	 *
	 * @return string Datum und Titel des Turniers als div-Container
	 */
	public function listTournaments($arrRow)
	{
		$strDate = Date::parse(Config::get('dateFormat'), $arrRow['date'] ?? 0);

		return '<div class="tl_content_left">' . $strDate . ' - ' . ($arrRow['title'] ?? '') . '</div>';
	}
}
