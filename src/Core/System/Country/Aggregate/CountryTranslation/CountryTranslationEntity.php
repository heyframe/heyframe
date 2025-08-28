<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Aggregate\CountryTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Country\CountryEntity;

#[Package('fundamentals@discovery')]
class CountryTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $countryId;

    protected ?string $name = null;

    protected ?CountryEntity $country = null;

    /**
     * @var array<array<string, array<string, string>>>|null
     */
    protected ?array $addressFormat = null;

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): void
    {
        $this->country = $country;
    }

    /**
     * @return array<array<string, array<string, string>>>|null
     */
    public function getAddressFormat(): ?array
    {
        return $this->addressFormat;
    }

    /**
     * @param array<array<string, array<string, string>>> $addressFormat
     */
    public function setAddressFormat(array $addressFormat): void
    {
        $this->addressFormat = $addressFormat;
    }
}
