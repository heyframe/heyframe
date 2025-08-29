<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Price\GrossPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class PriceActionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GrossPriceCalculator $grossCalculator
    ) {
    }

    #[Route(path: 'api/_action/calculate-price', name: 'api.action.calculate-price', methods: ['POST'])]
    public function calculate(Request $request, Context $context): JsonResponse
    {
        if (!$request->request->has('price')) {
            throw CartException::priceParameterIsMissing();
        }

        $price = (float) $request->request->get('price');
        $quantity = $request->request->getInt('quantity', 1);
        $output = (string) $request->request->get('output', 'gross');
        $preCalculated = $request->request->getBoolean('calculated', true);

        $data = $this->calculatePrice($price, $quantity, $output, $preCalculated);

        return new JsonResponse(
            ['data' => $data]
        );
    }

    #[Route(path: 'api/_action/calculate-prices', name: 'api.action.calculate-prices', methods: ['POST'])]
    public function calculatePrices(Request $request, Context $context): JsonResponse
    {
        $productPrices = $request->request->all('prices');

        if (empty($productPrices)) {
            throw CartException::pricesParameterIsMissing();
        }

        $data = [];

        foreach ($productPrices as $productId => $prices) {
            $calculatedPrices = [];
            foreach ($prices as $price) {
                $quantity = $price['quantity'] ?? 1;
                $output = $price['output'] ?? 'gross';
                $preCalculated = $price['calculated'] ?? true;

                $calculatedPrices[$price['currencyId']] = $this->calculatePrice((float) $price['price'], (int) $quantity, $output, (bool) $preCalculated);
            }

            $data[$productId] = $calculatedPrices;
        }

        return new JsonResponse(
            ['data' => $data]
        );
    }

    /**
     * @return array<mixed>
     */
    private function calculatePrice(float $price, int $quantity, string $output, bool $preCalculated): array
    {
        $calculator = $this->grossCalculator;

        $definition = new QuantityPriceDefinition($price, $quantity);
        $definition->setIsCalculated($preCalculated);

        $config = new CashRoundingConfig(50, 0.01, true);

        $calculated = $calculator->calculate($definition, $config);

        return json_decode((string) json_encode($calculated, \JSON_PRESERVE_ZERO_FRACTION), true, 512, \JSON_THROW_ON_ERROR);
    }
}
