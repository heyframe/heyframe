<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AttributeTranslationDefinition extends EntityTranslationDefinition
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(private readonly array $meta = [])
    {
    }

    public function getEntityName(): string
    {
        return $this->meta['entity_name'] . '_translation';
    }

    protected function getParentDefinitionClass(): string
    {
        return $this->meta['entity_name'];
    }

    protected function defineFields(): FieldCollection
    {
        $fields = [];
        foreach ($this->meta['fields'] as $field) {
            if (!isset($field['class'])) {
                continue;
            }
            if (!$field['translated']) {
                continue;
            }

            $instance = new $field['class'](...$field['args']);
            if (!$instance instanceof Field) {
                continue;
            }

            foreach ($field['flags'] ?? [] as $flag) {
                $flagInstance = new $flag['class'](...$flag['args'] ?? []);

                if ($flagInstance instanceof Flag) {
                    $instance->addFlags($flagInstance);
                }
            }

            $fields[] = $instance;
        }

        return new FieldCollection($fields);
    }
}
