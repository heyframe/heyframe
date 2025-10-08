<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Runtime;

use HeyFrame\Core\Content\ContentSystem\Element\Context\ContextConsumer;
use HeyFrame\Core\Content\ContentSystem\Element\Context\ContextDefinitions;
use HeyFrame\Core\Content\ContentSystem\Element\Context\ContextProvider;
use HeyFrame\Core\Content\ContentSystem\Element\DataRequirement\DataRequirements;
use HeyFrame\Core\Content\ContentSystem\Element\Slot\ElementSlots;
use HeyFrame\Core\Content\ContentSystem\Element\Visitor\ElementVisitor;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * Content element aggregate root with tree traversal and context management.
 * Properties store domain objects. Pipeline: PageBuilder → Refinery → Hydrator → Response
 *
 * @internal
 */
#[Package('discovery')]
class ContentElement extends Struct
{
    /**
     * @param string $id Element UUID
     * @param string $type Element type identifier
     * @param string|null $category Element category ('static' or 'service')
     * @param DataRequirements $dataRequirements Data loading specifications
     * @param array<string, mixed> $properties Element properties (stores domain objects)
     * @param ElementSlots $slots Child elements organized by slot
     * @param ContextDefinitions $contextDefinitions Context providers and consumers
     */
    public function __construct(
        protected string $id,
        protected string $type,
        protected ?string $category = null,
        protected DataRequirements $dataRequirements = new DataRequirements([]),
        protected array $properties = [],
        protected ElementSlots $slots = new ElementSlots([]),
        protected ContextDefinitions $contextDefinitions = new ContextDefinitions([], [])
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getDataRequirements(): DataRequirements
    {
        return $this->dataRequirements;
    }

    /**
     * Get all properties as array.
     *
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get a specific property value (can be domain object or primitive).
     */
    public function getProperty(string $key): mixed
    {
        return $this->properties[$key] ?? null;
    }

    /**
     * Check if property exists.
     */
    public function hasProperty(string $key): bool
    {
        return isset($this->properties[$key]);
    }

    /**
     * Set property value (used during hydration and context injection).
     * Can store domain objects or primitives.
     */
    public function setProperty(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }

    /**
     * Set all properties at once.
     *
     * @param array<string, mixed> $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function getSlots(): ElementSlots
    {
        return $this->slots;
    }

    public function getContextDefinitions(): ContextDefinitions
    {
        return $this->contextDefinitions;
    }

    // ========================================================================
    // BEHAVIORAL METHODS - Rich Domain Model
    // ========================================================================

    /**
     * Traverse element tree using Visitor pattern.
     *
     * Performs depth-first traversal:
     * 1. visitor->enter(this)
     * 2. Traverse all children recursively
     * 3. visitor->leave(this)
     */
    public function traverse(ElementVisitor $visitor): void
    {
        $visitor->enter($this);

        // Traverse all children in all slots
        foreach ($this->slots->allElements() as $child) {
            $child->traverse($visitor);
        }

        $visitor->leave($this);
    }

    /**
     * Get all descendant elements (lazy generator).
     *
     * @return \Generator<ContentElement>
     */
    public function descendants(): \Generator
    {
        foreach ($this->slots->allElements() as $child) {
            yield $child;
            yield from $child->descendants();
        }
    }

    /**
     * Find element by ID in subtree (depth-first search).
     */
    public function findById(string $id): ?self
    {
        if ($this->id === $id) {
            return $this;
        }

        foreach ($this->descendants() as $descendant) {
            if ($descendant->id === $id) {
                return $descendant;
            }
        }

        return null;
    }

    /**
     * Get all providers.
     *
     * @return array<string, ContextProvider>
     */
    public function getProvidesContext(): array
    {
        return $this->contextDefinitions->getAllProviders();
    }

    /**
     * Get all consumers.
     *
     * @return array<string, ContextConsumer>
     */
    public function getAcceptsContext(): array
    {
        return $this->contextDefinitions->getAllConsumers();
    }

    /**
     * Check if element provides a specific context.
     */
    public function providesContext(string $key): bool
    {
        return $this->contextDefinitions->provides($key);
    }

    /**
     * Check if element accepts a specific context.
     */
    public function acceptsContext(string $key): bool
    {
        return $this->contextDefinitions->accepts($key);
    }

    /**
     * Collect direct children that accept a specific context.
     *
     * @return array<ContentElement>
     */
    public function collectConsumers(string $contextKey): array
    {
        $consumers = [];

        foreach ($this->slots->allElements() as $child) {
            if ($child->acceptsContext($contextKey)) {
                $consumers[] = $child;
            }
        }

        return $consumers;
    }

    /**
     * Replace placeholders in properties recursively.
     *
     * Processes string properties and recurses into children.
     * No array/JSON conversion - works directly on objects.
     */
    public function replacePlaceholders(ResolvedData $data): void
    {
        // Replace in string properties
        foreach ($this->properties as $key => $value) {
            if (\is_string($value)) {
                $this->properties[$key] = $this->resolvePlaceholder($value, $data);
            }
            // TODO: Support PlaceholderAware interface for custom objects
        }

        // Recurse into children
        foreach ($this->slots->allElements() as $child) {
            $child->replacePlaceholders($data);
        }
    }

    // ========================================================================
    // SERIALIZATION - Array Conversion
    // ========================================================================

    /**
     * Create ContentElement from array structure (deserialization).
     *
     * Used during request processing to convert validated array structure
     * from ContentLayout into ContentElement objects.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Convert data requirements
        $dataRequirements = isset($data['data_requirements']) && \is_array($data['data_requirements'])
            ? DataRequirements::fromArray($data['data_requirements'])
            : DataRequirements::empty();

        // Convert slots
        $slots = isset($data['slots']) && \is_array($data['slots'])
            ? ElementSlots::fromArray($data['slots'])
            : ElementSlots::empty();

        // Convert context definitions
        $contextDefinitions = ContextDefinitions::fromArrays(
            $data['provides_context'] ?? [],
            $data['accepts_context'] ?? []
        );

        return new self(
            id: $data['id'],
            type: $data['type'],
            category: $data['category'] ?? null,
            dataRequirements: $dataRequirements,
            properties: $data['properties'] ?? [],
            slots: $slots,
            contextDefinitions: $contextDefinitions
        );
    }

    /**
     * Convert to array representation (serialization).
     *
     * Used for response serialization and database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'type' => $this->type,
            'properties' => $this->serializeProperties(),
        ];

        if ($this->category !== null) {
            $array['category'] = $this->category;
        }

        if (!$this->dataRequirements->isEmpty()) {
            $array['data_requirements'] = $this->dataRequirements->toArray();
        }

        if (!$this->slots->isEmpty()) {
            $array['slots'] = $this->slots->toArray();
        }

        if (!$this->contextDefinitions->isEmpty()) {
            $contextArrays = $this->contextDefinitions->toArray();
            if (!empty($contextArrays['provides_context'])) {
                $array['provides_context'] = $contextArrays['provides_context'];
            }
            if (!empty($contextArrays['accepts_context'])) {
                $array['accepts_context'] = $contextArrays['accepts_context'];
            }
        }

        return $array;
    }

    public function getApiAlias(): string
    {
        return 'content_element';
    }

    /**
     * Resolve placeholders in a string.
     */
    private function resolvePlaceholder(string $input, ResolvedData $data): string
    {
        $values = $data->getValues();

        foreach ($values as $key => $value) {
            if (\is_scalar($value)) {
                $placeholder = '{{' . $key . '}}';
                $input = \str_replace($placeholder, (string) $value, $input);
            }
        }

        return $input;
    }

    /**
     * Serialize properties to array (handles domain objects).
     *
     * @return array<string, mixed>
     */
    private function serializeProperties(): array
    {
        $serialized = [];

        foreach ($this->properties as $key => $value) {
            // Check if value has toArray() method (duck typing)
            if (\is_object($value) && method_exists($value, 'toArray')) {
                $serialized[$key] = $value->toArray();
            } else {
                $serialized[$key] = $value;
            }
        }

        return $serialized;
    }
}
