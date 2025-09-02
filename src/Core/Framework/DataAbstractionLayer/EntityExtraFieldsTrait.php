<?php
declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\ExtraFields;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
trait EntityExtraFieldsTrait
{
    /**
     * @var array<mixed>|null
     */
    #[ExtraFields]
    protected ?array $extraFields = null;

    /**
     * @return array<mixed>|null
     */
    public function getExtraFields(): ?array
    {
        return $this->extraFields;
    }

    /**
     * Easy accessor for extra fields.
     *
     * Returns an array with the field names as keys and the values as values will be returned.
     * If you pass multiple field names and one of the fields does not exist, the field will not be in the result.
     *
     * Example:
     * ```php
     * $entity->setExtraFields([
     *     'my_extra_field' => 'value',
     *     'my_other_extra_field' => 'value',
     * ]);
     *
     * $entity->getExtraFieldsValues('my_extra_field') === ['my_extra_field' => 'value'];
     *
     * $entity->getExtraFieldsValues('my_extra_field', 'my_other_extra_field') === [
     *    'my_extra_field' => 'value',
     *    'my_other_extra_field' => 'value',
     * ];
     *
     * $entity->getExtraFieldsValues('my_extra_field', 'my_other_extra_field', 'my_third_extra_field') === [
     *    'my_extra_field' => 'value',
     *    'my_other_extra_field' => 'value',
     * ];
     * ```
     *
     * @return array<string, mixed>
     */
    public function getExtraFieldsValues(string ...$fields): array
    {
        return \array_intersect_key($this->extraFields ?? [], \array_flip($fields));
    }

    /**
     * Easy accessor for a single extra field value.
     *
     * If the field does not exist, null will be returned.
     *
     * Example:
     * ```php
     * $entity->getExtraFieldsValue('my_extra_field') === 'value';
     * ```
     */
    public function getExtraFieldsValue(string $field): mixed
    {
        return $this->extraFields[$field] ?? null;
    }

    public function getTranslatedExtraFieldsValue(string $field): mixed
    {
        return $this->translated['extraFields'][$field] ?? null;
    }

    /**
     * @param array<mixed>|null $extraFields
     */
    public function setExtraFields(?array $extraFields): void
    {
        $this->extraFields = $extraFields;
    }

    /**
     * Allows to change extra fields.
     *
     * If you pass only one field name, the value of the field will be changed.
     * If you pass multiple field names, an array with the field names as keys and the values as values will be changed.
     *
     * Example:
     * ```php
     * $entity->setExtraFields([
     *      'my_extra_field' => 'value',
     *      'my_other_extra_field' => 'value',
     * ]);
     *
     * $entity->changeExtraFields([
     *      'my_extra_field' => 'new value',
     * ]);
     *
     * $entity->getExtraFieldsValue('my_extra_field') === 'new value';
     *
     * $entity->changeExtraFields([
     *      'my_extra_field' => 'new value',
     *      'my_other_extra_field' => 'new value',
     * ]);
     *
     * $entity->getExtraFieldsValues('my_extra_field', 'my_other_extra_field') === [
     *      'my_extra_field' => 'new value',
     *      'my_other_extra_field' => 'new value',
     * ];
     * ```
     *
     * @param array<string, mixed> $extraFields
     */
    public function changeExtraFields(array $extraFields): void
    {
        $this->extraFields = \array_replace(
            $this->extraFields ?? [],
            $extraFields
        );
    }
}
