<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Exception;

use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\InstanceId\FingerprintComparisonResult;
use HeyFrame\Core\Framework\App\InstanceId\InstanceId;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('framework')]
class InstanceIdChangeSuggestedException extends AppException
{
    public function __construct(
        public readonly InstanceId $instanceId,
        public readonly FingerprintComparisonResult $comparisonResult,
    ) {
        parent::__construct(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            AppException::SHOP_ID_CHANGE_SUGGESTED,
            'Changes in your system were detected that suggest a change of the shop ID.'
        );
    }
}
