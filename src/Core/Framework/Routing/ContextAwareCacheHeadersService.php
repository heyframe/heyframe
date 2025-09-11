<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\Framework\Adapter\Cache\Http\CacheRelevantRulesResolver;
use HeyFrame\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('framework')]
readonly class ContextAwareCacheHeadersService
{
    public function __construct(
        private EntityCacheKeyGenerator $cacheKeyGenerator,
        private CacheRelevantRulesResolver $rulesResolver,
    ) {
    }

    public function addContextHeaders(Request $request, Response $response, ChannelContext $context): void
    {
        // Add context headers to response
        $response->headers->set(PlatformRequest::HEADER_LANGUAGE_ID, $context->getLanguageId());
        $response->headers->set(PlatformRequest::HEADER_CURRENCY_ID, $context->getCurrencyId());
        $response->headers->set(PlatformRequest::HEADER_CONTEXT_HASH, $this->generateContextHash($context, $request));

        // Add vary headers for caching
        $this->addVaryHeaders($response);
    }

    private function generateContextHash(ChannelContext $context, Request $request): string
    {
        $ruleAreas = $this->rulesResolver->resolveRuleAreas($request, $context);

        return $this->cacheKeyGenerator->getChannelContextHash($context, $ruleAreas);
    }

    private function addVaryHeaders(Response $response): void
    {
        $varyHeaders = [
            PlatformRequest::HEADER_LANGUAGE_ID,
            PlatformRequest::HEADER_CURRENCY_ID,
            PlatformRequest::HEADER_CONTEXT_HASH,
        ];

        $existingVary = $response->headers->get('Vary', '');
        $varyArray = array_filter(explode(',', $existingVary));

        $newVaryArray = array_merge($varyArray, $varyHeaders);
        $newVaryArray = array_unique(array_map(fn (string $v) => \trim($v), $newVaryArray));

        $response->headers->set('Vary', implode(', ', $newVaryArray));
    }
}
