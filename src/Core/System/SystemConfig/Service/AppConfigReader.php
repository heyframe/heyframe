<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig\Service;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Source\SourceResolver;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\SystemConfig\Util\ConfigReader;

/**
 * @internal
 */
#[Package('framework')]
class AppConfigReader
{
    public function __construct(private readonly SourceResolver $sourceResolver, private readonly ConfigReader $configReader)
    {
    }

    /**
     * @return array<array<string, mixed>>|null
     */
    public function read(AppEntity $app): ?array
    {
        $fs = $this->sourceResolver->filesystemForApp($app);
        if (!$fs->has('Resources/config/config.xml')) {
            return null;
        }

        return $this->configReader->read($fs->path('Resources/config/config.xml'));
    }
}
