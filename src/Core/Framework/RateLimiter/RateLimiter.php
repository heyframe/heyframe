<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\RateLimiter;

use HeyFrame\Core\Framework\RateLimiter\Exception\RateLimitExceededException;

class RateLimiter
{
    final public const LOGIN_ROUTE = 'login';

    final public const RESET_PASSWORD = 'reset_password';

    final public const OAUTH = 'oauth';

    /*
     * @var array<string, RateLimiterFactory>
     */
    private array $factories;

    public function reset(string $route, string $key): void
    {
        $this->getFactory($route)->create($key)->reset();
    }

    public function ensureAccepted(string $route, string $key): void
    {
        $limiter = $this->getFactory($route)->create($key)->consume();

        if (!$limiter->isAccepted()) {
            throw new RateLimitExceededException($limiter->getRetryAfter()->getTimestamp());
        }
    }

    public function registerLimiterFactory(string $route, RateLimiterFactory $factory): void
    {
        $this->factories[$route] = $factory;
    }

    private function getFactory(string $route): RateLimiterFactory
    {
        $factory = $this->factories[$route] ?? null;

        if ($factory === null) {
            throw new \RuntimeException('Invalid factory.');
        }

        return $factory;
    }
}
