<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\DataAbstractionLayer;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('framework')]
class VariantListingConfig extends Struct
{
    /**
     * @param array<string, string>|null $configuratorGroupConfig
     */
    public function __construct(
        protected ?bool $displayParent,
        protected ?string $mainVariantId,
        protected ?array $configuratorGroupConfig
    ) {
    }

    public function getDisplayParent(): ?bool
    {
        return $this->displayParent;
    }

    public function getMainVariantId(): ?string
    {
        return $this->mainVariantId;
    }

    /**
     * @return array<string, string>|null
     */
    public function getConfiguratorGroupConfig(): ?array
    {
        return $this->configuratorGroupConfig;
    }
}
