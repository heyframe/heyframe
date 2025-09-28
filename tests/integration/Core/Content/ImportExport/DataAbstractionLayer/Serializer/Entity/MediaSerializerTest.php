<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity;

use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity\MediaSerializer;
use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity\MediaSerializerSubscriber;
use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\SerializerRegistry;
use HeyFrame\Core\Content\ImportExport\Exception\InvalidMediaUrlException;
use HeyFrame\Core\Content\ImportExport\Exception\MediaDownloadException;
use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Content\Media\File\FileSaver;
use HeyFrame\Core\Content\Media\File\MediaFile;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Content\Media\MediaService;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class MediaSerializerTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testDeserializeDownloadsAndPersistsMedia(): void
    {
        $context = Context::createDefaultContext();
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MediaSerializerSubscriber($mediaSerializer));

        $mediaId = Uuid::randomHex();
        $expectedDestination = 'heyframe-logo';
        $record = [
            'id' => $mediaId,
            'title' => 'Logo',
            'url' => 'http://172.16.11.80/heyframe-logo.png',
            'mediaFolderId' => Uuid::randomHex(),
        ];

        $expectedMediaFile = new MediaFile(
            '/tmp/foo/bar/baz',
            'image/png',
            'png',
            1337
        );
        $mediaService->expects($this->once())
            ->method('fetchFile')
            ->willReturn($expectedMediaFile);

        $fileSaver->expects($this->once())
            ->method('persistFileToMedia')
            ->willReturnCallback(function (MediaFile $m, string $dest, string $id) use ($expectedMediaFile, $expectedDestination, $mediaId): void {
                $this->assertSame($expectedMediaFile, $m);
                $this->assertSame($expectedDestination, $dest);
                $this->assertSame($mediaId, $id);
            });

        $result = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, $record);
        $result = \is_array($result) ? $result : iterator_to_array($result);

        $writtenResult = new EntityWriteResult($mediaId, $result, 'media', 'insert');
        $writtenEvent = new EntityWrittenEvent('media', [$writtenResult], $context);
        $eventDispatcher->dispatch($writtenEvent, 'media.written');
    }

    public function testExistingMediaWithSameUrlDoesNotDownload(): void
    {
        $context = Context::createDefaultContext();
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = $this->createMock(EntityRepository::class);
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MediaSerializerSubscriber($mediaSerializer));

        $mediaId = Uuid::randomHex();
        $record = [
            'id' => $mediaId,
            'url' => 'http://172.16.11.80/heyframe-logo.png',
        ];

        $mediaEntity = new MediaEntity();
        $mediaEntity->assign($record);

        $record['mediaFolderId'] = Uuid::randomHex();
        $record['translations'] = [
            Defaults::LANGUAGE_SYSTEM => [
                'title' => 'Logo',
                'alt' => 'Logo description',
            ],
        ];

        $mediaService->expects($this->never())
            ->method('fetchFile');

        $fileSaver->expects($this->never())
            ->method('persistFileToMedia');

        $searchResult = new EntitySearchResult('media', 1, new MediaCollection([$mediaEntity]), null, new Criteria(), $context);
        $mediaRepository->method('search')->willReturn($searchResult);

        $result = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, $record);
        $result = \is_array($result) ? $result : iterator_to_array($result);

        static::assertArrayNotHasKey('url', $result);

        $expected = $record;
        unset($expected['url']);

        // other properties are written
        static::assertSame($expected, $result);

        $writtenResult = new EntityWriteResult($mediaId, $result, 'media', 'insert');
        $writtenEvent = new EntityWrittenEvent('media', [$writtenResult], $context);
        $eventDispatcher->dispatch($writtenEvent, 'media.written');
    }

    public function testUrlWithWhitespaces(): void
    {
        Feature::skipTestIfActive('v6.8.0.0', $this);
        $context = Context::createDefaultContext();
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = static::getContainer()->get('media_folder.repository');
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MediaSerializerSubscriber($mediaSerializer));

        $mediaId = Uuid::randomHex();
        $expectedDestination = 'heyframe logo';
        $record = [
            'id' => $mediaId,
            'url' => 'http://172.16.11.80/heyframe logo.png',
        ];

        $expectedMediaFile = new MediaFile(
            '/tmp/foo/bar/baz',
            'image/png',
            'png',
            1337
        );
        $mediaService->expects($this->once())
            ->method('fetchFile')
            ->willReturn($expectedMediaFile);

        $fileSaver->expects($this->once())
            ->method('persistFileToMedia')
            ->willReturnCallback(function (MediaFile $m, string $dest, string $id) use ($expectedMediaFile, $expectedDestination, $mediaId): void {
                $this->assertSame($expectedMediaFile, $m);
                $this->assertSame($expectedDestination, $dest);
                $this->assertSame($mediaId, $id);
            });

        $result = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, $record);
        $result = \is_array($result) ? $result : iterator_to_array($result);

        $writtenResult = new EntityWriteResult($mediaId, $result, 'media', 'insert');
        $writtenEvent = new EntityWrittenEvent('media', [$writtenResult], $context);
        $eventDispatcher->dispatch($writtenEvent, 'media.written');
    }

    public function testOnlyUrl(): void
    {
        $context = Context::createDefaultContext();
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = static::getContainer()->get('media_folder.repository');
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MediaSerializerSubscriber($mediaSerializer));

        $expectedDestination = 'heyframe-logo';
        $record = [
            'url' => 'http://172.16.11.80/heyframe-logo.png',
        ];

        $expectedMediaFile = new MediaFile(
            '/tmp/foo/bar/baz',
            'image/png',
            'png',
            1337
        );
        $mediaService->expects($this->once())
            ->method('fetchFile')
            ->willReturn($expectedMediaFile);

        $fileSaver->expects($this->once())
            ->method('persistFileToMedia')
            ->willReturnCallback(function (MediaFile $m, string $dest) use ($expectedMediaFile, $expectedDestination): void {
                $this->assertSame($expectedMediaFile, $m);
                $this->assertSame($expectedDestination, $dest);
            });

        $result = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, $record);
        $result = \is_array($result) ? $result : iterator_to_array($result);

        $writtenResult = new EntityWriteResult($result['id'], $result, 'media', 'insert');
        $writtenEvent = new EntityWrittenEvent('media', [$writtenResult], $context);
        $eventDispatcher->dispatch($writtenEvent, 'media.written');
    }

    public function testInvalidUrl(): void
    {
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = static::getContainer()->get('media_folder.repository');
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $actual = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, ['url' => 'invalid']);
        $actual = \is_array($actual) ? $actual : iterator_to_array($actual);

        // only the error should be in the result
        static::assertCount(1, $actual);
        static::assertInstanceOf(InvalidMediaUrlException::class, $actual['_error']);
    }

    public function testEmpty(): void
    {
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = static::getContainer()->get('media_folder.repository');
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);
        $config = new Config([], [], []);

        $actual = $mediaSerializer->deserialize($config, $mediaDefinition, []);
        // should not contain url
        static::assertEmpty($actual);
    }

    public function testFailedDownload(): void
    {
        $serializerRegistry = static::getContainer()->get(SerializerRegistry::class);
        $mediaDefinition = static::getContainer()->get(MediaDefinition::class);

        $mediaService = $this->createMock(MediaService::class);
        $fileSaver = $this->createMock(FileSaver::class);

        $mediaFolderRepository = static::getContainer()->get('media_folder.repository');
        $mediaRepository = $this->createMock(EntityRepository::class);

        $mediaSerializer = new MediaSerializer($mediaService, $fileSaver, $mediaFolderRepository, $mediaRepository);
        $mediaSerializer->setRegistry($serializerRegistry);

        $record = [
            'url' => 'http://localhost/some/path/to/non/existing/image.png',
        ];

        $actual = $mediaSerializer->deserialize(new Config([], [], []), $mediaDefinition, $record);
        $actual = \is_array($actual) ? $actual : iterator_to_array($actual);
        static::assertInstanceOf(MediaDownloadException::class, $actual['_error']);
    }

    public function testSupportsOnlyMedia(): void
    {
        $serializer = new MediaSerializer(
            $this->createMock(MediaService::class),
            $this->createMock(FileSaver::class),
            static::getContainer()->get('media_folder.repository'),
            static::getContainer()->get('media.repository')
        );

        $definitionRegistry = static::getContainer()->get(DefinitionInstanceRegistry::class);
        foreach ($definitionRegistry->getDefinitions() as $definition) {
            $entity = $definition->getEntityName();

            if ($entity === MediaDefinition::ENTITY_NAME) {
                static::assertTrue($serializer->supports($entity));
            } else {
                static::assertFalse(
                    $serializer->supports($entity),
                    MediaSerializer::class . ' should not support ' . $entity
                );
            }
        }
    }
}
