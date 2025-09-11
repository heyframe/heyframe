<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Validation;

use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Package('inventory')]
class SeoUrlValidationFactory implements SeoUrlDataValidationFactoryInterface
{
    public function buildValidation(Context $context, ?SeoUrlRouteConfig $config): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('seo_url.create');

        $this->addConstraints($definition, $config, $context);

        return $definition;
    }

    private function addConstraints(
        DataValidationDefinition $definition,
        ?SeoUrlRouteConfig $routeConfig,
        Context $context
    ): void {
        $fkConstraints = [new NotBlank()];

        if ($routeConfig) {
            $fkConstraints[] = new EntityExists(
                entity: $routeConfig->getDefinition()->getEntityName(),
                context: $context,
            );
        }

        $definition
            ->add('foreignKey', ...$fkConstraints)
            ->add('routeName', new NotBlank(), new Type('string'))
            ->add('pathInfo', new NotBlank(), new Type('string'))
            ->add('seoPathInfo', new NotBlank(), new Type('string'))
            ->add('channelId', new NotBlank(), new EntityExists(
                entity: ChannelDefinition::ENTITY_NAME,
                context: $context,
            ));
    }
}
