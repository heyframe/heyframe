<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Api;

use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use HeyFrame\Core\Framework\Api\Exception\ExpectationFailedException;
use HeyFrame\Core\Framework\Api\Exception\InvalidChannelIdException;
use HeyFrame\Core\Framework\Api\Exception\InvalidSyncOperationException;
use HeyFrame\Core\Framework\Api\Exception\InvalidVersionNameException;
use HeyFrame\Core\Framework\Api\Exception\LiveVersionDeleteException;
use HeyFrame\Core\Framework\Api\Exception\MissingPrivilegeException;
use HeyFrame\Core\Framework\Api\Exception\NoEntityClonedException;
use HeyFrame\Core\Framework\Api\Exception\ResourceNotFoundException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\MissingReverseAssociation;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\Exception\ChannelNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(ApiException::class)]
class ApiExceptionTest extends TestCase
{
    public function testInvalidSyncCriteriaException(): void
    {
        $exception = ApiException::invalidSyncCriteriaException('operationKey');

        static::assertSame(ApiException::API_INVALID_SYNC_CRITERIA_EXCEPTION, $exception->getErrorCode());
        static::assertSame('Sync operation operationKey, with action "delete", requires a criteria with at least one filter and can only be applied for mapping entities', $exception->getMessage());
    }

    public function testInvalidSyncOperationException(): void
    {
        $exception = ApiException::invalidSyncOperationException('message');

        static::assertSame(ApiException::API_INVALID_SYNC_OPERATION_EXCEPTION, $exception->getErrorCode());
        static::assertSame('message', $exception->getMessage());
    }

    public function testResolverNotFoundException(): void
    {
        $exception = ApiException::resolverNotFoundException('name');

        static::assertSame(ApiException::API_RESOLVER_NOT_FOUND_EXCEPTION, $exception->getErrorCode());
        static::assertSame('Foreign key resolver for key name not found', $exception->getMessage());
    }

    public function testUnsupportedAssociation(): void
    {
        $exception = ApiException::unsupportedAssociation('name');

        static::assertSame(ApiException::API_UNSUPPORTED_ASSOCIATION_FIELD, $exception->getErrorCode());
        static::assertSame('Unsupported association for field name', $exception->getMessage());
    }

    public function testMissingPrivileges(): void
    {
        $exception = ApiException::missingPrivileges(['read', 'write']);

        static::assertInstanceOf(MissingPrivilegeException::class, $exception);
    }

    public function testMissingReverseAssociation(): void
    {
        $exception = ApiException::missingReverseAssociation('order', 'customer');

        static::assertInstanceOf(MissingReverseAssociation::class, $exception);
    }

    public function testUnsupportedMediaType(): void
    {
        $exception = ApiException::unsupportedMediaType('jpeg');

        static::assertInstanceOf(UnsupportedMediaTypeHttpException::class, $exception);
        static::assertSame('The Content-Type "jpeg" is unsupported.', $exception->getMessage());
    }

    public function testNotExistingRelation(): void
    {
        $exception = ApiException::notExistingRelation('demo');

        static::assertSame(ApiException::API_NOT_EXISTING_RELATION_EXCEPTION, $exception->getErrorCode());
        static::assertSame('Resource at path "demo" is not an existing relation.', $exception->getMessage());
    }

    public function testBadRequest(): void
    {
        $exception = ApiException::badRequest('Bad request');

        static::assertInstanceOf(BadRequestHttpException::class, $exception);
        static::assertSame('Bad request', $exception->getMessage());
    }

    public function testMethodNotAllowed(): void
    {
        $exception = ApiException::methodNotAllowed(['GET'], 'Get only');

        static::assertInstanceOf(MethodNotAllowedHttpException::class, $exception);
        static::assertSame('Get only', $exception->getMessage());
    }

    public function testUnauthorized(): void
    {
        $exception = ApiException::unauthorized('challenge', 'Message');

        static::assertInstanceOf(UnauthorizedHttpException::class, $exception);
        static::assertSame('Message', $exception->getMessage());
    }

    public function testNoEntityCloned(): void
    {
        $exception = ApiException::noEntityCloned('order', '1234');

        static::assertInstanceOf(NoEntityClonedException::class, $exception);
        static::assertSame('Could not clone entity order with id 1234.', $exception->getMessage());
    }

    public function testExpectationFailed(): void
    {
        $exception = ApiException::expectationFailed([]);

        static::assertInstanceOf(ExpectationFailedException::class, $exception);
        static::assertSame('API Expectations failed', $exception->getMessage());
    }

    public function testInvalidSyncOperation(): void
    {
        $exception = ApiException::invalidSyncOperation('Message');

        static::assertInstanceOf(InvalidSyncOperationException::class, $exception);
        static::assertSame('Message', $exception->getMessage());
    }

    public function testInvalidChannelId(): void
    {
        $exception = ApiException::invalidChannelId('123');

        static::assertInstanceOf(InvalidChannelIdException::class, $exception);
        static::assertSame('The provided channelId "123" is invalid.', $exception->getMessage());
    }

    public function testInvalidVersionName(): void
    {
        $exception = ApiException::invalidVersionName();

        static::assertInstanceOf(InvalidVersionNameException::class, $exception);
    }

    public function testChannelNotFound(): void
    {
        $exception = ApiException::channelNotFound();

        static::assertInstanceOf(ChannelNotFoundException::class, $exception);
    }

