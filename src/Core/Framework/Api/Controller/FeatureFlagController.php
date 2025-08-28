<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Controller;

use HeyFrame\Core\Framework\Adapter\Cache\CacheClearer;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Feature\FeatureFlagRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class FeatureFlagController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly FeatureFlagRegistry $featureFlagService,
        private readonly CacheClearer $cacheClearer
    ) {
    }

    #[Route(path: '/api/_action/feature-flag/enable/{feature}', name: 'api.action.feature-flag.enable', defaults: ['auth_required' => true, '_acl' => ['api_feature_flag_toggle']], methods: ['POST'])]
    public function enable(string $feature): JsonResponse
    {
        $this->featureFlagService->enable($feature);

        $this->cacheClearer->clear();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/_action/feature-flag/disable/{feature}', name: 'api.action.feature-flag.disable', defaults: ['auth_required' => true, '_acl' => ['api_feature_flag_toggle']], methods: ['POST'])]
    public function disable(string $feature): JsonResponse
    {
        $this->featureFlagService->disable($feature);

        $this->cacheClearer->clear();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/_action/feature-flag', name: 'api.action.feature-flag.load', defaults: ['auth_required' => true, '_acl' => ['api_feature_flag_toggle']], methods: ['GET'])]
    public function load(): JsonResponse
    {
        $featureFlags = Feature::getRegisteredFeatures();

        foreach ($featureFlags as $featureKey => $feature) {
            $featureFlags[$featureKey]['active'] = Feature::isActive($featureKey);
        }

        return new JsonResponse($featureFlags);
    }
}
