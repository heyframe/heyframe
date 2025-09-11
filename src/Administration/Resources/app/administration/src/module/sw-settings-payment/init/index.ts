import '../store/overview-cards.store';

/**
 * @sw-package checkout
 */

HeyFrame.ExtensionAPI.handle('uiModulePaymentOverviewCard', (componentConfig) => {
    if (componentConfig.component === 'sw-card') {
        componentConfig.component = 'mt-card';
    }

    HeyFrame.Store.get('paymentOverviewCard').add(componentConfig);
});
