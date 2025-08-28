<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule\DataAbstractionLayer;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Content\Rule\RuleEvents;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostInstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostUninstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class RuleIndexerSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly CartRuleLoader $cartRuleLoader
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostInstallEvent::class => 'refreshPlugin',
            PluginPostActivateEvent::class => 'refreshPlugin',
            PluginPostUpdateEvent::class => 'refreshPlugin',
            PluginPostDeactivateEvent::class => 'refreshPlugin',
            PluginPostUninstallEvent::class => 'refreshPlugin',
            RuleEvents::RULE_WRITTEN_EVENT => 'onRuleWritten',
        ];
    }

    public function refreshPlugin(): void
    {
        // Delete the payload and invalid flag of all rules
        $update = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('UPDATE `rule` SET `payload` = null, `invalid` = 0')
        );
        $update->execute();
    }

    public function onRuleWritten(): void
    {
        $this->cartRuleLoader->invalidate();
    }
}
