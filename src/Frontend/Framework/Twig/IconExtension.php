<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Framework\Twig\TokenParser\IconTokenParser;
use Twig\Extension\AbstractExtension;

#[Package('framework')]
class IconExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    public function getTokenParsers(): array
    {
        return [
            new IconTokenParser(),
        ];
    }
}
