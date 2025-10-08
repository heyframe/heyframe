<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Response;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPageStruct;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Transforms ContentPageStruct into standardized API response structure.
 * Separates API contract from hydration logic for versioning and metadata flexibility.
 *
 * @internal
 */
#[Package('discovery')]
class ContentResponseGenerator
{
    /**
     * Generate final API response from content page.
     *
     * @param ContentPageStruct $contentPage Hydrated content page
     * @param ChannelContext $context Sales channel context
     *
     * @return array<string, mixed> API response structure
     */
    public function generate(ContentPageStruct $contentPage, ChannelContext $context): array
    {
        return [
            'layout' => $this->buildLayoutMetadata($contentPage),
            'elements' => $this->extractElements($contentPage),
            'context' => $this->buildContextMetadata($context),
            'metadata' => $this->buildPageMetadata($contentPage, $context),
        ];
    }

    /**
     * Build layout metadata section.
     *
     * @return array<string, mixed>
     */
    private function buildLayoutMetadata(ContentPageStruct $contentPage): array
    {
        return [
            'id' => $contentPage->getLayoutId(),
            'version' => $contentPage->getLayoutVersion(),
            'name' => $contentPage->getLayoutName(),
        ];
    }

    /**
     * Extract elements from content page layout.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractElements(ContentPageStruct $contentPage): array
    {
        $layout = $contentPage->getLayout();

        if ($layout === null) {
            return [];
        }

        // Convert ContentElement to array for API response
        $layoutArray = $layout->toArray();

        // Return elements array if present, otherwise return layout as single element
        if (isset($layoutArray['elements']) && \is_array($layoutArray['elements'])) {
            return $layoutArray['elements'];
        }

        // If layout is a single element, wrap it in array
        if (isset($layoutArray['type'])) {
            return [$layoutArray];
        }

        return [];
    }

    /**
     * Build context metadata section with sales channel information.
     *
     * @return array<string, mixed>
     */
    private function buildContextMetadata(ChannelContext $context): array
    {
        return [
            'channelId' => $context->getChannel()->getId(),
            'languageId' => $context->getLanguageId(),
            'currencyId' => $context->getCurrency()->getId(),
            'customerId' => $context->getCustomer()?->getId(),
        ];
    }

    /**
     * Build page metadata (SEO, breadcrumbs, etc.).
     *
     * @return array<string, mixed>
     */
    private function buildPageMetadata(ContentPageStruct $contentPage, ChannelContext $context): array
    {
        // Future: Add SEO metadata, breadcrumbs, canonical URLs, etc.
        // For now, return empty metadata
        return [];
    }
}
