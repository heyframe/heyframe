<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
final class Configuration
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(private array $parameters)
    {
    }

    /**
     * @return array<string>
     */
    public function getAllowedNonDomainExceptions(): array
    {
        return $this->parameters['allowedNonDomainExceptions'] ?? [];
    }
}
