import type { SubContainer } from 'src/global.types';

import type { App } from 'vue';
import ExtensionStoreActionService from './extension-store-action.service';
import HeyFrameExtensionService from './heyframe-extension.service';
import ExtensionErrorService from './extension-error.service';

const { Application } = HeyFrame;

/**
 * @sw-package checkout
 */
declare global {
    interface ServiceContainer extends SubContainer<'service'> {
        extensionStoreActionService: ExtensionStoreActionService;
        heyframeExtensionService: HeyFrameExtensionService;
        extensionErrorService: ExtensionErrorService;
    }
}

Application.addServiceProvider('extensionStoreActionService', () => {
    return new ExtensionStoreActionService(
        HeyFrame.Application.getContainer('init').httpClient,
        HeyFrame.Service('loginService'),
    );
});

Application.addServiceProvider('heyframeExtensionService', () => {
    return new HeyFrameExtensionService(
        HeyFrame.Service('appModulesService'),
        HeyFrame.Service('extensionStoreActionService'),
        HeyFrame.Service('heyframeDiscountCampaignService'),
        HeyFrame.Service('storeService'),
    );
});

Application.addServiceProvider('extensionErrorService', () => {
    const root = HeyFrame.Application.getApplicationRoot() as App<Element>;

    return new ExtensionErrorService(
        {
            FRAMEWORK__APP_LICENSE_COULD_NOT_BE_VERIFIED: {
                title: 'sw-extension.errors.appLicenseCouldNotBeVerified.title',
                message: 'sw-extension.errors.appLicenseCouldNotBeVerified.message',
                autoClose: false,
                actions: [
                    {
                        label: root.$tc('sw-extension.errors.appLicenseCouldNotBeVerified.actionSetLicenseDomain'),
                        method: () => {
                            void root.$router.push({
                                name: 'sw.settings.store.index',
                            });
                        },
                    },
                    {
                        label: root.$tc('sw-extension.errors.appLicenseCouldNotBeVerified.actionLogin'),
                        method: () => {
                            void root.$router.push({
                                name: 'sw.extension.my-extensions.account',
                            });
                        },
                    },
                ],
            },
            FRAMEWORK__APP_NOT_COMPATIBLE: {
                title: 'global.default.error',
                message: 'sw-extension.errors.appIsNotCompatible',
            },
        },
        {
            title: 'global.default.error',
            message: 'global.notification.unspecifiedSaveErrorMessage',
        },
    );
});
