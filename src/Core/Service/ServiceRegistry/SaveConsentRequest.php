<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\ServiceRegistry;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class SaveConsentRequest implements \JsonSerializable
{
    public function __construct(
        public string $identifier,
        public string $consentingUserId,
        public string $instanceIdentifier,
        public string $consentDate,
        public string $consentRevision,
        public ?string $licenseHost = null,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'identifier' => $this->identifier,
            'consentingUserId' => $this->consentingUserId,
            'instanceIdentifier' => $this->instanceIdentifier,
            'consentDate' => $this->consentDate,
            'consentRevision' => $this->consentRevision,
            'licenseHost' => $this->licenseHost,
        ];
    }
}
