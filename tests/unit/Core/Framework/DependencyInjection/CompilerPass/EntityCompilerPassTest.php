<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\Api\Acl\Front\Role\CustomerRoleDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\AttributeEntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DependencyInjection\CompilerPass\EntityCompilerPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
#[CoversClass(EntityCompilerPass::class)]
class EntityCompilerPassTest extends TestCase
{
    public function testEntityRepositoryAutowiring(): void
    {
        $container = new ContainerBuilder();

        $container->register(CustomerRoleDefinition::class, CustomerRoleDefinition::class)
            ->addTag('heyframe.entity.definition');
        $container->register(CustomerDefinition::class, CustomerDefinition::class)
            ->addTag('heyframe.entity.definition');

        $container->register(DefinitionInstanceRegistry::class, DefinitionInstanceRegistry::class)
            ->addArgument(new Reference('service_container'))
            ->addArgument([
                CustomerDefinition::ENTITY_NAME => CustomerDefinition::class,
                CustomerRoleDefinition::ENTITY_NAME => CustomerRoleDefinition::class,
            ])
            ->addArgument([
                CustomerDefinition::ENTITY_NAME => 'customer.repository',
                CustomerRoleDefinition::ENTITY_NAME => 'customer_role.repository',
            ]);

        $entityCompilerPass = new EntityCompilerPass();
        $entityCompilerPass->process($container);

        static::assertTrue($container->hasAlias('HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository $customerRepository'));
        static::assertTrue($container->hasAlias('HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository $customerRoleRepository'));
    }

    public function testEntityRepositoryAutowiringForAlreadyDefinedRepositories(): void
    {
        $container = new ContainerBuilder();

        $container
            ->register(ProductDefinition::class, ProductDefinition::class)
            ->addTag('heyframe.entity.definition')
        ;

        $container
            ->register(DefinitionInstanceRegistry::class, DefinitionInstanceRegistry::class)
            ->addArgument(new Reference('service_container'))
            ->addArgument([
                ProductDefinition::ENTITY_NAME => ProductDefinition::class,
            ])
            ->addArgument([
                ProductDefinition::ENTITY_NAME => 'product.repository',
            ])
        ;

        $container
            ->register('product.repository', EntityRepository::class)
            ->addArgument(new Reference(ProductDefinition::class))
        ;

        $entityCompilerPass = new EntityCompilerPass();
        $entityCompilerPass->process($container);

        static::assertTrue($container->hasAlias('HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository $productRepository'));
    }

    public function testEntityRepositoryAutowiringWithAttributeEntity(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('test_attribute_entity.definition', AttributeEntityDefinition::class)
            ->addTag('heyframe.entity.definition')
        ;
        $container
            ->register(DefinitionInstanceRegistry::class, DefinitionInstanceRegistry::class)
            ->addArgument(new Reference('service_container'))
            ->addArgument([
                'test_attribute_entity' => 'test_attribute_entity.definition',
            ])
            ->addArgument([
                'test_attribute_entity' => 'test_attribute_entity.repository',
            ]);

        $entityCompilerPass = new EntityCompilerPass();
        $entityCompilerPass->process($container);

        static::assertCount(0, $container->getAliases());
    }
}
