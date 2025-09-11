<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
abstract class AbstractLandingPageRoute
{
    abstract public function getDecorated(): AbstractLandingPageRoute;

    abstract public function load(string $landingPageId, Request $request, ChannelContext $context): LandingPageRouteResponse;
}
