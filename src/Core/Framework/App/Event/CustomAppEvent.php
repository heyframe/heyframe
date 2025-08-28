<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Content\Flow\Dispatching\Aware\CustomAppAware;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class CustomAppEvent extends Event implements CustomAppAware, FlowEventAware
{
    /**
     * @param array<string, mixed>|null $appData
     */
    public function __construct(
        private readonly string $name,
        private readonly ?array $appData,
        private readonly Context $context
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCustomAppData(): ?array
    {
        return $this->appData;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return new EventDataCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
