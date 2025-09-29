<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrlRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class SeoUrlMapping
{
    public function __construct(
        private readonly Entity $entity,
        private readonly array $infoPathContext,
        private readonly array $seoPathInfoContext,
        private readonly ?string $error = null
    ) {
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function getSeoPathInfoContext(): array
    {
        return $this->seoPathInfoContext;
    }

    public function getInfoPathContext(): array
    {
        return $this->infoPathContext;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
