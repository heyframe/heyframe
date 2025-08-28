<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing\Extension;

use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @public this class is used as type-hint for all event listeners, so the class string is "public consumable" API
 *
 * @title Determination of a request redirect response
 *
 * @description This event allows interception of the request redirect check to modify the redirect response.
 *
 * @codeCoverageIgnore
 *
 * @extends Extension<?Response>
 */
#[Package('framework')]
final class CanonicalRedirectExtension extends Extension
{
    public const NAME = 'canonical-redirect';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(public readonly Request $request)
    {
    }
}
