<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Controller;

use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('fundamentals@framework')]
class AccessKeyController extends AbstractController
{
    #[Route(path: '/api/_action/access-key/intergration', name: 'api.action.access-key.integration', methods: ['GET'], defaults: ['_acl' => ['api_action_access-key_integration']])]
    public function generateIntegrationKey(): JsonResponse
    {
        return new JsonResponse([
            'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
            'secretAccessKey' => AccessKeyHelper::generateSecretAccessKey(),
        ]);
    }

    #[Route(path: '/api/_action/access-key/user', name: 'api.action.access-key.user', methods: ['GET'])]
    public function generateUserKey(): JsonResponse
    {
        return new JsonResponse([
            'accessKey' => AccessKeyHelper::generateAccessKey('user'),
            'secretAccessKey' => AccessKeyHelper::generateSecretAccessKey(),
        ]);
    }

    #[Route(path: '/api/_action/access-key/channel', name: 'api.action.access-key.channel', methods: ['GET'])]
    public function generateChannelKey(): JsonResponse
    {
        return new JsonResponse([
            'accessKey' => AccessKeyHelper::generateAccessKey('channel'),
        ]);
    }

    #[Route(path: '/api/_action/access-key/product-export', name: 'api.action.access-key.product-export', methods: ['GET'])]
    public function generateProductExportKey(): JsonResponse
    {
        return new JsonResponse([
            'accessKey' => AccessKeyHelper::generateAccessKey('product-export'),
        ]);
    }
}
