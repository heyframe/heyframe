<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\ApiProtectionException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\RuntimeFieldInCriteriaException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiCriteriaAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class ApiCriteriaValidator
{
    /**
     * @internal
     */
    public function __construct(private readonly DefinitionInstanceRegistry $registry)
    {
    }

    public function validate(string $entity, Criteria $criteria, Context $context): void
    {
        $definition = $this->registry->getByEntityName($entity);

        foreach ($criteria->getAllFields() as $accessor) {
            $fields = EntityDefinitionQueryHelper::getFieldsOfAccessor($definition, $accessor);

            foreach ($fields as $field) {
                if (!$field instanceof Field) {
                    continue;
                }

                if ($field->getFlag(ApiCriteriaAware::class)) {
                    continue;
                }

                $flag = $field->getFlag(ApiAware::class);

                if ($flag === null) {
                    throw new ApiProtectionException($accessor);
                }

                if (!$flag->isSourceAllowed($context->getSource()::class)) {
                    throw new ApiProtectionException($accessor);
                }

                $runtime = $field->getFlag(Runtime::class);

                if ($runtime !== null) {
                    throw new RuntimeFieldInCriteriaException($accessor);
                }
            }
        }
    }
}
