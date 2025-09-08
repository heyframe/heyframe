<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\CustomField;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\CustomField\CustomFieldCollection;
use HeyFrame\Core\System\CustomField\CustomFieldService;
use HeyFrame\Core\System\CustomField\CustomFieldTypes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CustomFieldServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<CustomFieldCollection>
     */
    private EntityRepository $attributeRepository;

    private CustomFieldService $attributeService;

    protected function setUp(): void
    {
        $this->attributeRepository = static::getContainer()->get('custom_field.repository');
        $this->attributeService = static::getContainer()->get(CustomFieldService::class);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function attributeFieldTestProvider(): array
    {
        return [
            [
                CustomFieldTypes::BOOL, BoolField::class,
                CustomFieldTypes::DATETIME, DateTimeField::class,
                CustomFieldTypes::FLOAT, FloatField::class,
                CustomFieldTypes::HTML, LongTextField::class,
                CustomFieldTypes::INT, IntField::class,
                CustomFieldTypes::JSON, JsonField::class,
                CustomFieldTypes::TEXT, LongTextField::class,
            ],
        ];
    }

    /**
     * @param class-string<Field> $expectedFieldClass
     */
    #[DataProvider('attributeFieldTestProvider')]
    public function testGetCustomFieldField(string $attributeType, string $expectedFieldClass): void
    {
        $attribute = [
            'name' => 'test_attr',
            'type' => $attributeType,
        ];
        $this->attributeRepository->create([$attribute], Context::createDefaultContext());

        static::assertInstanceOf(
            $expectedFieldClass,
            $this->attributeService->getCustomField('test_attr')
        );
    }

    public function testOnlyGetActive(): void
    {
        $id = Uuid::randomHex();
        $this->attributeRepository->upsert([[
            'id' => $id,
            'name' => 'test_attr',
            'active' => false,
            'type' => CustomFieldTypes::TEXT,
        ]], Context::createDefaultContext());

        $actual = $this->attributeService->getCustomField('test_attr');
        static::assertInstanceOf(JsonField::class, $actual);

        $this->attributeRepository->upsert([[
            'id' => $id,
            'active' => true,
        ]], Context::createDefaultContext());
        $this->attributeService->reset();

        $actual = $this->attributeService->getCustomField('test_attr');
        static::assertInstanceOf(LongTextField::class, $actual);
    }
}
