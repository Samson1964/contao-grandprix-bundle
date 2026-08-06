<?php

declare(strict_types=1);

namespace Schachbulle\ContaoGrandPrixBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony-Bundle des Berliner Schnellschach-Grand-Prix.
 *
 * Die Klasse enthält keine eigene Logik; sie meldet das Verzeichnis des Bundles
 * beim Kernel an, damit Contao die Konfiguration, die DCA-Dateien und die
 * Templates darunter findet.
 */
class ContaoGrandPrixBundle extends Bundle
{
}
