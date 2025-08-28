<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AddCacheTagEvent
{
    /**
     * @var string[]
     */
    public array $tags;

    public function __construct(string ...$tags)
    {
        $this->tags = $tags;
    }

    public function add(string ...$tags): self
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }
}
