<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Api;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\Framework\Store\Services\AbstractExtensionStoreLicensesService;
use HeyFrame\Core\Framework\Store\Struct\ReviewStruct;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID], '_acl' => ['system.plugin_maintain']])]
#[Package('checkout')]
class ExtensionStoreLicensesController extends AbstractController
{
    public function __construct(private readonly AbstractExtensionStoreLicensesService $extensionStoreLicensesService)
    {
    }

    #[Route(path: '/api/license/cancel/{licenseId}', name: 'api.license.cancel', methods: ['DELETE'])]
    public function cancelSubscription(int $licenseId, Context $context): JsonResponse
    {
        $this->extensionStoreLicensesService->cancelSubscription($licenseId, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/license/rate/{extensionId}', name: 'api.license.rate', methods: ['POST'])]
    public function rateLicensedExtension(int $extensionId, Request $request, Context $context): JsonResponse
    {
        $this->extensionStoreLicensesService->rateLicensedExtension(
            ReviewStruct::fromRequest($extensionId, $request),
            $context
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
