/**
 * @sw-package framework
 *
 * @private
 */
export default function initializeActions(): void {
    HeyFrame.ExtensionAPI.handle('actionExecute', async (actionConfiguration, additionalInformation) => {
        const extensionName = Object.keys(HeyFrame.Store.get('extensions').extensionsState).find((key) =>
            HeyFrame.Store.get('extensions').extensionsState[key].baseUrl.startsWith(additionalInformation._event_.origin),
        );

        if (!extensionName) {
            // eslint-disable-next-line max-len
            throw new Error(
                `Could not find an extension with the given event origin "${additionalInformation._event_.origin}"`,
            );
        }

        await HeyFrame.Service('extensionSdkService').runAction(
            {
                url: actionConfiguration.url,
                entity: actionConfiguration.entity,
                action: HeyFrame.Utils.createId(),
                appName: extensionName,
            },
            actionConfiguration.entityIds,
        );
    });
}
