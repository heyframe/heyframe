<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Seo\Event\SeoUrlUpdateEvent;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('inventory')]
class SeoUrlPersister
{
    /**
     * @internal
     *
     * @param EntityRepository<SeoUrlCollection> $seoUrlRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $seoUrlRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<string> $foreignKeys
     * @param iterable<array<string, mixed>|SeoUrlEntity> $seoUrls
     */
    public function updateSeoUrls(Context $context, string $routeName, array $foreignKeys, iterable $seoUrls, ChannelEntity $channel): void
    {
        $languageId = $context->getLanguageId();
        $canonicals = $this->findCanonicalPaths($routeName, $languageId, $foreignKeys);
        $dateTime = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        $insertQuery = new MultiInsertQueryQueue($this->connection, 250, false, true);

        $updatedFks = [];
        $obsoleted = [];

        $processed = [];

        $seoPathInfos = [];

        $channelId = $channel->getId();
        $updates = [];
        foreach ($seoUrls as $seoUrl) {
            if ($seoUrl instanceof SeoUrlEntity) {
                $seoUrl = $seoUrl->jsonSerialize();
            }
            $updates[] = $seoUrl;

            $fk = $seoUrl['foreignKey'];
            $channelId = $seoUrl['channelId'] ??= null;

            // skip duplicates
            if (isset($processed[$fk][$channelId])) {
                continue;
            }

            if (!isset($processed[$fk])) {
                $processed[$fk] = [];
            }
            $processed[$fk][$channelId] = true;

            $updatedFks[] = $fk;

            if (!empty($seoUrl['error'])) {
                continue;
            }
            $existing = $canonicals[$fk][$channelId] ?? null;

            if ($existing) {
                // entity has override or does not change
                /** @phpstan-ignore-next-line PHPStan could not recognize the array generated from the jsonSerialize method of the SeoUrlEntity */
                if ($this->skipUpdate($existing, $seoUrl)) {
                    continue;
                }
                $obsoleted[] = $existing['id'];
            }

            $seoPathInfos[] = $seoUrl['seoPathInfo'];

            $insert = [];
            $insert['id'] = Uuid::randomBytes();

            if ($channelId) {
                $insert['channel_id'] = Uuid::fromHexToBytes($channelId);
            }
            $insert['language_id'] = Uuid::fromHexToBytes($languageId);
            $insert['foreign_key'] = Uuid::fromHexToBytes($fk);

            $insert['path_info'] = $seoUrl['pathInfo'];
            $insert['seo_path_info'] = ltrim((string) $seoUrl['seoPathInfo'], '/');

            $insert['route_name'] = $routeName;
            $insert['is_canonical'] = ($seoUrl['isCanonical'] ?? true) ? 1 : null;
            $insert['is_modified'] = ($seoUrl['isModified'] ?? false) ? 1 : 0;
            $insert['is_deleted'] = ($seoUrl['isDeleted'] ?? true) ? 1 : 0;

            $insert['created_at'] = $dateTime;

            $insertQuery->addInsert($this->seoUrlRepository->getDefinition()->getEntityName(), $insert);
        }

        $inuseSeoUrls = $this->findInUseCanonicalSeoUrls($seoPathInfos, $languageId, $channelId);

        RetryableTransaction::retryable($this->connection, function () use ($obsoleted, $insertQuery, $foreignKeys, $updatedFks, $channelId): void {
            $this->obsoleteIds($obsoleted, $channelId);
            $insertQuery->execute();

            $deletedIds = array_diff($foreignKeys, $updatedFks);
            $notDeletedIds = array_unique(array_intersect($foreignKeys, $updatedFks));

            $this->markAsDeleted(true, $deletedIds, $channelId);
            $this->markAsDeleted(false, $notDeletedIds, $channelId);
        });

        // When a seoPathInfo is added that is already associated with a foreignKey, EX: Entity A,
        // the existing row is seamlessly replaced due to the useReplace flag being set to true within the MultiInsertQueryQueue configuration above.
        // Hence, we have to find the default seoUrls for Entity A and update it accordingly to set is_canonical and is_modified to true,
        // thereby preserving the canonical SEO URL for Entity A.
        $this->updateCanonicalSeoUrls($inuseSeoUrls, $languageId);

        $this->eventDispatcher->dispatch(new SeoUrlUpdateEvent($updates));
    }

    /**
     * @param array{isModified: bool, seoPathInfo: string, channelId: string} $existing
     * @param array{isModified?: bool, seoPathInfo: string, channelId: string} $seoUrl
     */
    private function skipUpdate(array $existing, array $seoUrl): bool
    {
        if ($existing['isModified'] && !($seoUrl['isModified'] ?? false) && trim($seoUrl['seoPathInfo']) !== '') {
            return true;
        }

        return $seoUrl['seoPathInfo'] === $existing['seoPathInfo']
            && $seoUrl['channelId'] === $existing['channelId'];
    }

