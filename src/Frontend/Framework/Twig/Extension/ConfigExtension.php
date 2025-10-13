<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig\Extension;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Frontend\Framework\FrontendFrameworkException;
use HeyFrame\Frontend\Framework\Twig\TemplateConfigAccessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('framework')]
class ConfigExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct(private readonly TemplateConfigAccessor $config)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', $this->config(...), ['needs_context' => true]),
            new TwigFunction('theme_config', $this->theme(...), ['needs_context' => true]),
            new TwigFunction('theme_scripts', $this->scripts(...), ['needs_context' => true]),
            new TwigFunction('component_scripts', $this->componentScripts(...), ['needs_context' => true]),
            new TwigFunction('mounted_component_scripts', $this->mountedComponentScripts(...), ['needs_context' => true]),
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return string|bool|array<mixed>|float|int|null
     */
    public function config(array $context, string $key)
    {
        return $this->config->config($key, $this->getChannelId($context));
    }

    /**
     * @param array<string, ChannelContext|string> $context
     *
     * @return string|bool|array<string, mixed>|float|int|null
     */
    public function theme(array $context, string $key)
    {
        return $this->config->theme($key, $this->getContext($context), $this->getThemeId($context));
    }

    /**
     * Returns all scripts, except components.
     *
     * @return array<int, string> $items
     */
    public function scripts(): array
    {
        return $this->config->scripts();
    }

    /**
     * Returns all scripts that belong to a component.
     *
     * @return array<int, string>
     */
    public function componentScripts(): array
    {
        return $this->config->componentScripts();
    }

    /**
     * Returns all scripts of components that have been mounted in the template.
     *
     * @return array<int, string>
     */
    public function mountedComponentScripts(): array
    {
        return $this->config->mountedComponentScripts();
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getChannelId(array $context): ?string
    {
        if (isset($context['context'])) {
            $channelContext = $context['context'];
            if ($channelContext instanceof ChannelContext) {
                return $channelContext->getChannelId();
            }
        }
        if (isset($context['channel'])) {
            $channel = $context['channel'];
            if ($channel instanceof ChannelEntity) {
                return $channel->getId();
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getThemeId(array $context): ?string
    {
        return $context['themeId'] ?? null;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getContext(array $context): ChannelContext
    {
        if (!isset($context['context'])) {
            throw FrontendFrameworkException::channelContextObjectNotFound();
        }

        $context = $context['context'];

        if (!$context instanceof ChannelContext) {
            throw FrontendFrameworkException::channelContextObjectNotFound();
        }

        return $context;
    }
}
