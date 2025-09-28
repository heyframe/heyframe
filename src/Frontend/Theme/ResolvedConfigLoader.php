<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Theme\Exception\ThemeException;

#[Package('framework')]
class ResolvedConfigLoader extends AbstractResolvedConfigLoader
{
    /**
     * @internal
     *
     * @param EntityRepository<MediaCollection> $repository
     */
    public function __construct(
        private readonly EntityRepository $repository,
        private readonly ThemeRuntimeConfigService $runtimeConfigService,
    ) {
    }

    public function getDecorated(): AbstractResolvedConfigLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $themeId, ChannelContext $context): array
    {
        $runtimeConfig = $this->runtimeConfigService->getRuntimeConfig($themeId);
        if ($runtimeConfig === null) {
            throw ThemeException::errorLoadingRuntimeConfig($themeId);
        }
        $config = $runtimeConfig->resolvedConfig;
        $resolvedConfig = [];
        $mediaItems = [];
        if (!\array_key_exists('fields', $config)) {
            return [];
        }

        foreach ($config['fields'] as $key => $data) {
            if (isset($data['type']) && $data['type'] === 'media' && $data['value'] && Uuid::isValid($data['value'])) {
                $mediaItems[$data['value']][] = $key;
            }
            $resolvedConfig[$key] = $data['value'];
        }

        $result = new MediaCollection();

        /** @var list<string> $mediaIds */
        $mediaIds = array_keys($mediaItems);
        if (!empty($mediaIds)) {
            $criteria = (new Criteria($mediaIds))
                ->setTitle('theme-service::resolve-media');

            $result = $this->repository->search($criteria, $context->getContext())->getEntities();
        }

        foreach ($result as $media) {
            if (!\array_key_exists($media->getId(), $mediaItems)) {
                continue;
            }

            foreach ($mediaItems[$media->getId()] as $key) {
                $resolvedConfig[$key] = $media->getUrl();
            }
        }

        return $resolvedConfig;
    }
}
