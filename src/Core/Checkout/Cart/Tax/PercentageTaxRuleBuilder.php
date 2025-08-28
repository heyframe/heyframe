<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Tax;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\TaxRule;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PercentageTaxRuleBuilder
{
    public function buildRules(CalculatedPrice $price): TaxRuleCollection
    {
        return $this->buildCollectionRules($price->getCalculatedTaxes(), $price->getTotalPrice());
    }

    public function buildCollectionRules(CalculatedTaxCollection $taxes, float $totalPrice): TaxRuleCollection
    {
        $rules = new TaxRuleCollection([]);

        foreach ($taxes as $tax) {
            $rules->add(
                new TaxRule(
                    $tax->getTaxRate(),
                    $totalPrice !== 0.0 ? $tax->getPrice() / $totalPrice * 100 : 0
                )
            );
        }

        return $rules;
    }
}
