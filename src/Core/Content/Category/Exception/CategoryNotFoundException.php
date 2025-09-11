<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class CategoryNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $categoryId)
    {
        parent::__construct(
            'Category "{{ categoryId }}" not found.',
            ['categoryId' => $categoryId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__CATEGORY_NOT_FOUND';
    }
}
