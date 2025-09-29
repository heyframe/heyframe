/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

describe('src/module/sw-navigation/page/sw-navigation-detail', () => {
    const saveMock = jest.fn(() => Promise.resolve());
    async function createWrapper() {
        return mount(await wrapTestComponent('sw-navigation-detail', { sync: true }), {
            global: {
                stubs: {
                    'sw-page': {
                        template: `
                    <div>
                        <slot name="smart-bar-actions"></slot>
                        <slot></slot>
                        <slot name="side-content"></slot>
                    </div>`,
                    },
                    'sw-navigation-tree': {
                        template: '<div class="sw-navigation-tree"></div>',
                        props: [
                            'allowEdit',
                            'allowCreate',
                            'allowDelete',
                        ],
                    },
                    'sw-button-process': {
                        template: '<div class="sw-button-process"><slot></slot></div>',
                        props: ['disabled'],
                    },
                    'sw-sidebar-collapse': {
                        template: `
                    <div class="sw-sidebar-collapse">
                        <slot name="header"></slot>
                        <slot name="actions"></slot>
                        <slot name="content"></slot>
                    </div>`,
                    },
                    'sw-collapse': await wrapTestComponent('sw-collapse'),
                    'sw-landing-page-tree': true,
                    'sw-search-bar': true,
                    'sw-language-switch': true,
                    'sw-skeleton': true,
                    'sw-navigation-view': true,
                    'sw-navigation-entry-point-overwrite-modal': true,
                    'sw-landing-page-view': true,
                    'sw-discard-changes-modal': true,
                    'sw-empty-state': true,
                },
                provide: {
                    cmsService: {
                        getEntityMappingTypes: () => {},
                    },
                    repositoryFactory: {
                        create: () => ({
                            search: () =>
                                Promise.resolve({
                                    get: () => ({ sections: [] }),
                                }),
                            save: saveMock,
                            get: () =>
                                Promise.resolve({
                                    slotConfig: '',
                                    navigationChannels: [],
                                    footerChannels: [],
                                    serviceChannels: [],
                                }),
                        }),
                    },
                    seoUrlService: {},
                    systemConfigApiService: {
                        getValues: () =>
                            Promise.resolve({
                                'core.cms.default_navigation_cms_page': 'foo',
                            }),
                    },
                },
            },
        });
    }

    beforeEach(() => {
        global.activeAclRoles = [];

        HeyFrame.Store.unregister('cmsPage');
        HeyFrame.Store.register({
            id: 'cmsPage',
            state: () => ({
                currentPage: null,
            }),
            actions: {
                resetCmsPageState: () => {},
                setCurrentMappingEntity: () => {},
                setCurrentMappingTypes: () => {},
                setCurrentDemoEntity: () => {},
                setCurrentPage: () => {},
            },
        });
    });

    it('should not allow to modify', async () => {
        const wrapper = await createWrapper();

        HeyFrame.Store.get('swCategoryDetail').navigation = {
            slotConfig: '',
        };

        await wrapper.setData({
            isLoading: false,
        });

        const saveButton = wrapper.getComponent('.sw-navigation-detail__save-action');

        expect(saveButton.props('disabled')).toBe(true);

        const navigationTree = wrapper.getComponent('.sw-navigation-tree');

        expect(navigationTree.props('allowCreate')).toBe(false);
        expect(navigationTree.props('allowEdit')).toBe(false);
        expect(navigationTree.props('allowDelete')).toBe(false);
    });

    it('should allow to edit', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        HeyFrame.Store.get('swCategoryDetail').navigation = {
            slotConfig: '',
        };

        await wrapper.setData({
            isLoading: false,
        });

        const saveButton = wrapper.getComponent('.sw-navigation-detail__save-action');

        expect(saveButton.props('disabled')).toBe(false);

        const navigationTree = wrapper.getComponent('.sw-navigation-tree');

        expect(navigationTree.props('allowCreate')).toBe(false);
        expect(navigationTree.props('allowEdit')).toBe(true);
        expect(navigationTree.props('allowDelete')).toBe(false);
    });

    it('should allow to create', async () => {
        global.activeAclRoles = [
            'navigation.creator',
            'navigation.editor',
        ];

        const wrapper = await createWrapper();

        HeyFrame.Store.get('swCategoryDetail').navigation = {
            slotConfig: '',
        };

        await wrapper.setData({
            isLoading: false,
        });

        const saveButton = wrapper.getComponent('.sw-navigation-detail__save-action');

        expect(saveButton.props('disabled')).toBe(false);

        const navigationTree = wrapper.getComponent('.sw-navigation-tree');

        expect(navigationTree.props('allowCreate')).toBe(true);
        expect(navigationTree.props('allowEdit')).toBe(true);
        expect(navigationTree.props('allowDelete')).toBe(false);
    });

    it('should allow to delete', async () => {
        global.activeAclRoles = [
            'navigation.creator',
            'navigation.editor',
            'navigation.deleter',
        ];

        const wrapper = await createWrapper();

        HeyFrame.Store.get('swCategoryDetail').navigation = {
            slotConfig: '',
        };

        await wrapper.setData({
            isLoading: false,
        });

        const saveButton = wrapper.getComponent('.sw-navigation-detail__save-action');

        expect(saveButton.props('disabled')).toBe(false);

        const navigationTree = wrapper.getComponent('.sw-navigation-tree');

        expect(navigationTree.props('allowCreate')).toBe(true);
        expect(navigationTree.props('allowEdit')).toBe(true);
        expect(navigationTree.props('allowDelete')).toBe(true);
    });

    it('should set default layout', async () => {
        global.activeAclRoles = [
            'navigation.creator',
            'navigation.editor',
            'navigation.deleter',
        ];

        const wrapper = await createWrapper();

        HeyFrame.Store.get('swCategoryDetail').navigation = {
            slotConfig: '',
            cmsPageId: 'foo',
            navigationChannels: [],
            footerChannels: [],
            serviceChannels: [],
        };

        await wrapper.setData({
            isLoading: false,
            cmsPage: null,
        });

        await wrapper.setProps({
            navigationId: 'foo',
        });

        await wrapper.vm.onSave();

        const lastCallParameters = saveMock.mock.lastCall;
        expect(lastCallParameters[0].cmsPageId).toBeUndefined();
    });
});
