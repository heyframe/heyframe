import { mount } from '@vue/test-utils';
import { MtModal, MtModalClose, MtModalAction } from '@heyframe-ag/meteor-component-library';
import SwSettingsServicesDeactivateModal from './index';

describe('src/module/sw-settings-services/component/sw-settings-services-deactivate-modal', () => {
    const location = window.location;

    beforeAll(() => {
        HeyFrame.Service().register('heyframeServicesService', () => ({
            disableAllServices: jest.fn(),
        }));
    });

    beforeEach(() => {
        Object.defineProperty(window, 'location', {
            configurable: true,
            value: { reload: jest.fn() },
        });
    });

    afterEach(() => {
        Object.defineProperty(window, 'location', { configurable: true, value: location });
    });

    it('can be opened and closed', async () => {
        const deactivateModal = await mount(SwSettingsServicesDeactivateModal);
        await flushPromises();

        let modal = deactivateModal.getComponent(MtModal);
        expect(modal.findComponent(MtModalClose).exists()).toBe(false);

        const openButton = deactivateModal.get('button');

        expect(openButton.text()).toBe('sw-settings-services.general.deactivate');

        await openButton.trigger('click');

        modal = deactivateModal.getComponent(MtModal);
        expect(modal.findComponent(MtModalClose).exists()).toBe(true);

        await modal.getComponent(MtModalClose).trigger('click');

        modal = deactivateModal.getComponent(MtModal);
        expect(modal.findComponent(MtModalClose).exists()).toBe(false);
    });

    it('sends deactivation call', async () => {
        const notificationStore = HeyFrame.Store.get('notification');
        const notificationSpy = jest.spyOn(notificationStore, 'createNotification');

        HeyFrame.Service('heyframeServicesService').disableAllServices.mockImplementationOnce(() => ({
            disabled: true,
        }));

        const deactivateModal = await mount(SwSettingsServicesDeactivateModal);
        await flushPromises();

        await deactivateModal.get('button').trigger('click');
        const modal = deactivateModal.getComponent(MtModal);
        await modal.getComponent(MtModalAction).trigger('click');
        await flushPromises();

        expect(notificationSpy).not.toHaveBeenCalled();
        expect(window.location.reload).toHaveBeenCalled();
    });

    it('shows notification if request fails', async () => {
        const notificationStore = HeyFrame.Store.get('notification');
        const notificationSpy = jest.spyOn(notificationStore, 'createNotification');

        HeyFrame.Service('heyframeServicesService').disableAllServices.mockImplementationOnce(() => {
            throw new Error('Deactivation failed');
        });

        const deactivateModal = await mount(SwSettingsServicesDeactivateModal);
        await flushPromises();

        await deactivateModal.get('button').trigger('click');
        const modal = deactivateModal.getComponent(MtModal);
        await modal.getComponent(MtModalAction).trigger('click');
        await flushPromises();

        expect(notificationSpy).toHaveBeenCalled();
        expect(notificationSpy).toHaveBeenCalledWith({
            title: 'global.default.error',
            variant: 'critical',
            message: 'Deactivation failed',
            autoClose: false,
        });
    });
});
