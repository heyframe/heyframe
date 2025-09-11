/**
 * @sw-package discovery
 */

import { mount } from '@vue/test-utils';
import { createRouter, createWebHistory } from 'vue-router';
import EntityCollection from 'src/core/data/entity-collection.data';
import getDomainLink from 'src/module/sw-channel/service/domain-link.service';

const responses = global.repositoryFactoryMock.responses;

responses.addResponse({
    method: 'Post',
    url: '/user-config',
    status: 200,
    response: {
        data: [],
    },
});

const defaultAdminLanguageId = '6a357734-afe4-4f17-a814-fb89ce9724fc';

const headlessChannel = {
    id: '342112f7-ba2f-4c73-a63f-82918e67f953',
    active: true,
    domains: [],
    type: {
        id: HeyFrame.Defaults.apiChannelTypeId,
        iconName: 'regular-shopping-basket',
    },
    translated: {
        name: 'Headless',
    },
};

const storeFrontWithStandardDomain = {
    id: '8106c8da-4528-406e-8b47-dcae65965f6b',
    active: true,
    domains: [
        {
            languageId: 'ab3e5a76-9e6a-493c-bc6c-117563976bcc',
            url: 'http://shop/custom-language',
        },
        {
            languageId: HeyFrame.Defaults.systemLanguageId,
            url: 'http://shop/default-language',
        },
    ],
    type: {
        id: HeyFrame.Defaults.storefrontChannelTypeId,
        iconName: 'default-building-shop',
    },
    translated: {
        name: 'Storefront with default domains',
    },
};

const storefrontWithoutDefaultDomain = {
    id: '0a660a4e-c1c8-4de7-a1cf-bd7a9c9886fa',
    active: true,
    domains: [
        {
            languageId: 'f084d9e0-cba4-4c42-bf99-3994e8fce125',
            url: 'http://shop/custom-language',
        },
        {
            languageId: defaultAdminLanguageId,
            url: 'http://shop/admin-language',
        },
    ],
    type: {
        id: HeyFrame.Defaults.storefrontChannelTypeId,
        iconName: 'default-building-shop',
    },
    translated: {
        name: 'Storefront with non mapped domain',
    },
};

const storefrontWithoutDomains = {
    id: '613cc4f6-1ace-4fbf-867a-e4b2ade87203',
    active: true,
    domains: [],
    type: {
        id: HeyFrame.Defaults.storefrontChannelTypeId,
        iconName: 'default-building-shop',
    },
    translated: {
        name: 'Storefront with non mapped domain',
    },
};

const inactiveStorefront = {
    id: 'a9237944-c347-4583-88b9-6d00719baff6',
    active: false,
    domains: [
        {
            languageId: '14383ce0-d2b6-4c44-94a7-cf71b42fa35a',
            url: 'http://shop/custom-language',
        },
        {
            languageId: defaultAdminLanguageId,
            url: 'http://shop/admin-language',
        },
    ],
    type: {
        id: HeyFrame.Defaults.storefrontChannelTypeId,
        iconName: 'default-building-shop',
    },
    translated: {
        name: 'Storefront with non mapped domain',
    },
};

let repositoryFactoryMock;

async function createWrapper(channels = []) {
    repositoryFactoryMock = {
        search: jest.fn((criteria, context) => {
            const channelsWithLimit = channels.slice(0, criteria.limit);

            return Promise.resolve(
                new EntityCollection(
                    'channel',
                    'channel',
                    context,
                    criteria,
                    channelsWithLimit,
                    channels.length,
                    null,
                ),
            );
        }),
    };
    const router = createRouter({
        history: createWebHistory(),
        routes: [
            {
                name: 'sw.sales.channel.detail',
                path: '/sw/sales/channel/detail/:id',
                component: await wrapTestComponent('sw-channel-detail', {
                    sync: true,
                }),
            },
            {
                name: 'sw.sales.channel.list',
                path: '/sw/sales/channel/list',
                component: await wrapTestComponent('sw-channel-list', {
                    sync: true,
                }),
            },
        ],
    });

    router.push({
        name: 'sw.sales.channel.detail',
        // the id is the storeFrontWithStandardDomain channel
        params: { id: '8106c8da-4528-406e-8b47-dcae65965f6b' },
    });

    await router.isReady();

    return mount(await wrapTestComponent('sw-channel-menu', { sync: true }), {
        global: {
            stubs: {
                'sw-admin-menu-item': {
                    template:
                        '<div class="sw-admin-menu-item" :class="$attrs.class"><div>{{ entry.label }}</div><slot name="additional-text"></slot></div>',
                    props: ['entry'],
                },
                'sw-context-button': true,
                'sw-context-menu-item': true,
                'sw-loader': true,
                'sw-internal-link': true,
                'sw-channel-modal': true,
                'router-link': true,
            },
            provide: {
                domainLinkService: {
                    getDomainLink: getDomainLink,
                },
                repositoryFactory: {
                    create: () => repositoryFactoryMock,
                },
            },
        },
    });
}

