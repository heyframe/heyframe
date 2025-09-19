<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use HeyFrame\Core\System\Language\LanguageEntity;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use HeyFrame\Core\System\Tag\TagCollection;
use HeyFrame\Core\System\User\UserEntity;

#[Package('checkout')]
class OrderEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $orderNumber = null;

    protected string $currencyId;

    protected float $currencyFactor;

    protected string $channelId;

    protected \DateTimeInterface $orderDateTime;

    protected \DateTimeInterface $orderDate;

    protected CartPrice $price;

    protected float $amountTotal;

    protected float $amountNet;

    protected float $positionPrice;

    protected ?OrderCustomerEntity $orderCustomer = null;

    protected ?CurrencyEntity $currency = null;

    protected string $languageId;

    protected ?LanguageEntity $language = null;

    protected ?ChannelEntity $channel = null;

    protected ?OrderLineItemCollection $lineItems = null;

    protected ?OrderTransactionCollection $transactions = null;

    protected ?string $deepLinkCode = null;

    protected int $autoIncrement;

    protected ?StateMachineStateEntity $stateMachineState = null;

    protected string $stateId;

    protected ?string $primaryOrderDeliveryId = null;

    protected ?string $primaryOrderDeliveryVersionId = null;

    protected ?OrderTransactionEntity $primaryOrderTransaction = null;

    protected ?string $primaryOrderTransactionId = null;

    protected ?string $primaryOrderTransactionVersionId = null;

    protected ?TagCollection $tags = null;

    protected ?string $affiliateCode = null;

    protected ?string $campaignCode = null;

    /**
     * @var array<string>|null
     */
    protected ?array $ruleIds = [];

    protected ?string $createdById = null;

    protected ?UserEntity $createdBy = null;

    protected ?string $updatedById = null;

    protected ?UserEntity $updatedBy = null;

    protected ?CashRoundingConfig $itemRounding = null;

    protected ?CashRoundingConfig $totalRounding = null;

    protected ?string $source = null;

    protected ?string $taxCalculationType = null;

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getCurrencyFactor(): float
    {
        return $this->currencyFactor;
    }

    public function setCurrencyFactor(float $currencyFactor): void
    {
        $this->currencyFactor = $currencyFactor;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getOrderDateTime(): \DateTimeInterface
    {
        return $this->orderDateTime;
    }

    public function setOrderDateTime(\DateTimeInterface $orderDateTime): void
    {
        $this->orderDateTime = $orderDateTime;
    }

    public function getOrderDate(): \DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): void
    {
        $this->orderDate = $orderDate;
    }

    public function getPrice(): CartPrice
    {
        return $this->price;
    }

    public function setPrice(CartPrice $price): void
    {
        $this->price = $price;
    }

    public function getAmountTotal(): float
    {
        return $this->amountTotal;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function getPositionPrice(): float
    {
        return $this->positionPrice;
    }

    public function getOrderCustomer(): ?OrderCustomerEntity
    {
        return $this->orderCustomer;
    }

    public function setOrderCustomer(OrderCustomerEntity $orderCustomer): void
    {
        $this->orderCustomer = $orderCustomer;
    }

    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }

    public function getChannel(): ?ChannelEntity
    {
        return $this->channel;
    }

    public function setChannel(ChannelEntity $channel): void
    {
        $this->channel = $channel;
    }

    public function getLineItems(): ?OrderLineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(OrderLineItemCollection $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    public function getTransactions(): ?OrderTransactionCollection
    {
        return $this->transactions;
    }

    public function setTransactions(OrderTransactionCollection $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function getDeepLinkCode(): ?string
    {
        return $this->deepLinkCode;
    }

    public function setDeepLinkCode(string $deepLinkCode): void
    {
        $this->deepLinkCode = $deepLinkCode;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getStateMachineState(): ?StateMachineStateEntity
    {
        return $this->stateMachineState;
    }

    public function setStateMachineState(StateMachineStateEntity $stateMachineState): void
    {
        $this->stateMachineState = $stateMachineState;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    public function setAmountTotal(float $amountTotal): void
    {
        $this->amountTotal = $amountTotal;
    }

    public function setAmountNet(float $amountNet): void
    {
        $this->amountNet = $amountNet;
    }

    public function setPositionPrice(float $positionPrice): void
    {
        $this->positionPrice = $positionPrice;
    }

    public function getPrimaryOrderDeliveryId(): ?string
    {
        return $this->primaryOrderDeliveryId;
    }

    public function setPrimaryOrderDeliveryId(?string $primaryOrderDeliveryId): void
    {
        $this->primaryOrderDeliveryId = $primaryOrderDeliveryId;
    }

    public function getPrimaryOrderTransaction(): ?OrderTransactionEntity
    {
        return $this->primaryOrderTransaction;
    }

    public function setPrimaryOrderTransaction(?OrderTransactionEntity $primaryOrderTransaction): void
    {
        $this->primaryOrderTransaction = $primaryOrderTransaction;
    }

    public function getPrimaryOrderTransactionId(): ?string
    {
        return $this->primaryOrderTransactionId;
    }

    public function setPrimaryOrderTransactionId(?string $primaryOrderTransactionId): void
    {
        $this->primaryOrderTransactionId = $primaryOrderTransactionId;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getNestedLineItems(): ?OrderLineItemCollection
    {
        $lineItems = $this->getLineItems();

        if (!$lineItems) {
            return null;
        }

        /** @var OrderLineItemCollection $roots */
        $roots = $lineItems->filterByProperty('parentId', null);
        $roots->sortByPosition();
        $this->addChildren($lineItems, $roots);

        return $roots;
    }

    public function getAffiliateCode(): ?string
    {
        return $this->affiliateCode;
    }

    public function setAffiliateCode(?string $affiliateCode): void
    {
        $this->affiliateCode = $affiliateCode;
    }

    public function getCampaignCode(): ?string
    {
        return $this->campaignCode;
    }

    public function setCampaignCode(?string $campaignCode): void
    {
        $this->campaignCode = $campaignCode;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function getTaxCalculationType(): ?string
    {
        return $this->taxCalculationType;
    }

    public function setTaxCalculationType(?string $taxCalculationType): void
    {
        $this->taxCalculationType = $taxCalculationType;
    }

    /**
     * @return array<string>|null
     */
    public function getRuleIds(): ?array
    {
        return $this->ruleIds;
    }

    /**
     * @param array<string>|null $ruleIds
     */
    public function setRuleIds(?array $ruleIds): void
    {
        $this->ruleIds = $ruleIds;
    }

    public function getCreatedById(): ?string
    {
        return $this->createdById;
    }

    public function setCreatedById(string $createdById): void
    {
        $this->createdById = $createdById;
    }

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserEntity $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedById(): ?string
    {
        return $this->updatedById;
    }

    public function setUpdatedById(string $updatedById): void
    {
        $this->updatedById = $updatedById;
    }

    public function getUpdatedBy(): ?UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserEntity $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getItemRounding(): ?CashRoundingConfig
    {
        return $this->itemRounding;
    }

    public function setItemRounding(?CashRoundingConfig $itemRounding): void
    {
        $this->itemRounding = $itemRounding;
    }

    public function getTotalRounding(): ?CashRoundingConfig
    {
        return $this->totalRounding;
    }

    public function setTotalRounding(?CashRoundingConfig $totalRounding): void
    {
        $this->totalRounding = $totalRounding;
    }

    public function getPrimaryOrderDeliveryVersionId(): ?string
    {
        return $this->primaryOrderDeliveryVersionId;
    }

    public function setPrimaryOrderDeliveryVersionId(?string $primaryOrderDeliveryVersionId): void
    {
        $this->primaryOrderDeliveryVersionId = $primaryOrderDeliveryVersionId;
    }

    public function getPrimaryOrderTransactionVersionId(): ?string
    {
        return $this->primaryOrderTransactionVersionId;
    }

    public function setPrimaryOrderTransactionVersionId(?string $primaryOrderTransactionVersionId): void
    {
        $this->primaryOrderTransactionVersionId = $primaryOrderTransactionVersionId;
    }

    private function addChildren(OrderLineItemCollection $lineItems, OrderLineItemCollection $parents): void
    {
        foreach ($parents as $parent) {
            /** @var OrderLineItemCollection $children */
            $children = $lineItems->filterByProperty('parentId', $parent->getId());
            $children->sortByPosition();

            $parent->setChildren($children);

            $this->addChildren($lineItems, $children);
        }
    }
}
