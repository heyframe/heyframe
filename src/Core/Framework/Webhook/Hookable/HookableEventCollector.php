<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\Hookable;

use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Entity as EntityAttribute;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Event\BusinessEventCollector;
use HeyFrame\Core\Framework\Event\BusinessEventDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Webhook\Hookable;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class HookableEventCollector implements ResetInterface
{
    private const PRIVILEGES = 'privileges';

    /**
     * @var string[][][]
     */
    private array $hookableEventNamesWithPrivileges = [];

    /**
     * @var array<string>|null
     */
    private ?array $hookableEntities = null;

    /**
     * @param iterable<EntityDefinition|Entity> $hookableEntityDefinitions
     */
    public function __construct(
        private readonly BusinessEventCollector $businessEventCollector,
        private readonly DefinitionInstanceRegistry $definitionRegistry,
        private readonly iterable $hookableEntityDefinitions
    ) {
    }

    /**
     * @return array<array<array<string>>>
     */
    public function getHookableEventNamesWithPrivileges(Context $context): array
    {
        if (!$this->hookableEventNamesWithPrivileges) {
            $this->hookableEventNamesWithPrivileges = $this->getEventNamesWithPrivileges($context);
        }

        return $this->hookableEventNamesWithPrivileges;
    }

    /**
     * @return list<string>
     */
    public function getPrivilegesFromBusinessEventDefinition(BusinessEventDefinition $businessEventDefinition): array
    {
        $privileges = [];
        foreach ($businessEventDefinition->getData() as $data) {
            if ($data['type'] !== 'entity') {
                continue;
            }

            $entityName = $this->definitionRegistry->get($data['entityClass'])->getEntityName();
            $privileges[] = $entityName . ':' . AclRoleDefinition::PRIVILEGE_READ;
        }

        return $privileges;
    }

    /**
     * @return array<string, array{privileges: list<string>}>
     */
    public function getEntityWrittenEventNamesWithPrivileges(): array
    {
        $entityWrittenEventNames = [];
        foreach ($this->getHookableEntities() as $entity) {
            $privileges = [
                self::PRIVILEGES => [$entity . ':' . AclRoleDefinition::PRIVILEGE_READ],
            ];

            $entityWrittenEventNames[$entity . '.written'] = $privileges;
            $entityWrittenEventNames[$entity . '.deleted'] = $privileges;
        }

        return $entityWrittenEventNames;
    }

    /**
     * Dynamically discovers all hookable entities by checking for services tagged with 'heyframe.entity.hookable'.
     *
     * @return array<string>
     */
    public function getHookableEntities(): array
    {
        if ($this->hookableEntities !== null) {
            return $this->hookableEntities;
        }

        $hookableEntities = [];

        foreach ($this->hookableEntityDefinitions as $definition) {
            if ($definition instanceof EntityDefinition) {
                $hookableEntities[] = $definition->getEntityName();
            } elseif ($definition instanceof Entity) {
                $reflection = new \ReflectionClass($definition::class);
                $collection = $reflection->getAttributes(EntityAttribute::class);

                if (empty($collection)) {
                    continue;
                }

                /** @var EntityAttribute $instance */
                $instance = $collection[0]->newInstance();
                $hookableEntities[] = $instance->name;
            }
        }

        $this->hookableEntities = array_unique($hookableEntities);

        return $this->hookableEntities;
    }

    public function reset(): void
    {
        $this->hookableEventNamesWithPrivileges = [];
        $this->hookableEntities = null;
    }

    /**
     * @return array<string, array{privileges: list<string>}>
     */
    private function getEventNamesWithPrivileges(Context $context): array
    {
        return array_merge(
            $this->getEntityWrittenEventNamesWithPrivileges(),
            $this->getBusinessEventNamesWithPrivileges($context),
            $this->getHookableEventNames()
        );
    }

    /**
     * @return array<string, array{privileges: list<string>}>
     */
    private function getHookableEventNames(): array
    {
        return array_reduce(array_values(
            array_map(static fn ($hookableEvent) => [$hookableEvent => [self::PRIVILEGES => []]], Hookable::HOOKABLE_EVENTS)
        ), 'array_merge', []);
    }

    /**
     * @return array<string, array{privileges: list<string>}>
     */
    private function getBusinessEventNamesWithPrivileges(Context $context): array
    {
        $response = $this->businessEventCollector->collect($context);

        return array_map(function (BusinessEventDefinition $businessEventDefinition) {
            $privileges = $this->getPrivilegesFromBusinessEventDefinition($businessEventDefinition);

            return [
                self::PRIVILEGES => $privileges,
            ];
        }, $response->getElements());
    }
}
