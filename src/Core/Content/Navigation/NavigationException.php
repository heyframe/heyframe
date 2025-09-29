<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation;

use HeyFrame\Core\Content\Navigation\Exception\NavigationNotFoundException;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class NavigationException extends HttpException
{
    public static function navigationNotFound(string $id): HeyFrameHttpException
    {
        return new NavigationNotFoundException($id);
    }
}
