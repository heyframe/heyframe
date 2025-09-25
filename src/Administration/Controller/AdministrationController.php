<?php declare(strict_types=1);

namespace HeyFrame\Administration\Controller;

use Doctrine\DBAL\Connection;
use HeyFrame\Administration\Events\PreResetExcludedSearchTermEvent;
use HeyFrame\Administration\Framework\Routing\AdministrationRouteScope;
use HeyFrame\Administration\Framework\Routing\KnownIps\KnownIpsCollectorInterface;
use HeyFrame\Administration\Snippet\SnippetFinderInterface;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Adapter\Twig\TemplateFinder;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RoutingException;
use HeyFrame\Core\Framework\Store\Services\FirstRunWizardService;
use HeyFrame\Core\Framework\Util\HtmlSanitizer;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [AdministrationRouteScope::ID]])]
#[Package('framework')]
class AdministrationController extends AbstractController
{
    /**
     * @internal
     *
     * @param array<int, int> $supportedApiVersions
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<CurrencyCollection> $currencyRepository
     */
    public function __construct(
        private readonly TemplateFinder $finder,
        private readonly FirstRunWizardService $firstRunWizardService,
        private readonly SnippetFinderInterface $snippetFinder,
        private readonly array $supportedApiVersions,
        private readonly KnownIpsCollectorInterface $knownIpsCollector,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $heyframeCoreDir,
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $currencyRepository,
        private readonly HtmlSanitizer $htmlSanitizer,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly SystemConfigService $systemConfigService,
        private readonly FilesystemOperator $fileSystem,
        private readonly string $serviceRegistryUrl,
        private readonly string $refreshTokenTtl = 'P1W',
    ) {
    }

    #[Route(path: '/%heyframe_administration.path_name%', name: 'administration.index', defaults: ['auth_required' => false], methods: ['GET'])]
    public function index(Request $request, Context $context): Response
    {
        $template = $this->finder->find('@Administration/administration/index.html.twig');

        $defaultCurrency = $this->currencyRepository->search(new Criteria([Defaults::CURRENCY]), $context)->getEntities()->first();

        $refreshTokenInterval = new \DateInterval($this->refreshTokenTtl);
        $refreshTokenTtl = $refreshTokenInterval->s + $refreshTokenInterval->i * 60 + $refreshTokenInterval->h * 3600 + $refreshTokenInterval->d * 86400;

        return $this->render($template, [
            'features' => Feature::getAll(),
            'systemLanguageId' => Defaults::LANGUAGE_SYSTEM,
            'defaultLanguageIds' => [Defaults::LANGUAGE_SYSTEM],
            'systemCurrencyId' => Defaults::CURRENCY,
            'systemCurrencyISOCode' => $defaultCurrency?->getIsoCode(),
            'liveVersionId' => Defaults::LIVE_VERSION,
            'firstRunWizard' => $this->firstRunWizardService->frwShouldRun(),
            'apiVersion' => $this->getLatestApiVersion(),
            'cspNonce' => $request->attributes->get(PlatformRequest::ATTRIBUTE_CSP_NONCE),
            'refreshTokenTtl' => $refreshTokenTtl * 1000,
            'serviceRegistryUrl' => $this->serviceRegistryUrl,
        ]);
    }

    #[Route(path: '/api/_admin/snippets', name: 'api.admin.snippets', methods: ['GET'])]
    public function snippets(Request $request): Response
    {
        $snippets = [];
        $locale = $request->query->get('locale', 'zh-CN');
        $snippets[$locale] = $this->snippetFinder->findSnippets((string) $locale);

        if ($locale !== 'zh-CN') {
            $snippets['zh-CN'] = $this->snippetFinder->findSnippets('zh-CN');
        }

        return new JsonResponse($snippets);
    }

    #[Route(path: '/api/_admin/known-ips', name: 'api.admin.known-ips', methods: ['GET'])]
    public function knownIps(Request $request): Response
    {
        $ips = [];

        foreach ($this->knownIpsCollector->collectIps($request) as $ip => $name) {
            $ips[] = [
                'name' => $name,
                'value' => $ip,
            ];
        }

        return new JsonResponse(['ips' => $ips]);
    }

    #[Route(path: '/%heyframe_administration.path_name%/{pluginName}/index.html', name: 'administration.plugin.index', defaults: ['auth_required' => false], methods: ['GET'])]
    public function pluginIndex(string $pluginName): Response
    {
        try {
            $publicAssetBaseUrl = $this->fileSystem->publicUrl('/');
            $viteIndexHtml = $this->fileSystem->read('bundles/' . $pluginName . '/meteor-app/index.html');
        } catch (FilesystemException) {
            return new Response('Plugin index.html not found', Response::HTTP_NOT_FOUND);
        }

        $indexHtml = str_replace('__$ASSET_BASE_PATH$__', \sprintf('%sbundles/%s/meteor-app/', $publicAssetBaseUrl, $pluginName), $viteIndexHtml);

        $response = new Response($indexHtml, Response::HTTP_OK, [
            'Content-Type' => 'text/html',
            'Content-Security-Policy' => 'script-src * \'unsafe-eval\' \'unsafe-inline\'',
            PlatformRequest::HEADER_FRAME_OPTIONS => 'sameorigin',
        ]);
        $response->setSharedMaxAge(3600);

        return $response;
    }

