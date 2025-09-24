<?php declare(strict_types=1);

namespace HeyFrame\Administration\Service;

use HeyFrame\Administration\Framework\Search\CriteriaCollection;
use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AdminSearcher
{
    /**
     * @internal
     */
    public function __construct(private readonly DefinitionInstanceRegistry $definitionRegistry)
    {
    }

    /**
     * @return array<array-key, array{data: EntityCollection<covariant Entity>, total: int}>
     */
    public function search(CriteriaCollection $entities, Context $context): array
    {
        $result = [];

        foreach ($entities as $entityName => $criteria) {
            if (!$this->definitionRegistry->has($entityName)) {
                continue;
            }

            if (!$context->isAllowed($entityName . ':' . AclRoleDefinition::PRIVILEGE_READ)) {
                continue;
            }

            $repository = $this->definitionRegistry->getRepository($entityName);
            $collection = $repository->search($criteria, $context);

            $result[$entityName] = [
                'data' => $collection->getEntities(),
                'total' => $collection->getTotal(),
            ];
        }

        return $result;
    }
}
