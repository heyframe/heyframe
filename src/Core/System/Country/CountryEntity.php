<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateCollection;
use HeyFrame\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationCollection;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;

#[Package('fundamentals@discovery')]
class CountryEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $name = null;

    protected ?string $iso = null;

    protected int $position;

    protected bool $active;

    protected ?string $iso3 = null;

    protected ?CountryStateCollection $states = null;

    protected ?CountryTranslationCollection $translations = null;

    protected ?ChannelCollection $channelDefaultAssignments = null;

    protected ?ChannelCollection $channels = null;

    protected ?CurrencyCountryRoundingCollection $currencyCountryRoundings = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(?string $iso): void
    {
        $this->iso = $iso;
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

    public function getIso3(): ?string
    {
        return $this->iso3;
    }

    public function setIso3(?string $iso3): void
    {
        $this->iso3 = $iso3;
    }

    public function getStates(): ?CountryStateCollection
    {
        return $this->states;
    }

    public function setStates(CountryStateCollection $states): void
    {
        $this->states = $states;
    }

    public function getTranslations(): ?CountryTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CountryTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getChannelDefaultAssignments(): ?ChannelCollection
    {
        return $this->channelDefaultAssignments;
    }

    public function setChannelDefaultAssignments(ChannelCollection $channelDefaultAssignments): void
    {
        $this->channelDefaultAssignments = $channelDefaultAssignments;
    }

    public function getChannels(): ?ChannelCollection
    {
        return $this->channels;
    }

    public function setChannels(ChannelCollection $channels): void
    {
        $this->channels = $channels;
    }

    public function getCurrencyCountryRoundings(): ?CurrencyCountryRoundingCollection
    {
        return $this->currencyCountryRoundings;
    }

    public function setCurrencyCountryRoundings(CurrencyCountryRoundingCollection $currencyCountryRoundings): void
    {
        $this->currencyCountryRoundings = $currencyCountryRoundings;
    }
}
