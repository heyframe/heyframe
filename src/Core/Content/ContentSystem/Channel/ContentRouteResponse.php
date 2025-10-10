<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel;

use HeyFrame\Core\Content\ContentSystem\Channel\Struct\ContentPage;
use HeyFrame\Core\Content\ContentSystem\Channel\Struct\DecomposedContentPage;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @final
 *
 * @extends StoreApiResponse<DecomposedContentPage>
 */
#[Package('discovery')]
class ContentRouteResponse extends StoreApiResponse
{
    public function __construct(
        public readonly ContentPage $contentPage,
    ) {
        parent::__construct($this->contentPage->getDecomposedContentPage());
    }

    public function getDecomposedContentPage(): DecomposedContentPage
    {
        return $this->object;
    }
}
