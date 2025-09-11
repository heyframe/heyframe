/**
 * @sw-package framework
 */
import initializeApiServices from 'src/app/init-pre/api-services.init';

describe('src/app/init-pre/api-services.init.ts', () => {
    beforeEach(() => {
        HeyFrame._private.ApiServices = jest.fn(() => {
            const services = [];
            const serviceNames = [
                'aclApiService',
                'appActionButtonService',
                'appCmsBlocks',
                'appModulesService',
                'appUrlChangeService',
                'businessEventService',
                'cacheApiService',
                'calculate-price',
                'cartStoreService',
                'checkoutStoreService',
                'configService',
                'customSnippetApiService',
                'customerGroupRegistrationService',
                'customerValidationService',
                'documentService',
                'excludedSearchTermService',
                'extensionSdkService',
                'firstRunWizardService',
                'flowActionService',
                'importExportService',
                'integrationService',
                'knownIpsService',
                'languagePluginService',
                'mailService',
                'mediaFolderService',
                'mediaService',
                'messageQueueService',
                'notificationsService',
                'numberRangeService',
                'orderDocumentApiService',
                'orderStateMachineService',
                'orderService',
                'productExportService',
                'productStreamPreviewService',
                'promotionSyncService',
                'recommendationsService',
                'ruleConditionsConfigApiService',
                'salesChannelService',
                'scheduledTaskService',
                'searchService',
                'seoUrlTemplateService',
                'seoUrlService',
                'snippetSetService',
                'snippetService',
                'stateMachineService',
                'contextStoreService',
                'storeService',
                'syncService',
                'systemConfigApiService',
                'tagApiService',
                'updateService',
                'userActivityApiService',
                'userConfigService',
                'userInputSanitizeService',
                'userRecoveryService',
                'userValidationService',
                'userService',
                'shopIdChangeService',
            ];

            serviceNames.forEach((serviceName) => {
                const MockApiService = jest.fn().mockImplementation(function () {
                    this.name = serviceName;
                });
                services.push(() => Promise.resolve({ default: MockApiService }));
            });

            return services;
        });
    });

    it('should initialize the api services', async () => {
        expect(HeyFrame.Service('aclApiService')).toBeUndefined();
        expect(HeyFrame.Service('appActionButtonService')).toBeUndefined();
        expect(HeyFrame.Service('appCmsBlocks')).toBeUndefined();
        expect(HeyFrame.Service('appModulesService')).toBeUndefined();
        expect(HeyFrame.Service('appUrlChangeService')).toBeUndefined();
        expect(HeyFrame.Service('businessEventService')).toBeUndefined();
        expect(HeyFrame.Service('cacheApiService')).toBeUndefined();
        expect(HeyFrame.Service('calculate-price')).toBeUndefined();
        expect(HeyFrame.Service('cartStoreService')).toBeUndefined();
        expect(HeyFrame.Service('checkoutStoreService')).toBeUndefined();
        expect(HeyFrame.Service('configService')).toBeUndefined();
        expect(HeyFrame.Service('customSnippetApiService')).toBeUndefined();
        expect(HeyFrame.Service('customerGroupRegistrationService')).toBeUndefined();
        expect(HeyFrame.Service('customerValidationService')).toBeUndefined();
        expect(HeyFrame.Service('documentService')).toBeUndefined();
        expect(HeyFrame.Service('excludedSearchTermService')).toBeUndefined();
        expect(HeyFrame.Service('extensionSdkService')).toBeUndefined();
        expect(HeyFrame.Service('firstRunWizardService')).toBeUndefined();
        expect(HeyFrame.Service('flowActionService')).toBeUndefined();
        expect(HeyFrame.Service('importExportService')).toBeUndefined();
        expect(HeyFrame.Service('integrationService')).toBeUndefined();
        expect(HeyFrame.Service('knownIpsService')).toBeUndefined();
        expect(HeyFrame.Service('languagePluginService')).toBeUndefined();
        expect(HeyFrame.Service('mailService')).toBeUndefined();
        expect(HeyFrame.Service('mediaFolderService')).toBeUndefined();
        expect(HeyFrame.Service('mediaService')).toBeUndefined();
        expect(HeyFrame.Service('messageQueueService')).toBeUndefined();
        expect(HeyFrame.Service('notificationsService')).toBeUndefined();
        expect(HeyFrame.Service('numberRangeService')).toBeUndefined();
        expect(HeyFrame.Service('orderDocumentApiService')).toBeUndefined();
        expect(HeyFrame.Service('orderStateMachineService')).toBeUndefined();
        expect(HeyFrame.Service('orderService')).toBeUndefined();
        expect(HeyFrame.Service('productExportService')).toBeUndefined();
        expect(HeyFrame.Service('productStreamPreviewService')).toBeUndefined();
        expect(HeyFrame.Service('promotionSyncService')).toBeUndefined();
        expect(HeyFrame.Service('recommendationsService')).toBeUndefined();
        expect(HeyFrame.Service('ruleConditionsConfigApiService')).toBeUndefined();
        expect(HeyFrame.Service('salesChannelService')).toBeUndefined();
        expect(HeyFrame.Service('scheduledTaskService')).toBeUndefined();
        expect(HeyFrame.Service('searchService')).toBeUndefined();
        expect(HeyFrame.Service('seoUrlTemplateService')).toBeUndefined();
        expect(HeyFrame.Service('seoUrlService')).toBeUndefined();
        expect(HeyFrame.Service('snippetSetService')).toBeUndefined();
        expect(HeyFrame.Service('snippetService')).toBeUndefined();
        expect(HeyFrame.Service('stateMachineService')).toBeUndefined();
        expect(HeyFrame.Service('contextStoreService')).toBeUndefined();
        expect(HeyFrame.Service('storeService')).toBeUndefined();
        expect(HeyFrame.Service('syncService')).toBeUndefined();
        expect(HeyFrame.Service('systemConfigApiService')).toBeUndefined();
        expect(HeyFrame.Service('tagApiService')).toBeUndefined();
        expect(HeyFrame.Service('updateService')).toBeUndefined();
        expect(HeyFrame.Service('userActivityApiService')).toBeUndefined();
        expect(HeyFrame.Service('userConfigService')).toBeUndefined();
        expect(HeyFrame.Service('userInputSanitizeService')).toBeUndefined();
        expect(HeyFrame.Service('userRecoveryService')).toBeUndefined();
        expect(HeyFrame.Service('userValidationService')).toBeUndefined();
        expect(HeyFrame.Service('userService')).toBeUndefined();
        expect(HeyFrame.Service('shopIdChangeService')).toBeUndefined();

        await initializeApiServices();

        expect(HeyFrame.Service('aclApiService')).toBeDefined();
        expect(HeyFrame.Service('appActionButtonService')).toBeDefined();
        expect(HeyFrame.Service('appCmsBlocks')).toBeDefined();
        expect(HeyFrame.Service('appModulesService')).toBeDefined();
        expect(HeyFrame.Service('appUrlChangeService')).toBeDefined();
        expect(HeyFrame.Service('businessEventService')).toBeDefined();
        expect(HeyFrame.Service('cacheApiService')).toBeDefined();
        expect(HeyFrame.Service('calculate-price')).toBeDefined();
        expect(HeyFrame.Service('cartStoreService')).toBeDefined();
        expect(HeyFrame.Service('checkoutStoreService')).toBeDefined();
        expect(HeyFrame.Service('configService')).toBeDefined();
        expect(HeyFrame.Service('customSnippetApiService')).toBeDefined();
        expect(HeyFrame.Service('customerGroupRegistrationService')).toBeDefined();
        expect(HeyFrame.Service('customerValidationService')).toBeDefined();
        expect(HeyFrame.Service('documentService')).toBeDefined();
        expect(HeyFrame.Service('excludedSearchTermService')).toBeDefined();
        expect(HeyFrame.Service('extensionSdkService')).toBeDefined();
        expect(HeyFrame.Service('firstRunWizardService')).toBeDefined();
        expect(HeyFrame.Service('flowActionService')).toBeDefined();
        expect(HeyFrame.Service('importExportService')).toBeDefined();
        expect(HeyFrame.Service('integrationService')).toBeDefined();
        expect(HeyFrame.Service('knownIpsService')).toBeDefined();
        expect(HeyFrame.Service('languagePluginService')).toBeDefined();
        expect(HeyFrame.Service('mailService')).toBeDefined();
        expect(HeyFrame.Service('mediaFolderService')).toBeDefined();
        expect(HeyFrame.Service('mediaService')).toBeDefined();
        expect(HeyFrame.Service('messageQueueService')).toBeDefined();
        expect(HeyFrame.Service('notificationsService')).toBeDefined();
        expect(HeyFrame.Service('numberRangeService')).toBeDefined();
        expect(HeyFrame.Service('orderDocumentApiService')).toBeDefined();
        expect(HeyFrame.Service('orderStateMachineService')).toBeDefined();
        expect(HeyFrame.Service('orderService')).toBeDefined();
        expect(HeyFrame.Service('productExportService')).toBeDefined();
        expect(HeyFrame.Service('productStreamPreviewService')).toBeDefined();
        expect(HeyFrame.Service('promotionSyncService')).toBeDefined();
        expect(HeyFrame.Service('recommendationsService')).toBeDefined();
        expect(HeyFrame.Service('ruleConditionsConfigApiService')).toBeDefined();
        expect(HeyFrame.Service('salesChannelService')).toBeDefined();
        expect(HeyFrame.Service('scheduledTaskService')).toBeDefined();
        expect(HeyFrame.Service('searchService')).toBeDefined();
        expect(HeyFrame.Service('seoUrlTemplateService')).toBeDefined();
        expect(HeyFrame.Service('seoUrlService')).toBeDefined();
        expect(HeyFrame.Service('snippetSetService')).toBeDefined();
        expect(HeyFrame.Service('snippetService')).toBeDefined();
        expect(HeyFrame.Service('stateMachineService')).toBeDefined();
        expect(HeyFrame.Service('contextStoreService')).toBeDefined();
        expect(HeyFrame.Service('storeService')).toBeDefined();
        expect(HeyFrame.Service('syncService')).toBeDefined();
        expect(HeyFrame.Service('systemConfigApiService')).toBeDefined();
        expect(HeyFrame.Service('tagApiService')).toBeDefined();
        expect(HeyFrame.Service('updateService')).toBeDefined();
        expect(HeyFrame.Service('userActivityApiService')).toBeDefined();
        expect(HeyFrame.Service('userConfigService')).toBeDefined();
        expect(HeyFrame.Service('userInputSanitizeService')).toBeDefined();
        expect(HeyFrame.Service('userRecoveryService')).toBeDefined();
        expect(HeyFrame.Service('userValidationService')).toBeDefined();
        expect(HeyFrame.Service('userService')).toBeDefined();
        expect(HeyFrame.Service('shopIdChangeService')).toBeDefined();
    });
});
