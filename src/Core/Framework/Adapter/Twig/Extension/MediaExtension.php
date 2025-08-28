<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Extension;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('framework')]
class MediaExtension extends AbstractExtension
{
    /**
     * @internal
     *
     * @param EntityRepository<MediaCollection> $mediaRepository
     */
    public function __construct(private readonly EntityRepository $mediaRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('searchMedia', $this->searchMedia(...)),
        ];
    }

    /**
     * @param array<string> $ids
     */
    public function searchMedia(array $ids, Context $context): MediaCollection
    {
        if (empty($ids)) {
            return new MediaCollection();
        }

        $criteria = new Criteria($ids);

        return $this->mediaRepository->search($criteria, $context)->getEntities();
    }
}
