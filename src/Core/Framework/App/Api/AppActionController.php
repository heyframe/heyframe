<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Api;

use HeyFrame\Core\Framework\App\ActionButton\ActionButtonLoader;
use HeyFrame\Core\Framework\App\ActionButton\AppActionLoader;
use HeyFrame\Core\Framework\App\ActionButton\Executor;
use HeyFrame\Core\Framework\App\Manifest\ModuleLoader;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal only for use by the app-system
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class AppActionController extends AbstractController
{
    public function __construct(
        private readonly ActionButtonLoader $actionButtonLoader,
        private readonly AppActionLoader $appActionFactory,
        private readonly Executor $executor,
        private readonly ModuleLoader $moduleLoader
    ) {
    }

    #[Route(path: 'api/app-system/action-button/{entity}/{view}', name: 'api.app_system.action_buttons', methods: ['GET'])]
    public function getActionsPerView(string $entity, string $view, Context $context): Response
    {
        return new JsonResponse([
            'actions' => $this->actionButtonLoader->loadActionButtonsForView($entity, $view, $context),
        ]);
    }

    #[Route(path: 'api/app-system/action-button/run/{id}', name: 'api.app_system.action_button.run', methods: ['POST'], defaults: ['_acl' => ['app']])]
    public function runAction(string $id, Request $request, Context $context): Response
    {
        $entityIds = $request->get('ids', []);

        $action = $this->appActionFactory->loadAppAction($id, $entityIds, $context);

        return $this->executor->execute($action, $context);
    }

    #[Route(path: 'api/app-system/modules', name: 'api.app_system.modules', methods: ['GET'])]
    public function getModules(Context $context): Response
    {
        return new JsonResponse(['modules' => $this->moduleLoader->loadModules($context)]);
    }
}
