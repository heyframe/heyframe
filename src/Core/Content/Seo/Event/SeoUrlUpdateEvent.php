<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Event;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('inventory')]
class SeoUrlUpdateEvent extends Event
{
    public function __construct(protected array $seoUrls)
    {
    }

    public function getSeoUrls(): array
    {
        return $this->seoUrls;
    }
}
