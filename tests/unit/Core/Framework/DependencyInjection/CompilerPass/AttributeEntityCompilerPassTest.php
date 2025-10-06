<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\ManyToMany;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\OnDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Translations;
use HeyFrame\Core\Framework\DataAbstractionLayer\AttributeEntityCompiler;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use HeyFrame\Core\Framework\DependencyInjection\CompilerPass\AttributeEntityCompilerPass;
use HeyFrame\Core\Framework\Struct\ArrayEntity;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
#[CoversClass(AttributeEntityCompilerPass::class)]
class AttributeEntityCompilerPassTest extends TestCase
{
    public function testAttributeEntityDefinitionHasTag(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(DefinitionInstanceRegistry::class, new Definition(DefinitionInstanceRegistry::class));
        $container->setDefinition(ChannelDefinitionInstanceRegistry::class, new Definition(ChannelDefinitionInstanceRegistry::class));

        $attributeEntity = new Definition(TestAttributeEntity::class);
        $attributeEntity->setPublic(true);
        $attributeEntity->addTag('heyframe.entity');
        $container->setDefinition(TestAttributeEntity::class, $attributeEntity);

        $compiler = new AttributeEntityCompiler();

        $compilerPass = new AttributeEntityCompilerPass($compiler);
        $compilerPass->process($container);

        static::assertTrue($container->hasDefinition('test_attribute_entity.definition'));
        static::assertTrue($container->getDefinition('test_attribute_entity.definition')->hasTag('heyframe.entity.definition'));

        static::assertTrue($container->hasDefinition('test_attribute_entity_translation.definition'));
        static::assertTrue($container->getDefinition('test_attribute_entity_translation.definition')->hasTag('heyframe.entity.definition'));

        static::assertTrue($container->hasDefinition('customer_test_attribute_entity.definition'));
        static::assertTrue($container->getDefinition('customer_test_attribute_entity.definition')->hasTag('heyframe.entity.definition'));
    }
}

/**
 * @internal
 */
#[Entity('test_attribute_entity')]
class TestAttributeEntity extends EntityStruct
{
    #[PrimaryKey]
    #[Field(type: FieldType::UUID)]
    public string $id;

    #[Required]
    #[Field(type: FieldType::STRING, translated: true)]
    public string $name;

    /**
     * @var array<string, ArrayEntity>|null
     */
    #[Translations]
    public ?array $translations = null;

    /**
     * @var array<string, CustomerEntity>|null
     */
    #[ManyToMany(entity: 'customer', onDelete: OnDelete::SET_NULL)]
    public ?array $customers = null;
}
