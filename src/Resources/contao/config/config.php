<?php

declare(strict_types=1);

/**
 * Berliner Schnellschach-Grand-Prix für Contao 4.13 und Contao 5
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

use Contao\ArrayUtil;
use Schachbulle\ContaoGrandPrixBundle\ContentElements\GrandPrix;

/*
 * Backend-Bereich BSV an erster Stelle anlegen, wenn noch nicht vorhanden
 * (andere Bundles des Berliner Schachverbands nutzen denselben Bereich)
 */
if (!isset($GLOBALS['BE_MOD']['bsv']))
{
	ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 0, array('bsv' => array()));
}

/*
 * Backend-Modul
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
