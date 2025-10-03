/**
 * @sw-package discovery
 */

import type { AxiosInstance } from 'axios';
import ApiService from '../api.service';
import type { LoginService } from '../login.service';
import type { ContextSwitchParameters } from '../../../module/sw-order/order.types';

/**
 * Gateway for the API end point "channel-context"
 * Uses the _proxy endpoint of the admin api to connect to the store-api endpoint cart
 * @class
 * @extends ApiService
 */
class StoreContextService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'channel-context') {
        super(httpClient, loginService, apiEndpoint, 'application/json');

        this.name = 'contextStoreService';
    }

    updateCustomerContext(
        customerId: string,
        channelId: string,
        contextToken: string,
        additionalParams = {},
        additionalHeaders = {},
        permissions = ['allowProductPriceOverwrites'],
    ) {
        const route = '_proxy/switch-customer';
        const headers = this.getBasicHeaders({
            ...additionalHeaders,
            'sw-context-token': contextToken,
        });

        return this.httpClient.patch(
            route,
            {
                customerId: customerId,
                channelId: channelId,
                permissions: permissions,
            },
            { ...additionalParams, headers },
        );
    }

    updateContext(
        context: ContextSwitchParameters,
        channelId: string,
        contextToken: string | null,
        additionalParams = {},
        additionalHeaders = {},
    ) {
        const route = `_proxy/front-api/${channelId}/context`;
        const headers = this.getBasicHeaders({
            ...additionalHeaders,
            'sw-context-token': contextToken,
        });

        return this.httpClient.patch(route, context, {
            ...additionalParams,
            headers,
        });
    }

    getChannelContext(
        channelId: string,
        contextToken: string | null,
        additionalParams = {},
        additionalHeaders = {},
    ) {
        const route = `_proxy/front-api/${channelId}/context`;
        const headers = this.getBasicHeaders({
            ...additionalHeaders,
            'sw-context-token': contextToken,
        });

        return this.httpClient.get(route, { ...additionalParams, headers });
    }
}

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default StoreContextService;
