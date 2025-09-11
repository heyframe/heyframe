/**
 * @sw-package checkout
 */
import PromotionCodeApiService from '../service/promotion-code.api.service';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('promotionCodeApiService', () => {
    return new PromotionCodeApiService(
        HeyFrame.Application.getContainer('init').httpClient,
        HeyFrame.Service('loginService'),
    );
});
