<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Struct;

use HeyFrame\Core\Content\ContentSystem\Resolver\EntityIdMap;
use HeyFrame\Core\Content\ContentSystem\Resolver\ParameterMap;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * Rich value object representing resolved routing data.
 *
 * Encapsulates entity IDs (resolved from database) and scalar parameters
 * (pass-through values). Provides behavior for placeholder resolution.
 *
 * @internal
 */
#[Package('discovery')]
class ResolvedData extends Struct
{
    public function __construct(
        protected readonly EntityIdMap $entityIds,
        protected readonly ParameterMap $parameters,
        protected ?string $resolvedLayoutId = null
    ) {
    }

    /**
     * Create empty resolved data.
     */
    public static function empty(): self
    {
        return new self(
            EntityIdMap::empty(),
            ParameterMap::empty()
        );
    }

    /**
     * Get an entity ID by placeholder name.
     */
    public function getEntityId(string $placeholder): ?string
    {
        return $this->entityIds->get($placeholder);
    }

    /**
     * Get a pass-through parameter by name.
     */
    public function getParameter(string $name): int|string|bool|float|null
    {
        return $this->parameters->get($name);
    }

    /**
     * Check if an entity ID exists for the given placeholder.
     */
    public function hasEntityId(string $placeholder): bool
    {
        return $this->entityIds->has($placeholder);
    }

    /**
     * Check if a parameter exists with the given name.
     */
    public function hasParameter(string $name): bool
    {
        return $this->parameters->has($name);
    }

    /**
     * Get entity ID map.
     */
    public function getEntityIds(): EntityIdMap
    {
        return $this->entityIds;
    }

    /**
     * Get parameter map.
     */
    public function getParameters(): ParameterMap
    {
        return $this->parameters;
    }

    /**
     * Get all values (entity IDs + parameters combined).
     * For template access and placeholder resolution.
     *
     * @return array<string, string|int|bool|float>
     */
    public function getValues(): array
    {
        return \array_merge($this->entityIds->toArray(), $this->parameters->toArray());
    }

    /**
     * Get a value by placeholder/parameter name (unified access).
     * Checks entity IDs first, then parameters.
     */
    public function getValue(string $name): string|int|bool|float|null
    {
        return $this->entityIds->get($name) ?? $this->parameters->get($name);
    }

    public function getResolvedLayoutId(): ?string
    {
        return $this->resolvedLayoutId;
    }

    public function setResolvedLayoutId(?string $layoutId): void
    {
        $this->resolvedLayoutId = $layoutId;
    }

    /**
     * Resolve placeholders in a string.
     *
     * Replaces {{placeholder}} patterns with actual values.
     * Used by ContentElement->replacePlaceholders().
     */
    public function resolvePlaceholdersInString(string $input): string
    {
        $values = $this->getValues();

        foreach ($values as $key => $value) {
            if (\is_scalar($value)) {
                $placeholder = '{{' . $key . '}}';
                $input = \str_replace($placeholder, (string) $value, $input);
            }
        }

        return $input;
    }

    /**
     * Add entity ID (returns new instance).
     */
    public function withEntityId(string $placeholder, string $entityId): self
    {
        return new self(
            $this->entityIds->add($placeholder, $entityId),
            $this->parameters,
            $this->resolvedLayoutId
        );
    }

    /**
     * Add parameter (returns new instance).
     */
    public function withParameter(string $name, int|string|bool|float $value): self
    {
        return new self(
            $this->entityIds,
            $this->parameters->add($name, $value),
            $this->resolvedLayoutId
        );
    }

    /**
     * Merge entity IDs (returns new instance).
     */
    public function mergeEntityIds(EntityIdMap $entityIds): self
    {
        return new self(
            $this->entityIds->merge($entityIds),
            $this->parameters,
            $this->resolvedLayoutId
        );
    }

    /**
     * Merge parameters (returns new instance).
     */
    public function mergeParameters(ParameterMap $parameters): self
    {
        return new self(
            $this->entityIds,
            $this->parameters->merge($parameters),
            $this->resolvedLayoutId
        );
    }

    public function getApiAlias(): string
    {
        return 'content_resolved_data';
    }
}
