<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cms;

use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\Channel\Struct\ProductSliderStruct;
use HeyFrame\Core\Content\Cms\DataResolver\CriteriaCollection;
use HeyFrame\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Content\Product\Cms\ProductSlider\AbstractProductSliderProcessor;
use HeyFrame\Core\Framework\Log\Package;
use Psr\Log\LoggerInterface;

#[Package('discovery')]
class ProductSliderCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @var array<string, AbstractProductSliderProcessor>
     */
    private array $processors = [];

    /**
     * @param iterable<AbstractProductSliderProcessor> $processors
     *
     * @internal
     */
    public function __construct(
        iterable $processors,
        private readonly LoggerInterface $logger
    ) {
        foreach ($processors as $processor) {
            $this->processors[$processor->getSource()] = $processor;
        }
    }

    public function getType(): string
    {
        return 'product-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $productConfig = $config->get('products');

        if (!$productConfig || !$productConfig->getValue()) {
            return null;
        }

        $source = $productConfig->getSource();
        $processor = $this->processors[$source] ?? null;

        if (!$processor) {
            $this->logNoProcessorFoundError($source);

            return null;
        }

        return $processor->collect($slot, $config, $resolverContext);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $slider = new ProductSliderStruct();
        $slot->setData($slider);

        $productConfig = $config->get('products');

        if (!$productConfig) {
            return;
        }

        $source = $productConfig->getSource();
        $processor = $this->processors[$source] ?? null;

        if (!$processor) {
            $this->logNoProcessorFoundError($source);

            return;
        }

        $processor->enrich($slot, $result, $resolverContext);
    }

    private function logNoProcessorFoundError(string $source): void
    {
        $this->logger->error(\sprintf('No product slider processor found by provided source: "%s"', $source));
    }
}
