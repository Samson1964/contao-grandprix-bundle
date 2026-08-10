<?php

declare(strict_types=1);

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Schachbulle\ContaoGrandPrixBundle\ContentElements\GrandPrix;

/*
 * Backend-Modul
 *
 * Der Backend-Bereich "bsv" wird nicht mehr von diesem Bundle angelegt,
 * sondern von einer eigenen Erweiterung des Berliner Schachverbands
 * bereitgestellt.
 */
$GLOBALS['BE_MOD']['bsv']['grandprix'] = array
(
	'tables' => array('tl_grandprix', 'tl_grandprix_tournaments'),
	'icon'   => 'bundles/contaograndprix/icons/icon.png',
);

/*
 * Inhaltselement
 */
$GLOBALS['TL_CTE']['schach']['grandprix'] = GrandPrix::class;
