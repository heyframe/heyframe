<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

/**
 * @extends StoreApiResponse<StateMachineStateEntity>
 */
#[Package('checkout')]
class CancelOrderRouteResponse extends StoreApiResponse
{
    public function getState(): StateMachineStateEntity
    {
        return $this->object;
    }
}
