<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Filter;

use HeyFrame\Core\Checkout\Customer\Service\EmailIdnConverter;
use HeyFrame\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @internal
 */
#[Package('checkout')]
class EmailIdnTwigFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('decodeIdnEmail', EmailIdnConverter::decode(...)),
            new TwigFilter('encodeIdnEmail', EmailIdnConverter::encode(...)),
        ];
    }
}
