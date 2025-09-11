/**
 * @sw-package framework
 *
 * @private
 */
export default function initMainModules(): void {
    HeyFrame.ExtensionAPI.handle('mainModuleAdd', async (mainModuleConfig, additionalInformation) => {
        const extensionName = Object.keys(HeyFrame.Store.get('extensions').extensionsState).find((key) =>
            HeyFrame.Store.get('extensions').extensionsState[key].baseUrl.startsWith(additionalInformation._event_.origin),
        );

        if (!extensionName) {
            throw new Error(`Extension with the origin "${additionalInformation._event_.origin}" not found.`);
        }

        const extension = HeyFrame.Store.get('extensions').extensionsState?.[extensionName];

        await HeyFrame.Store.get('extensionSdkModules')
            .addModule({
                heading: mainModuleConfig.heading,
                locationId: mainModuleConfig.locationId,
                displaySearchBar: mainModuleConfig.displaySearchBar ?? true,
                baseUrl: extension.baseUrl,
            })
            .then((moduleId) => {
                if (typeof moduleId !== 'string') {
                    return;
                }

                HeyFrame.Store.get('extensionMainModules').addMainModule({
                    extensionName,
                    moduleId,
                });
            });
    });

    HeyFrame.ExtensionAPI.handle('smartBarButtonAdd', (configuration) => {
        HeyFrame.Store.get('extensionSdkModules').addSmartBarButton(configuration);
    });

    HeyFrame.ExtensionAPI.handle('smartBarHide', (configuration) => {
        HeyFrame.Store.get('extensionSdkModules').addHiddenSmartBar(configuration.locationId);
    });
}
