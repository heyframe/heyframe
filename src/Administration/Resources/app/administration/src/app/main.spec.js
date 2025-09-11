/**
 * @sw-package framework
 */
describe('src/app/main.ts', () => {
    let VueAdapter;

    const serviceMocks = {
        FeatureService: undefined,
        MenuService: undefined,
        PrivilegesService: undefined,
        AclService: undefined,
        LoginService: undefined,
        EntityMappingService: undefined,
        JsonApiParser: undefined,
        ValidationService: undefined,
        TimezoneService: undefined,
        RuleConditionService: undefined,
        ProductStreamConditionService: undefined,
        StateStyleService: undefined,
        CustomFieldService: undefined,
        ExtensionHelperService: undefined,
        LanguageAutoFetchingService: undefined,
        SearchTypeService: undefined,
        LicenseViolationsService: undefined,
        ShortcutService: undefined,
        LocaleToLanguageService: undefined,
        addPluginUpdatesListener: undefined,
        addHeyFrameUpdatesListener: undefined,
        addCustomerGroupRegistrationListener: undefined,
        LocaleHelperService: undefined,
        FilterService: undefined,
        AppCmsService: undefined,
        MediaDefaultFolderService: undefined,
        AppAclService: undefined,
        HeyFrameDiscountCampaignService: undefined,
        SearchRankingService: undefined,
        SearchPreferencesService: undefined,
        RecentlySearchService: undefined,
        UserActivityService: undefined,
        EntityValidationService: undefined,
        CustomEntityDefinitionService: undefined,
        addUsageDataConsentListener: undefined,
        FileValidationService: undefined,
    };

    beforeAll(async () => {
        // Start with a clean state
        jest.resetModules();

        // Mock some initializers
        jest.mock('src/app/init/http.init', () => {
            return jest.fn(() => {
                return jest.fn({});
            });
        });

        // Mock all service imports
        jest.mock('src/app/service/feature.service');
        serviceMocks.FeatureService = (await import('src/app/service/feature.service')).default;

        jest.mock('src/app/service/menu.service');
        serviceMocks.MenuService = (await import('src/app/service/menu.service')).default;

        jest.mock('src/app/service/privileges.service');
        serviceMocks.PrivilegesService = (await import('src/app/service/privileges.service')).default;

        jest.mock('src/app/service/acl.service');
        serviceMocks.AclService = (await import('src/app/service/acl.service')).default;

        jest.mock('src/core/service/login.service', () => {
            return jest.fn(() => {});
        });
        serviceMocks.LoginService = (await import('src/core/service/login.service')).default;

        jest.mock('src/core/service/entity-mapping.service');
        serviceMocks.EntityMappingService = (await import('src/core/service/entity-mapping.service')).default;

        jest.mock('src/core/service/jsonapi-parser.service');
        serviceMocks.JsonApiParser = (await import('src/core/service/jsonapi-parser.service')).default;

        jest.mock('src/core/service/validation.service');
        serviceMocks.ValidationService = (await import('src/core/service/validation.service')).default;

        jest.mock('src/core/service/timezone.service');
        serviceMocks.TimezoneService = (await import('src/core/service/timezone.service')).default;

        jest.mock('src/app/service/rule-condition.service', () => {
            return jest.fn(() => {
                return {
                    addCondition: jest.fn(() => {}),
                    getRestrictionsByGroup: jest.fn(() => []),
                    addAwarenessConfiguration: jest.fn(() => {}),
                };
            });
        });
        serviceMocks.RuleConditionService = (await import('src/app/service/rule-condition.service')).default;

        jest.mock('src/app/service/product-stream-condition.service');
        serviceMocks.ProductStreamConditionService = (
            await import('src/app/service/product-stream-condition.service')
        ).default;

        jest.mock('src/app/service/state-style.service');
        serviceMocks.StateStyleService = (await import('src/app/service/state-style.service')).default;

        jest.mock('src/app/service/custom-field.service');
        serviceMocks.CustomFieldService = (await import('src/app/service/custom-field.service')).default;

        jest.mock('src/app/service/extension-helper.service');
        serviceMocks.ExtensionHelperService = (await import('src/app/service/extension-helper.service')).default;

        jest.mock('src/app/service/language-auto-fetching.service');
        serviceMocks.LanguageAutoFetchingService = (await import('src/app/service/language-auto-fetching.service')).default;

        jest.mock('src/app/service/search-type.service');
        serviceMocks.SearchTypeService = (await import('src/app/service/search-type.service')).default;

        jest.mock('src/app/service/license-violations.service');
        serviceMocks.LicenseViolationsService = (await import('src/app/service/license-violations.service')).default;

        jest.mock('src/app/service/shortcut.service');
        serviceMocks.ShortcutService = (await import('src/app/service/shortcut.service')).default;

        jest.mock('src/app/service/locale-to-language.service');
        serviceMocks.LocaleToLanguageService = (await import('src/app/service/locale-to-language.service')).default;

        jest.mock('src/core/service/plugin-updates-listener.service');
        serviceMocks.PluginUpdatesListener = (await import('src/core/service/plugin-updates-listener.service')).default;

        jest.mock('src/core/service/heyframe-updates-listener.service');
        serviceMocks.HeyFrameUpdatesListener = (await import('src/core/service/heyframe-updates-listener.service')).default;

        jest.mock('src/core/service/customer-group-registration-listener.service');
        serviceMocks.CustomerGroupRegistrationListener = (
            await import('src/core/service/customer-group-registration-listener.service')
        ).default;

        jest.mock('src/app/service/locale-helper.service');
        serviceMocks.LocaleHelperService = (await import('src/app/service/locale-helper.service')).default;

        jest.mock('src/app/service/filter.service');
        serviceMocks.FilterService = (await import('src/app/service/filter.service')).default;

        jest.mock('src/app/service/app-cms.service');
        serviceMocks.AppCmsService = (await import('src/app/service/app-cms.service')).default;

        jest.mock('src/app/service/media-default-folder.service');
        serviceMocks.MediaDefaultFolderService = (await import('src/app/service/media-default-folder.service')).default;

        jest.mock('src/app/service/app-acl.service');
        serviceMocks.AppAclService = (await import('src/app/service/app-acl.service')).default;

        jest.mock('src/app/service/discount-campaign.service');
        serviceMocks.HeyFrameDiscountCampaignService = (await import('src/app/service/discount-campaign.service')).default;

        jest.mock('src/app/service/search-ranking.service');
        serviceMocks.SearchRankingService = (await import('src/app/service/search-ranking.service')).default;

        jest.mock('src/app/service/search-preferences.service');
        serviceMocks.SearchPreferencesService = (await import('src/app/service/search-preferences.service')).default;

        jest.mock('src/app/service/recently-search.service');
        serviceMocks.RecentlySearchService = (await import('src/app/service/recently-search.service')).default;

        jest.mock('src/app/service/user-activity.service');
        serviceMocks.UserActivityService = (await import('src/app/service/user-activity.service')).default;

        jest.mock('src/app/service/entity-validation.service');
        serviceMocks.EntityValidationService = (await import('src/app/service/entity-validation.service')).default;

        jest.mock('src/app/service/custom-entity-definition.service');
        serviceMocks.CustomEntityDefinitionService = (
            await import('src/app/service/custom-entity-definition.service')
        ).default;

        jest.mock('src/core/service/usage-data-consent-listener.service');
        serviceMocks.addUsageDataConsentListener = (
            await import('src/core/service/usage-data-consent-listener.service')
        ).default;

        jest.mock('src/app/service/file-validation.service');
        serviceMocks.FileValidationService = (await import('src/app/service/file-validation.service')).default;

        // Reset the HeyFrame object to make sure that the application is not already initialized
        HeyFrame = undefined;
        // Import the HeyFrame object
        HeyFrame = (await import('src/core/heyframe')).HeyFrameInstance;
        // Initialize the main application
        await import('src/app/main');
        // Import the VueAdapter to check if it is set in the application
        VueAdapter = (await import('src/app/adapter/view/vue.adapter')).default;

        // Mock services from other places
        HeyFrame.Service().register('repositoryFactory', () => {
            return {
                create: () => {},
            };
        });

        jest.spyOn(HeyFrame, 'Context', 'get').mockReturnValue({ api: {} });
    });

    it('should create the global application DI container in the HeyFrame object', () => {
        expect(HeyFrame.Application).toBeDefined();
    });

    it('should set the VueAdapter into the application', () => {
        expect(HeyFrame.Application.view).toBeInstanceOf(VueAdapter);
    });

    it('should add all initializer to Application', () => {
        const expectedInitializers = [
            'apiServices',
            'state',
            'coreMixin',
            'coreDirectives',
            'coreFilter',
            'baseComponents',
            'coreModuleRoutes',
            'login',
            'router',
            'locale',
            'repositoryFactory',
            'shortcut',
            'httpClient',
            'componentHelper',
            'filterFactory',
            'notification',
            'context',
            'window',
            'extensionComponentSections',
            'tabs',
            'cms',
            'menu',
            'settingItems',
            'modals',
            'mainModules',
            'actionButton',
            'actions',
            'extensionDataHandling',
            'language',
            'userInformation',
            'worker',
            'inAppPurchaseCheckout',
            'store',
            'topbarButton',
            'teaserPopover',
        ];

        const initializers = HeyFrame.Application.getContainer('init').$list();
        initializers.push(...HeyFrame.Application.getContainer('init-pre').$list());
        initializers.push(...HeyFrame.Application.getContainer('init-post').$list());

        expectedInitializers.forEach((initializer) => {
            expect(initializers).toContain(initializer);
        });
    });

    it('should add all services to Application', () => {
        const services = HeyFrame.Application.getContainer('service').$list();

        expect(services).toContain('feature');
        expect(services).toContain('customEntityDefinitionService');
        expect(services).toContain('menuService');
        expect(services).toContain('privileges');
        expect(services).toContain('acl');
        expect(services).toContain('loginService');
        expect(services).toContain('jsonApiParserService');
        expect(services).toContain('validationService');
        expect(services).toContain('entityValidationService');
        expect(services).toContain('timezoneService');
        expect(services).toContain('ruleConditionDataProviderService');
        expect(services).toContain('productStreamConditionService');
        expect(services).toContain('customFieldDataProviderService');
        expect(services).toContain('extensionHelperService');
        expect(services).toContain('languageAutoFetchingService');
        expect(services).toContain('stateStyleDataProviderService');
        expect(services).toContain('searchTypeService');
        expect(services).toContain('localeToLanguageService');
        expect(services).toContain('entityMappingService');
        expect(services).toContain('shortcutService');
        expect(services).toContain('licenseViolationService');
        expect(services).toContain('localeHelper');
        expect(services).toContain('filterService');
        expect(services).toContain('mediaDefaultFolderService');
        expect(services).toContain('appAclService');
        expect(services).toContain('appCmsService');
        expect(services).toContain('heyframeDiscountCampaignService');
        expect(services).toContain('searchRankingService');
        expect(services).toContain('recentlySearchService');
        expect(services).toContain('searchPreferencesService');
        expect(services).toContain('userActivityService');
        expect(services).toContain('fileValidationService');
    });

    it('should create imported services on usage', () => {
        // Initialize needed initializers
        const preInitializers = HeyFrame.Application.getContainer('init-pre');
        expect(preInitializers.state).toBeDefined();

        // Check if all services get executed correctly
        expect(serviceMocks.FeatureService).not.toHaveBeenCalled();
        HeyFrame.Service('feature');
        expect(serviceMocks.FeatureService).toHaveBeenCalled();

        expect(serviceMocks.CustomEntityDefinitionService).not.toHaveBeenCalled();
        HeyFrame.Service('customEntityDefinitionService');
        expect(serviceMocks.CustomEntityDefinitionService).toHaveBeenCalled();

        expect(serviceMocks.MenuService).not.toHaveBeenCalled();
        HeyFrame.Service('menuService');
        expect(serviceMocks.MenuService).toHaveBeenCalled();

        expect(serviceMocks.PrivilegesService).not.toHaveBeenCalled();
        HeyFrame.Service('privileges');
        expect(serviceMocks.PrivilegesService).toHaveBeenCalled();

        expect(serviceMocks.AclService).not.toHaveBeenCalled();
        HeyFrame.Service('acl');
        expect(serviceMocks.AclService).toHaveBeenCalled();

        expect(serviceMocks.LoginService).not.toHaveBeenCalled();
        HeyFrame.Service('loginService');
        expect(serviceMocks.LoginService).toHaveBeenCalled();

        expect(serviceMocks.JsonApiParser).not.toHaveBeenCalled();
        const jsonApiParserService = HeyFrame.Service('jsonApiParserService');
        expect(jsonApiParserService).toBe(serviceMocks.JsonApiParser);

        const validationService = HeyFrame.Service('validationService');
        expect(validationService).toEqual(serviceMocks.ValidationService);

        expect(serviceMocks.EntityValidationService).not.toHaveBeenCalled();
        HeyFrame.Service('entityValidationService');
        expect(serviceMocks.EntityValidationService).toHaveBeenCalled();

        expect(serviceMocks.TimezoneService).not.toHaveBeenCalled();
        HeyFrame.Service('timezoneService');
        expect(serviceMocks.TimezoneService).toHaveBeenCalled();

        expect(serviceMocks.RuleConditionService).not.toHaveBeenCalled();
        HeyFrame.Service('ruleConditionDataProviderService');
        expect(serviceMocks.RuleConditionService).toHaveBeenCalled();

        expect(serviceMocks.ProductStreamConditionService).not.toHaveBeenCalled();
        HeyFrame.Service('productStreamConditionService');
        expect(serviceMocks.ProductStreamConditionService).toHaveBeenCalled();

        expect(serviceMocks.CustomFieldService).not.toHaveBeenCalled();
        HeyFrame.Service('customFieldDataProviderService');
        expect(serviceMocks.CustomFieldService).toHaveBeenCalled();

        expect(serviceMocks.ExtensionHelperService).not.toHaveBeenCalled();
        HeyFrame.Service('extensionHelperService');
        expect(serviceMocks.ExtensionHelperService).toHaveBeenCalled();

        expect(serviceMocks.LanguageAutoFetchingService).not.toHaveBeenCalled();
        HeyFrame.Service('languageAutoFetchingService');
        expect(serviceMocks.LanguageAutoFetchingService).toHaveBeenCalled();

        expect(serviceMocks.StateStyleService).not.toHaveBeenCalled();
        HeyFrame.Service('stateStyleDataProviderService');
        expect(serviceMocks.StateStyleService).toHaveBeenCalled();

        expect(serviceMocks.SearchTypeService).not.toHaveBeenCalled();
        HeyFrame.Service('searchTypeService');
        expect(serviceMocks.SearchTypeService).toHaveBeenCalled();

        expect(serviceMocks.LocaleToLanguageService).not.toHaveBeenCalled();
        HeyFrame.Service('localeToLanguageService');
        expect(serviceMocks.LocaleToLanguageService).toHaveBeenCalled();

        const entityMappingService = HeyFrame.Service('entityMappingService');
        expect(entityMappingService).toEqual(serviceMocks.EntityMappingService);

        expect(serviceMocks.ShortcutService).not.toHaveBeenCalled();
        HeyFrame.Service('shortcutService');
        expect(serviceMocks.ShortcutService).toHaveBeenCalled();

        expect(serviceMocks.LicenseViolationsService).not.toHaveBeenCalled();
        HeyFrame.Service('licenseViolationService');
        expect(serviceMocks.LicenseViolationsService).toHaveBeenCalled();

        expect(serviceMocks.LocaleHelperService).not.toHaveBeenCalled();
        HeyFrame.Service('localeHelper');
        expect(serviceMocks.LocaleHelperService).toHaveBeenCalled();

        expect(serviceMocks.FilterService).not.toHaveBeenCalled();
        HeyFrame.Service('filterService');
        expect(serviceMocks.FilterService).toHaveBeenCalled();

        expect(serviceMocks.MediaDefaultFolderService).not.toHaveBeenCalled();
        HeyFrame.Service('mediaDefaultFolderService');
        expect(serviceMocks.MediaDefaultFolderService).toHaveBeenCalled();

        expect(serviceMocks.AppAclService).not.toHaveBeenCalled();
        HeyFrame.Service('appAclService');
        expect(serviceMocks.AppAclService).toHaveBeenCalled();

        expect(serviceMocks.AppCmsService).not.toHaveBeenCalled();
        HeyFrame.Service('appCmsService');
        expect(serviceMocks.AppCmsService).toHaveBeenCalled();

        expect(serviceMocks.HeyFrameDiscountCampaignService).not.toHaveBeenCalled();
        HeyFrame.Service('heyframeDiscountCampaignService');
        expect(serviceMocks.HeyFrameDiscountCampaignService).toHaveBeenCalled();

        expect(serviceMocks.SearchRankingService).not.toHaveBeenCalled();
        HeyFrame.Service('searchRankingService');
        expect(serviceMocks.SearchRankingService).toHaveBeenCalled();

        expect(serviceMocks.RecentlySearchService).not.toHaveBeenCalled();
        HeyFrame.Service('recentlySearchService');
        expect(serviceMocks.RecentlySearchService).toHaveBeenCalled();

        expect(serviceMocks.SearchPreferencesService).not.toHaveBeenCalled();
        HeyFrame.Service('searchPreferencesService');
        expect(serviceMocks.SearchPreferencesService).toHaveBeenCalled();

        expect(serviceMocks.UserActivityService).not.toHaveBeenCalled();
        HeyFrame.Service('userActivityService');
        expect(serviceMocks.UserActivityService).toHaveBeenCalled();

        expect(serviceMocks.FileValidationService).not.toHaveBeenCalled();
        HeyFrame.Service('fileValidationService');
        expect(serviceMocks.FileValidationService).toHaveBeenCalled();
    });
});
