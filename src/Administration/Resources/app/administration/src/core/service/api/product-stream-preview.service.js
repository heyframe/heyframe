import { deepMergeObject } from 'src/core/service/utils/object.utils';
import ApiService from '../api.service';

/**
 * @private
 * @sw-package inventory
 */
export default class ProductStreamPreviewService extends ApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'productStreamPreviewService';
    }

    /**
     * @param {string} channelId
     * @param {Criteria} criteria
     * @param {Array} filter
     * @param {Object} additionalHeaders
     *
     * @returns Object
     */
    preview(channelId, criteria, filter, additionalHeaders = {}) {
        const body = deepMergeObject(criteria.parse(), {
            filter,
        });

        return this.httpClient
            .post(`_admin/product-stream-preview/${channelId}`, body, {
                headers: this.getBasicHeaders(additionalHeaders),
            })
            .then((response) => ApiService.handleResponse(response));
    }
}
