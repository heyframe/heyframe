<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order\Transformer;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Api\Context\AdminChannelApiSource;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Json;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CartTransformer
{
    /**
     * @deprecated tag:v6.8.0 - reason:parameter-name-change - parameter `$setOrderDate` will be renamed to `$setPersistentData`
     *
     * @return array<string, mixed>
     */
    public static function transform(Cart $cart, ChannelContext $context, string $stateId, bool $setOrderDate = true): array
    {
        $currency = $context->getCurrency();

        $data = [
            'price' => $cart->getPrice(),
            'stateId' => $stateId,
            'currencyId' => $currency->getId(),
            'currencyFactor' => $currency->getFactor(),
            'channelId' => $context->getChannelId(),
            'lineItems' => [],
            'source' => $cart->getSource(),
        ];

        if ($setOrderDate) {
            $data['orderDateTime'] = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $data['deepLinkCode'] = Random::getBase64UrlString(32);
        }

        $source = $context->getContext()->getSource();
        if ($source instanceof AdminChannelApiSource) {
            $originalContextSource = $source->getOriginalContext()->getSource();
            if ($originalContextSource instanceof AdminApiSource) {
                $data['createdById'] = $originalContextSource->getUserId();
            }
        }

        $data['itemRounding'] = json_decode(Json::encode($context->getItemRounding()), true, 512, \JSON_THROW_ON_ERROR);
        $data['totalRounding'] = json_decode(Json::encode($context->getTotalRounding()), true, 512, \JSON_THROW_ON_ERROR);

        return $data;
    }
}
