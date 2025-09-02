<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Storer;

use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\RequestStack;

#[Package('after-sales')]
class TimezoneStorer extends FlowStorer
{
    final public const TIMEZONE_COOKIE = 'timezone';

    /**
     * @internal
     */
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    public function store(FlowEventAware $event, array $stored): array
    {
        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
    }

    private function getTimezone(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return 'Asia/Shanghai';
        }

        $timezone = (string) $request->cookies->get(self::TIMEZONE_COOKIE);

        if (!$timezone || !\in_array($timezone, timezone_identifiers_list(), true)) {
            // Default will be UTC @see https://symfony.com/doc/current/reference/configuration/twig.html#timezone
            return 'Asia/Shanghai';
        }

        return $timezone;
    }
}
