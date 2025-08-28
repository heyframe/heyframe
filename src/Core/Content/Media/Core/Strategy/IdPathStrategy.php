<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Core\Strategy;

use HeyFrame\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;
use HeyFrame\Core\Content\Media\Core\Params\MediaLocationStruct;
use HeyFrame\Core\Content\Media\Core\Params\ThumbnailLocationStruct;

/**
 * @internal Concrete implementation is not allowed to be decorated or extended. The implementation details can change
 */
class IdPathStrategy extends AbstractMediaPathStrategy
{
    public function name(): string
    {
        return 'id';
    }

    protected function value(MediaLocationStruct|ThumbnailLocationStruct $location): ?string
    {
        return $location instanceof MediaLocationStruct ? $location->id : $location->media->id;
    }
}
