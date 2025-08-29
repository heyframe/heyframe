<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use HeyFrame\Core\System\Currency\CurrencyCollection;

/**
 * @extends FrontApiResponse<CurrencyCollection>
 */
#[Package('fundamentals@framework')]
class CurrencyRouteResponse extends FrontApiResponse
{
    public function getCurrencies(): CurrencyCollection
    {
        return $this->object;
    }
}
