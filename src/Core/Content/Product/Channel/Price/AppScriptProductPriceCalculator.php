<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Price;

use HeyFrame\Core\Checkout\Cart\Facade\ScriptPriceStubs;
use HeyFrame\Core\Content\Product\Hook\Pricing\ProductPricingHook;
use HeyFrame\Core\Content\Product\Hook\Pricing\ProductProxy;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\ScriptExecutor;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class AppScriptProductPriceCalculator extends AbstractProductPriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductPriceCalculator $decorated,
        private readonly ScriptExecutor $scriptExecutor,
        private readonly ScriptPriceStubs $priceStubs
    ) {
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        return $this->decorated;
    }

    public function calculate(iterable $products, ChannelContext $context): void
    {
        $this->decorated->calculate($products, $context);

        $proxies = [];
        foreach ($products as $product) {
            $proxies[$product->get('id')] = new ProductProxy($product, $context, $this->priceStubs);
        }

        $this->scriptExecutor->execute(new ProductPricingHook($proxies, $context));
    }
}
