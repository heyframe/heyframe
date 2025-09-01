<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Api;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ApiAliasTest extends TestCase
{
    use KernelTestBehaviour;

    public function testUniqueAliases(): void
    {
        $classLoader = KernelLifecycleManager::getClassLoader();
        /** @var list<class-string> $classes */
        $classes = array_keys($classLoader->getClassMap());

        if (!\array_key_exists(Kernel::class, $classes)) {
            static::markTestSkipped('This test does not work if the root package is heyframe/platform');
        }

        $entities = self::getContainer()->get(DefinitionInstanceRegistry::class)
            ->getDefinitions();

        $aliases = array_keys($entities);
        $aliases = array_flip($aliases);

        $count = \count($aliases);

        foreach ($classes as $class) {
            $parts = explode('\\', $class);
            if ($parts[0] !== 'HeyFrame') {
                continue;
            }

            /** @phpstan-ignore argument.unresolvableType (class-string could not be resolved at this point) */
            $reflector = new \ReflectionClass($class);

            if (!$reflector->isSubclassOf(Struct::class)) {
                continue;
            }

            if ($reflector->isAbstract() || $reflector->isInterface() || $reflector->isTrait()) {
                continue;
            }

            /** @phpstan-ignore method.alreadyNarrowedType (PHPStan could not detect the condition correctly, due to the ignored error above) */
            if ($reflector->isSubclassOf(AggregationResult::class)) {
                continue;
            }

            $instance = $reflector->newInstanceWithoutConstructor();

            if ($instance instanceof Entity) {
                continue;
            }

            if (!$instance instanceof Struct) {
                continue;
            }

            $alias = $instance->getApiAlias();

            if ($alias === 'aggregation-' || $alias === 'dal_entity_search_result') {
                continue;
            }

            static::assertArrayNotHasKey($alias, $aliases);
            $aliases[$alias] = true;
        }

        static::assertTrue(\count($aliases) > $count, 'Validated only entities, please check registered classes of class loader');
    }
}
