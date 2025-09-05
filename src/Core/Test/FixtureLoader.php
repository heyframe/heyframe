<?php declare(strict_types=1);

namespace HeyFrame\Core\Test;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Sync\SyncOperation;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityWriter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('framework')]
class FixtureLoader
{
    private readonly Connection $connection;

    private readonly EntityWriterInterface $writer;

    public function __construct(
        private readonly ContainerInterface $container
    ) {
        $this->connection = $container->get(Connection::class);
        $this->writer = $container->get(EntityWriter::class);
    }

    public function load(string $content, ?IdsCollection $ids = null): IdsCollection
    {
        if (!$ids) {
            $ids = new IdsCollection([
                'currency' => Defaults::CURRENCY,
                'api-type' => Defaults::CHANNEL_TYPE_API,
                'comparison-type' => Defaults::CHANNEL_TYPE_PRODUCT_COMPARISON,
                'frontend-type' => Defaults::CHANNEL_TYPE_FRONTEND,
                'language' => Defaults::LANGUAGE_SYSTEM,
                'locale' => $this->getLocaleIdOfSystemLanguage(),
                'es-locale' => $this->getLocaleIdFromLocaleCode('es-ES'),
            ]);
        }

        $content = $this->replaceIds($ids, $content);
        $this->sync(\json_decode($content, true, 512, \JSON_THROW_ON_ERROR));
        $this->container->get(EntityIndexerRegistry::class)->index(false);

        return $ids;
    }

    private function replaceIds(IdsCollection $ids, string $content): string
    {
        return (string) \preg_replace_callback('/"{.*}"/mU', function (array $match) use ($ids) {
            $key = \str_replace(['"{', '}"'], '', $match[0]);

            return '"' . $ids->create($key) . '"';
        }, $content);
    }

    /**
     * @param array<array<int, mixed>> $content
     */
    private function sync(array $content): void
    {
        $operations = [];
        foreach ($content as $entity => $data) {
            $operations[] = new SyncOperation($entity, $entity, 'upsert', $data);
        }

        $this->writer->sync($operations, WriteContext::createFromContext(Context::createDefaultContext()));
    }

    private function getLocaleIdOfSystemLanguage(): string
    {
        return $this->connection
            ->fetchOne(
                'SELECT LOWER(HEX(locale_id)) FROM language WHERE id = UNHEX(:systemLanguageId)',
                ['systemLanguageId' => Defaults::LANGUAGE_SYSTEM]
            );
    }

    private function getLocaleIdFromLocaleCode(string $code): string
    {
        return $this->connection
            ->fetchOne(
                'SELECT LOWER(HEX(id)) from locale WHERE code = :code',
                ['code' => $code]
            );
    }
}
