<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Api;

use GuzzleHttp\Exception\ClientException;
use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use HeyFrame\Core\Framework\Api\Context\Exception\InvalidContextSourceUserException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\Framework\Store\Exception\StoreApiException;
use HeyFrame\Core\Framework\Store\Exception\StoreInvalidCredentialsException;
use HeyFrame\Core\Framework\Store\Exception\StoreTokenMissingException;
use HeyFrame\Core\Framework\Store\Services\AbstractExtensionDataProvider;
use HeyFrame\Core\Framework\Store\Services\StoreClient;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\User\UserCollection;
use HeyFrame\Core\System\User\UserEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class StoreController extends AbstractController
{
    /**
     * @param EntityRepository<UserCollection> $userRepository
     */
    public function __construct(
        private readonly StoreClient $storeClient,
        private readonly EntityRepository $userRepository,
        private readonly AbstractExtensionDataProvider $extensionDataProvider
    ) {
    }

    #[Route(path: '/api/_action/store/login', name: 'api.custom.store.login', methods: ['POST'])]
    public function login(Request $request, Context $context): JsonResponse
    {
        $heyframeId = $request->request->get('heyframeId');
        $password = $request->request->get('password');

        if (!\is_string($heyframeId) || !\is_string($password)) {
            throw new StoreInvalidCredentialsException();
        }

        try {
            $this->storeClient->loginWithHeyFrameId($heyframeId, $password, $context);
        } catch (ClientException $exception) {
            throw new StoreApiException($exception);
        }

        return new JsonResponse();
    }

    #[Route(path: '/api/_action/store/checklogin', name: 'api.custom.store.checklogin', methods: ['POST'])]
    public function checkLogin(Context $context): Response
    {
        try {
            // Throws StoreTokenMissingException if no token is present
            $this->getUserStoreToken($context);

            $userInfo = $this->storeClient->userInfo($context);

            return new JsonResponse([
                'userInfo' => $userInfo,
            ]);
        } catch (StoreTokenMissingException|ClientException) {
            return new JsonResponse([
                'userInfo' => null,
            ]);
        }
    }

    #[Route(path: '/api/_action/store/logout', name: 'api.custom.store.logout', methods: ['POST'])]
    public function logout(Context $context): Response
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context): void {
            $source = $context->getSource();
            \assert($source instanceof AdminApiSource);
            $this->userRepository->update([['id' => $source->getUserId(), 'storeToken' => null]], $context);
        });

        return new Response();
    }

    #[Route(path: '/api/_action/store/updates', name: 'api.custom.store.updates', methods: ['GET'])]
    public function getUpdateList(Context $context): JsonResponse
    {
        $extensions = $this->extensionDataProvider->getInstalledExtensions($context, false);

        try {
            $updatesList = $this->storeClient->getExtensionUpdateList($extensions, $context);
        } catch (ClientException $exception) {
            throw new StoreApiException($exception);
        }

        return new JsonResponse([
            'items' => $updatesList,
            'total' => \count($updatesList),
        ]);
    }

    #[Route(path: '/api/_action/store/license-violations', name: 'api.custom.store.license-violations', methods: ['POST'])]
    public function getLicenseViolations(Request $request, Context $context): JsonResponse
    {
        $extensions = $this->extensionDataProvider->getInstalledExtensions($context, false);

        $indexedExtensions = [];

        foreach ($extensions as $extension) {
            $name = $extension->getName();
            $indexedExtensions[$name] = [
                'name' => $name,
                'version' => $extension->getVersion(),
                'active' => $extension->getActive(),
            ];
        }

        try {
            $violations = $this->storeClient->getLicenseViolations($context, $indexedExtensions, $request->getHost());
        } catch (ClientException $exception) {
            throw new StoreApiException($exception);
        }

        return new JsonResponse([
            'items' => $violations,
            'total' => \count($violations),
        ]);
    }

    #[Route(path: '/api/_action/store/plugin/search', name: 'api.action.store.plugin.search', methods: ['POST'])]
    public function searchPlugins(Request $request, Context $context): Response
    {
        $extensions = $this->extensionDataProvider->getInstalledExtensions($context, false);

        try {
            $this->storeClient->checkForViolations($context, $extensions, $request->getHost());
        } catch (\Exception) {
        }

        return new JsonResponse([
            'total' => $extensions->count(),
            'items' => $extensions,
        ]);
    }

    protected function getUserStoreToken(Context $context): string
    {
        $contextSource = $context->getSource();

        if (!$contextSource instanceof AdminApiSource) {
            throw new InvalidContextSourceException(AdminApiSource::class, $contextSource::class);
        }

        $userId = $contextSource->getUserId();
        if ($userId === null) {
            throw new InvalidContextSourceUserException($contextSource::class);
        }

        /** @var UserEntity|null $user */
        $user = $this->userRepository->search(new Criteria([$userId]), $context)->first();

        if ($user === null) {
            throw new StoreTokenMissingException();
        }

        $storeToken = $user->getStoreToken();
        if ($storeToken === null) {
            throw new StoreTokenMissingException();
        }

        return $storeToken;
    }
}
