<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Api\Controller;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Api\Route\ApiRouteLoader;
use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\FilesystemBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseHelper\TestUser;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
#[Group('slow')]
class ApiControllerTest extends TestCase
{
    use AdminApiTestBehaviour;
    use BasicTestDataBehaviour;
    use FilesystemBehaviour;
    use KernelTestBehaviour;

    private const DELETE_VALIDATION_MESSAGE = 'Cannot delete default language id from language list of the sales channel with id "%s".';
    private const INSERT_VALIDATION_MESSAGE = 'The channel with id "%s" does not have a default channel language id in the language list.';

    private Connection $connection;

    protected function setUp(): void
    {
        $dropStatement = <<<EOF
DROP TABLE IF EXISTS `named`;
DROP TABLE IF EXISTS `named_optional_group`;
EOF;

        $namedOptionalGroupStatement = <<<EOF
CREATE TABLE `named_optional_group` (
    `id` binary(16) NOT NULL,
    `name` varchar(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY `id` (`id`)
);
EOF;

        $namedStatement = <<<EOF
CREATE TABLE `named` (
    `id` binary(16) NOT NULL,
    `name` varchar(255) NOT NULL,
    `optional_group_id` varbinary(16) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY `id` (`id`),
    CONSTRAINT `fk` FOREIGN KEY (`optional_group_id`) REFERENCES `named_optional_group` (`id`) ON DELETE SET NULL
);
EOF;
        $this->connection = static::getContainer()->get(Connection::class);
        $this->connection->executeStatement($dropStatement);
        $this->connection->executeStatement($namedOptionalGroupStatement);
        $this->connection->executeStatement($namedStatement);

        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();

        $this->connection->executeStatement('DROP TABLE IF EXISTS `named`');
        $this->connection->executeStatement('DROP TABLE IF EXISTS `named_optional_group`');

        parent::tearDown();
    }

    public function testInsert(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'stock' => 1,
            'productType' => 'type',
            'name' => $id,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 50]],
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/product', $data);
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $this->getBrowser()->jsonRequest('GET', '/api/product/' . $id);
        static::assertSame(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
    }

