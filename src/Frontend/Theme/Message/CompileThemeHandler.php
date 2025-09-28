<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Message;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Notification\NotificationService;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Frontend\Theme\ConfigLoader\AbstractConfigLoader;
use HeyFrame\Frontend\Theme\Exception\ThemeException;
use HeyFrame\Frontend\Theme\FrontendPluginRegistry;
use HeyFrame\Frontend\Theme\ThemeCompilerInterface;
use HeyFrame\Frontend\Theme\ThemeRuntimeConfigService;
use HeyFrame\Frontend\Theme\ThemeService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('framework')]
final readonly class CompileThemeHandler
{
    /**
     * @param EntityRepository<ChannelCollection> $saleschannelRepository
     */
    public function __construct(
        private ThemeCompilerInterface $themeCompiler,
        private AbstractConfigLoader $configLoader,
        private FrontendPluginRegistry $extensionRegistry,
        private NotificationService $notificationService,
        private EntityRepository $saleschannelRepository,
        private ThemeRuntimeConfigService $runtimeConfigService,
    ) {
    }

    public function __invoke(CompileThemeMessage $message): void
    {
        $message->getContext()->addState(ThemeService::STATE_NO_QUEUE);
        $themeConfig = $this->configLoader->load($message->getThemeId(), $message->getContext());
        $this->themeCompiler->compileTheme(
            $message->getChannelId(),
            $message->getThemeId(),
            $themeConfig,
            $this->extensionRegistry->getConfigurations(),
            $message->isWithAssets(),
            $message->getContext()
        );

        $this->runtimeConfigService->refreshRuntimeConfig(
            $message->getThemeId(),
            $themeConfig,
            $message->getContext(),
            false,
            $this->extensionRegistry->getConfigurations(),
        );

        if ($message->getContext()->getScope() !== Context::USER_SCOPE) {
            return;
        }

        $channel = $this->saleschannelRepository->search(
            new Criteria([$message->getChannelId()]),
            $message->getContext()
        )->getEntities()->first();
        if (!$channel) {
            throw ThemeException::channelNotFound($message->getChannelId());
        }

        $this->notificationService->createNotification(
            [
                'id' => Uuid::randomHex(),
                'status' => 'info',
                'message' => 'Compilation for sales channel ' . $channel->getName() . ' completed',
                'requiredPrivileges' => [],
            ],
            $message->getContext()
        );
    }
}