HeyFrame.Application.addServiceProvider('channelFavorites', () => {
    const favorites = [];

    return {
        state: { favorites },
        initService() {
            favorites.length = 0;
            return Promise.resolve();
        },
        getFavoriteIds() {
            return favorites;
        },
        isFavorite(id) {
            return favorites.includes(id);
        },
        update(state, channelId) {
            if (state && !this.isFavorite(channelId)) {
                favorites.push(channelId);
            } else if (!state && this.isFavorite(channelId)) {
                const index = this.state.favorites.indexOf(channelId);

                favorites.splice(index, 1);
            }
        },
    };
});

describe('src/module/sw-channel/component/structure/sw-channel-menu', () => {
    beforeEach(async () => {
        HeyFrame.Service('channelFavorites').state.favorites = [];
        HeyFrame.Store.get('session').languageId = defaultAdminLanguageId;
        global.repositoryFactoryMock.showError = false;
    });

    it('should be able to create sales channels when user has the privilege', async () => {
        global.activeAclRoles = ['channel.creator'];

        const wrapper = await createWrapper();

        const buttonCreateChannel = wrapper.find('.sw-admin-menu__headline-action');
        expect(buttonCreateChannel.exists()).toBeTruthy();
    });

    it('should not be able to create sales channels when user has not the privilege', async () => {
        global.activeAclRoles = [];

        const wrapper = await createWrapper();

        const buttonCreateChannel = wrapper.find('.sw-admin-menu__headline-action');
        expect(buttonCreateChannel.exists()).toBeFalsy();
    });

    it('should search the right sales channels', async () => {
        const wrapper = await createWrapper();

        const parsedCriteria = wrapper.vm.channelCriteria.parse();

        expect(parsedCriteria).toEqual(
            expect.objectContaining({
                associations: expect.objectContaining({
                    type: expect.any(Object),
                    domains: expect.any(Object),
                }),
            }),
        );
    });

    it('should show an entry for every sales channel returned from api', async () => {
        const testChannels = [
            headlessChannel,
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        const wrapper = await createWrapper(testChannels);

        await flushPromises();

        const channelItems = wrapper.findAll('.sw-admin-menu__channel-item');

        expect(channelItems).toHaveLength(testChannels.length);
    });

    it('does not add a link to sales channel for non storefront sales channel', async () => {
        const wrapper = await createWrapper([headlessChannel]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        expect(channelMenuEntry.find('button.sw-channel-menu-domain-link').exists()).toBe(false);
    });

    it('should use link to default language if exists', async () => {
        window.open = jest.fn();

        const wrapper = await createWrapper([storeFrontWithStandardDomain]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        const domainLinkButton = channelMenuEntry.get('button.sw-channel-menu-domain-link');

        await domainLinkButton.trigger('click');

        expect(window.open).toHaveBeenCalledWith('http://shop/default-language', '_blank');
    });

    it('prefers link to domain with actual admin language over others', async () => {
        window.open = jest.fn();

        const wrapper = await createWrapper([storefrontWithoutDefaultDomain]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        const domainLinkButton = channelMenuEntry.get('button.sw-channel-menu-domain-link');

        await domainLinkButton.trigger('click');

        expect(window.open).toHaveBeenCalledWith('http://shop/admin-language', '_blank');
    });

    it('takes first domain link if neither default language nor admin language exists', async () => {
        window.open = jest.fn();
        HeyFrame.Store.get('session').languageId = HeyFrame.Utils.createId();

        const wrapper = await createWrapper([storefrontWithoutDefaultDomain]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        const domainLinkButton = channelMenuEntry.get('button.sw-channel-menu-domain-link');

        await domainLinkButton.trigger('click');

        expect(window.open).toHaveBeenCalledWith('http://shop/custom-language', '_blank');
    });

    it('does not pick a storefront domain if there is none', async () => {
        const wrapper = await createWrapper([storefrontWithoutDomains]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        expect(channelMenuEntry.find('button.sw-channel-menu-domain-link').exists()).toBe(false);
    });

    it('does not show a storefront domain if storefront is not active', async () => {
        const wrapper = await createWrapper([inactiveStorefront]);

        await flushPromises();

        const channelMenuEntry = wrapper.find('.sw-admin-menu__channel-item');
        expect(channelMenuEntry.find('button.sw-channel-menu-domain-link').exists()).toBe(false);
    });

    it('shows "more" when no favourites are selected and there are more than 7 saleschannels', async () => {
        const channels = [
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        for (let i = 0; i < 3; i += 1) {
            channels.push({
                id: `${i}a`,
                translated: { name: `${i}a` },
                type: {
                    id: HeyFrame.Defaults.apiChannelTypeId,
                    iconName: 'regular-shopping-basket',
                },
            });
        }

        const wrapper = await createWrapper(channels);

        await flushPromises();

        // check if "more" item is visible
        const moreItems = wrapper.find('.sw-admin-menu__channel-more-items');
        expect(moreItems.isVisible()).toBe(true);
        expect(moreItems.text()).toContain('sw-channel.general.titleMenuMoreItems');
    });

    it('shows "more" when more than 50 sales channels are available and marked as favourites', async () => {
        const channels = [
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        for (let i = 0; i < 51; i += 1) {
            channels.push({
                id: `${i}a`,
                translated: { name: `${i}a` },
                type: {
                    id: HeyFrame.Defaults.apiChannelTypeId,
                    iconName: 'regular-shopping-basket',
                },
            });
        }

        HeyFrame.Service('channelFavorites').state.favorites = channels.map((el) => el.id);

        const wrapper = await createWrapper(channels);

        await flushPromises();

        // check if "more" item is visible
        const moreItems = wrapper.find('.sw-admin-menu__channel-more-items');
        expect(moreItems.isVisible()).toBe(true);
        expect(moreItems.text()).toContain('sw-channel.general.titleMenuMoreItems');
    });

    it('hide "more" when less than 7 sales channels are available and no favourites are selected', async () => {
        const wrapper = await createWrapper([
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
            {
                id: '1a',
                translated: { name: '1a' },
                type: {
                    id: HeyFrame.Defaults.apiChannelTypeId,
                    iconName: 'regular-shopping-basket',
                },
            },
            {
                id: '2b',
                translated: { name: '2b' },
                type: {
                    id: HeyFrame.Defaults.apiChannelTypeId,
                    iconName: 'regular-shopping-basket',
                },
            },
        ]);

        await flushPromises();

        // check if "more" item is hidden
        const moreItems = wrapper.find('.sw-admin-menu__channel-more-items');
        expect(moreItems.exists()).toBe(false);
    });

    it('hide "more" when less than 50 sales channels are available and favourites are selected', async () => {
        const channels = [
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        const wrapper = await createWrapper(channels);

        HeyFrame.Service('channelFavorites').state.favorites = channels.map((el) => el.id);

        await flushPromises();

        // check if "more" item is hidden
        const moreItems = wrapper.find('.sw-admin-menu__channel-more-items');
        expect(moreItems.exists()).toBe(false);
    });

    it('should only load the sales channel once when no favorites are defined', async () => {
        const channels = [
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        const wrapper = await createWrapper(channels);

        await flushPromises();

        expect(wrapper.vm.channelRepository.search).toHaveBeenCalledTimes(1);
    });

    it('should only load the sales channel once when also favorites are defined', async () => {
        const channels = [
            storeFrontWithStandardDomain,
            storefrontWithoutDefaultDomain,
            headlessChannel,
            storefrontWithoutDomains,
            inactiveStorefront,
        ];

        HeyFrame.Service('channelFavorites').state.favorites = channels.map((el) => el.id);
        const wrapper = await createWrapper(channels);

        await flushPromises();

        expect(wrapper.vm.channelRepository.search).toHaveBeenCalledTimes(1);
    });

    it.each([
        'sw-channel-detail-base-channel-change',
        'sw-channel-list-add-new-channel',
    ])('should show the sales channel modal when "%s" event is triggered', async (eventName) => {
        const wrapper = await createWrapper();

        expect(wrapper.find('sw-channel-modal-stub').exists()).toBe(false);

        HeyFrame.Utils.EventBus.emit(eventName);
        await flushPromises();

        expect(wrapper.find('sw-channel-modal-stub').exists()).toBe(true);
    });
});
