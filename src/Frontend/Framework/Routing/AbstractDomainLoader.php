<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Routing;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @phpstan-type Domain = array{url: string, id: string, channelId: string, typeId: string, snippetSetId: string, currencyId: string, languageId: string, themeId: string, maintenance: string, maintenanceIpWhitelist: string, locale: string, themeName: string, parentThemeName: string}
 */
#[Package('framework')]
abstract class AbstractDomainLoader
{
    abstract public function getDecorated(): AbstractDomainLoader;

    /**
     * @return array<string, Domain>
     */
    abstract public function load(): array;
}
