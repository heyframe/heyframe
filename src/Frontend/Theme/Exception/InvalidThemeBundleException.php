<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidThemeBundleException extends HeyFrameHttpException
{
    public function __construct(string $themeName)
    {
        parent::__construct('Unable to find the theme.json for "{{ themeName }}"', ['themeName' => $themeName]);
    }

    public function getErrorCode(): string
    {
        return 'THEME__INVALID_THEME_BUNDLE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
