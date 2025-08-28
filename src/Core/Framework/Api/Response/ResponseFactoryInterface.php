<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Response;

use HeyFrame\Core\Framework\Api\Context\ContextSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
interface ResponseFactoryInterface
{
    public function supports(string $contentType, ContextSource $origin): bool;

    public function createDetailResponse(
        Criteria $criteria,
        Entity $entity,
        EntityDefinition $definition,
        Request $request,
        Context $context,
        bool $setLocationHeader = false
    ): Response;

    /**
     * @param EntitySearchResult<covariant EntityCollection<covariant Entity>> $searchResult
     */
    public function createListingResponse(
        Criteria $criteria,
        EntitySearchResult $searchResult,
        EntityDefinition $definition,
        Request $request,
        Context $context
    ): Response;

    public function createRedirectResponse(
        EntityDefinition $definition,
        string $id,
        Request $request,
        Context $context
    ): Response;
}
