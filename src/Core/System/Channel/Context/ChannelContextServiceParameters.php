<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('framework')]
class ChannelContextServiceParameters extends Struct
{
    public function __construct(
        protected string $channelId,
        protected string $token,
        protected ?string $languageId = null,
        protected ?string $currencyId = null,
        protected ?string $domainId = null,
        protected ?Context $originalContext = null,
        protected ?string $customerId = null,
    ) {
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getLanguageId(): ?string
    {
        return $this->languageId;
    }

    public function getCurrencyId(): ?string
    {
        return $this->currencyId;
    }

    public function getDomainId(): ?string
    {
        return $this->domainId;
    }

    public function getOriginalContext(): ?Context
    {
        return $this->originalContext;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }
}
