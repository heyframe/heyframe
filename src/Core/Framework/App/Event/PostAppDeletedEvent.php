<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @final
 */
#[Package('framework')]
class PostAppDeletedEvent extends Event implements HeyFrameEvent
{
    final public const NAME = 'app.deleted.post';

    public function __construct(
        public readonly string $appName,
        public readonly string $sourceType,
        private readonly Context $context,
        public readonly bool $keepUserData = false
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
