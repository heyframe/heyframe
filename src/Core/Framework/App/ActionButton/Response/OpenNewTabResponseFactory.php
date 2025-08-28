<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ActionButton\Response;

use HeyFrame\Core\Framework\App\ActionButton\AppAction;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Hmac\QuerySigner;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class OpenNewTabResponseFactory implements ActionButtonResponseFactoryInterface
{
    public function __construct(private readonly QuerySigner $signer)
    {
    }

    public function supports(string $actionType): bool
    {
        return $actionType === OpenNewTabResponse::ACTION_TYPE;
    }

    public function create(AppAction $action, array $payload, Context $context): ActionButtonResponse
    {
        $this->validate($payload, $action->getActionId());

        $appSecret = $action->getApp()->getAppSecret();
        if ($appSecret) {
            $payload['redirectUrl'] = (string) $this->signer->signUri($payload['redirectUrl'], $action->getApp(), $context);
        }

        $response = new OpenNewTabResponse();
        $response->assign($payload);

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validate(array $payload, string $actionId): void
    {
        if (empty($payload['redirectUrl'])) {
            throw AppException::actionButtonProcessException($actionId, 'The app provided an invalid redirectUrl');
        }
    }
}
