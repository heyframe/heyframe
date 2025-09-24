<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Media\Core\Application;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use HeyFrame\Core\Content\Media\Core\Application\RemoteThumbnailLoader;
use HeyFrame\Core\Content\Media\Extension\ResolveRemoteThumbnailUrlExtension;
use HeyFrame\Core\Content\Media\Infrastructure\Path\MediaUrlGenerator;
use HeyFrame\Core\Framework\Adapter\Filesystem\PrefixFilesystem;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
#[CoversClass(RemoteThumbnailLoader::class)]
class RemoteThumbnailLoaderTest extends TestCase
{
    /**
     * @param array<array<string, string>> $thumbnailSizes
     * @param array{media: string, thumbnails: array<string>} $expected
     */
    #[DataProvider('loadProvider')]
    public function testLoad(IdsCollection $ids, PartialEntity $entity, array $thumbnailSizes, array $expected): void
    {
        $filesystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'http://localhost:8000']);

        $prefixFilesystem = $this->createMock(PrefixFilesystem::class);
        $prefixFilesystem->method('publicUrl')->willReturn('http://localhost:8000');

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn($thumbnailSizes);

        $dispatcher = new EventDispatcher();
        $extensionDispatcher = new ExtensionDispatcher($dispatcher);

        $loader = new RemoteThumbnailLoader(
            new MediaUrlGenerator($filesystem),
            $connection,
            $prefixFilesystem,
            $extensionDispatcher,
            '{mediaUrl}/{mediaPath}?width={width}&ts={mediaUpdatedAt}'
        );

        $loader->load([$entity]);

        $actual = [$entity->get('id') => $entity->get('url')];

        static::assertArrayHasKey($ids->get('media'), $actual);
        static::assertSame($expected['media'], $actual[$ids->get('media')]);

        if (\count($thumbnailSizes) > 0) {
            static::assertIsIterable($entity->get('thumbnails'));

            foreach ($entity->get('thumbnails') as $thumbnail) {
                static::assertInstanceOf(MediaThumbnailEntity::class, $thumbnail);
                static::assertTrue(\in_array($thumbnail->get('url'), $expected['thumbnails'], true));
                static::assertSame($ids->get('media'), $thumbnail->getMediaId());
            }
        }
    }

    public static function loadProvider(): \Generator
    {
        $ids = new IdsCollection();
        yield 'Test without updated at' => [
            $ids,
            (new PartialEntity())->assign([
                'id' => $ids->get('media'),
                'path' => 'foo/bar.png',
                'mediaFolderId' => $ids->get('mediaFolderId'),
                'updatedAt' => new \DateTimeImmutable('2000-01-01'),
                'private' => false,
            ]),
            [
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '200', 'height' => '200'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '400', 'height' => '400'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '600', 'height' => '600'],
            ],
            [
                'media' => 'http://localhost:8000/foo/bar.png?ts=946656000',
                'thumbnails' => [
                    'http://localhost:8000/foo/bar.png?width=200&ts=',
                    'http://localhost:8000/foo/bar.png?width=400&ts=',
                    'http://localhost:8000/foo/bar.png?width=600&ts=',
                ],
            ],
        ];

        yield 'Test with updated at' => [
            $ids,
            (new PartialEntity())->assign([
                'id' => $ids->get('media'),
                'path' => 'foo/bar.png',
                'mediaFolderId' => $ids->get('mediaFolderId'),
                'updatedAt' => new \DateTimeImmutable('2000-01-01'),
                'private' => false,
            ]),
            [
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '200', 'height' => '200'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '400', 'height' => '400'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '600', 'height' => '600'],
            ],
            [
                'media' => 'http://localhost:8000/foo/bar.png?ts=946656000',
                'thumbnails' => [
                    'http://localhost:8000/foo/bar.png?width=200&ts=946656000',
                    'http://localhost:8000/foo/bar.png?width=400&ts=946656000',
                    'http://localhost:8000/foo/bar.png?width=600&ts=946656000',
                ],
            ],
        ];

        yield 'Test without thumbnail sizes' => [
            $ids,
            (new PartialEntity())->assign([
                'id' => $ids->get('media'),
                'path' => 'foo/bar.png',
                'mediaFolderId' => $ids->get('mediaFolderId'),
                'private' => false,
            ]),
            [],
            [
                'media' => 'http://localhost:8000/foo/bar.png',
                'thumbnails' => [],
            ],
        ];

        yield 'Test with media path is an external url' => [
            $ids,
            (new PartialEntity())->assign([
                'id' => $ids->get('media'),
                'path' => 'https://test.com/photo/flower.jpg',
                'mediaFolderId' => $ids->get('mediaFolderId'),
                'updatedAt' => new \DateTimeImmutable('2000-01-01'),
                'private' => false,
            ]),
            [
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '200', 'height' => '200'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '400', 'height' => '400'],
                ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '600', 'height' => '600'],
            ],
            [
                'media' => 'https://test.com/photo/flower.jpg?ts=946656000',
                'thumbnails' => [
                    'https://test.com/photo/flower.jpg?width=200&ts=946656000',
                    'https://test.com/photo/flower.jpg?width=400&ts=946656000',
                    'https://test.com/photo/flower.jpg?width=600&ts=946656000',
                ],
            ],
        ];
    }

    public function testReset(): void
    {
        $ids = new IdsCollection();
        $filesystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'http://localhost:8000']);

        $thumbnailSizes = [
            ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '200', 'height' => '200'],
            ['media_folder_id' => $ids->get('mediaFolderId'), 'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'), 'width' => '400', 'height' => '400'],
        ];

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn($thumbnailSizes);

        $entity = (new PartialEntity())->assign([
            'id' => $ids->get('media'),
            'path' => 'foo/bar.png',
            'mediaFolderId' => $ids->get('mediaFolderId'),
            'updatedAt' => new \DateTimeImmutable('2000-01-01'),
            'private' => false,
        ]);

        $dispatcher = new EventDispatcher();
        $extensionDispatcher = new ExtensionDispatcher($dispatcher);

        $loader = new RemoteThumbnailLoader(
            new MediaUrlGenerator($filesystem),
            $connection,
            $this->createMock(PrefixFilesystem::class),
            $extensionDispatcher,
            '{mediaUrl}/{mediaPath}?width={width}&ts={mediaUpdatedAt}'
        );

        $loader->load([$entity]);
        static::assertNotEmpty(ReflectionHelper::getPropertyValue($loader, 'mediaFolderThumbnailSizes'));

        $loader->reset();
        static::assertEmpty(ReflectionHelper::getPropertyValue($loader, 'mediaFolderThumbnailSizes'));
    }

    public function testExtensionSkipThumbnail(): void
    {
        $ids = new IdsCollection();
        $filesystem = new Filesystem(new InMemoryFilesystemAdapter(), ['public_url' => 'http://localhost:8000']);

        $thumbnailSizes = [
            [
                'media_folder_id' => $ids->get('mediaFolderId'),
                'width' => '200',
                'height' => '200',
                'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'),
            ],
            [
                'media_folder_id' => $ids->get('mediaFolderId'),
                'width' => '400',
                'height' => '400',
                'media_thumbnail_size_id' => $ids->get('mediaThumbnailSizeId'),
            ],
        ];

        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn($thumbnailSizes);

        $entity = (new PartialEntity())->assign([
            'id' => $ids->get('media'),
            'path' => 'foo/bar.png',
            'updatedAt' => new \DateTimeImmutable('2000-01-01'),
            'mediaFolderId' => $ids->get('mediaFolderId'),
            'private' => false,
        ]);

        $dispatcher = new EventDispatcher();
        $extensionDispatcher = new ExtensionDispatcher($dispatcher);

        $loader = new RemoteThumbnailLoader(
            new MediaUrlGenerator($filesystem),
            $connection,
            $this->createMock(PrefixFilesystem::class),
            $extensionDispatcher,
            '{mediaUrl}/{mediaPath}?width={width}&ts={mediaUpdatedAt}'
        );

        $dispatcher->addListener(
            ResolveRemoteThumbnailUrlExtension::NAME . '.pre',
            function (ResolveRemoteThumbnailUrlExtension $event): void {
                if ($event->width === '400') {
                    $event->result = null;
                    $event->stopPropagation();
                }
            }
        );

        $loader->load([$entity]);

        static::assertCount(1, $entity->get('thumbnails'));
    }
}
