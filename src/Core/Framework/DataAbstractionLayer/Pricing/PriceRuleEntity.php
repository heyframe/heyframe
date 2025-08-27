<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Pricing;

use HeyFrame\Core\Framework\DataAbstractionLayer\Contract\IdAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PriceRuleEntity extends Entity implements IdAware
{
    use EntityIdTrait;

    protected string $ruleId;

    protected PriceCollection $price;

    public function getRuleId(): string
    {
        return $this->ruleId;
    }

    public function setRuleId(string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getPrice(): PriceCollection
    {
        return $this->price;
    }

    public function setPrice(PriceCollection $price): void
    {
        $this->price = $price;
    }
}
