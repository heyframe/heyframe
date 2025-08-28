<?php
declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer;

use HeyFrame\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\AllowEmptyString;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\HtmlSanitizer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[Package('framework')]
class LongTextFieldSerializer extends AbstractFieldSerializer
{
    /**
     * @internal
     */
    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry,
        private readonly HtmlSanitizer $sanitizer
    ) {
        parent::__construct($validator, $definitionRegistry);
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof LongTextField) {
            throw DataAbstractionLayerException::invalidSerializerField(LongTextField::class, $field);
        }

        if ($data->getValue() === '' && !$field->is(AllowEmptyString::class)) {
            $data->setValue(null);
        }

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        $sanitizedValue = $this->sanitize($this->sanitizer, $data, $field, $existence);

        if ($sanitizedValue === '' && !$field->is(AllowEmptyString::class)) {
            $data->setValue(null);
        } else {
            $data->setValue($sanitizedValue);
        }

        $this->validateIfNeeded($field, $existence, $data, $parameters);

        yield $field->getStorageName() => $data->getValue() !== null ? (string) $data->getValue() : null;
    }

    public function decode(Field $field, mixed $value): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (\is_array($value)) {
            throw DataAbstractionLayerException::invalidArraySerialization($field, $value);
        }

        return (string) $value;
    }

    protected function getConstraints(Field $field): array
    {
        $constraints = [
            new Type('string'),
        ];

        if (!$field->is(AllowEmptyString::class)) {
            $constraints[] = new NotBlank();
        }

        if ($field->is(AllowEmptyString::class) && $field->is(Required::class)) {
            $constraints[] = new NotNull();
        }

        return $constraints;
    }
}
