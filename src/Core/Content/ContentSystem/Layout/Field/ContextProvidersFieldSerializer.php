<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\BroadcastDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\IndexedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\IteratorDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\KeyedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config\SlicedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DistributionStrategy;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\Context\ContextProvider;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Json;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Serializes context providers map to/from JSON.
 *
 * @internal
 */
#[Package('discovery')]
class ContextProvidersFieldSerializer extends AbstractFieldSerializer
{
    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry
    ) {
        parent::__construct($validator, $definitionRegistry);
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof StorageAware) {
            throw ContentSystemException::invalidFieldType(StorageAware::class, $field::class);
        }

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        $value = $data->getValue();

        if (\is_array($value)) {
            $encoded = [];
            foreach ($value as $key => $provider) {
                if ($provider instanceof ContextProvider) {
                    $encoded[$key] = $this->serializeContextProvider($provider);
                } else {
                    $encoded[$key] = $provider;
                }
            }
            $value = $encoded;
        }

        if ($value !== null) {
            $value = Json::encode($value);
        }

        yield $field->getStorageName() => $value;
    }

    /**
     * @return array<string, ContextProvider>|null
     */
    public function decode(Field $field, mixed $value): ?array
    {
        if (!$field instanceof ContextProvidersField) {
            throw ContentSystemException::invalidFieldType(ContextProvidersField::class, $field::class);
        }

        if ($value === null) {
            return null;
        }

        if (\is_string($value)) {
            $value = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        }

        if (!\is_array($value)) {
            throw ContentSystemException::invalidFieldValueType('provides_context', 'array', \gettype($value));
        }

        $providers = [];
        foreach ($value as $key => $config) {
            if (!\is_array($config)) {
                continue;
            }
            $providers[$key] = $this->deserializeContextProvider($key, $config);
        }

        return $providers;
    }

    /**
     * Serializes a ContextProvider to array format for storage.
     * Public to allow other serializers to use it if needed.
     *
     * Note: Uses DistributionConfig::toArray() which must remain for runtime usage.
     *
     * @return array<string, mixed>
     */
    public function serializeContextProvider(ContextProvider $provider): array
    {
        return \array_merge(
            [
                'type' => $provider->type->value,
                'strategy' => $provider->config->getStrategy()->value,
            ],
            $provider->config->toArray()
        );
    }

    protected function getConstraints(Field $field): array
    {
        $constraints = [
            new Type('array'),
        ];

        if ($field->is(Required::class)) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    /**
     * Deserializes a context provider from configuration array.
     *
     * @param array<string, mixed> $config
     */
    private function deserializeContextProvider(string $key, array $config): ContextProvider
    {
        $type = ContextType::from($config['type'] ?? 'single');

        if ($type === ContextType::Single) {
            // Single context provider uses broadcast strategy
            return new ContextProvider(
                type: $type,
                config: new BroadcastDistributionConfig()
            );
        }

        // Collection context provider - determine distribution strategy
        $strategyName = $config['distribution'] ?? 'broadcast';
        $strategy = DistributionStrategy::from($strategyName);

        $distributionConfig = match ($strategy) {
            DistributionStrategy::Indexed => new IndexedDistributionConfig(),
            DistributionStrategy::Keyed => new KeyedDistributionConfig(
                keyProperty: $config['key_property'] ?? 'data_key'
            ),
            DistributionStrategy::Sliced => new SlicedDistributionConfig(
                sliceSize: $config['slice_size'] ?? 10
            ),
            DistributionStrategy::Iterator => new IteratorDistributionConfig(),
            DistributionStrategy::Broadcast => new BroadcastDistributionConfig(),
        };

        return new ContextProvider(
            type: $type,
            config: $distributionConfig
        );
    }
}
