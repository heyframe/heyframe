<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\FindVariant;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class FindProductVariantRoute extends AbstractFindProductVariantRoute
{
    /**
     * @internal
     *
     * @param ChannelRepository<ProductCollection> $productRepository
     */
    public function __construct(
        private readonly ChannelRepository $productRepository,
    ) {
    }

    public function getDecorated(): AbstractFindProductVariantRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/front-api/product/{productId}/find-variant',
        name: 'front-api.product.find-variant',
        defaults: ['_entity' => 'product'],
        methods: ['POST']
    )]
    public function load(string $productId, Request $request, ChannelContext $context): FindProductVariantRouteResponse
    {
        $switchedGroup = $request->get('switchedGroup');

        $options = $request->get('options') ? $request->get('options', []) : [];

        foreach ($options as $optionId) {
            if (!\is_string($optionId)) {
                throw ProductException::invalidOptionsParameter();
            }
        }

        $variantId = $this->searchForOptions($productId, $context, $options);

        if ($variantId !== null) {
            return new FindProductVariantRouteResponse(new FoundCombination($variantId, $options));
        }

        while (\count($options) > 1) {
            foreach ($options as $groupId => $_optionId) {
                if ($groupId !== $switchedGroup) {
                    unset($options[$groupId]);

                    break;
                }
            }

            $variantId = $this->searchForOptions($productId, $context, $options);

            if ($variantId) {
                return new FindProductVariantRouteResponse(new FoundCombination($variantId, $options));
            }
        }

        throw ProductException::variantNotFound($productId, $options);
    }

    /**
     * @param array<string> $options
     */
    private function searchForOptions(
        string $productId,
        ChannelContext $channelContext,
        array $options
    ): ?string {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->setLimit(1);

        foreach ($options as $optionId) {
            $criteria->addFilter(new EqualsFilter('product.optionIds', $optionId));
        }

        return $this->productRepository->searchIds($criteria, $channelContext)->firstId();
    }
}
