<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\Context\ContextConsumer;
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
 * Serializes context consumers map to/from JSON.
 *
 * @internal
 */
#[Package('discovery')]
class ContextConsumersFieldSerializer extends AbstractFieldSerializer
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
            foreach ($value as $key => $consumer) {
                if ($consumer instanceof ContextConsumer) {
                    $encoded[$key] = $this->serializeContextConsumer($consumer);
                } else {
                    $encoded[$key] = $consumer;
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
     * @return array<string, ContextConsumer>|null
     */
    public function decode(Field $field, mixed $value): ?array
    {
        if (!$field instanceof ContextConsumersField) {
            throw ContentSystemException::invalidFieldType(ContextConsumersField::class, $field::class);
        }

        if ($value === null) {
            return null;
        }

        if (\is_string($value)) {
            $value = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        }

        if (!\is_array($value)) {
            throw ContentSystemException::invalidFieldValueType('accepts_context', 'array', \gettype($value));
        }

        $consumers = [];
        foreach ($value as $key => $config) {
            if (!\is_array($config)) {
                continue;
            }
            $consumers[$key] = $this->deserializeContextConsumer($key, $config);
        }

        return $consumers;
    }

    /**
     * Serializes a ContextConsumer to array format for storage.
     * Public to allow other serializers to use it if needed.
     *
     * @return array<string, mixed>
     */
    public function serializeContextConsumer(ContextConsumer $consumer): array
    {
        return [
            'type' => $consumer->type->value,
            'required' => $consumer->required,
        ];
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
     * Deserializes a context consumer from configuration array.
     *
     * @param array<string, mixed> $config
     */
    private function deserializeContextConsumer(string $key, array $config): ContextConsumer
    {
        $type = ContextType::from($config['type'] ?? 'single');
        $required = $config['required'] ?? false;

        return new ContextConsumer(
            type: $type,
            required: $required
        );
    }
}
