<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\AttributeMappingDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\AttributeTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntityDefinitionHasSinceTest extends TestCase
{
    use KernelTestBehaviour;

    public function testAllDefinitionsHasSince(): void
    {
        $service = static::getContainer()->get(DefinitionInstanceRegistry::class);

        $definitionsWithoutSince = [];

        foreach ($service->getDefinitions() as $definition) {
            if ($definition instanceof AttributeMappingDefinition || $definition instanceof AttributeTranslationDefinition) {
                continue;
            }

            if ($definition->since() === null) {
                $definitionsWithoutSince[] = $definition->getEntityName();
            }
        }

        static::assertCount(0, $definitionsWithoutSince, \sprintf('Following definitions does not have a since version: %s', implode(',', $definitionsWithoutSince)));
    }
}
