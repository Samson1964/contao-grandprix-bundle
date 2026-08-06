<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Schachbulle\ContaoGrandPrixBundle\ContaoGrandPrixBundle;

/**
 * Meldet das Bundle beim Contao Manager an.
 */
class Plugin implements BundlePluginInterface
{
	/**
	 * Gibt die Ladereihenfolge des Bundles bekannt.
	 *
	 * Das Bundle muss nach dem Contao-Kern geladen werden, weil es dessen
	 * DCA-Dateien erweitert (tl_content) und den Backend-Bereich "bsv" in
	 * $GLOBALS['BE_MOD'] einsortiert.
	 *
	 * @param ParserInterface $parser Wird zum Auflösen von Konfigurationsdateien
	 *                                benötigt, hier aber nicht verwendet
	 *
	 * @return BundleConfig[] Die Konfiguration dieses einen Bundles
	 */
	public function getBundles(ParserInterface $parser): array
	{
		return [
			BundleConfig::create(ContaoGrandPrixBundle::class)
				->setLoadAfter([ContaoCoreBundle::class]),
		];
	}
}
