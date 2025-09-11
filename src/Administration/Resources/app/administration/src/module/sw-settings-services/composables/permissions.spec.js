import { createPinia, setActivePinia } from 'pinia';
import { revokePermissions, grantPermissions } from './permissions';
import { useHeyFrameServicesStore } from '../store/heyframe-services.store';

describe('src/module/sw-settings-services/composables/permissions', () => {
    let originalLocation;

    beforeAll(() => {
        HeyFrame.Service().register('heyframeServicesService', () => ({
            acceptRevision: jest.fn(),
            revokePermissions: jest.fn(),
        }));

        originalLocation = window.location;

        Object.defineProperty(window, 'location', { configurable: true, value: { reload: jest.fn() } });
    });

    beforeEach(() => {
        setActivePinia(createPinia());
        useHeyFrameServicesStore();
    });

    afterAll(() => {
        Object.defineProperty(window, 'location', { configurable: true, value: originalLocation });
    });

    it('calls heyframe service and reloads', async () => {
        const heyframeServicesStore = useHeyFrameServicesStore();

        heyframeServicesStore.revisions = {
            'latest-revision': '2025-06-25',
            'available-revisions': [
                {
                    revision: '2025-06-25',
                    links: {},
                },
            ],
        };

        await grantPermissions();

        expect(HeyFrame.Service('heyframeServicesService').acceptRevision).toHaveBeenCalledWith('2025-06-25');
        expect(window.location.reload).toHaveBeenCalled();
    });

    it('throws exception if there is no current revision', async () => {
        await expect(() => grantPermissions()).rejects.toThrow(new Error('No revision available'));
    });

    it('calls heyframe service to revoke permissions and reloads', async () => {
        await revokePermissions();

        expect(HeyFrame.Service('heyframeServicesService').revokePermissions).toHaveBeenCalled();
        expect(window.location.reload).toHaveBeenCalled();
    });
});
