<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\ApiDefinition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;

/**
 * @internal
 *
 * @phpstan-import-type Api from DefinitionService
 * @phpstan-import-type ApiType from DefinitionService
 * @phpstan-import-type OpenApiSpec from DefinitionService
 * @phpstan-import-type ApiSchema from DefinitionService
 */
#[Package('framework')]
interface ApiDefinitionGeneratorInterface
{
    public function supports(string $format, string $api): bool;

    /**
     * @param array<string, EntityDefinition>|array<string, EntityDefinition&ChannelDefinitionInterface> $definitions
     * @param Api $api
     * @param ApiType $apiType
     *
     * @return OpenApiSpec
     */
    public function generate(array $definitions, string $api, string $apiType, ?string $bundleName): array;

    /**
     * @param array<string, EntityDefinition>|array<string, EntityDefinition&ChannelDefinitionInterface> $definitions
     *
     * @return ApiSchema
     */
    public function getSchema(array $definitions): array;
}
