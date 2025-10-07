<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Struct;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class ResolvedData extends Struct
{
    /**
     * @param array<string, string> $entityIds Entity placeholder => Entity ID (UUID)
     * @param array<string, int|string|bool|float> $parameters Parameter name => Scalar value
     */
    public function __construct(
        protected readonly array $entityIds,
        protected readonly array $parameters,
        protected ?string $resolvedLayoutId = null
    ) {
        $this->validateEntityIds($entityIds);
        $this->validateParameters($parameters);
    }

    /**
     * Get an entity ID by placeholder name.
     */
    public function getEntityId(string $placeholder): ?string
    {
        return $this->entityIds[$placeholder] ?? null;
    }

    /**
     * Get a pass-through parameter by name.
     */
    public function getParameter(string $name): int|string|bool|float|null
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * Check if an entity ID exists for the given placeholder.
     */
    public function hasEntityId(string $placeholder): bool
    {
        return isset($this->entityIds[$placeholder]);
    }

    /**
     * Check if a parameter exists with the given name.
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Get all entity IDs.
     *
     * @return array<string, string>
     */
    public function getEntityIds(): array
    {
        return $this->entityIds;
    }

    /**
     * Get all parameters.
     *
     * @return array<string, int|string|bool|float>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get all values (entity IDs + parameters combined).
     * For backward compatibility and template access.
     *
     * @return array<string, string|int|bool|float>
     */
    public function getValues(): array
    {
        return \array_merge($this->entityIds, $this->parameters);
    }

    /**
     * Get a value by placeholder/parameter name (unified access).
     * Checks entity IDs first, then parameters.
     */
    public function getValue(string $name): string|int|bool|float|null
    {
        return $this->entityIds[$name] ?? $this->parameters[$name] ?? null;
    }

    public function getResolvedLayoutId(): ?string
    {
        return $this->resolvedLayoutId;
    }

    public function setResolvedLayoutId(?string $layoutId): void
    {
        $this->resolvedLayoutId = $layoutId;
    }

    public function getApiAlias(): string
    {
        return 'content_resolved_data';
    }

    /**
     * @param array<string, mixed> $entityIds
     */
    private function validateEntityIds(array $entityIds): void
    {
        foreach ($entityIds as $key => $value) {
            if (!\is_string($key)) {
                throw ContentSystemException::invalidResolvedData(
                    \sprintf('Entity ID key must be string, got %s', \get_debug_type($key))
                );
            }

            if (!\is_string($value)) {
                throw ContentSystemException::invalidResolvedData(
                    \sprintf('Entity ID value for "%s" must be string (UUID), got %s', $key, \get_debug_type($value))
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            if (!\is_string($key)) {
                throw ContentSystemException::invalidResolvedData(
                    \sprintf('Parameter key must be string, got %s', \get_debug_type($key))
                );
            }

            if (!\is_scalar($value)) {
                throw ContentSystemException::invalidResolvedData(
                    \sprintf('Parameter value for "%s" must be scalar (int, string, bool, float), got %s', $key, \get_debug_type($value))
                );
            }
        }
    }
}
