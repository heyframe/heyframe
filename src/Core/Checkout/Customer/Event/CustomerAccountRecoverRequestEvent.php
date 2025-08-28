<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryEntity;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Content\Flow\Dispatching\Action\FlowMailVariables;
use HeyFrame\Core\Content\Flow\Dispatching\Aware\CustomerRecoveryAware;
use HeyFrame\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Event\EventData\ScalarValueType;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\MailAware;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CustomerAccountRecoverRequestEvent extends Event implements ChannelAware, HeyFrameChannelEvent, CustomerAware, MailAware, CustomerRecoveryAware, ScalarValuesAware, FlowEventAware
{
    public const EVENT_NAME = 'customer.recovery.request';

    private readonly string $shopName;

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly ChannelContext $channelContext,
        private readonly CustomerRecoveryEntity $customerRecovery,
        private readonly string $resetUrl,
    ) {
        $this->shopName = $channelContext->getChannel()->getTranslation('name');
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return [
            FlowMailVariables::RESET_URL => $this->resetUrl,
            FlowMailVariables::SHOP_NAME => $this->shopName,
        ];
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getCustomerRecovery(): CustomerRecoveryEntity
    {
        return $this->customerRecovery;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('customerRecovery', new EntityType(CustomerRecoveryDefinition::class))
            ->add('customer', new EntityType(CustomerDefinition::class))
            ->add('resetUrl', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('shopName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct) {
            $customer = $this->customerRecovery->getCustomer();
            \assert($customer !== null);

            $this->mailRecipientStruct = new MailRecipientStruct([
                $customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getChannelId(): string
    {
        return $this->channelContext->getChannelId();
    }

    public function getResetUrl(): string
    {
        return $this->resetUrl;
    }

    public function getShopName(): string
    {
        return $this->shopName;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customerRecovery->getCustomer();
    }

    public function getCustomerId(): string
    {
        return $this->getCustomerRecovery()->getCustomerId();
    }

    public function getCustomerRecoveryId(): string
    {
        return $this->customerRecovery->getId();
    }
}
