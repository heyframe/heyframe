<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;
use HeyFrame\Core\System\Currency\CurrencyCollection;

/**
 * @extends StoreApiResponse<CurrencyCollection>
 */
#[Package('fundamentals@framework')]
class CurrencyRouteResponse extends StoreApiResponse
{
    public function getCurrencies(): CurrencyCollection
    {
        return $this->object;
    }
}
