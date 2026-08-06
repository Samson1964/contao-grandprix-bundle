<?php

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\DataContainer;
use Contao\DC_Table;

/**
 * Tabelle tl_grandprix
 */
$GLOBALS['TL_DCA']['tl_grandprix'] = array
(

	// Konfiguration
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'ctable'                      => array('tl_grandprix_tournaments'),
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                  => 'primary',
			)
		)
	),

	// Listenansicht
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_SORTED,
			'fields'                  => array('saison'),
			'panelLayout'             => 'filter,sort;search,limit',
			'flag'                    => DataContainer::SORT_DESC,
			'disableGrouping'         => true,
		),
		'label' => array
		(
			'fields'                  => array('id', 'saison', 'title'),
			'showColumns'             => true,
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
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['edit'],
				'href'                => 'table=tl_grandprix_tournaments',
				'icon'                => 'edit.svg',
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.svg',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['toggle'],
				'href'                => 'act=toggle&amp;field=published',
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_grandprix']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Paletten
	'palettes' => array
	(
		'default'                     => '{title_legend},title,saison;{options_legend},rating,viewnull;{rating_legend},max,better_points,higher_tourns;{winner_legend},name,points;{publish_legend},published'
	),

	// Felder
	'fields' => array
	(
		'id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['id'],
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'saison' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['saison'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'inputType'               => 'text',
			'flag'                    => DataContainer::SORT_DESC,
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'maxlength'           => 64
			),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'rating' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['rating'],
			'exclude'                 => true,
			'default'                 => '20,17,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1',
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'long',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'viewnull' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['viewnull'],
			'exclude'                 => true,
			'default'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'filter'                  => true,
			'search'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'points' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['points'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 20,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'maxlength'           => 3,
				'rgxp'                => 'natural'
			),
			'sql'                     => "int(3) unsigned NOT NULL default '0'"
		),
		'max' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['max'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 5,
			'filter'                  => true,
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'maxlength'           => 2,
				'rgxp'                => 'natural'
			),
			'sql'                     => "int(1) unsigned NOT NULL default '0'"
		),
		'better_points' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['better_points'],
			'exclude'                 => true,
			'default'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50 clr',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'higher_tourns' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['higher_tourns'],
			'exclude'                 => true,
			'default'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_grandprix']['published'],
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
