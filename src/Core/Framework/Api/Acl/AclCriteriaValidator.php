<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl;

use HeyFrame\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\FrameworkException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Package('framework')]
class AclCriteriaValidator
{
    /**
     * @internal
     */
    public function __construct(private readonly DefinitionInstanceRegistry $registry)
    {
    }

    /**
     * @throws AccessDeniedHttpException
     *
     * @return array<string>
     */
    public function validate(string $entity, Criteria $criteria, Context $context): array
    {
        $privilege = $entity . ':' . AclRoleDefinition::PRIVILEGE_READ;

        $missing = [];

        if (!$context->isAllowed($privilege)) {
            $missing[] = $privilege;
        }

        $definition = $this->registry->getByEntityName($entity);

        foreach ($criteria->getAssociations() as $field => $nested) {
            $association = $definition->getField($field);

            if (!$association instanceof AssociationField) {
                throw FrameworkException::associationNotFound($field);
            }

            $reference = $association->getReferenceDefinition()->getEntityName();
            if ($association instanceof ManyToManyAssociationField) {
                $reference = $association->getToManyReferenceDefinition()->getEntityName();
            }

            $missing = array_merge($missing, $this->validate($reference, $nested, $context));
        }

        foreach ($criteria->getAllFields() as $accessor) {
            $fields = EntityDefinitionQueryHelper::getFieldsOfAccessor($definition, $accessor);

            foreach ($fields as $field) {
                if (!$field instanceof AssociationField) {
                    continue;
                }

                $reference = $field->getReferenceDefinition()->getEntityName();
                if ($field instanceof ManyToManyAssociationField) {
                    $reference = $field->getToManyReferenceDefinition()->getEntityName();
                }

                $privilege = $reference . ':' . AclRoleDefinition::PRIVILEGE_READ;

                if (!$context->isAllowed($privilege)) {
                    $missing[] = $privilege;
                }
            }
        }

        return array_unique(array_filter($missing));
    }
}
