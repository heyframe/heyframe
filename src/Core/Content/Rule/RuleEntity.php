<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountCollection;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use HeyFrame\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\System\Tag\TagCollection;

#[Package('fundamentals@after-sales')]
class RuleEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $name;

    protected ?string $description = null;

    protected int $priority;

    /**
     * @internal
     */
    protected string|Rule|null $payload = null;

    /**
     * @var string[]|null
     */
    protected ?array $moduleTypes = null;

    protected ?ProductPriceCollection $productPrices = null;

    protected ?PaymentMethodCollection $paymentMethods = null;

    protected ?RuleConditionCollection $conditions = null;

    protected bool $invalid;

    /**
     * @var string[]|null
     */
    protected ?array $areas = null;

    protected ?PromotionDiscountCollection $promotionDiscounts = null;

    protected ?PromotionSetGroupCollection $promotionSetGroups = null;

    protected ?PromotionCollection $personaPromotions = null;

    protected ?FlowSequenceCollection $flowSequences = null;

    protected ?TagCollection $tags = null;

    protected ?PromotionCollection $orderPromotions = null;

    protected ?PromotionCollection $cartPromotions = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Rule|string|null
     */
    public function getPayload()
    {
        $this->checkIfPropertyAccessIsAllowed('payload');

        return $this->payload;
    }

    /**
     * @internal
     *
     * @param Rule|string|null $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getProductPrices(): ?ProductPriceCollection
    {
        return $this->productPrices;
    }

    public function setProductPrices(ProductPriceCollection $productPrices): void
    {
        $this->productPrices = $productPrices;
    }

    public function getPaymentMethods(): ?PaymentMethodCollection
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(PaymentMethodCollection $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    public function getConditions(): ?RuleConditionCollection
    {
        return $this->conditions;
    }

    public function setConditions(RuleConditionCollection $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function isInvalid(): bool
    {
        return $this->invalid;
    }

    public function setInvalid(bool $invalid): void
    {
        $this->invalid = $invalid;
    }

    /**
     * @return string[]|null
     */
    public function getAreas(): ?array
    {
        return $this->areas;
    }

    /**
     * @param string[] $areas
     */
    public function setAreas(array $areas): void
    {
        $this->areas = $areas;
    }

    /**
     * @return string[]|null
     */
    public function getModuleTypes(): ?array
    {
        return $this->moduleTypes;
    }

    /**
     * @param string[]|null $moduleTypes
     */
    public function setModuleTypes(?array $moduleTypes): void
    {
        $this->moduleTypes = $moduleTypes;
    }

    public function getPromotionDiscounts(): ?PromotionDiscountCollection
    {
        return $this->promotionDiscounts;
    }

    public function setPromotionDiscounts(PromotionDiscountCollection $promotionDiscounts): void
    {
        $this->promotionDiscounts = $promotionDiscounts;
    }

    public function getPromotionSetGroups(): ?PromotionSetGroupCollection
    {
        return $this->promotionSetGroups;
    }

    public function setPromotionSetGroups(PromotionSetGroupCollection $promotionSetGroups): void
    {
        $this->promotionSetGroups = $promotionSetGroups;
    }

    /**
     * Gets a list of all promotions where this rule
     * is being used within the Persona Conditions
     */
    public function getPersonaPromotions(): ?PromotionCollection
    {
        return $this->personaPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Persona Condition
     */
    public function setPersonaPromotions(PromotionCollection $personaPromotions): void
    {
        $this->personaPromotions = $personaPromotions;
    }

    public function getFlowSequences(): ?FlowSequenceCollection
    {
        return $this->flowSequences;
    }

    public function setFlowSequences(FlowSequenceCollection $flowSequences): void
    {
        $this->flowSequences = $flowSequences;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * Gets a list of all promotions where this rule is
     * being used within the Order Conditions.
     */
    public function getOrderPromotions(): ?PromotionCollection
    {
        return $this->orderPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Order Condition.
     */
    public function setOrderPromotions(PromotionCollection $orderPromotions): void
    {
        $this->orderPromotions = $orderPromotions;
    }

    /**
     * Gets a list of all promotions where this rule is
     * being used within the Cart Conditions.
     */
    public function getCartPromotions(): ?PromotionCollection
    {
        return $this->cartPromotions;
    }

    /**
     * Sets a list of all promotions where this rule should be
     * used as Cart Condition.
     */
    public function setCartPromotions(PromotionCollection $cartPromotions): void
    {
        $this->cartPromotions = $cartPromotions;
    }
}
