<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Exception;

use HeyFrame\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed, use DataAbstractionLayerException::invalidSortingDirection() instead
 */
#[Package('framework')]
class InvalidSortingDirectionException extends DataAbstractionLayerException
{
    public function __construct(string $direction)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            'FRAMEWORK__INVALID_SORT_DIRECTION',
            'The given sort direction "{{ direction }}" is invalid.',
            ['direction' => $direction]
        );
    }
}
