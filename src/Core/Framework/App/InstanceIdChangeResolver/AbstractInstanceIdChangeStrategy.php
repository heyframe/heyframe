<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InstanceIdChangeResolver;

use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Lifecycle\Registration\AppRegistrationService;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Source\SourceResolver;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
abstract class AbstractInstanceIdChangeStrategy
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly SourceResolver $sourceResolver,
        private readonly EntityRepository $appRepository,
        private readonly AppRegistrationService $registrationService
    ) {
    }

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function resolve(Context $context): void;

    abstract public function getDecorated(): self;

    /**
     * @param callable(Manifest, AppEntity, Context): void $callback
     */
    protected function forEachInstalledApp(Context $context, callable $callback): void
    {
        $apps = $this->appRepository->search(new Criteria(), $context);

        foreach ($apps as $app) {
            $fs = $this->sourceResolver->filesystemForApp($app);
            $path = $fs->hasFile('manifest.local.xml') ? 'manifest.local.xml' : 'manifest.xml';
            $manifest = Manifest::createFromXmlFile($fs->path($path));

            if (!$manifest->getSetup()) {
                continue;
            }

            $callback($manifest, $app, $context);
        }
    }

    protected function reRegisterApp(Manifest $manifest, AppEntity $app, Context $context): void
    {
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $this->appRepository->update([
            [
                'id' => $app->getId(),
                'integration' => [
                    'id' => $app->getIntegrationId(),
                    'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
                    'secretAccessKey' => $secret,
                ],
            ],
        ], $context);

        $this->registrationService->registerApp($manifest, $app->getId(), $secret, $context);
    }
}
