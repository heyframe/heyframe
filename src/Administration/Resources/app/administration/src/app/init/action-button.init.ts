/**
 * @sw-package framework
 *
 * @private
 */
import '../store/action-buttons.store';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default function initializeActionButtons(): void {
    HeyFrame.ExtensionAPI.handle('actionButtonAdd', (configuration) => {
        HeyFrame.Store.get('actionButtons').add(configuration);
    });
}
