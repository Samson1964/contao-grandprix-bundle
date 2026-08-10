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
 */
$GLOBALS['BE_MOD']['content']['grandprix'] = array
(
	'tables' => array('tl_grandprix', 'tl_grandprix_tournaments'),
	'icon'   => 'bundles/contaograndprix/icons/icon.png',
);

/*
 * Inhaltselement
 */
$GLOBALS['TL_CTE']['schach']['grandprix'] = GrandPrix::class;
