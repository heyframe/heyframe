<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Channel;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('discovery')]
class MediaRoute extends AbstractMediaRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<MediaCollection> $mediaRepository
     */
    public function __construct(
        private readonly EntityRepository $mediaRepository
    ) {
    }

    public function getDecorated(): AbstractMediaRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/media', name: 'store-api.media.detail', methods: ['POST'])]
    public function load(Request $request, ChannelContext $context): MediaRouteResponse
    {
        $ids = $request->get('ids', []);
        if (empty($ids)) {
            throw MediaException::emptyMediaId();
        }

        return new MediaRouteResponse($this->findMediaByIds($ids, $context->getContext()));
    }

    /**
     * @param array<string> $ids
     */
    private function findMediaByIds(array $ids, Context $context): MediaCollection
    {
        $criteria = new Criteria($ids);
        $criteria->addFilter(new EqualsFilter('private', false));

        $mediaSearchResult = $this->mediaRepository
            ->search($criteria, $context);

        return $mediaSearchResult->getEntities();
    }
}
