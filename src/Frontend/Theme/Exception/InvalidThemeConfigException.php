<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidThemeConfigException extends HeyFrameHttpException
{
    public function __construct(string $fieldName)
    {
        parent::__construct('Unable to find setter for config field "{{ fieldName }}"', ['fieldName' => $fieldName]);
    }

    public function getErrorCode(): string
    {
        return 'THEME__INVALID_THEME_CONFIG';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
