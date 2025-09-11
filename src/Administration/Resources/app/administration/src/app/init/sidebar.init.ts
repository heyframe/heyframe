/**
 * @sw-package framework
 *
 * @private
 */
export default function initializeSidebar(): void {
    // eslint-disable-next-line @typescript-eslint/require-await
    HeyFrame.ExtensionAPI.handle('uiSidebarAdd', async (sidebarConfig, { _event_ }) => {
        const extension = Object.values(HeyFrame.Store.get('extensions').extensionsState).find((ext) =>
            ext.baseUrl.startsWith(_event_.origin),
        );

        if (!extension) {
            throw new Error(`Extension with the origin "${_event_.origin}" not found.`);
        }

        // create sidebar store
        HeyFrame.Store.get('sidebar').addSidebar({
            baseUrl: extension.baseUrl,
            active: false,
            ...sidebarConfig,
        });
    });

    HeyFrame.ExtensionAPI.handle('uiSidebarClose', ({ locationId }) => {
        HeyFrame.Store.get('sidebar').closeSidebar(locationId);
    });

    HeyFrame.ExtensionAPI.handle('uiSidebarRemove', ({ locationId }) => {
        HeyFrame.Store.get('sidebar').removeSidebar(locationId);
    });
}
