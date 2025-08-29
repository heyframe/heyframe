<?php declare(strict_types=1);

return [
    'filePatterns' => [
        '**/Test/**', // Testing
        '**/src/WebInstaller/**', // WebInstaller TODO: remove after first 6.7 release
        '**/src/Core/Framework/Update/**', // Updater
        '**/src/Core/TestBootstrapper.php', // Testing
        '**/src/Core/Framework/Demodata/Faker/Commerce.php', // dev dependency
        '**/src/Core/DevOps/StaticAnalyze/**', // dev dependency
        '**/src/Core/Profiling/Doctrine/BacktraceDebugDataHolder.php', // dev dependency
        '**/src/Core/Migration/Traits/MigrationUntouchedDbTestTrait.php', // Test code in prod
        '**src/Core/Framework/Script/ServiceStubs.php', // never intended to be extended
        '**/src/Core/Framework/App/AppException.php', // intended to be internal
    ],
    'errors' => [
        // Don't complain about doctrine library changes
        'Doctrine\\\\DBAL',

        // Will be typed in Symfony 8 (maybe)
        preg_quote('Symfony\Component\Console\Command\Command#configure() changed from no type to void', '/'),

        // Version related const values changed for 7.3 update
        preg_quote('Value of constant Symfony\Component\HttpKernel\Kernel', '/'),

        // Can not be inspected through reflection https://github.com/Roave/BetterReflection/issues/1376
        'An enum expression .* is not supported in .*',

        // Incorrectly deprecated
        'The return type of HeyFrame\\\\Core\\\\Checkout\\\\Document\\\\DocumentException.* changed from self',
        preg_quote('The return type of HeyFrame\Core\Content\Product\ProductException::productNotFound() changed from self|HeyFrame\Core\Content\Product\Exception\ProductNotFoundException to HeyFrame\Core\Content\Product\Exception\ProductNotFoundException', '/'),

        // Expected to be appended when new event is added
        preg_quote('Value of constant HeyFrame\Core\Framework\Webhook\Hookable', '/'),

        // Adding optional parameters to a constructor is not a BC
        preg_quote('ADDED: Parameter prefixMatch was added to Method __construct() of class HeyFrame\Elasticsearch\Product\SearchFieldConfig', '/'),
        preg_quote('ADDED: Parameter label was added to Method __construct() of class HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTax', '/'),
        preg_quote('ADDED: Parameter senderName was added to Method __construct() of class HeyFrame\Core\Content\Mail\Service\SendMailTemplateParams', '/'),
        preg_quote('ADDED: Parameter response was added to Method __construct() of class HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\Event\ElasticsearchEntitySearcherSearchedEvent', '/'),
        preg_quote('ADDED: Parameter clock was added to Method __construct() of class HeyFrame\Core\Checkout\Promotion\Gateway\Template\ActiveDateRange', '/'),
        preg_quote('ADDED: Parameter visibility was added to Method __construct() of class HeyFrame\Core\Framework\Adapter\Filesystem\Plugin\CopyBatchInput', '/'),
        preg_quote('ADDED: Parameter versionId was added to Method createIterator() of class HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory', '/'),
        preg_quote('ADDED: Parameter useForSorting was added to Method __construct() of class HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField', '/'),
        preg_quote('ADDED: Parameter countryId was added to Method __construct() of class HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode', '/'),
        preg_quote('ADDED: Parameter caseSensitiveCheck was added to Method __construct() of class HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode', '/'),

        // Fix to make promotions work with order recalculation
        'Value of constant HeyFrame\\\\Core\\\\Checkout\\\\Cart\\\\Order\\\\OrderConverter::ADMIN_EDIT_ORDER_PERMISSIONS changed from array \((\n.*)*skipPromotion.*(\n.*)*to array \((\n.*)*pinAutomaticPromotions',

        // Only new additions
        'Value of constant HeyFrame\\\\Core\\\\Checkout\\\\Cart\\\\Order\\\\OrderConverter::ADMIN_EDIT_ORDER_PERMISSIONS changed from array \((\n.*)*to array \((\n.*)*skipCartPersistence(.*\n.*)*skipPrimaryOrderIds(.*\n.*)*automaticPromotionDeletionNotices',

        // No break as mixed is the top type and every other type is a subtype of mixed
        preg_quote('The parameter $value of HeyFrame\Frontend\Event\FrontendRenderEvent#setParameter() changed from no type to mixed', '/'),

        // No break as the `{get,set}SeoLink()` changes have not been released
        preg_quote('REMOVED: Property HeyFrame\Core\Content\Category\Channel\ChannelCategoryEntity#$seoLink was removed', '/'),
        'REMOVED: Method HeyFrame\\\\Core\\\\Content\\\\Category\\\\Channel\\\\ChannelCategoryEntity#(get|set)SeoLink\(\) was removed',

        // The type has been extended and the old type is still accepted
        'CHANGED: The parameter \$context of HeyFrame\\\\Core\\\\Framework\\\\Adapter\\\\Twig\\\\Extension\\\\BuildBreadcrumbExtension#(getFullBreadcrumb|getFullBreadcrumbById)\(\) changed from HeyFrame\\\\Core\\\\Framework\\\\Context to HeyFrame\\\\Core\\\\Framework\\\\Context\|HeyFrame\\\\Core\\\\System\\\\Channel\\\\ChannelContext',

        // The parameters are optional, so this is not a BC break
        'ADDED: Parameter .* was added to Method accessDeniedForXmlHttpRequest\(\) of class HeyFrame\\\\Core\\\\Framework\\\\Routing\\\\RoutingException',

        // Widening the property type with null is necessary and not a BC break
        preg_quote('CHANGED: Type of property HeyFrame\Core\System\Tax\Aggregate\TaxRule\TaxRuleEntity#$type changed from HeyFrame\Core\System\Tax\Aggregate\TaxRuleType\TaxRuleTypeEntity to HeyFrame\Core\System\Tax\Aggregate\TaxRuleType\TaxRuleTypeEntity|null', '/'),
        preg_quote('CHANGED: Type of property HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity#$url changed from string to string|null', '/'),
        preg_quote('CHANGED: Type of property HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity#$mediaId changed from string to string|null', '/'),

        // The media thumbnail size id changes have not been released
        preg_quote('CHANGED: Type of property HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity#$mediaThumbnailSizeId changed from string to string|null', '/'),
        preg_quote('CHANGED: The return type of HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity#getMediaThumbnailSizeId() changed from string to the non-covariant string|null', '/'),
        preg_quote('CHANGED: The return type of HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity#getMediaThumbnailSizeId() changed from string to string|null', '/'),

        // The class has not been released
        'REMOVED: Class HeyFrame\\\\Elasticsearch\\\\Product\\\\CachedSearchConfigLoader has been deleted',

        // Removing empty constructor methods should not be a BC break
        preg_quote('REMOVED: Method HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\AllowEmptyString#__construct() was removed', '/'),
        preg_quote('REMOVED: Method HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\Required#__construct() was removed', '/'),
        preg_quote('REMOVED: Method HeyFrame\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey#__construct() was removed', '/'),

        // The rule class is declared @final, and its constructor is marked as @internal.
        preg_quote('REMOVED: Property HeyFrame\Core\Framework\Rule\Container\MatchAllLineItemsRule#$type was removed', '/'),

        // The classes were never properly released and are not use.
        preg_quote('REMOVED: Class HeyFrame\Core\System\Snippet\Struct\Language has been deleted', '/'),
        preg_quote('REMOVED: Class HeyFrame\Core\System\Snippet\Struct\SnippetPaths has been deleted', '/'),
        preg_quote('CHANGED: Type of property HeyFrame\Core\System\Snippet\Struct\TranslationConfig#$repositoryUrl changed from string to GuzzleHttp\Psr7\Uri', '/'),
        preg_quote('CHANGED: Type of property HeyFrame\Core\System\Snippet\Struct\TranslationConfig#$languages changed from HeyFrame\Core\System\Snippet\Struct\LanguageCollection to HeyFrame\Core\System\Snippet\DataTransfer\Language\LanguageCollection', '/'),
        preg_quote('CHANGED: Type of property HeyFrame\Core\System\Snippet\Struct\TranslationConfig#$pluginMapping changed from array to HeyFrame\Core\System\Snippet\DataTransfer\PluginMapping\PluginMappingCollection', '/'),
        preg_quote('ADDED: Parameter previous was added to Method translationConfigurationFileDoesNotExist() of class HeyFrame\Core\System\Snippet\SnippetException', '/'),
    ],
];
