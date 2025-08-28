<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class ConfiguredSystemConfigLoader extends AbstractSystemConfigLoader
{
    public function __construct(
        private readonly AbstractSystemConfigLoader $decorated,
        private readonly SymfonySystemConfigService $config,
    ) {
    }

    public function getDecorated(): AbstractSystemConfigLoader
    {
        return $this->decorated;
    }

    /**
     * @return array<mixed>
     */
    public function load(?string $channelId): array
    {
        $config = $this->decorated->load($channelId);

        return $this->config->override($config, $channelId);
    }
}
