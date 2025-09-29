/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

const { Context } = HeyFrame;
const { EntityCollection } = HeyFrame.Data;

async function createWrapper(navigation = {}) {
    const defaultCategory = {
        navigationChannels: [],
        footerChannels: [],
        serviceChannels: [],
    };
    const mergedCategory = {
        ...defaultCategory,
        ...navigation,
    };

    return mount(await wrapTestComponent('sw-navigation-entry-point-card', { sync: true }), {
        global: {
            stubs: {
                'mt-card': {
                    template: '<div class="mt-card"><slot></slot></div>',
                },
                'sw-cms-list-item': true,
                'sw-single-select': {
                    template: '<div class="sw-single-select"></div>',
                    props: ['disabled'],
                },
                'sw-navigation-channel-multi-select': true,
                'router-link': true,
                'sw-navigation-entry-point-modal': true,
            },
        },
        props: {
            navigation: mergedCategory,
        },
    });
}

describe('src/module/sw-navigation/component/sw-navigation-entry-point-card', () => {
    beforeEach(() => {
        global.activeAclRoles = [];
    });

    it('should have an disabled navigation selection', async () => {
        const wrapper = await createWrapper();

        const selection = wrapper.getComponent('.sw-navigation-entry-point-card__entry-point-selection');

        expect(selection.props('disabled')).toBe(true);
    });

    it('should have an enabled navigation selection', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        const selection = wrapper.getComponent('.sw-navigation-entry-point-card__entry-point-selection');

        expect(selection.props('disabled')).toBe(false);
    });

    it('should have no initial entry point', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('');
    });

    it('should have main navigation as initial entry point', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const Channels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const wrapper = await createWrapper({
            navigationChannels: Channels,
        });

        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('navigationChannels');
    });

    it('should have footer navigation as initial entry point', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const Channels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const wrapper = await createWrapper({
            footerChannels: Channels,
        });

        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('footerChannels');
    });

    it('should have service navigation as initial entry point', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const Channels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const wrapper = await createWrapper({
            serviceChannels: Channels,
        });

        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('serviceChannels');
    });

    it('should reset its channel collections', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const navigationChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);
        const footerChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);
        const serviceChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const wrapper = await createWrapper({
            navigationChannels,
            footerChannels,
            serviceChannels,
        });

        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('navigationChannels');
        wrapper.vm.resetChannelCollections();
        // it should stay on 'navigationChannels' but the other collections should be cleared.
        expect(wrapper.vm.getInitialEntryPointFromCategory()).toBe('navigationChannels');

        expect(navigationChannels).toHaveLength(1);
        expect(footerChannels).toHaveLength(0);
        expect(serviceChannels).toHaveLength(0);
    });

    it('should add newly selected channels', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const navigationChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);
        const footerChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);
        const serviceChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const selectionChannels = new EntityCollection('/sales_channel', 'sales_channel', Context.api, null, [
            {
                id: '',
                name: '',
                translated: {
                    name: '',
                },
            },
        ]);

        const wrapper = await createWrapper({
            navigationChannels,
            footerChannels,
            serviceChannels,
        });

        wrapper.vm.onChannelChange(selectionChannels);

        // the navigation should now have two channels in its 'navigationChannel' collection.
        expect(wrapper.vm.navigation[wrapper.vm.selectedEntryPoint]).toHaveLength(2);
    });
});
