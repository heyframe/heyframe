<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ActionButton\Response;

use HeyFrame\Core\Framework\App\ActionButton\AppAction;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
interface ActionButtonResponseFactoryInterface
{
    public function supports(string $actionType): bool;

    /**
     * @param array<string, mixed> $payload
     */
    public function create(AppAction $action, array $payload, Context $context): ActionButtonResponse;
}
