<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\LandingPage\LandingPageEntity;
use HeyFrame\Core\Content\Sitemap\Service\ConfigHandler;
use HeyFrame\Core\Content\Sitemap\Struct\Url;
use HeyFrame\Core\Content\Sitemap\Struct\UrlResult;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Routing\RouterInterface;

#[Package('discovery')]
class LandingPageUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'daily';

    /**
     * @internal
     */
    public function __construct(
        private readonly ConfigHandler $configHandler,
        private readonly Connection $connection,
        private readonly RouterInterface $router
    ) {
    }

    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'landing_page';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getUrls(ChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $landingPages = $this->getLandingPages($context, $limit, $offset);

        if (empty($landingPages)) {
            return new UrlResult([], null);
        }

        $ids = array_column($landingPages, 'id');

        $seoUrls = $this->getSeoUrls($ids, 'frontend.landing.page', $context, $this->connection);

        /** @var array<string, array{seo_path_info: string}> $seoUrls */
        $seoUrls = FetchModeHelper::groupUnique($seoUrls);

        $urls = [];
        foreach ($landingPages as $landingPage) {
            $url = new Url();

            if (isset($seoUrls[$landingPage['id']])) {
                $url->setLoc($seoUrls[$landingPage['id']]['seo_path_info']);
            } else {
                $url->setLoc($this->router->generate('frontend.landing.page', ['landingPageId' => $landingPage['id']]));
            }

            $lastMod = $landingPage['updated_at'] ?: $landingPage['created_at'];

            $url->setLastmod(new \DateTime($lastMod));
            $url->setChangefreq(self::CHANGE_FREQ);
            $url->setResource(LandingPageEntity::class);
            $url->setIdentifier($landingPage['id']);

            $urls[] = $url;
        }

        $nextOffset = null;
        if (\count($landingPages) === $limit) {
            $nextOffset = (int) $offset + $limit;
        }

        return new UrlResult($urls, $nextOffset);
    }

    /**
     * @return list<array{id: string, created_at: string, updated_at: string}>
     */
    private function getLandingPages(ChannelContext $context, int $limit, ?int $offset): array
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->select('lp.id', 'lp.created_at', 'lp.updated_at')
            ->from('landing_page', 'lp')
            ->join('lp', 'landing_page_channel', 'lp_sc', 'lp_sc.landing_page_id = lp.id AND lp_sc.landing_page_version_id = lp.version_id')
            ->where('lp.version_id = :versionId')
            ->andWhere('lp.active = 1')
            ->andWhere('lp_sc.channel_id = :channelId')
            ->setMaxResults($limit);

        $query->setFirstResult(0);
        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        $excludedLandingPageIds = $this->getExcludedLandingPageIds($context);
        if (!empty($excludedLandingPageIds)) {
            $query->andWhere('lp.id NOT IN (:landingPageIds)');
            $query->setParameter('landingPageIds', Uuid::fromHexToBytesList($excludedLandingPageIds), ArrayParameterType::BINARY);
        }

        $query->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('channelId', Uuid::fromHexToBytes($context->getChannelId()));

        /** @var list<array{id: string, created_at: string, updated_at: string}> $result */
        $result = $query->executeQuery()->fetchAllAssociative();

        return array_map(static function (array $landingPage): array {
            $landingPage['id'] = Uuid::fromBytesToHex($landingPage['id']);

            return $landingPage;
        }, $result);
    }

    /**
     * @return array<string>
     */
    private function getExcludedLandingPageIds(ChannelContext $channelContext): array
    {
        $channelId = $channelContext->getChannelId();

        $excludedUrls = $this->configHandler->get(ConfigHandler::EXCLUDED_URLS_KEY);
        if (empty($excludedUrls)) {
            return [];
        }

        $excludedUrls = array_filter($excludedUrls, static function (array $excludedUrl) use ($channelId) {
            if ($excludedUrl['resource'] !== LandingPageEntity::class) {
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
