/**
 * @sw-package checkout
 *
 * @private
 */
import 'src/app/store/in-app-purchase-checkout.store';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default function initializeInAppPurchaseCheckout(): void {
    HeyFrame.ExtensionAPI.handle('iapCheckout', (entry, { _event_ }) => {
        const extension = Object.values(HeyFrame.Store.get('extensions').extensionsState).find((ext) =>
            ext.baseUrl.startsWith(_event_.origin),
        );

        if (!extension) {
            throw new Error(`Extension with the origin "${_event_.origin}" not found.`);
        }

        HeyFrame.Store.get('inAppPurchaseCheckout').request(entry, extension.name);
    });
}
