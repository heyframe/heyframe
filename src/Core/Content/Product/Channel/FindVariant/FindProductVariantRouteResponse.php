<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\FindVariant;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<FoundCombination>
 */
#[Package('inventory')]
class FindProductVariantRouteResponse extends FrontApiResponse
{
    public function getFoundCombination(): FoundCombination
    {
        return $this->object;
    }
}
