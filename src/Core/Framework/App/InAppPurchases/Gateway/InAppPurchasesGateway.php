<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InAppPurchases\Gateway;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\InAppPurchases\Event\InAppPurchasesGatewayEvent;
use HeyFrame\Core\Framework\App\InAppPurchases\Payload\InAppPurchasesPayload;
use HeyFrame\Core\Framework\App\InAppPurchases\Payload\InAppPurchasesPayloadService;
use HeyFrame\Core\Framework\App\InAppPurchases\Response\InAppPurchasesResponse;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class InAppPurchasesGateway
{
    public function __construct(
        private readonly InAppPurchasesPayloadService $payloadService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function process(InAppPurchasesPayload $payload, Context $context, AppEntity $app): ?InAppPurchasesResponse
    {
        $url = $app->getInAppPurchasesGatewayUrl();

        if ($url === null) {
            return null;
        }

        $response = $this->payloadService->request($url, $payload, $app, $context);

        $this->eventDispatcher->dispatch(new InAppPurchasesGatewayEvent($response));

        return $response;
    }
}
