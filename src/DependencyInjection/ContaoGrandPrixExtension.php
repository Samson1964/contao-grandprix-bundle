<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Bindet die Dienstkonfiguration des Bundles in den Symfony-Container ein.
 */
class ContaoGrandPrixExtension extends Extension
{
	/**
	 * Lädt die Datei services.yaml aus dem Bundle.
	 *
	 * @param array<mixed>     $mergedConfig Die zusammengeführte Bundle-Konfiguration;
	 *                                       das Bundle wertet sie nicht aus, weil es
	 *                                       keine eigenen Konfigurationsschlüssel hat
	 * @param ContainerBuilder $container    Der Container, in den die Dienste geladen werden
	 */
	public function load(array $mergedConfig, ContainerBuilder $container): void
	{
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);

		$loader->load('services.yaml');
	}
}
