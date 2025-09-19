<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Channel;

use HeyFrame\Core\Content\Cookie\Struct\CookieGroupCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @codeCoverageIgnore
 *
 * @extends FrontApiResponse<ArrayStruct<array{elements: CookieGroupCollection, hash: string}>>
 */
#[Package('framework')]
class CookieRouteResponse extends FrontApiResponse
{
    public function __construct(
        CookieGroupCollection $cookieGroups,
        string $hash,
    ) {
        parent::__construct(new ArrayStruct([
            'elements' => $cookieGroups,
            'hash' => $hash,
        ], 'cookie_groups_hash'));
    }

    public function getCookieGroups(): CookieGroupCollection
    {
        return $this->object->get('elements');
    }

    public function getHash(): string
    {
        return $this->object->get('hash');
    }
}
