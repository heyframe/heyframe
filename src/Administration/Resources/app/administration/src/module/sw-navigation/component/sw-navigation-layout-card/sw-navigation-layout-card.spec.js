/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

const navigationId = 'some-navigation-id';
const cmsPageId = 'some-cms-page-id';

async function createWrapper() {
    return mount(await wrapTestComponent('sw-navigation-layout-card', { sync: true }), {
        global: {
            stubs: {
                'router-link': true,
                'sw-loader': true,
                'sw-cms-list-item': {
                    template: '<div class="sw-cms-list-item"></div>',
                    props: ['disabled'],
                },
                'mt-card': {
                    template: '<div class="mt-card"><slot></slot></div>',
                },
                'sw-cms-layout-modal': true,
            },
            mocks: {
                $route: {
                    params: {},
                },
            },
            provide: {
                cmsPageTypeService: {
                    getType(type) {
                        return {
                            title: type,
                        };
                    },
                },
            },
        },
        props: {
            navigation: {
                id: navigationId,
                cmsPageId,
            },
        },
    });
}

describe('src/module/sw-navigation/component/sw-navigation-layout-card', () => {
    beforeEach(() => {
        global.activeAclRoles = [];
    });

    it('should have an enabled cms list item', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        const cmsListItem = wrapper.getComponent('.sw-cms-list-item');

        expect(cmsListItem.props('disabled')).toBe(false);
    });

    it('should have an disabled cms list item', async () => {
        const wrapper = await createWrapper();

        const cmsListItem = wrapper.getComponent('.sw-cms-list-item');

        expect(cmsListItem.props('disabled')).toBe(true);
    });

    it('should have an enabled button for changing the layout', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        const changeLayoutButton = wrapper.find('.sw-navigation-detail-layout__change-layout-action');

        expect(changeLayoutButton.attributes('disabled')).toBeUndefined();
    });

    it('should have an disabled button for changing the layout', async () => {
        const wrapper = await createWrapper();

        const changeLayoutButton = wrapper.find('.sw-navigation-detail-layout__change-layout-action');

        expect(changeLayoutButton.attributes('disabled') === undefined).toBe(false);
    });

    it('should have an enabled button for open the page builder', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        const pageBuilderButton = wrapper.find('.sw-navigation-detail-layout__open-in-pagebuilder');

        expect(pageBuilderButton.attributes('disabled')).toBeUndefined();
    });

    it('should have an disabled button for open the page builder', async () => {
        const wrapper = await createWrapper();

        const pageBuilderButton = wrapper.find('.sw-navigation-detail-layout__open-in-pagebuilder');

        expect(pageBuilderButton.attributes('disabled') !== undefined).toBe(true);
    });

    it('should have an enabled button for resetting the layout', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        await wrapper.setProps({
            cmsPage: {
                type: 'landingpage',
            },
        });
        await flushPromises();

        const resetLayoutButton = wrapper.find('.sw-navigation-detail-layout__layout-reset');

        expect(resetLayoutButton.attributes('disabled')).toBeUndefined();
    });

    it('should have an disabled button for resetting the layout', async () => {
        const wrapper = await createWrapper();

        await wrapper.setProps({
            cmsPage: {
                type: 'landingpage',
            },
        });
        await flushPromises();

        const resetLayoutButton = wrapper.find('.sw-navigation-detail-layout__layout-reset');

        expect(resetLayoutButton.attributes('disabled') !== undefined).toBe(true);
    });

    it('should pass the navigation id and type to the sw.cms.create route', async () => {
        global.activeAclRoles = ['navigation.editor'];
        const wrapper = await createWrapper();

        await wrapper.find('button.sw-navigation-detail-layout__open-in-pagebuilder').trigger('click');

        const routerPush = wrapper.vm.$router.push;

        expect(routerPush).toHaveBeenCalledTimes(1);
        expect(routerPush).toHaveBeenLastCalledWith({
            name: 'sw.cms.create',
            params: {
                id: navigationId,
                type: 'navigation',
            },
        });
    });

    it('should pass the navigation id to the sw.cms.create route', async () => {
        global.activeAclRoles = ['navigation.editor'];
        const wrapper = await createWrapper();

        await wrapper.setProps({
            cmsPage: {
                id: cmsPageId,
                type: 'landingpage',
            },
        });

        await wrapper.find('button.sw-navigation-detail-layout__open-in-pagebuilder').trigger('click');

        const routerPush = wrapper.vm.$router.push;

        expect(routerPush).toHaveBeenCalledTimes(1);
        expect(routerPush).toHaveBeenLastCalledWith({
            name: 'sw.cms.detail',
            params: { id: cmsPageId },
        });
    });
});
