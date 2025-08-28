<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<HeyFrameHttpException>
 */
#[Package('framework')]
class ExceptionCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'plugin_exception_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return HeyFrameHttpException::class;
    }
}
