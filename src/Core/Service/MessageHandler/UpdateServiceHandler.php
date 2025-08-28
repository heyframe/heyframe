<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\MessageHandler;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Message\UpdateServiceMessage;
use HeyFrame\Core\Service\ServiceLifecycle;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('framework')]
#[AsMessageHandler]
final readonly class UpdateServiceHandler
{
    public function __construct(private ServiceLifecycle $serviceLifecycle)
    {
    }

    public function __invoke(UpdateServiceMessage $updateServiceMessage): void
    {
        $this->serviceLifecycle->update($updateServiceMessage->name, Context::createDefaultContext());
    }
}
