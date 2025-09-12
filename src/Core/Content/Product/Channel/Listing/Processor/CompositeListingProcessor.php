<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Listing\Processor;

use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
final readonly class CompositeListingProcessor
{
    /**
     * @param iterable<AbstractListingProcessor> $processors
     *
     * @internal
     */
    public function __construct(private iterable $processors)
    {
    }

    public function getDecorated(): AbstractListingProcessor
    {
        throw new DecorationPatternException(self::class);
    }

    public function prepare(Request $request, Criteria $criteria, ChannelContext $context): void
    {
        foreach ($this->processors as $processor) {
            $processor->prepare($request, $criteria, $context);
        }
    }

    public function process(Request $request, ProductListingResult $result, ChannelContext $context): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($request, $result, $context);
        }
    }
}