    #[Route(path: '/api/_admin/reset-excluded-search-term', name: 'api.admin.reset-excluded-search-term', defaults: ['_acl' => ['system_config:update', 'system_config:create', 'system_config:delete']], methods: ['POST'])]
    public function resetExcludedSearchTerm(Context $context): JsonResponse
    {
        $searchConfigId = $this->connection->fetchOne('SELECT id FROM product_search_config WHERE language_id = :language_id', ['language_id' => Uuid::fromHexToBytes($context->getLanguageId())]);

        if ($searchConfigId === false) {
            throw RoutingException::languageNotFound($context->getLanguageId());
        }

        $zhLanguageId = $this->fetchLanguageIdByName('zh-CN', $this->connection);
        $enLanguageId = $this->fetchLanguageIdByName('en-GB', $this->connection);

        switch ($context->getLanguageId()) {
            case $zhLanguageId:
                $defaultExcludedTerm = require $this->heyframeCoreDir . '/Migration/Fixtures/stopwords/zh.php';

                break;
            case $enLanguageId:
                $defaultExcludedTerm = require $this->heyframeCoreDir . '/Migration/Fixtures/stopwords/en.php';

                break;
            default:
                $preResetExcludedSearchTermEvent = $this->eventDispatcher->dispatch(new PreResetExcludedSearchTermEvent($searchConfigId, [], $context));
                $defaultExcludedTerm = $preResetExcludedSearchTermEvent->getExcludedTerms();
        }

        $this->connection->executeStatement(
            'UPDATE `product_search_config` SET `excluded_terms` = :excludedTerms WHERE `id` = :id',
            [
                'excludedTerms' => json_encode($defaultExcludedTerm, \JSON_THROW_ON_ERROR),
                'id' => $searchConfigId,
            ]
        );

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/api/_admin/check-customer-email-valid', name: 'api.admin.check-customer-email-valid', methods: ['POST'])]
    public function checkCustomerEmailValid(Request $request, Context $context): JsonResponse
    {
        $params = [];
        if (!$request->request->has('email')) {
            throw RoutingException::missingRequestParameter('email');
        }

        $email = (string) $request->request->get('email');
        $isCustomerBoundChannel = $this->systemConfigService->get('core.systemWideLoginRegistration.isCustomerBoundToChannel');
        $boundChannelId = null;
        if ($isCustomerBoundChannel) {
            $boundChannelId = $request->request->get('boundChannelId');
            if ($boundChannelId !== null && !\is_string($boundChannelId)) {
                throw RoutingException::invalidRequestParameter('boundChannelId');
            }
        }

        $customer = $this->getCustomerByEmail((string) $request->request->get('id'), $email, $context, $boundChannelId);
        if (!$customer) {
            return new JsonResponse(
                ['isValid' => true]
            );
        }

        $message = 'The email address {{ email }} is already in use';
        $params['{{ email }}'] = $email;

        if ($customer->getBoundChannel()) {
            $message .= ' in the Sales Channel {{ channel }}';
            $params['{{ channel }}'] = (string) $customer->getBoundChannel()->getName();
        }

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation(
            str_replace(array_keys($params), array_values($params), $message),
            $message,
            $params,
            null,
            null,
            $email,
            null,
            '79d30fe0-febf-421e-ac9b-1bfd5c9007f7'
        ));

        throw new ConstraintViolationException($violations, $request->request->all());
    }

    #[Route(path: '/api/_admin/sanitize-html', name: 'api.admin.sanitize-html', methods: ['POST'])]
    public function sanitizeHtml(Request $request, Context $context): JsonResponse
    {
        if (!$request->request->has('html')) {
            throw RoutingException::missingRequestParameter('html');
        }

        $html = (string) $request->request->get('html');
        $field = (string) $request->request->get('field');

        if ($field === '') {
            return new JsonResponse(
                ['preview' => $this->htmlSanitizer->sanitize($html)]
            );
        }

        [$entityName, $propertyName] = explode('.', $field);
        $property = $this->definitionInstanceRegistry->getByEntityName($entityName)->getField($propertyName);

        if ($property === null) {
            throw RoutingException::invalidRequestParameter($field);
        }

        $flag = $property->getFlag(AllowHtml::class);

        if ($flag === null) {
            return new JsonResponse(
                ['preview' => strip_tags($html)]
            );
        }

        if ($flag instanceof AllowHtml && !$flag->isSanitized()) {
            return new JsonResponse(
                ['preview' => $html]
            );
        }

        return new JsonResponse(
            ['preview' => $this->htmlSanitizer->sanitize($html, [], false, $field)]
        );
    }

    private function fetchLanguageIdByName(string $isoCode, Connection $connection): ?string
    {
        $languageId = $connection->fetchOne(
            '
            SELECT `language`.id FROM `language`
            INNER JOIN locale ON language.translation_code_id = locale.id
            WHERE `code` = :code',
            ['code' => $isoCode]
        );

        return $languageId === false ? null : Uuid::fromBytesToHex($languageId);
    }

    private function getLatestApiVersion(): ?int
    {
        $sortedSupportedApiVersions = array_values($this->supportedApiVersions);

        usort($sortedSupportedApiVersions, fn (int $version1, int $version2) => \version_compare((string) $version1, (string) $version2));

        return array_pop($sortedSupportedApiVersions);
    }

    private function getCustomerByEmail(string $customerId, string $email, Context $context, ?string $boundChannelId): ?CustomerEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        if ($boundChannelId) {
            $criteria->addAssociation('boundChannel');
        }

        $criteria->addFilter(new EqualsFilter('email', $email));
        $criteria->addFilter(new NotEqualsFilter('id', $customerId));

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('boundChannelId', null),
            new EqualsFilter('boundChannelId', $boundChannelId),
        ]));

        return $this->customerRepository->search($criteria, $context)->getEntities()->first();
    }
}
