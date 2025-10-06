<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('after-sales')]
class MailTemplateRendererException extends HeyFrameHttpException
{
    public function __construct(string $twigMessage)
    {
        parent::__construct(
            'Failed rendering mail template using Twig: {{ errorMessage }}',
            ['errorMessage' => $twigMessage]
        );
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__MAIL_TEMPLATING_FAILED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
