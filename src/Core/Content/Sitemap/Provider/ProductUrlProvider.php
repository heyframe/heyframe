<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Sitemap\Service\ConfigHandler;
use HeyFrame\Core\Content\Sitemap\Struct\Url;
use HeyFrame\Core\Content\Sitemap\Struct\UrlResult;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\RouterInterface;

#[Package('discovery')]
class ProductUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'hourly';

    private const CONFIG_EXCLUDE_LINKED_PRODUCTS = 'core.sitemap.excludeLinkedProducts';

    private const CONFIG_HIDE_AFTER_CLOSEOUT = 'core.listing.hideCloseoutProductsWhenOutOfStock';

    /**
     * @internal
     */
    public function __construct(
        private readonly ConfigHandler $configHandler,
        private readonly Connection $connection,
        private readonly ProductDefinition $definition,
        private readonly IteratorFactory $iteratorFactory,
        private readonly RouterInterface $router,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getUrls(ChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $products = $this->getProducts($context, $limit, $offset);

        if (empty($products)) {
            return new UrlResult([], null);
        }

        $keys = FetchModeHelper::keyPair($products);

        $seoUrls = $this->getSeoUrls(array_values($keys), 'frontend.detail.page', $context, $this->connection);

        /** @var array<string, array{seo_path_info: string}> $seoUrls */
        $seoUrls = FetchModeHelper::groupUnique($seoUrls);

        $urls = [];
        $url = new Url();

        foreach ($products as $product) {
            $lastMod = $product['updated_at'] ?: $product['created_at'];

            $lastMod = (new \DateTime($lastMod))->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $newUrl = clone $url;

            if (isset($seoUrls[$product['id']])) {
                $newUrl->setLoc($seoUrls[$product['id']]['seo_path_info']);
            } else {
                $newUrl->setLoc($this->router->generate('frontend.detail.page', ['productId' => $product['id']]));
            }

            $newUrl->setLastmod(new \DateTime($lastMod));
            $newUrl->setChangefreq(self::CHANGE_FREQ);
            $newUrl->setResource(ProductEntity::class);
            $newUrl->setIdentifier($product['id']);

            $urls[] = $newUrl;
        }

        $keys = array_keys($keys);
        $nextOffset = array_pop($keys);

        return new UrlResult($urls, $nextOffset !== null ? (int) $nextOffset : null);
    }

    /**
     * @return list<array{id: string, created_at: string, updated_at: string}>
     */
    private function getProducts(ChannelContext $context, int $limit, ?int $offset): array
    {
        $lastId = null;
        if ($offset) {
            $lastId = ['offset' => $offset];
        }

        $iterator = $this->iteratorFactory->createIterator($this->definition, $lastId);
        $query = $iterator->getQuery();
        $query->setMaxResults($limit);

        $showAfterCloseout = !$this->systemConfigService->get(self::CONFIG_HIDE_AFTER_CLOSEOUT, $context->getChannelId());

        $query->addSelect(
            '`product`.created_at as created_at',
            '`product`.updated_at as updated_at',
        );

        $query->leftJoin('`product`', '`product`', 'parent', '`product`.parent_id = parent.id');
        $query->innerJoin('`product`', 'product_visibility', 'visibilities', 'product.visibilities = visibilities.product_id');

        $query->andWhere('`product`.version_id = :versionId');

        if ($showAfterCloseout) {
            $query->andWhere('(`product`.available = 1 OR `product`.is_closeout)');
        } else {
            $query->andWhere('`product`.available = 1');
        }

        $query->andWhere('IFNULL(`product`.active, parent.active) = 1');
        $query->andWhere('(`product`.child_count = 0 OR `product`.parent_id IS NOT NULL)');
        $query->andWhere('(`product`.parent_id IS NULL OR parent.canonical_product_id IS NULL OR parent.canonical_product_id = `product`.id)');
        $query->andWhere('visibilities.product_version_id = :versionId');
        $query->andWhere('visibilities.channel_id = :channelId');

        $excludedProductIds = $this->getExcludedProductIds($context);
        if (!empty($excludedProductIds)) {
            $query->andWhere('`product`.id NOT IN (:productIds)');
            $query->setParameter('productIds', Uuid::fromHexToBytesList($excludedProductIds), ArrayParameterType::BINARY);
        }

        $excludeLinkedProducts = $this->systemConfigService->getBool(self::CONFIG_EXCLUDE_LINKED_PRODUCTS, $context->getChannelId());
        if ($excludeLinkedProducts) {
            $query->andWhere('visibilities.visibility != :excludedVisibility');
            $query->setParameter('excludedVisibility', ProductVisibilityDefinition::VISIBILITY_LINK);
        }

        $query->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('channelId', Uuid::fromHexToBytes($context->getChannelId()));

        /** @var list<array{id: string, created_at: string, updated_at: string}> $result */
        $result = $query->executeQuery()->fetchAllAssociative();

        return $result;
    }

    /**
     * @return array<string>
     */
    private function getExcludedProductIds(ChannelContext $channelContext): array
    {
        $channelId = $channelContext->getChannelId();

        $excludedUrls = $this->configHandler->get(ConfigHandler::EXCLUDED_URLS_KEY);
        if (empty($excludedUrls)) {
            return [];
        }

        $excludedUrls = array_filter($excludedUrls, static function (array $excludedUrl) use ($channelId) {
            if ($excludedUrl['resource'] !== ProductEntity::class) {
                return false;
            }

            if ($excludedUrl['channelId'] !== $channelId) {
                return false;
            }

            return true;
        });

        return array_column($excludedUrls, 'identifier');
    }
}
