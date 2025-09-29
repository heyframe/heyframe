<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Validation;

use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;

#[Package('inventory')]
interface SeoUrlDataValidationFactoryInterface
{
    public function buildValidation(Context $context, SeoUrlRouteConfig $config): DataValidationDefinition;
}
