<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Field;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Layout\Element\DataRequirement\DataRequirement;
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
 * @internal
 */
#[Package('discovery')]
class DataRequirementsFieldSerializer extends AbstractFieldSerializer
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
            $serialized = [];
            foreach ($value as $key => $requirement) {
                if ($requirement instanceof DataRequirement) {
                    $serialized[$key] = $this->serializeDataRequirement($requirement);
                } else {
                    $serialized[$key] = $requirement;
                }
            }
            $value = $serialized;
        }

        if ($value !== null) {
            $value = Json::encode($value);
        }

        yield $field->getStorageName() => $value;
    }

    /**
     * @return array<string, DataRequirement>|null
     */
    public function decode(Field $field, mixed $value): ?array
    {
        if (!$field instanceof DataRequirementsField) {
            throw ContentSystemException::invalidFieldType(DataRequirementsField::class, $field::class);
        }

        if ($value === null) {
            return null;
        }

        if (\is_string($value)) {
            $value = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        }

        if (!\is_array($value)) {
            throw ContentSystemException::invalidFieldValueType('data_requirements', 'array', \gettype($value));
        }

        return $this->deserializeDataRequirements($value);
    }

    /**
     * Serializes a DataRequirement to array format for storage.
     * Public to allow other serializers to use it if needed.
     *
     * @return array<string, mixed>
     */
    public function serializeDataRequirement(DataRequirement $requirement): array
    {
        return [
            'key' => $requirement->key,
            'source' => $requirement->source,
            'config' => $requirement->config,
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
     * @param array<string, array<string, mixed>> $data
     *
     * @return array<string, DataRequirement>
     */
    private function deserializeDataRequirements(array $data): array
    {
        $requirements = [];

        foreach ($data as $key => $requirementData) {
            $requirementData['key'] = $requirementData['key'] ?? $key;
            $requirements[$key] = $this->deserializeDataRequirement($requirementData);
        }

        return $requirements;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deserializeDataRequirement(array $data): DataRequirement
    {
        return new DataRequirement(
            key: $data['key'],
            source: $data['source'],
            config: $data['config'] ?? []
        );
    }
}
