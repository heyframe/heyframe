<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends FrontApiResponse<ArrayStruct<array{redirectUrl?: string}>>|ArrayStruct|null
 */
#[Package('checkout')]
class HandlePaymentMethodRouteResponse extends FrontApiResponse
{
    public function __construct(RedirectResponse|ArrayStruct|null $response)
    {
        if ($response instanceof RedirectResponse) {
            parent::__construct(
                new ArrayStruct(['redirectResponse' => $response])
            );
        } else {
            parent::__construct($response);
        }
    }

    public function getRedirectResponse(): ?RedirectResponse
    {
        return $this->object->get('redirectResponse');
    }

    /**
     * @return ArrayStruct<array{redirectUrl?: string}|array<string,mixed>>
     *
     * @phpstan-ignore method.childReturnType (it is intended to return a different ArrayStruct)
     */
    public function getObject(): ArrayStruct
    {
        if ($this->getRedirectResponse()) {
            return new ArrayStruct([
                'redirectUrl' => $this->getRedirectResponse()->getTargetUrl(),
            ]);
        }

        return $this->object ?? new ArrayStruct([]);
    }
}
