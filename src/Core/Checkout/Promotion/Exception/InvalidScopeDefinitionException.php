<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed, use PromotionException::invalidScopeDefinition() instead
 */
#[Package('checkout')]
class InvalidScopeDefinitionException extends HeyFrameHttpException
{
    public function __construct(string $scope)
    {
        parent::__construct(
            'Invalid discount calculator scope definition "{{ label }}"',
            ['label' => $scope]
        );
    }

    public function getErrorCode(): string
    {
        return 'CHECKOUT__INVALID_DISCOUNT_SCOPE_DEFINITION';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
