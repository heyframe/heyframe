<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class LanguageOfChannelDomainDeleteException extends HeyFrameHttpException
{
    public function __construct(?\Throwable $e = null)
    {
        parent::__construct(
            'The language cannot be deleted because saleschannel domains with this language exist.',
            [],
            $e
        );
    }

    public function getErrorCode(): string
    {
        return 'SYSTEM__LANGUAGE_OF_CHANNEL_DOMAIN_DELETE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
