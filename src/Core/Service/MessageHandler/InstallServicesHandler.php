<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\MessageHandler;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\LifecycleManager;
use HeyFrame\Core\Service\Message\InstallServicesMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('framework')]
#[AsMessageHandler]
final readonly class InstallServicesHandler
{
    public function __construct(private LifecycleManager $manager)
    {
    }

    public function __invoke(InstallServicesMessage $installServicesMessage): void
    {
        $this->manager->install(Context::createDefaultContext());
    }
}
