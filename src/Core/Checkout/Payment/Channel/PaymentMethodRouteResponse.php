<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<EntitySearchResult<PaymentMethodCollection>>
 */
#[Package('checkout')]
class PaymentMethodRouteResponse extends FrontApiResponse
{
    public function getPaymentMethods(): PaymentMethodCollection
    {
        return $this->object->getEntities();
    }
}
