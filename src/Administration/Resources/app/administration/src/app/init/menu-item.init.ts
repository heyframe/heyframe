/**
 * @sw-package framework
 *
 * @private
 */
export default function initMenuItems(): void {
    HeyFrame.ExtensionAPI.handle('menuItemAdd', async (menuItemConfig, additionalInformation) => {
        const extension = Object.values(HeyFrame.Store.get('extensions').extensionsState).find((ext) =>
            ext.baseUrl.startsWith(additionalInformation._event_.origin),
        );

        if (!extension) {
            throw new Error(`Extension with the origin "${additionalInformation._event_.origin}" not found.`);
        }

        await HeyFrame.Store.get('extensionSdkModules')
            .addModule({
                heading: menuItemConfig.label,
                locationId: menuItemConfig.locationId,
                displaySearchBar: menuItemConfig.displaySearchBar!,
                displaySmartBar: menuItemConfig.displaySmartBar,
                baseUrl: extension.baseUrl,
            })
            .then((moduleId) => {
                if (typeof moduleId !== 'string') {
                    return;
                }

                HeyFrame.Store.get('menuItem').addMenuItem({
                    ...menuItemConfig,
                    moduleId,
                });
            });
    });

    HeyFrame.ExtensionAPI.handle('menuCollapse', () => {
        HeyFrame.Store.get('adminMenu').collapseSidebar();
    });

    HeyFrame.ExtensionAPI.handle('menuExpand', () => {
        HeyFrame.Store.get('adminMenu').expandSidebar();
    });
}
