<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Pricing;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('framework')]
class Price extends Struct
{
    public function __construct(
        protected string $currencyId,
        protected float $gross,
        protected ?Price $listPrice = null,
        protected ?array $percentage = null,
        protected ?Price $regulationPrice = null
    ) {
    }

    public function getGross(): float
    {
        return $this->gross;
    }

    public function setGross(float $gross): void
    {
        $this->gross = $gross;
    }

    public function add(self $price): void
    {
        $this->gross += $price->getGross();
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function setListPrice(?Price $listPrice): void
    {
        $this->listPrice = $listPrice;
    }

    public function getListPrice(): ?Price
    {
        return $this->listPrice;
    }

    public function getPercentage(): ?array
    {
        return $this->percentage;
    }

    public function setPercentage(?array $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function getApiAlias(): string
    {
        return 'price';
    }

    public function getRegulationPrice(): ?Price
    {
        return $this->regulationPrice;
    }

    public function setRegulationPrice(?Price $regulationPrice): void
    {
        $this->regulationPrice = $regulationPrice;
    }
}
