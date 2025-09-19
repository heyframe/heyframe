<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cms\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class PageNotFoundException extends HeyFrameHttpException
{
    final public const ERROR_CODE = 'CONTENT__CMS_PAGE_NOT_FOUND';

    public function __construct(string $pageId)
    {
        parent::__construct(
            'Page with id "{{ pageId }}" was not found.',
            ['pageId' => $pageId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
