<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Exception;

use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('framework')]
class InstanceIdChangeStrategyNotFoundException extends AppException
{
    public function __construct(string $strategyName)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            AppException::SHOP_ID_CHANGE_STRATEGY_NOT_FOUND,
            'Shop ID change resolver with name "{{ strategyName }}" not found.',
            ['strategyName' => $strategyName]
        );
    }
}
