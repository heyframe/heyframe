<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Checkout\Cart\CartDataCollectorInterface;
use HeyFrame\Core\Checkout\Cart\CartProcessorInterface;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupPackagerInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupSorterInterface;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use HeyFrame\Core\Checkout\Customer\Password\LegacyEncoder\LegacyEncoderInterface;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\Filter\FilterPickerInterface;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\Filter\FilterSorterInterface;
use HeyFrame\Core\Content\Flow\Dispatching\Storer\FlowStorer;
use HeyFrame\Core\Content\Product\Channel\Listing\Filter\AbstractListingFilterHandler;
use HeyFrame\Core\Content\Product\Channel\Listing\Processor\AbstractListingProcessor;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use HeyFrame\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;
use HeyFrame\Core\Framework\Adapter\Twig\NamespaceHierarchy\TemplateNamespaceHierarchyBuilderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\BulkEntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldSerializerInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use HeyFrame\Core\Framework\Routing\AbstractRouteScope;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Webhook\Hookable\HookableEntityInterface;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\NumberRange\ValueGenerator\Pattern\AbstractValueGenerator;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class AutoconfigureCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(EntityDefinition::class)
            ->addTag('heyframe.entity.definition');

        $container
            ->registerForAutoconfiguration(HookableEntityInterface::class)
            ->addTag('heyframe.entity.hookable');

        $container
            ->registerForAutoconfiguration(ChannelDefinition::class)
            ->addTag('heyframe.channel.entity.definition');

        $container
            ->registerForAutoconfiguration(AbstractRouteScope::class)
            ->addTag('heyframe.route_scope');

        $container
            ->registerForAutoconfiguration(EntityExtension::class)
            ->addTag('heyframe.entity.extension');

        $container
            ->registerForAutoconfiguration(BulkEntityExtension::class)
            ->addTag('heyframe.bulk.entity.extension');

        $container
            ->registerForAutoconfiguration(CartProcessorInterface::class)
            ->addTag('heyframe.cart.processor');

        $container
            ->registerForAutoconfiguration(CartDataCollectorInterface::class)
            ->addTag('heyframe.cart.collector');

        $container
            ->registerForAutoconfiguration(ScheduledTask::class)
            ->addTag('heyframe.scheduled.task');

        $container
            ->registerForAutoconfiguration(CartValidatorInterface::class)
            ->addTag('heyframe.cart.validator');

        $container
            ->registerForAutoconfiguration(LineItemFactoryInterface::class)
            ->addTag('heyframe.cart.line_item.factory');

        $container
            ->registerForAutoconfiguration(LineItemGroupPackagerInterface::class)
            ->addTag('lineitem.group.packager');

        $container
            ->registerForAutoconfiguration(LineItemGroupSorterInterface::class)
            ->addTag('lineitem.group.sorter');

        $container
            ->registerForAutoconfiguration(LegacyEncoderInterface::class)
            ->addTag('heyframe.legacy_encoder');

        $container
            ->registerForAutoconfiguration(EntityIndexer::class)
            ->addTag('heyframe.entity_indexer');

        $container
            ->registerForAutoconfiguration(ExceptionHandlerInterface::class)
            ->addTag('heyframe.dal.exception_handler');

        $container
            ->registerForAutoconfiguration(AbstractPaymentHandler::class)
            ->addTag('heyframe.payment.method');

        $container
            ->registerForAutoconfiguration(FilterSorterInterface::class)
            ->addTag('promotion.filter.sorter');

        $container
            ->registerForAutoconfiguration(FilterPickerInterface::class)
            ->addTag('promotion.filter.picker');

        $container
            ->registerForAutoconfiguration(Rule::class)
            ->addTag('heyframe.rule.definition');

        $container
            ->registerForAutoconfiguration(FieldSerializerInterface::class)
            ->addTag('heyframe.field_serializer');

        $container
            ->registerForAutoconfiguration(FlowStorer::class)
            ->addTag('flow.storer');

        $container
            ->registerForAutoconfiguration(AbstractUrlProvider::class)
            ->addTag('heyframe.sitemap_url_provider');

        $container
            ->registerForAutoconfiguration(AdapterFactoryInterface::class)
            ->addTag('heyframe.filesystem.factory');

        $container
            ->registerForAutoconfiguration(SeoUrlRouteInterface::class)
            ->addTag('heyframe.seo_url.route');

        $container
            ->registerForAutoconfiguration(AbstractValueGenerator::class)
            ->addTag('heyframe.value_generator_pattern');

        $container
            ->registerForAutoconfiguration(AbstractListingProcessor::class)
            ->addTag('heyframe.listing.processor');

        $container
              ->registerForAutoconfiguration(AbstractListingFilterHandler::class)
              ->addTag('heyframe.listing.filter.handler');

        $container
            ->registerForAutoconfiguration(TemplateNamespaceHierarchyBuilderInterface::class)
            ->addTag('heyframe.twig.hierarchy_builder');

        $container->registerAliasForArgument('heyframe.filesystem.private', FilesystemOperator::class, 'privateFilesystem');
        $container->registerAliasForArgument('heyframe.filesystem.public', FilesystemOperator::class, 'publicFilesystem');
    }
}
