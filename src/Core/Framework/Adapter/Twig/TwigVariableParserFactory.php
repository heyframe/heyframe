<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig;

use HeyFrame\Core\Framework\Log\Package;
use Twig\Environment;

#[Package('framework')]
class TwigVariableParserFactory
{
    public function getParser(Environment $twig): TwigVariableParser
    {
        return new TwigVariableParser($twig);
    }
}
