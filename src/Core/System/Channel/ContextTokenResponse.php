<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\PlatformRequest;

/**
 * @extends FrontApiResponse<ArrayStruct<array{redirectUrl: string|null}>>
 */
#[Package('framework')]
class ContextTokenResponse extends FrontApiResponse
{
    public function __construct(
        string $token,
        ?string $redirectUrl = null
    ) {
        parent::__construct(new ArrayStruct([
            'redirectUrl' => $redirectUrl,
        ]));

        $this->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $token);
    }

    public function getToken(): string
    {
        return $this->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
    }

    public function getRedirectUrl(): ?string
    {
        return $this->object->get('redirectUrl');
    }
}
