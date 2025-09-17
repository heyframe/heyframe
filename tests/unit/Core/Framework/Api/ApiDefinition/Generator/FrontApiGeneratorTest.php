<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Api\ApiDefinition\Generator;

use HeyFrame\Core\Framework\Api\ApiDefinition\DefinitionService;
use HeyFrame\Core\Framework\Api\ApiDefinition\Generator\BundleSchemaPathCollection;
use HeyFrame\Core\Framework\Api\ApiDefinition\Generator\FrontApiGenerator;
use HeyFrame\Core\Framework\Api\ApiDefinition\Generator\OpenApi\OpenApiDefinitionSchemaBuilder;
use HeyFrame\Core\Framework\Api\ApiDefinition\Generator\OpenApi\OpenApiSchemaBuilder;
use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityWriteGatewayInterface;
use HeyFrame\Core\Test\Stub\DataAbstractionLayer\StaticDefinitionInstanceRegistry;
use HeyFrame\Tests\Unit\Core\Framework\Api\ApiDefinition\Generator\_fixtures\CustomBundleWithApiSchema\HeyFrameBundleWithName;
use HeyFrame\Tests\Unit\Core\Framework\Api\ApiDefinition\Generator\_fixtures\SimpleDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[CoversClass(FrontApiGenerator::class)]
class FrontApiGeneratorTest extends TestCase
{
    private FrontApiGenerator $generator;

    private FrontApiGenerator $customApiGenerator;

    private Bundle $customBundleSchemas;

    private StaticDefinitionInstanceRegistry $definitionRegistry;

    protected function setUp(): void
    {
        $this->generator = new FrontApiGenerator(
            new OpenApiSchemaBuilder('0.1.0'),
            new OpenApiDefinitionSchemaBuilder(),
            [
                'Framework' => ['path' => __DIR__ . '/_fixtures'],
            ],
            new BundleSchemaPathCollection([]),
        );

        $this->customBundleSchemas = new HeyFrameBundleWithName();
        $customBundlePathCollection = new BundleSchemaPathCollection([$this->customBundleSchemas]);

        $this->customApiGenerator = new FrontApiGenerator(
            new OpenApiSchemaBuilder('0.1.0'),
            new OpenApiDefinitionSchemaBuilder(),
            [
                'Framework' => ['path' => __DIR__ . '/_fixtures'],
            ],
            $customBundlePathCollection,
        );
        $this->definitionRegistry = new StaticDefinitionInstanceRegistry(
            [
                SimpleDefinition::class,
            ],
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EntityWriteGatewayInterface::class)
        );
    }

    public function testSchemaContainsCorrectPaths(): void
    {
        $schema = $this->generator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            null
        );
        $paths = $schema['paths'];

        static::assertArrayHasKey('post', $paths['/_action/order_delivery/{orderDeliveryId}/state/{transition}']);
    }

    public function testSchemaContainsCorrectEntities(): void
    {
        $schema = $this->generator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            null
        );
        $entities = $schema['components']['schemas'];
        static::assertArrayHasKey('Simple', $entities);
        static::assertArrayHasKey('infoConfigResponse', $entities);
    }

    public function testSchemaContainsCustomEntitiesOnly(): void
    {
        $schema = $this->customApiGenerator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            $this->customBundleSchemas->getName()
        );

        $entities = $schema['components']['schemas'];
        static::assertArrayHasKey('Presentation', $entities);
        static::assertArrayHasKey('infoConfigResponse', $entities);
        static::assertSame('Experimental', $schema['tags'][0]['name'] ?? null);
    }

    public function testSchemaContainsCustomPathsOnly(): void
    {
        $schema = $this->customApiGenerator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            $this->customBundleSchemas->getName()
        );

        $paths = $schema['paths'];

        static::assertArrayHasKey('post', $paths['/search/guided-shopping-presentation']);
        static::assertArrayNotHasKey('/_action/order_delivery/{orderDeliveryId}/state/{transition}', $paths);
    }

    public function testMergeComponentsSchemaRequiredFieldsRecursive(): void
    {
        $schema = $this->customApiGenerator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            $this->customBundleSchemas->getName()
        );

        $entities = $schema['components']['schemas'];

        static::assertArrayHasKey('Simple', $entities);
        static::assertArrayHasKey('required', $entities['Simple']);
        static::assertCount(2, $entities['Simple']['required']);
        static::assertContains('requiredField', $entities['Simple']['required']);
        static::assertContains('apiAlias', $entities['Simple']['required']);
    }

    public function testGroupsParametersParsing(): void
    {
        $schema = $this->generator->generate(
            $this->definitionRegistry->getDefinitions(),
            DefinitionService::STORE_API,
            DefinitionService::TYPE_JSON_API,
            null
        );

        // Assert that the schema does not contain 'x-parameter-groups' component
        static::assertArrayHasKey('components', $schema);
        static::assertArrayNotHasKey('x-parameter-groups', $schema['components']);

        // Check schema
        static::assertArrayHasKey('paths', $schema);
        static::assertArrayHasKey('/category', $schema['paths']);
        static::assertArrayHasKey('get', $schema['paths']['/category']);

        $operation = $schema['paths']['/category']['get'];
        static::assertArrayHasKey('parameters', $operation);
        static::assertIsArray($operation['parameters']);

        // Schema should contain all defined parameters
        $parameterNames = array_column($operation['parameters'], 'name');
        static::assertContains('sw-language-id', $parameterNames);
        static::assertContains('page', $parameterNames);
        static::assertContains('limit', $parameterNames);
        // but not left-overs of replaced parameter groups
        static::assertCount(3, $operation['parameters']);

        foreach ($operation['parameters'] as $parameter) {
            static::assertArrayHasKey('name', $parameter);
            static::assertArrayHasKey('in', $parameter);
            static::assertArrayHasKey('schema', $parameter);
        }
    }

    public function testGetSchemaThrowsUnsupportedException(): void
    {
        $this->expectExceptionObject(ApiException::unsupportedFrontApiSchemaEndpoint());

        $this->generator->getSchema($this->definitionRegistry->getDefinitions());
    }
}
