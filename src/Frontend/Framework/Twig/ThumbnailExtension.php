<?php
declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig;

use HeyFrame\Core\Framework\Adapter\Twig\TemplateFinder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Framework\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Extension\AbstractExtension;

#[Package('framework')]
class ThumbnailExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct(private readonly TemplateFinder $finder)
    {
    }

    public function getTokenParsers(): array
    {
        return [
            new ThumbnailTokenParser(),
        ];
    }

    public function getFinder(): TemplateFinder
    {
        return $this->finder;
    }
}
