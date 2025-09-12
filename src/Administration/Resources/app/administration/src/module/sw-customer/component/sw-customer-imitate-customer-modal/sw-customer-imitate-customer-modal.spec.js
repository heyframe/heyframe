import { mount } from '@vue/test-utils';

/**
 * @sw-package checkout
 */

const responses = global.repositoryFactoryMock.responses;

responses.addResponse({
    method: 'Post',
    url: '/search/channel-domain',
    status: 200,
    response: {
        data: [
            {
                attributes: {
                    id: 'channel-domain-id',
                    channelId: 'channel-id',
                    channel: {
                        translated: {
                            name: 'Test sales channel',
                        },
                    },
                    url: 'http://localhost:8000',
                },
                relationships: {},
            },
        ],
    },
});

async function createWrapper() {
    return mount(
        await wrapTestComponent('sw-customer-imitate-customer-modal', {
            sync: true,
        }),
        {
            global: {
                stubs: {
                    'sw-modal': await wrapTestComponent('sw-modal', {
                        sync: true,
                    }),
                    'sw-container': await wrapTestComponent('sw-container'),
                    'sw-context-menu-item': await wrapTestComponent('sw-context-menu-item'),
                    'i18n-t': {
                        template: '<div class="i18n-stub"><slot></slot></div>',
                    },
                    'sw-loader': true,
                    'router-link': true,
                },
                provide: {
                    shortcutService: {
                        stopEventListener: () => {},
                        startEventListener: () => {},
                    },
                    contextStoreService: {
                        generateImitateCustomerToken: async () => ({
                            token: 'a-token',
                        }),
                        redirectToChannelUrl: () => {},
                    },
                },
            },
            props: {
                customer: {
                    id: 'customer-id',
                    email: null,
                    boundChannelId: null,
                },
            },
        },
    );
}

describe('module/sw-customer-imitate-customer-modal', () => {
    let wrapper;

    it('should fetch all sales channel domains', async () => {
        wrapper = await createWrapper();

        await flushPromises();

        expect(wrapper.vm.channelDomains).toHaveLength(1);
    });

    it('should forward modal close', async () => {
        wrapper = await createWrapper();

        const closeButton = await wrapper.find('.sw-modal__close');
        expect(closeButton.exists()).toBe(true);

        await closeButton.trigger('click');

        await flushPromises();

        expect(wrapper.emitted('modal-close')).toBeDefined();
    });

    it('should log in on item clicked', async () => {
        wrapper = await createWrapper();

        const generateTokenSpy = jest.spyOn(wrapper.vm.contextStoreService, 'generateImitateCustomerToken');
        const redirectChannelSpy = jest.spyOn(wrapper.vm.contextStoreService, 'redirectToChannelUrl');

        await flushPromises();

        const item = await wrapper.find('.sw-context-menu-item');
        expect(item.exists()).toBe(true);

        await item.trigger('click');

        await flushPromises();

        expect(generateTokenSpy).toHaveBeenCalledWith('customer-id', 'channel-id');
        expect(redirectChannelSpy).toHaveBeenCalledWith('http://localhost:8000', 'a-token', 'customer-id', undefined);
    });
});
