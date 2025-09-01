<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Aggregate\CountryState;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Country\Aggregate\CountryStateTranslation\CountryStateTranslationCollection;
use HeyFrame\Core\System\Country\CountryEntity;

#[Package('fundamentals@discovery')]
class CountryStateEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $countryId;

    protected string $shortCode;

    protected ?string $name = null;

    protected int $position;

    protected bool $active;

    protected ?CountryEntity $country = null;

    protected ?CountryStateTranslationCollection $translations = null;

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): void
    {
        $this->shortCode = $shortCode;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): void
    {
        $this->country = $country;
    }

    public function getTranslations(): ?CountryStateTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CountryStateTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}
