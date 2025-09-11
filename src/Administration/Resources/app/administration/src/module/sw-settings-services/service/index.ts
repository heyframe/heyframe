/**
 * @sw-package framework
 */
import type { SubContainer } from '../../../global.types';
import HeyFrameServicesService from './heyframe-services.service';
import ServiceRegistryClient from './service-registry-client';

declare global {
    interface ServiceContainer extends SubContainer<'service'> {
        heyframeServicesService: HeyFrameServicesService;
        serviceRegistryClient: ServiceRegistryClient;
    }
}

/**
 * @private
 */
HeyFrame.Service().register('heyframeServicesService', () => {
    return new HeyFrameServicesService(
        HeyFrame.Application.getContainer('init').httpClient,
        HeyFrame.Service('loginService'),
        HeyFrame.Service('systemConfigApiService'),
    );
});

/**
 * @private
 */
HeyFrame.Service().register('serviceRegistryClient', () => {
    return new ServiceRegistryClient(HeyFrame.Context.api.serviceRegistryUrl!);
});
