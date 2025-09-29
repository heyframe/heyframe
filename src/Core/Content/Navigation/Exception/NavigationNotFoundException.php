<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class NavigationNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $navigationId)
    {
        parent::__construct(
            'Navigation "{{ navigationId }}" not found.',
            ['navigationId' => $navigationId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__NAVIGATION_NOT_FOUND';
    }
}
