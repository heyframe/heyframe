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
     * @param array<string, string|array<string>|null> $parts
     */
    public function __construct(
        public readonly Request $request,
        public readonly ChannelContext $context,
        private array $parts
    ) {
    }

    /**
     * @return string|array<string>|null
     */
    public function get(string $key): string|array|null
    {
        return $this->parts[$key] ?? null;
    }

    /**
     * @param string|array<string> $value
     */
    public function add(string $key, string|array $value): void
    {
        $this->parts[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->parts[$key]);
    }

    /**
     * @return array<string, string|array<string>|null>
     */
    public function getParts(): array
    {
        $parts = $this->parts;
        ksort($parts);

        return $parts;
    }
}
