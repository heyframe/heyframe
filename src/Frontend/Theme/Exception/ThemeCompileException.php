<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ThemeCompileException extends HeyFrameHttpException
{
    public function __construct(
        string $themeName,
        string $message = '',
        ?\Throwable $e = null
    ) {
        parent::__construct(
            'Unable to compile the theme "{{ themeName }}". {{ message }}',
            [
                'themeName' => $themeName,
                'message' => $message,
            ],
            $e
        );
    }

    public function getErrorCode(): string
    {
        return 'THEME__COMPILING_ERROR';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
