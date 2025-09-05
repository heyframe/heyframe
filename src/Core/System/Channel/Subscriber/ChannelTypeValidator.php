<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Subscriber;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Aggregate\ChannelType\ChannelTypeDefinition;
use HeyFrame\Core\System\Channel\Exception\DefaultChannelTypeCannotBeDeleted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('discovery')]
class ChannelTypeValidator implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preWriteValidateEvent',
        ];
    }

    public function preWriteValidateEvent(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommands() as $command) {
            if (!$command instanceof DeleteCommand || $command->getEntityName() !== ChannelTypeDefinition::ENTITY_NAME) {
                continue;
            }

            $id = Uuid::fromBytesToHex($command->getPrimaryKey()['id']);

            if (\in_array($id, [Defaults::CHANNEL_TYPE_API, Defaults::CHANNEL_TYPE_FRONTEND, Defaults::CHANNEL_TYPE_PRODUCT_COMPARISON], true)) {
                $event->getExceptions()->add(new DefaultChannelTypeCannotBeDeleted($id));
            }
        }
    }
}
