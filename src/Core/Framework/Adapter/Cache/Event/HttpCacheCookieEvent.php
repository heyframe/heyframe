<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class HttpCacheCookieEvent
{
    public const RULE_IDS = 'rule-ids';
    public const VERSION_ID = 'version-id';
    public const CURRENCY_ID = 'currency-id';
    public const LOGGED_IN_STATE = 'logged-in';

    /**
     * @param array<string|int, mixed> $parts
     */
    public function __construct(
        public readonly Request $request,
        public readonly ChannelContext $context,
        private array $parts
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->parts[$key] ?? null;
    }

    public function add(string $key, string $value): void
    {
        $this->parts[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->parts[$key]);
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
