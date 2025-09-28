<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('framework')]
readonly class ThemeScripts
{
    /**
     * @internal
     */
    public function __construct(
        private RequestStack $requestStack,
        private ThemeRuntimeConfigService $themeRuntimeConfigService,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getThemeScripts(): array
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return [];
        }

        $themeId = $request->attributes->get(ChannelRequest::ATTRIBUTE_THEME_ID);

        if ($themeId === null) {
            return [];
        }

        $channelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$channelContext instanceof ChannelContext) {
            return [];
        }

        $runtimeConfig = $this->themeRuntimeConfigService->getResolvedRuntimeConfig($themeId);

        if ($runtimeConfig === null) {
            return [];
        }
        \assert($runtimeConfig->scriptFiles !== null);

        return $runtimeConfig->scriptFiles;
    }
}
