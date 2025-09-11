import { mount } from '@vue/test-utils';
import { MtModal, MtModalClose, MtModalAction } from '@heyframe-ag/meteor-component-library';
import SwSettingsServicesGrantPermissionsModal from './index';
import { useHeyFrameServicesStore } from '../../store/heyframe-services.store';

describe('src/module/sw-settings-services/component/sw-settings-services-grant-permissions-modal', () => {
    let originalLocation;

    beforeAll(() => {
        HeyFrame.Service().register('serviceRegistryClient', () => ({
            getCurrentRevision: jest.fn(async () => ({
                'latest-revision': '2025-06-25',
                'available-revisions': [
                    {
                        revision: '2025-06-25',
                        links: {
                            'feedback-url': 'https://heyframe.com/feedback',
                            'docs-url': 'https://docs.heyframe.com/services',
                            'tos-url': 'https://heyframe.com/agb',
                        },
                    },
                ],
            })),
        }));

        HeyFrame.Service().register('heyframeServicesService', () => ({
            acceptRevision: jest.fn(),
        }));

        originalLocation = window.location;

        Object.defineProperty(window, 'location', { configurable: true, value: { reload: jest.fn() } });
    });

    afterAll(() => {
        Object.defineProperty(window, 'location', { configurable: true, value: originalLocation });
    });

    it('can be opened by the pinia store', async () => {
        const heyframeServicesStore = useHeyFrameServicesStore();
        expect(heyframeServicesStore.revisions).toBeNull();

        const grantPermissionsModal = await mount(SwSettingsServicesGrantPermissionsModal);
        const modal = grantPermissionsModal.getComponent(MtModal);

        expect(modal.findComponent(MtModalClose).exists()).toBe(false);

        heyframeServicesStore.showGrantPermissionsModal = true;
        await flushPromises();

        expect(heyframeServicesStore.revisions).toEqual({
            'latest-revision': '2025-06-25',
            'available-revisions': [
                {
                    revision: '2025-06-25',
                    links: {
                        'feedback-url': 'https://heyframe.com/feedback',
                        'docs-url': 'https://docs.heyframe.com/services',
                        'tos-url': 'https://heyframe.com/agb',
                    },
                },
            ],
        });

        await modal.getComponent(MtModalClose).trigger('click');

        expect(modal.findComponent(MtModalClose).exists()).toBe(false);
        expect(heyframeServicesStore.showGrantPermissionsModal).toBe(false);
    });

    it('sends grant permissions request', async () => {
        const heyframeServicesStore = useHeyFrameServicesStore();
        const notificationStore = HeyFrame.Store.get('notification');
        const notificationSpy = jest.spyOn(notificationStore, 'createNotification');

        const grantPermissionsModal = await mount(SwSettingsServicesGrantPermissionsModal);

        heyframeServicesStore.showGrantPermissionsModal = true;
        await flushPromises();
        const modal = grantPermissionsModal.getComponent(MtModal);
        await modal.getComponent(MtModalAction).trigger('click');
        await flushPromises();

        expect(notificationSpy).not.toHaveBeenCalled();
        expect(HeyFrame.Service('heyframeServicesService').acceptRevision).toHaveBeenCalledWith('2025-06-25');

        expect(window.location.reload).toHaveBeenCalled();
    });

    it('shows error notification if no revision is available', async () => {
        const heyframeServicesStore = useHeyFrameServicesStore();
        const notificationStore = HeyFrame.Store.get('notification');
        const notificationSpy = jest.spyOn(notificationStore, 'createNotification');

        const grantPermissionsModal = await mount(SwSettingsServicesGrantPermissionsModal);

        heyframeServicesStore.showGrantPermissionsModal = true;
        await flushPromises();
        heyframeServicesStore.revisions = null;

        const modal = grantPermissionsModal.getComponent(MtModal);
        await modal.getComponent(MtModalAction).trigger('click');
        await flushPromises();

        expect(notificationSpy).toHaveBeenCalledWith({
            variant: 'critical',
            title: 'global.default.error',
            message: 'No revision available',
        });
        expect(HeyFrame.Service('heyframeServicesService').acceptRevision).not.toHaveBeenCalled();
        expect(window.location.reload).not.toHaveBeenCalled();
    });
});
