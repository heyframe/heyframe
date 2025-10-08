<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Context;

use HeyFrame\Core\Content\ContentSystem\DataContext\Consumer\ContextConsumerFactory;
use HeyFrame\Core\Content\ContentSystem\DataContext\Provider\ContextProviderFactory;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Collection of context definitions for a content element.
 *
 * Manages both provider and consumer definitions, providing convenient
 * access methods and behavior for context handling.
 *
 * Immutable - modifications return new instances.
 *
 * @internal
 */
#[Package('discovery')]
class ContextDefinitions
{
    /**
     * @param array<string, ContextProvider> $providers Indexed by context key
     * @param array<string, ContextConsumer> $consumers Indexed by context key
     */
    public function __construct(
        private readonly array $providers = [],
        private readonly array $consumers = []
    ) {
    }

    /**
     * Create empty definitions.
     */
    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * Add a provider (returns new instance).
     */
    public function addProvider(string $key, ContextProvider $provider): self
    {
        $providers = $this->providers;
        $providers[$key] = $provider;

        return new self($providers, $this->consumers);
    }

    /**
     * Add a consumer (returns new instance).
     */
    public function addConsumer(string $key, ContextConsumer $consumer): self
    {
        $consumers = $this->consumers;
        $consumers[$key] = $consumer;

        return new self($this->providers, $consumers);
    }

    /**
     * Check if element provides a specific context.
     */
    public function provides(string $key): bool
    {
        return isset($this->providers[$key]);
    }

    /**
     * Check if element accepts a specific context.
     */
    public function accepts(string $key): bool
    {
        return isset($this->consumers[$key]);
    }

    /**
     * Get provider for a context key.
     */
    public function getProvider(string $key): ?ContextProvider
    {
        return $this->providers[$key] ?? null;
    }

    /**
     * Get consumer for a context key.
     */
    public function getConsumer(string $key): ?ContextConsumer
    {
        return $this->consumers[$key] ?? null;
    }

    /**
     * Get all providers.
     *
     * @return array<string, ContextProvider>
     */
    public function getAllProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get all consumers.
     *
     * @return array<string, ContextConsumer>
     */
    public function getAllConsumers(): array
    {
        return $this->consumers;
    }

    /**
     * Get all provider keys.
     *
     * @return array<string>
     */
    public function allProviderKeys(): array
    {
        return \array_keys($this->providers);
    }

    /**
     * Get all consumer keys.
     *
     * @return array<string>
     */
    public function allConsumerKeys(): array
    {
        return \array_keys($this->consumers);
    }

    /**
     * Check if element has any providers.
     */
    public function hasProviders(): bool
    {
        return !empty($this->providers);
    }

    /**
     * Check if element has any consumers.
     */
    public function hasConsumers(): bool
    {
        return !empty($this->consumers);
    }

    /**
     * Check if definitions are empty (no providers or consumers).
     */
    public function isEmpty(): bool
    {
        return empty($this->providers) && empty($this->consumers);
    }

    /**
     * Create from array structure (deserialization).
     *
     * @param array<string, array<string, mixed>> $providesContext
     * @param array<string, array<string, mixed>> $acceptsContext
     */
    public static function fromArrays(array $providesContext, array $acceptsContext): self
    {
        $providers = [];
        foreach ($providesContext as $key => $config) {
            $providerDef = ContextProviderFactory::fromArray($config);
            // Convert to our ContextProvider (wraps the old definition)
            $providers[$key] = self::convertProvider($providerDef);
        }

        $consumers = [];
        foreach ($acceptsContext as $key => $config) {
            $consumerDef = ContextConsumerFactory::fromArray($config);
            // Convert to our ContextConsumer (wraps the old definition)
            $consumers[$key] = self::convertConsumer($consumerDef);
        }

        return new self($providers, $consumers);
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array{provides_context: array<string, array<string, mixed>>, accepts_context: array<string, array<string, mixed>>}
     */
    public function toArray(): array
    {
        $providesContext = [];
        foreach ($this->providers as $key => $provider) {
            $providesContext[$key] = $provider->toArray();
        }

        $acceptsContext = [];
        foreach ($this->consumers as $key => $consumer) {
            $acceptsContext[$key] = $consumer->toArray();
        }

        return [
            'provides_context' => $providesContext,
            'accepts_context' => $acceptsContext,
        ];
    }

    /**
     * Convert old provider definition to new ContextProvider.
     */
    private static function convertProvider(mixed $providerDef): ContextProvider
    {
        // Temporary bridge: extract values from old definition
        $type = $providerDef->getType();
        $distribution = $providerDef->getDistribution();

        return new ContextProvider(
            type: $type,
            strategy: $distribution->getStrategy(),
            config: $distribution
        );
    }

    /**
     * Convert old consumer definition to new ContextConsumer.
     */
    private static function convertConsumer(mixed $consumerDef): ContextConsumer
    {
        // Temporary bridge: extract values from old definition
        return new ContextConsumer(
            type: $consumerDef->getType(),
            required: $consumerDef->isRequired()
        );
    }
}