    public function testDeleteLiveVersion(): void
    {
        $exception = ApiException::deleteLiveVersion();

        static::assertInstanceOf(LiveVersionDeleteException::class, $exception);
    }

    public function testResourceNotFound(): void
    {
        $exception = ApiException::resourceNotFound('order', []);

        static::assertInstanceOf(ResourceNotFoundException::class, $exception);
    }

    public function testUnsupportedOperation(): void
    {
        $exception = ApiException::unsupportedOperation('invalid_operation');

        static::assertSame(ApiException::API_UNSUPPORTED_OPERATION_EXCEPTION, $exception->getErrorCode());
        static::assertSame('Unsupported invalid_operation operation.', $exception->getMessage());
    }

    public function testInvalidVersionId(): void
    {
        $exception = ApiException::invalidVersionId('invalid_version_id');

        static::assertSame(ApiException::API_INVALID_VERSION_ID, $exception->getErrorCode());
        static::assertSame('versionId invalid_version_id is not a valid uuid.', $exception->getMessage());
    }

    public function testInvalidApiType(): void
    {
        $exception = ApiException::invalidApiType('invalid_type');

        static::assertSame(ApiException::API_TYPE_PARAMETER_INVALID, $exception->getErrorCode());
        static::assertSame('Parameter type invalid_type is invalid.', $exception->getMessage());
    }

    public function testAppIdParameterIsMissing(): void
    {
        $exception = ApiException::appIdParameterIsMissing();

        static::assertSame(ApiException::API_APP_ID_PARAMETER_IS_MISSING, $exception->getErrorCode());
        static::assertSame('Parameter "id" is missing.', $exception->getMessage());
    }

    public function testChannelIdParameterIsMissing(): void
    {
        $exception = ApiException::channelIdParameterIsMissing();

        static::assertSame(ApiException::API_CHANNEL_ID_PARAMETER_IS_MISSING, $exception->getErrorCode());
        static::assertSame('Parameter "channelId" is missing.', $exception->getMessage());
    }

    public function testCustomerIdParameterIsMissing(): void
    {
        $exception = ApiException::customerIdParameterIsMissing();

        static::assertSame(ApiException::API_CUSTOMER_ID_PARAMETER_IS_MISSING, $exception->getErrorCode());
        static::assertSame('Parameter "customerId" is missing.', $exception->getMessage());
    }

    public function testShippingCostsParameterIsMissing(): void
    {
        $exception = ApiException::shippingCostsParameterIsMissing();

        static::assertSame(ApiException::API_SHIPPING_COSTS_PARAMETER_IS_MISSING, $exception->getErrorCode());
        static::assertSame('Parameter "shippingCosts" is missing.', $exception->getMessage());
    }

    public function testUnableGenerateBundle(): void
    {
        $exception = ApiException::unableGenerateBundle('bundleName');

        static::assertSame(ApiException::API_UNABLE_GENERATE_BUNDLE, $exception->getErrorCode());
        static::assertSame('Unable to generate bundle directory for bundle "bundleName".', $exception->getMessage());
    }

    public function testSchemaDefinitionNotReadable(): void
    {
        $exception = ApiException::schemaDefinitionNotReadable('file');

        static::assertSame(ApiException::API_SCHEMA_DEFINITION_NOT_READABLE, $exception->getErrorCode());
    }

    public function testInvalidSchemaDefinitions(): void
    {
        $exception = ApiException::invalidSchemaDefinitions('file', new \JsonException());

        static::assertSame(ApiException::API_INVALID_SCHEMA_DEFINITION_EXCEPTION, $exception->getErrorCode());
    }

    public function testInvalidAccessKey(): void
    {
        $exception = ApiException::invalidAccessKey();

        static::assertSame(ApiException::API_INVALID_ACCESS_KEY_EXCEPTION, $exception->getErrorCode());
    }

    public function testInvalidAccessKeyIdentifier(): void
    {
        $exception = ApiException::invalidAccessKeyIdentifier();

        static::assertSame(ApiException::API_INVALID_ACCESS_KEY_IDENTIFIER_EXCEPTION, $exception->getErrorCode());
    }

    public function testChannelInMaintenanceMode(): void
    {
        $exception = ApiException::channelInMaintenanceMode();

        static::assertSame(ApiException::API_CHANNEL_MAINTENANCE_MODE, $exception->getErrorCode());
    }

    public function testAdminApiSourceExpected(): void
    {
        $exception = ApiException::invalidAdminSource('fooSource');

        static::assertSame(InvalidContextSourceException::class, $exception::class);
        static::assertSame(ApiException::API_INVALID_CONTEXT_SOURCE, $exception->getErrorCode());
    }

    public function testUserNotLoggedIn(): void
    {
        $exception = ApiException::userNotLoggedIn();

        static::assertSame(ApiException::class, $exception::class);
        static::assertSame(ApiException::API_EXPECTED_USER, $exception->getErrorCode());
    }

    public function testUnsupportedFrontApiSchemaEndpoint(): void
    {
        $exception = ApiException::unsupportedFrontApiSchemaEndpoint();

        static::assertSame(ApiException::API_UNSUPPORTED_FRONT_API_SCHEMA_ENDPOINT, $exception->getErrorCode());
        static::assertSame(
            'The Store-API does not support the entity schema endpoint. Use `/front-api/_info/openapi3.json` for the OpenAPI specification.',
            $exception->getMessage()
        );
    }
}
