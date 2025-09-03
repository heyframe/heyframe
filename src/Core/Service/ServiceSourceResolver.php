<?php declare(strict_types=1);

namespace HeyFrame\Core\Service;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\AppExtractor;
use HeyFrame\Core\Framework\App\Exception\AppArchiveValidationFailure;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Source\Source;
use HeyFrame\Core\Framework\App\Source\TemporaryDirectoryFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginException;
use HeyFrame\Core\Framework\Util\Filesystem;
use HeyFrame\Core\Service\ServiceRegistry\Client;
use Symfony\Component\Filesystem\Filesystem as Io;
use Symfony\Component\Filesystem\Path;

/**
 * @internal
 *
 * @phpstan-type ServiceSourceConfig array{version: string, hash: string, revision: string, zip-url: string, hash-algorithm: ?string, min-shop-supported-version: ?string}
 */
#[Package('framework')]
class ServiceSourceResolver implements Source
{
    public function __construct(
        private readonly Client $client,
        private readonly TemporaryDirectoryFactory $temporaryDirectoryFactory,
        private readonly AppExtractor $appExtractor,
        private readonly Io $io
    ) {
    }

    public static function name(): string
    {
        return 'service';
    }

    public function filesystemForVersion(AppInfo $appInfo): Filesystem
    {
        return new Filesystem($this->downloadVersion($appInfo->name, $appInfo->zipUrl));
    }

    public function supports(Manifest|AppEntity $app): bool
    {
        return match (true) {
            $app instanceof AppEntity => $app->getSourceType() === $this->name(),
            $app instanceof Manifest => preg_match('#^https?://#', $app->getPath()) && $app->getMetadata()->isSelfManaged(),
        };
    }

    public function filesystem(Manifest|AppEntity $app): Filesystem
    {
        $temporaryDirectory = $this->temporaryDirectoryFactory->path();

        $name = $app instanceof Manifest ? $app->getMetadata()->getName() : $app->getName();

        // app is already on the filesystem, use that
        $appPath = Path::join($temporaryDirectory, $name);
        if ($this->io->exists($appPath)) {
            return new Filesystem($appPath);
        }

        /** @var ServiceSourceConfig $sourceConfig */
        $sourceConfig = $app->getSourceConfig();
        $appInfo = AppInfo::fromNameAndSourceConfig($name, $sourceConfig);

        return $this->filesystemForVersion($appInfo);
    }

    public function reset(array $filesystems): void
    {
    }

    private function downloadVersion(
        string $serviceName,
        string $zipUrl,
    ): string {
        $destination = Path::join($this->temporaryDirectoryFactory->path(), $serviceName);
        $localZipLocation = Path::join($destination, $serviceName . '.zip');

        try {
            $zipData = $this->client->fetchServiceZip($zipUrl);
            $this->io->mkdir($destination);
            foreach ($zipData as $chunk) {
                $this->io->appendToFile($localZipLocation, $chunk->getContent());
            }
        } catch (\Exception $e) {
            $this->io->remove($destination); // corrupted download, remove partially written data
            throw AppException::cannotMountAppFilesystem( // @phpstan-ignore heyframe.domainException
                $serviceName,
                ServiceException::cannotWriteAppToDestination($destination, $e)
            );
        }

        try {
            $this->appExtractor->extract(
                $localZipLocation,
                $this->temporaryDirectoryFactory->path(),
                $serviceName,
            );
        } catch (PluginException|AppArchiveValidationFailure $e) {
            throw AppException::cannotMountAppFilesystem($serviceName, $e); // @phpstan-ignore heyframe.domainException
        } finally {
            $this->io->remove($localZipLocation);
        }

        return $destination;
    }
}
