<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Channel;

use HeyFrame\Core\Framework\Api\ApiDefinition\DefinitionService;
use HeyFrame\Core\Framework\Api\ApiDefinition\Generator\OpenApi3Generator;
use HeyFrame\Core\Framework\Api\Route\ApiRouteInfoResolver;
use HeyFrame\Core\Framework\Api\Route\RouteInfo;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Routing\RoutingException;
use HeyFrame\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class StoreApiInfoController
{
    /**
     * @internal
     *
     * @param array{administration?: string} $cspTemplates
     */
    public function __construct(
        protected DefinitionService $definitionService,
        private readonly Environment $twig,
        private readonly array $cspTemplates,
        private readonly ApiRouteInfoResolver $apiRouteInfoResolver,
    ) {
    }

    #[Route(
        path: '/front-api/_info/openapi3.json',
        name: 'front-api.info.openapi3',
        defaults: ['auth_required' => '%heyframe.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function info(Request $request): JsonResponse
    {
        $apiType = $request->query->getAlpha('type', DefinitionService::TYPE_JSON_API);

        $apiType = $this->definitionService->toApiType($apiType);
        if ($apiType === null) {
            throw RoutingException::invalidRequestParameter('type');
        }

        $data = $this->definitionService->generate(OpenApi3Generator::FORMAT, DefinitionService::STORE_API, $apiType);

        return new JsonResponse($data);
    }

    #[Route(
        path: '/front-api/_info/open-api-schema.json',
        name: 'front-api.info.open-api-schema',
        defaults: ['auth_required' => '%heyframe.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function openApiSchema(): JsonResponse
    {
        $data = $this->definitionService->getSchema(OpenApi3Generator::FORMAT, DefinitionService::STORE_API);

        return new JsonResponse($data);
    }

    #[Route(
        path: '/front-api/_info/stoplightio.html',
        name: 'front-api.info.stoplightio',
        defaults: ['auth_required' => '%heyframe.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function stoplightIoInfoHtml(Request $request): Response
    {
        $nonce = $request->attributes->get(PlatformRequest::ATTRIBUTE_CSP_NONCE);
        $apiType = $request->query->getAlpha('type', DefinitionService::TYPE_JSON_API);
        $response = new Response($this->twig->render(
            '@Framework/stoplightio.html.twig',
            [
                'schemaUrl' => 'front-api.info.openapi3',
                'cspNonce' => $nonce,
                'apiType' => $apiType,
            ]
        ));

        $cspTemplate = $this->cspTemplates['administration'] ?? '';
        $cspTemplate = trim($cspTemplate);
        if ($cspTemplate !== '') {
            $csp = str_replace('%nonce%', $nonce, $cspTemplate);
            $csp = str_replace(["\n", "\r"], ' ', $csp);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    #[Route(
        path: '/front-api/_info/routes',
        name: 'front-api.info.routes',
        defaults: ['auth_required' => '%heyframe.api.api_browser.auth_required_str%'],
        methods: ['GET']
    )]
    public function getRoutes(): JsonResponse
    {
        $endpoints = array_map(
            static fn (RouteInfo $endpoint) => ['path' => $endpoint->path, 'methods' => $endpoint->methods],
            $this->apiRouteInfoResolver->getApiRoutes(FrontApiRouteScope::ID)
        );

        return new JsonResponse(['endpoints' => $endpoints]);
    }
}
