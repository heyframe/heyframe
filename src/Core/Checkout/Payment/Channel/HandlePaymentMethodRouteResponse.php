<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends FrontApiResponse<ArrayStruct<array{redirectResponse: RedirectResponse|null}>>
 */
#[Package('checkout')]
class HandlePaymentMethodRouteResponse extends FrontApiResponse
{
    public function __construct(?RedirectResponse $response)
    {
        parent::__construct(
            new ArrayStruct(['redirectResponse' => $response])
        );
    }

    public function getRedirectResponse(): ?RedirectResponse
    {
        return $this->object->get('redirectResponse');
    }

    /**
     * @return ArrayStruct<array{redirectUrl: string|null}>
     *
     * @phpstan-ignore method.childReturnType (it is intended to return a different ArrayStruct)
     */
    public function getObject(): ArrayStruct
    {
        return new ArrayStruct([
            'redirectUrl' => $this->getRedirectResponse()?->getTargetUrl(),
        ]);
    }
}
