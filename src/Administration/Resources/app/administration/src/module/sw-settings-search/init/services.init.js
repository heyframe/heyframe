/**
 * @sw-package inventory
 */
import ProductIndexService from '../service/productIndex.api.service';
import LiveSearchApiService from '../service/livesearch.api.service';
import ExcludedSearchTermService from '../../../core/service/api/excludedSearchTerm.api.service';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('productIndexService', () => {
    return new ProductIndexService(HeyFrame.Application.getContainer('init').httpClient, HeyFrame.Service('loginService'));
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('liveSearchService', () => {
    return new LiveSearchApiService(HeyFrame.Application.getContainer('init').httpClient, HeyFrame.Service('loginService'));
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('excludedSearchTermService', () => {
    return new ExcludedSearchTermService(
        HeyFrame.Application.getContainer('init').httpClient,
        HeyFrame.Service('loginService'),
    );
});
