<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Event;

use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Content\Flow\Dispatching\Action\SendMailAction;
use HeyFrame\Core\Content\MailTemplate\Exception\MailEventConfigurationException;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\A11yRenderedDocumentAware;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\MailAware;
use HeyFrame\Core\Framework\Event\OrderAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class OrderStateMachineStateChangeEvent extends Event implements ChannelAware, OrderAware, MailAware, CustomerAware, A11yRenderedDocumentAware, FlowEventAware
{
    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly string $name,
        private readonly OrderEntity $order,
        private readonly Context $context
    ) {
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('order', new EntityType(OrderDefinition::class));
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $orderCustomer = $this->order->getOrderCustomer();
            if (!$orderCustomer) {
                throw new MailEventConfigurationException('Data for mailRecipientStruct not available.', self::class);
            }

            $this->mailRecipientStruct = new MailRecipientStruct([
                $orderCustomer->getEmail() => $orderCustomer->getFirstName() . ' ' . $orderCustomer->getLastName(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getChannelId(): string
    {
        return $this->order->getChannelId();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrderId(): string
    {
        return $this->order->getId();
    }

    public function getCustomerId(): string
    {
        $orderCustomer = $this->order->getOrderCustomer();

        if (!$orderCustomer?->getCustomerId()) {
            throw OrderException::orderCustomerDeleted($this->order->getId());
        }

        return $orderCustomer->getCustomerId();
    }

    /**
     * @return array<string>
     */
    public function getA11yDocumentIds(): array
    {
        $extension = $this->context->getExtension(SendMailAction::MAIL_CONFIG_EXTENSION);
        if (!$extension instanceof MailSendSubscriberConfig) {
            return [];
        }

        return array_filter($extension->getDocumentIds());
    }
}
