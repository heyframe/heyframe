<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Http;

use HeyFrame\Core\Framework\Adapter\Cache\Http\Extension\ResolveCacheRelevantRuleIdsExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 */
#[Package('framework')]
readonly class CacheRelevantRulesResolver
{
    /**
     * @internal
     */
    public function __construct(
        private ExtensionDispatcher $extensions,
    ) {
    }

    /**
     * @return list<string> List of rule IDs which should be considered for the HTTP Cache in the context cookie / header
     */
    public function resolveRuleAreas(Request $request, ChannelContext $context): array
    {
        $ruleIdsExtension = new ResolveCacheRelevantRuleIdsExtension($request, [RuleAreas::PRODUCT_AREA], $context);

        /** @var list<string> $ruleAreas */
        $ruleAreas = $this->extensions->publish(
            name: ResolveCacheRelevantRuleIdsExtension::NAME,
            extension: $ruleIdsExtension,
            function: function (Request $request, array $ruleAreas, ChannelContext $channelContext): array {
                return $ruleAreas;
            },
        );

        return $ruleAreas;
    }
}
