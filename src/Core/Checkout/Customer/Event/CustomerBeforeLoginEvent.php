<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\EventData\ScalarValueType;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CustomerBeforeLoginEvent extends Event implements ChannelAware, HeyFrameChannelEvent, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.customer.before.login';

    public function __construct(
        private readonly ChannelContext $channelContext,
        private readonly string $email
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getChannelId(): string
    {
        return $this->channelContext->getChannelId();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('email', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }
}