    public function testInsertAuthenticatedWithIntegration(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'stock' => 1,
            'name' => $id,
            'productType' => 'type',
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 50],
            ],
        ];

        $this->getBrowserAuthenticatedWithIntegration()->jsonRequest('POST', '/api/product', $data);
        $response = $this->getBrowserAuthenticatedWithIntegration()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $this->getBrowserAuthenticatedWithIntegration()->jsonRequest('GET', '/api/product/' . $id);
        static::assertSame(Response::HTTP_OK, $this->getBrowserAuthenticatedWithIntegration()->getResponse()->getStatusCode(), (string) $this->getBrowserAuthenticatedWithIntegration()->getResponse()->getContent());
    }

    public function testOneToManyInsert(): void
    {
        $id = Uuid::randomHex();

        $data = ['id' => $id, 'name' => $id];

        $this->getBrowser()->jsonRequest('POST', '/api/country', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $this->getBrowser()->jsonRequest('GET', '/api/country/' . $id);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $data = [
            'id' => $id,
            'name' => 'test_state',
            'shortCode' => 'test',
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/country/' . $id . '/states/', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country-state/' . $id, $response->headers->get('Location'));

        $this->getBrowser()->jsonRequest('GET', '/api/country/' . $id . '/states/');
        $response = $this->getBrowser()->getResponse();
        $responseData = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertIsArray($responseData);
        static::assertArrayHasKey('data', $responseData);
        static::assertCount(1, $responseData['data'], \sprintf('Expected country %s has only one state', $id));

        static::assertArrayHasKey('meta', $responseData);
        static::assertArrayHasKey('total', $responseData['meta']);
        static::assertSame(1, $responseData['meta']['total']);

        static::assertSame($data['name'], $responseData['data'][0]['attributes']['name']);
        static::assertSame($data['shortCode'], $responseData['data'][0]['attributes']['shortCode']);
    }

    public function testOneToManyInsertWithoutPermission(): void
    {
        $id = Uuid::randomHex();

        $data = ['id' => $id, 'name' => $id];
        $browser = $this->getBrowser();
        $connection = $this->getBrowser()->getContainer()->get(Connection::class);
        $user = TestUser::createNewTestUser($connection, ['country:create', 'country:read']);
        $admin = TestUser::getAdmin();

        $user->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/country', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($browser, 'country', $id);

        $data = [
            'id' => $id,
            'name' => 'test_state',
            'shortCode' => 'test',
        ];

        $browser->jsonRequest('POST', '/api/country/' . $id . '/states/', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());

        $admin->authorizeBrowser($browser);

        $this->assertEntityNotExists($browser, 'country-state', $id);
    }

    public function testTranslatedPropertiesWritableWithParentDefinitionPermissions(): void
    {
        $id = Uuid::randomHex();

        $data = ['id' => $id, 'name' => $id];

        $this->getBrowser()->jsonRequest('POST', '/api/country', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $browser = $this->getBrowser();
        $connection = $this->getBrowser()->getContainer()->get(Connection::class);
        $user = TestUser::createNewTestUser($connection, ['country:update', 'country:read']);

        $user->authorizeBrowser($browser);

        $data = ['name' => 'not in system language'];
        $languageId = $this->getNonSystemLanguageId();
        $browser->setServerParameter('HTTP_sw-language-id', $languageId);

        $browser->jsonRequest(
            'PATCH',
            '/api/country/' . $id,
            $data
        );

        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($browser, 'country', $id);
    }

    public function testCreateAndDeleteWithPermissions(): void
    {
        $connection = static::getContainer()->get(Connection::class);

        $user = TestUser::createNewTestUser($connection, ['product:create', 'product:delete']);

        $browser = $this->getBrowser();
        $user->authorizeBrowser($browser);

        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'name' => 'test',
            'productNumber' => Uuid::randomHex(),
            'productType' => 'type',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 15],
            ],
        ];

        $browser->jsonRequest('POST', '/api/product', $data);
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());

        $browser->jsonRequest('DELETE', '/api/product/' . $id);
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
    }

    public function testTranslatedPropertiesNotWritableWithoutParentDefinitionPermissions(): void
    {
        $id = Uuid::randomHex();

        $data = ['id' => $id, 'name' => $id];

        $this->getBrowser()->jsonRequest('POST', '/api/country', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $browser = $this->getBrowser();
        $connection = $this->getBrowser()->getContainer()->get(Connection::class);
        $user = TestUser::createNewTestUser($connection, ['country:create', 'country:read']);

        $user->authorizeBrowser($browser);

        $data = ['name' => 'not in system language'];
        $languageId = $this->getNonSystemLanguageId();
        $browser->setServerParameter('HTTP_sw-language-id', $languageId);

        $browser->jsonRequest(
            'PATCH',
            '/api/country/' . $id,
            $data
        );

        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());
    }

    public function testManyToManyInsert(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'stock' => 1,
            'productType' => 'type',
            'name' => $id,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 50, 'net' => 25, 'linked' => false]],
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/product', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $data = [
            'id' => $id,
            'name' => 'Category - 1',
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/product/' . $id . '/tags/', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/tag/' . $id, $response->headers->get('Location'));

        $this->getBrowser()->jsonRequest('GET', '/api/product/' . $id . '/tags/');
        $responseData = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        static::assertArrayHasKey('data', $responseData);
        static::assertCount(1, $responseData['data']);
        static::assertArrayHasKey('attributes', $responseData['data'][0]);
        static::assertArrayHasKey('name', $responseData['data'][0]['attributes'], print_r($responseData, true));
        static::assertSame($data['name'], $responseData['data'][0]['attributes']['name']);
        static::assertSame($data['id'], $responseData['data'][0]['id']);
    }

    public function testManyToManyInsertWithoutPermission(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => $id,
            'productType' => 'type',
            'productNumber' => '00',
            'stock' => 12,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 15]],
        ];

        $browser = $this->getBrowser();

        $connection = $this->getBrowser()->getContainer()->get(Connection::class);
        $user = TestUser::createNewTestUser(
            $connection,
            ['product:create', 'product:read', 'tag:create', 'tag:read', 'product_price:create', 'product_price:read', 'version_commit_data:create', ':version_commitcreate']
        );
        $admin = TestUser::getAdmin();

        $user->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/product', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $data = [
            'id' => $id,
            'name' => 'Category - 1',
        ];

        $browser->jsonRequest('POST', '/api/product/' . $id . '/tags/', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());

        $admin->authorizeBrowser($browser);

        $this->assertEntityNotExists($browser, 'category', $id);

        $browser->jsonRequest('GET', '/api/product/' . $id . '/tags/');
        $responseData = json_decode((string) $browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(Response::HTTP_OK, $browser->getResponse()->getStatusCode());

        static::assertArrayHasKey('data', $responseData);
        static::assertCount(0, $responseData['data']);
    }

    public function testDelete(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'productType' => Uuid::randomHex(),
            'stock' => 1,
            'name' => $id,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 50, 'net' => 25, 'linked' => false]],
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/product', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($this->getBrowser(), 'product', $id);

        $this->getBrowser()->jsonRequest('DELETE', '/api/product/' . $id);
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());

        $this->assertEntityNotExists($this->getBrowser(), 'product', $id);
    }

    public function testDeleteVersion(): void
    {
        $id = Uuid::randomHex();
        $browser = $this->getBrowser();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'productType' => 'type2',
            'stock' => 1,
            'name' => $id,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 50]],
        ];

        $browser->jsonRequest('POST', '/api/product', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/product/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($browser, 'product', $id);

        $browser->jsonRequest('POST', '/api/_action/version/product/' . $id);
        $response = json_decode((string) $browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(Response::HTTP_OK, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertIsArray($response);
        static::assertArrayHasKey('versionId', $response);
        static::assertArrayHasKey('versionName', $response);
        static::assertArrayHasKey('id', $response);
        static::assertArrayHasKey('entity', $response);
        static::assertTrue(Uuid::isValid($response['versionId']));
        $versionId = $response['versionId'];

        $browser->jsonRequest('POST', '/api/_action/version/' . $response['versionId'] . '/product/' . $id);
        $response = json_decode((string) $browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(Response::HTTP_OK, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertEmpty($response);

        $this->assertEntityExists($browser, 'product', $id);

        /** @var EntityRepository<ProductCollection> $productRepo */
        $productRepo = static::getContainer()->get(ProductDefinition::ENTITY_NAME . '.repository');
        $criteria = new Criteria([$id]);
        $criteria->addFilter(
            new EqualsFilter('versionId', $versionId)
        );

        static::assertCount(0, $productRepo->search($criteria, Context::createDefaultContext()));
    }

    public function testDeleteVersionWithLiveVersion(): void
    {
        $id = Uuid::randomHex();
        $browser = $this->getBrowser();

        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'productType' => Uuid::randomHex(),
            'stock' => 1,
            'name' => $id,
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 50, 'net' => 25, 'linked' => false]],
        ];

        $browser->jsonRequest('POST', '/api/product', $data);

        $browser->jsonRequest('POST', '/api/_action/version/' . Defaults::LIVE_VERSION . '/product/' . $id);

        $repo = static::getContainer()->get(ProductDefinition::ENTITY_NAME . '.repository');
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter('versionId', Defaults::LIVE_VERSION));

        static::assertNotNull($repo->search($criteria, Context::createDefaultContext())->getEntities()->first());

        $response = $browser->getResponse();

        static::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode(), (string) $response->getContent());

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(ApiException::deleteLiveVersion()->getErrorCode(), $content['errors'][0]['code']);
    }

    public function testDeleteWithoutPermission(): void
    {
        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'name' => 'test tag',
        ];

        $browser = $this->getBrowser();

        TestUser::createNewTestUser(
            $browser->getContainer()->get(Connection::class),
            ['tag:read', 'tag:create']
        )->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/tag', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $browser->jsonRequest('DELETE', '/api/tag/' . $id, ['name' => 'foo']);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());

        $this->assertEntityExists($browser, 'tag', $id);
    }

    public function testDeleteOneToMany(): void
    {
        $id = Uuid::randomHex();
        $stateId = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => $id,
            'states' => [
                ['id' => $stateId, 'shortCode' => 'test', 'name' => 'test'],
            ],
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/country', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($this->getBrowser(), 'country', $id);
        $this->assertEntityExists($this->getBrowser(), 'country-state', $stateId);

        $this->getBrowser()->jsonRequest('DELETE', '/api/country/' . $id . '/states/' . $stateId, $data);
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());

        $this->assertEntityExists($this->getBrowser(), 'country', $id);
        $this->assertEntityNotExists($this->getBrowser(), 'country-state', $stateId);
    }

    public function testDeleteOneToManyWithoutPermission(): void
    {
        $id = Uuid::randomHex();
        $stateId = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => $id,
            'states' => [
                ['id' => $stateId, 'shortCode' => 'test', 'name' => 'test'],
            ],
        ];

        $browser = $this->getBrowser();

        TestUser::createNewTestUser(
            $browser->getContainer()->get(Connection::class),
            ['country_state:create', 'country_state:read', 'country:create', 'country:read']
        )->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/country', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($browser, 'country', $id);
        $this->assertEntityExists($browser, 'country-state', $stateId);

        $browser->jsonRequest('DELETE', '/api/country/' . $id . '/states/' . $stateId, $data);
        static::assertSame(Response::HTTP_FORBIDDEN, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());

        $this->assertEntityExists($browser, 'country', $id);
        $this->assertEntityExists($browser, 'country-state', $stateId);
    }

    public function testDeleteManyToOne(): void
    {
        $id = Uuid::randomHex();
        $groupId = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => 'Test product',
            'optionalGroup' => [
                'id' => $groupId,
                'name' => 'Gramm',
            ],
        ];
        $this->getBrowser()->jsonRequest('POST', '/api/named', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/named/' . $id, $response->headers->get('Location'));

        $this->assertEntityExists($this->getBrowser(), 'named', $id);
        $this->assertEntityExists($this->getBrowser(), 'named-optional-group', $groupId);

        $this->getBrowser()->jsonRequest('DELETE', '/api/named/' . $id . '/optional-group/' . $groupId);
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());

        $this->assertEntityExists($this->getBrowser(), 'named', $id);
        $this->assertEntityNotExists($this->getBrowser(), 'named-optional-group', $groupId);
    }

    public function testNestedSearchOnOneToManyWithoutPermissionOnChild(): void
    {
        $id = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => $id,
            'states' => [
                [
                    'name' => 'test_state',
                    'shortCode' => 'test',
                ],
                [
                    'name' => 'test_state_2',
                    'shortCode' => 'test 2',
                ],
            ],
        ];

        $browser = $this->getBrowser();
        $browser->jsonRequest('POST', '/api/country', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $browser->getResponse()->getStatusCode(), (string) $browser->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/country/' . $id, $response->headers->get('Location'));

        TestUser::createNewTestUser(
            $browser->getContainer()->get(Connection::class),
            ['country:list']
        )->authorizeBrowser($browser);

        $filter = [
            'filter' => [
                [
                    'type' => 'equals',
                    'field' => 'country_state.name',
                    'value' => 'test_state',
                ],
            ],
        ];

        $path = '/api/search/country/' . $id . '/states';
        $browser->jsonRequest('POST', $path, $filter);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());
    }

    public function testParentChildLocation(): void
    {
        $childId = Uuid::randomHex();
        $parentId = Uuid::randomHex();

        $data = [
            'id' => $childId,
            'name' => 'Child Language',
            'localeId' => $this->getLocaleIdOfSystemLanguage(),
            'active' => true,
            'parent' => [
                'id' => $parentId,
                'name' => 'Parent Language',
                'locale' => [
                    'code' => 'x-tst_' . Uuid::randomHex(),
                    'name' => 'test name',
                    'territory' => 'test territory',
                ],
                'translationCode' => [
                    'code' => 'x-tst_' . Uuid::randomHex(),
                    'name' => 'test name',
                    'territory' => 'test territory',
                ],
                'active' => true,
            ],
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/language', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $this->getBrowser()->getResponse()->getStatusCode(), (string) $this->getBrowser()->getResponse()->getContent());
        static::assertNotEmpty($response->headers->get('Location'));
        static::assertSame('http://localhost/api/language/' . $childId, $response->headers->get('Location'));
    }

    public function testCloneEntity(): void
    {
        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'name' => 'test tag clone',
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/tag', $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $this->getBrowser()->jsonRequest('GET', '/api/tag/' . $id);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $tax = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('data', $tax);
        static::assertSame($id, $tax['data']['id']);

        $this->getBrowser()->jsonRequest('POST', '/api/_action/clone/tag/' . $id, $data);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $data = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('id', $data);
        static::assertNotSame($id, $data['id']);

        $newId = $data['id'];
        $this->getBrowser()->jsonRequest('GET', '/api/tag/' . $newId);
        $response = $this->getBrowser()->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $data = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame('test tag clone', $data['data']['attributes']['name']);
    }

    public function testCloneEntityWithoutPermission(): void
    {
        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'name' => 'test tag clone',
        ];

        $browser = $this->getBrowser();
        $browser->jsonRequest('POST', '/api/tag', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $connection = $browser->getContainer()->get(Connection::class);
        TestUser::createNewTestUser(
            $connection,
            ['tag:read']
        )->authorizeBrowser($browser);

        $browser->jsonRequest('GET', '/api/tag/' . $id);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $tag = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('data', $tag);
        static::assertSame($id, $tag['data']['id']);

        $browser->jsonRequest('POST', '/api/_action/clone/tag/' . $id, $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());
    }

    public function testUpdateWithoutPermission(): void
    {
        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'name' => 'test tag',
        ];
        $browser = $this->getBrowser();
        TestUser::createNewTestUser(
            $browser->getContainer()->get(Connection::class),
            ['tag:read', 'tag:create']
        )->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/tag', $data);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $browser->jsonRequest('PATCH', '/api/tag/' . $id, ['name' => 'foo']);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());

        $browser->jsonRequest('GET', '/api/tag/' . $id);
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $tag = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('data', $tag);
        static::assertSame('test tag', $tag['data']['attributes']['name']);
    }

    public function testAggregationWorksForAdminStartPage(): void
    {
        $data = [
            'page' => 1,
            'limit' => 10,
            'filter' => [
                [
                    'type' => 'range',
                    'field' => 'orderDate',
                    'parameters' => [
                        'gte' => '2020-05-16',
                    ],
                ],
            ],
            'aggregations' => [
                [
                    'type' => 'histogram',
                    'name' => 'order_count_month',
                    'field' => 'orderDateTime',
                    'interval' => 'day',
                    'format' => null,
                    'aggregation' => [
                        'type' => 'sum',
                        'name' => 'totalAmount',
                        'field' => 'amountTotal',
                    ],
                ],
            ],
            'total-count-mode' => 1,
        ];

        $this->getBrowser()->jsonRequest('POST', '/api/search/order', $data);
        static::assertSame(Response::HTTP_OK, $this->getBrowser()->getResponse()->getStatusCode());

        $response = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('aggregations', $response);
        static::assertArrayHasKey('order_count_month', $response['aggregations']);
    }

    public function testAccessDeniedAfterChangingUserPassword(): void
    {
        $browser = $this->getBrowser();

        $connection = $browser->getContainer()->get(Connection::class);
        $admin = TestUser::createNewTestUser($connection, ['product:read']);

        $admin->authorizeBrowser($browser);

        $browser->jsonRequest('POST', '/api/search/product');
        $response = $browser->getResponse();
        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $userRepository = static::getContainer()->get('user.repository');

        // Change user password
        $userRepository->update([[
            'id' => $admin->getUserId(),
            'password' => Uuid::randomHex(),
        ]], Context::createDefaultContext());

        $browser->jsonRequest('POST', '/api/search/product');
        $response = $browser->getResponse();

        static::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), (string) $response->getContent());
        $jsonResponse = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame('Access token is expired', $jsonResponse['errors'][0]['detail']);
    }

    public function testPreventCreationOfChannelWithoutDefaultChannelLanguage(): void
    {
        $channelId = Uuid::randomHex();
        $data = $this->getChannelData($channelId, $this->getNonSystemLanguageId());

        $browser = $this->getBrowser();
        $browser->jsonRequest('POST', '/api/channel/', $data, $data);

        $response = $browser->getResponse();
        static::assertSame(400, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $error = $content['errors'][0];

        static::assertSame(\sprintf(self::INSERT_VALIDATION_MESSAGE, $channelId), $error['detail']);
    }

    public function testPreventDeletionOfDefaultChannelLanguageFromLanguageList(): void
    {
        $channelId = Uuid::randomHex();
        $this->createChannel($channelId);

        $browser = $this->getBrowser();
        $browser->jsonRequest('DELETE', '/api/channel/' . $channelId . '/languages/' . Defaults::LANGUAGE_SYSTEM);

        $response = $browser->getResponse();
        static::assertSame(400, $response->getStatusCode());

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $error = $content['errors'][0];

        static::assertSame(\sprintf(self::DELETE_VALIDATION_MESSAGE, $channelId), $error['detail']);
    }

    #[DataProvider('provideEntityName')]
    public function testMustMatchEntityNameRegex(bool $match, string $entityName, string $routeName): void
    {
        $router = static::getContainer()->get(RouterInterface::class);
        $routes = $router->getRouteCollection();

        $urlGenerator = new UrlGenerator(
            $routes,
            $router->getContext(),
        );
        $urlGenerator->setStrictRequirements(true);

        if (!$match) {
            static::expectException(InvalidParameterException::class);
            static::expectExceptionMessage('Parameter "entity" for route "' . $routeName . '" must match "[0-9a-zA-Z-]+" ("' . $entityName . '" given) to generate a corresponding URL.');
        }

        $url = $urlGenerator->generate($routeName, [
            'id' => Uuid::randomHex(),
            'entity' => $entityName,
            'versionId' => Uuid::randomHex(),
            'entityId' => Uuid::randomHex(),
        ], 0);

        if (!$match) {
            return;
        }

        static::assertStringContainsString($entityName, $url);
    }

    public static function provideEntityName(): \Generator
    {
        yield 'not match / clone' => [false, 'named!', 'api.clone'];
        yield 'match / clone' => [true, 'named', 'api.clone'];

        yield 'not match / create version' => [false, 'named!345!@#', 'api.createVersion'];
        yield 'match / create version' => [true, 'named-123', 'api.createVersion'];

        yield 'not match / merge version' => [false, 'named@#$@8678', 'api.mergeVersion'];
        yield 'match / merge version' => [true, 'b2b-named-123', 'api.mergeVersion'];

        yield 'not match / delete version' => [false, 'named_12313', 'api.deleteVersion'];
        yield 'match / delete version' => [true, 'named-12313', 'api.deleteVersion'];
    }

    public function testLoader(): void
    {
        $definitionRegistry = static::getContainer()->get(DefinitionInstanceRegistry::class);
        $loader = new ApiRouteLoader($definitionRegistry);

        $routers = $loader->load('test');

        $apiDetail = $routers->all()['api._test_lock.detail'];
        $apiList = $routers->all()['api._test_lock.list'];

        static::assertInstanceOf(Route::class, $apiDetail);
        static::assertInstanceOf(Route::class, $apiList);

        static::assertSame('[0-9a-f]{32}(\/(extensions\/)?[0-9a-zA-Z-]+\/[0-9a-f]{32})*\/?', $apiDetail->getRequirements()['path']);
        static::assertSame('(\/[0-9a-f]{32}\/(extensions\/)?[0-9a-zA-Z-]+)*\/?', $apiList->getRequirements()['path']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getChannelData(string $channelId, string $languageId = Defaults::LANGUAGE_SYSTEM): array
    {
        return [
            'id' => $channelId,
            'accessKey' => AccessKeyHelper::generateAccessKey('channel'),
            'typeId' => Defaults::CHANNEL_TYPE_API,
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'currencyId' => Defaults::CURRENCY,
            'currencyVersionId' => Defaults::LIVE_VERSION,
            'paymentMethodId' => $this->getValidPaymentMethodId(),
            'paymentMethodVersionId' => Defaults::LIVE_VERSION,
            'navigationVersionId' => Defaults::LIVE_VERSION,
            'countryId' => $this->getValidCountryId(),
            'navigationCategoryId' => $this->getValidCategoryId(),
            'countryVersionId' => Defaults::LIVE_VERSION,
            'currencies' => [['id' => Defaults::CURRENCY]],
            'languages' => [['id' => $languageId]],
            'paymentMethods' => [['id' => $this->getValidPaymentMethodId()]],
            'countries' => [['id' => $this->getValidCountryId()]],
            'name' => 'first channel',
            'customerGroupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
        ];
    }

    private function createChannel(string $id): void
    {
        $data = $this->getChannelData($id);

        static::getContainer()->get('channel.repository')->create([$data], Context::createDefaultContext());
    }

    private function getNonSystemLanguageId(): string
    {
        /** @var EntityRepository<LanguageCollection> $languageRepository */
        $languageRepository = static::getContainer()->get('language.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new NotFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('id', Defaults::LANGUAGE_SYSTEM),
            ]
        ));
        $criteria->setLimit(1);

        $id = $languageRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
        static::assertIsString($id);

        return $id;
    }
}
