<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Store;

use HeyFrame\Core\Framework\App\Lifecycle\AppLifecycle;
use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppInstallParameters;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
trait ServiceBehaviour
{
    use ExtensionBehaviour;

    public function installService(string $path, bool $install = true): void
    {
        $appRepository = static::getContainer()->get('app.repository');
        $idResult = $appRepository->searchIds(new Criteria(), Context::createDefaultContext());

        /** @var array<string> $ids */
        $ids = $idResult->getIds();
        if (\count($ids)) {
            $appRepository->delete(array_map(fn (string $id) => ['id' => $id], $ids), Context::createDefaultContext());
        }

        $fs = new Filesystem();

        $name = basename($path);
        $appDir = static::getContainer()->getParameter('heyframe.app_dir') . '/' . $name;
        $fs->mirror($path, $appDir);

        $manifest = Manifest::createFromXmlFile($appDir . '/manifest.xml');
        $manifest->getMetadata()->setSelfManaged(true);

        if ($install) {
            static::getContainer()
                ->get(AppLifecycle::class)
                ->install($manifest, new AppInstallParameters(), Context::createDefaultContext());
        }
    }
}
