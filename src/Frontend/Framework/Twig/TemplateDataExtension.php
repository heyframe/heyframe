<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Term\Filter\AbstractTokenFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

#[Package('framework')]
class TemplateDataExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly bool $showStagingBanner,
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return [];
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof ChannelContext) {
            return [];
        }

        [$controllerName, $controllerAction] = $this->getControllerInfo($request);

        $themeId = $request->attributes->get(ChannelRequest::ATTRIBUTE_THEME_ID);

        return [
            'heyframe' => [
                'dateFormat' => \DATE_ATOM,
                'minSearchLength' => $this->minSearchLength($context),
                'showStagingBanner' => $this->showStagingBanner,
            ],
            'themeId' => $themeId, /** Not used in Twig template directly, but in @see \HeyFrame\Storefront\Framework\Twig\Extension\ConfigExtension::getThemeId */
            'context' => $context,
            'activeRoute' => $request->attributes->get('_route'),
            'formViolations' => $request->attributes->get('formViolations'),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function getControllerInfo(Request $request): array
    {
        $controller = $request->attributes->getString('_controller');
        if ($controller === '') {
            return ['', ''];
        }

        $matches = [];
        preg_match('/Controller\\\\(\w+)Controller::?(\w+)$/', $controller, $matches);
        if ($matches) {
            return [$matches[1], $matches[2]];
        }

        return ['', ''];
    }

    private function minSearchLength(ChannelContext $context): int
    {
        $min = (int) $this->connection->fetchOne(
            'SELECT `min_search_length` FROM `product_search_config` WHERE `language_id` = :id',
            ['id' => Uuid::fromHexToBytes($context->getLanguageId())]
        );

        return $min ?: AbstractTokenFilter::DEFAULT_MIN_SEARCH_TERM_LENGTH;
    }
}
