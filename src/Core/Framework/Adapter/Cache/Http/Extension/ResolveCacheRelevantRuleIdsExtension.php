<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Http\Extension;

use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends Extension<array<string>>
 */
#[Package('framework')]
final class ResolveCacheRelevantRuleIdsExtension extends Extension
{
    public const NAME = 'cache-response.resolve-rule-areas';

    /**
     * @internal HeyFrame owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The HTTP request object
         */
        public readonly Request $request,

        /**
         * @public
         *
         * @description RuleAreas which should be considered for the HTTP Cache in the context cookie
         *
         * @var list<string>
         */
        public array $ruleAreas,

        /**
         * @public
         *
         * @description The sales channel context
         */
        public readonly ChannelContext $channelContext,
    ) {
    }
}
