<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\FrontendPluginConfiguration;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<FrontendPluginConfiguration>
 */
#[Package('framework')]
class FrontendPluginConfigurationCollection extends Collection
{
    public function __construct(iterable $elements = [])
    {
        parent::__construct();

        foreach ($elements as $element) {
            $this->validateType($element);

            $this->set($element->getTechnicalName(), $element);
        }
    }

    public function add($element): void
    {
        $this->validateType($element);

        $this->set($element->getTechnicalName(), $element);
    }

    public function getByTechnicalName(string $name): ?FrontendPluginConfiguration
    {
        return $this->filter(fn (FrontendPluginConfiguration $config) => $config->getTechnicalName() === $name)->first();
    }

    public function getThemes(): FrontendPluginConfigurationCollection
    {
        return $this->filter(fn (FrontendPluginConfiguration $configuration) => $configuration->getIsTheme());
    }

    public function getNoneThemes(): FrontendPluginConfigurationCollection
    {
        return $this->filter(fn (FrontendPluginConfiguration $configuration) => !$configuration->getIsTheme());
    }

    protected function getExpectedClass(): ?string
    {
        return FrontendPluginConfiguration::class;
    }
}