    /**
     * @param array<string> $foreignKeys
     *
     * @return array<string, mixed>
     */
    private function findCanonicalPaths(string $routeName, string $languageId, array $foreignKeys): array
    {
        $fks = Uuid::fromHexToBytesList($foreignKeys);
        $languageId = Uuid::fromHexToBytes($languageId);

        $query = $this->connection->createQueryBuilder();
        $query->select(
            'LOWER(HEX(seo_url.id)) as id',
            'LOWER(HEX(seo_url.foreign_key)) foreignKey',
            'LOWER(HEX(seo_url.channel_id)) channelId',
            'seo_url.is_modified as isModified',
            'seo_url.seo_path_info seoPathInfo',
        );
        $query->from('seo_url', 'seo_url');

        $query->andWhere('seo_url.route_name = :routeName');
        $query->andWhere('seo_url.language_id = :language_id');
        $query->andWhere('seo_url.is_canonical = 1');
        $query->andWhere('seo_url.foreign_key IN (:foreign_keys)');

        $query->setParameter('routeName', $routeName);
        $query->setParameter('language_id', $languageId);
        $query->setParameter('foreign_keys', $fks, ArrayParameterType::BINARY);

        $rows = $query->executeQuery()->fetchAllAssociative();

        $canonicals = [];
        foreach ($rows as $row) {
            $row['isModified'] = (bool) $row['isModified'];
            $foreignKey = (string) $row['foreignKey'];
            if (!isset($canonicals[$foreignKey])) {
                $canonicals[$foreignKey] = [$row['channelId'] => $row];

                continue;
            }
            $canonicals[$foreignKey][$row['channelId']] = $row;
        }

        return $canonicals;
    }

    /**
     * @param array<string> $seoPathInfos
     *
     * @return array<array<string, mixed>>
     */
    private function findInUseCanonicalSeoUrls(array $seoPathInfos, string $languageId, ?string $channelId = null): array
    {
        if (empty($seoPathInfos)) {
            return [];
        }

        $query = 'SELECT id, channel_id channelId, foreign_key foreignKey, route_name routeName
        FROM seo_url
        WHERE is_canonical = 1 AND language_id = :languageId AND seo_path_info IN (:seoPathInfos)';

        $params = ['seoPathInfos' => $seoPathInfos, 'languageId' => Uuid::fromHexToBytes($languageId)];
        $types = ['seoPathInfos' => ArrayParameterType::BINARY];

        if ($channelId !== null) {
            $query .= ' AND channel_id = :channelId';
            $params['channelId'] = Uuid::fromHexToBytes($channelId);
        }

        return $this->connection->fetchAllAssociative($query, $params, $types);
    }

    /**
     * Find the earliest valid SEO URL created. This means it is the default SEO URL and update the `is_canonical` and `is_modified` fields.
     *
     * @param array<array<string, mixed>> $seoUrls
     */
    private function updateCanonicalSeoUrls(array $seoUrls, string $languageId): void
    {
        if (empty($seoUrls)) {
            return;
        }

        $languageId = Uuid::fromHexToBytes($languageId);

        $ids = [];
        foreach ($seoUrls as $seoUrl) {
            $id = $this->connection->fetchOne(
                'SELECT id
                 FROM seo_url
                 WHERE language_id = :languageId
                   AND foreign_key = :foreignKey
                   AND channel_id = :channelId
                   AND route_name = :routeName
                   AND is_canonical IS NULL AND is_deleted = 0
                 ORDER BY created_at ASC
                 LIMIT 1',
                [
                    'languageId' => $languageId,
                    'foreignKey' => $seoUrl['foreignKey'],
                    'channelId' => $seoUrl['channelId'],
                    'routeName' => (string) $seoUrl['routeName'],
                ]
            );

            if ($id !== false) {
                $ids[] = $id;
            }
        }

        if (empty($ids)) {
            return;
        }

        $this->connection->executeStatement(
            'UPDATE seo_url SET is_canonical = 1, is_modified = 1 WHERE id IN (:ids)',
            ['ids' => $ids],
            ['ids' => ArrayParameterType::BINARY]
        );
    }

    /**
     * @param list<string> $ids
     */
    private function obsoleteIds(array $ids, ?string $channelId): void
    {
        if (empty($ids)) {
            return;
        }

        $ids = Uuid::fromHexToBytesList($ids);

        $query = $this->connection->createQueryBuilder()
            ->update('seo_url')
            ->set('is_canonical', 'NULL')
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::BINARY);

        if ($channelId) {
            $query->andWhere('channel_id = :channelId');
            $query->setParameter('channelId', Uuid::fromHexToBytes($channelId));
        }

        RetryableQuery::retryable($this->connection, function () use ($query): void {
            $query->executeStatement();
        });
    }

    /**
     * @param array<string> $ids
     */
    private function markAsDeleted(bool $deleted, array $ids, ?string $channelId): void
    {
        if (empty($ids)) {
            return;
        }

        $ids = Uuid::fromHexToBytesList($ids);
        $query = $this->connection->createQueryBuilder()
            ->update('seo_url')
            ->set('is_deleted', $deleted ? '1' : '0')
            ->where('foreign_key IN (:fks)')
            ->setParameter('fks', $ids, ArrayParameterType::BINARY);

        if ($channelId) {
            $query->andWhere('channel_id = :channelId');
            $query->setParameter('channelId', Uuid::fromHexToBytes($channelId));
        }

        $query->executeStatement();
    }
}
