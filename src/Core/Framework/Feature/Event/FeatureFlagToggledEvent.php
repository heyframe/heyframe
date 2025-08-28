<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Feature\Event;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class FeatureFlagToggledEvent extends Event
{
    public function __construct(
        public readonly string $feature,
        public readonly bool $active
    ) {
    }
}
