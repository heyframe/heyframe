/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';
import { createRouter, createWebHashHistory } from 'vue-router';

async function createWrapper(categories = null) {
    const routes = [
        {
            name: 'sw.navigation.detail',
            path: '/navigation/detail/:id',
        },
    ];

    const router = createRouter({
        routes,
        history: createWebHashHistory(),
    });

    return mount(await wrapTestComponent('sw-navigation-tree', { sync: true }), {
        global: {
            mocks: {
                $router: router,
            },
            stubs: {
                'sw-loader': true,
                'sw-skeleton': true,
                'sw-tree': {
                    props: ['items'],
                    template: `
                        <div class="sw-tree">
                          <slot name="items" :treeItems="items" :checkItem="() => {}"></slot>
                        </div>
                    `,
                },
                'sw-tree-item': true,
            },
            provide: {
                syncService: {},
                repositoryFactory: {
                    create: () => ({
                        search: () =>
                            Promise.resolve([
                                {
                                    id: '1a',
                                },
                            ]),
                        delete: () => Promise.resolve(),
                        get: (id) => {
                            if (!categories) {
                                return Promise.resolve();
                            }

                            const children = Object.values(categories).filter((navigation) => navigation.parentId === id);

                            return Promise.resolve({
                                ...categories[id],
                                children,
                            });
                        },
                        saveAll: () => Promise.resolve(),
                        syncDeleted: () => Promise.resolve(),
                    }),
                },
            },
        },
        props: {
            currentLanguageId: '1a2b3c',
        },
    });
}

describe('src/module/sw-navigation/component/sw-navigation-tree', () => {
    it('should be able to sort the items', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const tree = wrapper.find('.sw-tree');
        expect(tree.attributes().sortable).toBeDefined();
    });

    it('should not be able to sort the items', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowEdit: false,
        });

        expect(wrapper.vm.sortable).toBe(false);
    });

    it('should be able to delete the items in sw-tree', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const tree = wrapper.find('.sw-tree');
        expect(tree.attributes()['allow-delete-categories']).toBeDefined();
    });

    it('should not be able to delete the items in sw-tree', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowDelete: false,
        });

        const tree = wrapper.find('.sw-tree');
        expect(tree.attributes()['allow-delete-categories']).toBeUndefined();
    });

    it('should be able to create new categories in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['allow-new-categories']).toBeDefined();
    });

    it('should not be able to create new categories in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowCreate: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['allow-new-categories']).toBeUndefined();
    });

    it('should be able to delete categories in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['allow-delete-categories']).toBeDefined();
    });

    it('should not be able to delete categories in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowDelete: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['allow-delete-categories']).toBeUndefined();
    });

    it('should show the checkbox in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['display-checkbox']).toBeDefined();
    });

    it('should not show the checkbox in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowEdit: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['display-checkbox']).toBeUndefined();
    });

    it('should show the custom tooltip text in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        await wrapper.setProps({
            allowEdit: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['context-menu-tooltip-text']).toBe('sw-privileges.tooltip.warning');
    });

    it('should not show the custom tooltip text in sw-tree-item', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const treeItem = wrapper.find('sw-tree-item-stub');
        expect(treeItem.attributes()['context-menu-tooltip-text']).toBeUndefined();
    });

    it('should get right navigation url', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const itemUrl = wrapper.vm.getCategoryUrl({ id: '1a2b' });
        expect(itemUrl).toBe('#/navigation/detail/1a2b');
    });

    it('should get wrong navigation url', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const itemUrl = wrapper.vm.getCategoryUrl({ id: '1a2b' });
        expect(itemUrl).not.toBe('#/detail/1a2b');
    });

    [
        { serviceChannels: [{ id: '4d9ef75adbb149aa99785a0a969b3b7a' }] },
        {
            navigationChannels: [
                { id: '4d9ef75adbb149aa99785a0a969b3b7b' },
            ],
        },
        { footerChannels: [{ id: '4d9ef75adbb149aa99785a0a969b3b7c' }] },
    ].forEach((entryPoint) => {
        it(`should not be able to delete a navigation having ${Object.keys(entryPoint)[0]} as initial entry point`, async () => {
            const wrapper = await createWrapper();
            wrapper.vm.createNotificationError = jest.fn();

            await wrapper.setData({
                isLoadingInitialData: false,
            });

            const navigation = {
                id: '1a',
                isNew: () => false,
                parentId: 'parent',
                ...entryPoint,
            };

            await wrapper.vm.onDeleteCategory({ data: navigation, children: [] });

            const notificationMock = wrapper.vm.createNotificationError;

            expect(notificationMock).toHaveBeenCalledTimes(1);
            expect(notificationMock).toHaveBeenCalledWith({
                message: 'sw-navigation.general.errorNavigationEntryPoint',
            });

            wrapper.vm.createNotificationError.mockRestore();
        });
    });

    it('should not be able to delete a navigation having serviceChannels as initial entry point', async () => {
        const wrapper = await createWrapper();
        wrapper.vm.createNotificationError = jest.fn();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const entryPoint = {
            serviceChannels: [{ id: '4d9ef75adbb149aa99785a0a969b3b7a' }],
        };
        const navigation = {
            id: '1a',
            isNew: () => false,
            parentId: 'parent',
            ...entryPoint,
        };

        await wrapper.vm.onDeleteCategory({ data: navigation, children: [] });

        const notificationMock = wrapper.vm.createNotificationError;

        expect(notificationMock).toHaveBeenCalledTimes(1);
        expect(notificationMock).toHaveBeenCalledWith({
            message: 'sw-navigation.general.errorNavigationEntryPoint',
        });

        wrapper.vm.createNotificationError.mockRestore();
    });

    it('should be able to delete a navigation having an empty entry point', async () => {
        const wrapper = await createWrapper();
        wrapper.vm.createNotificationError = jest.fn();

        await wrapper.setData({
            isLoadingInitialData: false,
        });

        const navigation = {
            id: '1a',
            isNew: () => false,
        };

        await wrapper.vm.onDeleteCategory({ data: navigation, children: [] });

        const notificationMock = wrapper.vm.createNotificationError;

        expect(notificationMock).toHaveBeenCalledTimes(0);
        expect(wrapper.emitted()['navigation-checked-elements-count']).toBeUndefined();

        wrapper.vm.createNotificationError.mockRestore();
    });

    it('should be able to set elements count when delete navigation is checked', async () => {
        const wrapper = await createWrapper();

        await wrapper.setData({
            isLoadingInitialData: false,
        });
        wrapper.vm.$refs.navigationTree.checkedElementsCount = 2;

        const navigation = {
            id: '1a',
            isNew: () => false,
        };

        await wrapper.vm.onDeleteCategory({
            data: navigation,
            children: [],
            checked: true,
        });

        const emitted = wrapper.emitted()['navigation-checked-elements-count'];

        expect(emitted).toBeTruthy();
        expect(emitted).toEqual([[1]]);
        expect(wrapper.vm.$refs.navigationTree.checkedElementsCount).toBe(1);
    });

    it('should fix the sorting right after deleting a single navigation', async () => {
        const wrapper = await createWrapper();

        const navigation = {
            id: '2',
            isNew: () => false,
            parentId: '1',
            afterCategoryId: '1',
        };

        await wrapper.setData({
            loadedNavigations: {
                1: { id: '1', parentId: '1', afterCategoryId: null },
                2: { id: '2', parentId: '1', afterCategoryId: '1' },
                // The `afterCategoryId` is "1" here, because in the actual code it was already fixed before
                // `onDeleteCategory` is executed, see `sw-tree`::deleteElement()
                3: { id: '3', parentId: '1', afterCategoryId: '1' },
                4: { id: '4', parentId: '1', afterCategoryId: '3' },
            },
        });

        await wrapper.vm.onDeleteCategory({ data: navigation, children: [] });

        expect(wrapper.vm.loadedNavigations[3].afterCategoryId).toBe('1');
    });

    it('should fix the sorting right after deleting multiple categories', async () => {
        const wrapper = await createWrapper();

        const categories = {
            2: {},
            4: {},
            5: {},
        };

        await wrapper.setData({
            loadedNavigations: {
                1: {
                    id: '1',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: null,
                },
                2: {
                    id: '2',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: '1',
                },
                3: {
                    id: '3',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: '2',
                },
                4: {
                    id: '4',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: '3',
                },
                5: {
                    id: '5',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: '4',
                },
                6: {
                    id: '6',
                    parentId: '1',
                    navigationChannels: null,
                    afterCategoryId: '5',
                },
            },
        });

        await wrapper.vm.deleteCheckedItems(categories);

        expect(wrapper.vm.loadedNavigations[3].afterCategoryId).toBe('1');
        expect(wrapper.vm.loadedNavigations[6].afterCategoryId).toBe('3');
    });

    it('should open the tree for active navigation on navigation change', async () => {
        const loadedNavigations = {
            1: {
                id: '1',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: null,
            },
            2: {
                id: '2',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: '1',
            },
            3: {
                id: '3',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: '2',
            },
            4: {
                id: '4',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: '3',
            },
            5: {
                id: '5',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: '4',
            },
            6: {
                id: '6',
                parentId: '1',
                navigationChannels: null,
                afterCategoryId: '5',
            },
        };
        const toLoadNavigations = {
            7: {
                id: '7',
                parentId: '2',
                navigationChannels: null,
                afterCategoryId: '2',
                path: '|1|2|',
            },
            8: {
                id: '8',
                parentId: '2',
                navigationChannels: null,
                afterCategoryId: '7',
                path: '|1|2|',
            },
            9: {
                id: '9',
                parentId: '8',
                navigationChannels: null,
                afterCategoryId: '8',
                path: '|1|2|8|',
            },
            10: {
                id: '10',
                parentId: '8',
                navigationChannels: null,
                afterCategoryId: '9',
                path: '|1|2|8|',
            },
            11: {
                id: '11',
                parentId: '8',
                navigationChannels: null,
                afterCategoryId: '10',
                path: '|1|2|8|',
            },
        };
        const categories = {
            ...loadedNavigations,
            ...toLoadNavigations,
        };

        const wrapper = await createWrapper(categories);

        await wrapper.setData({
            loadedNavigations: loadedNavigations,
        });

        const initialLoadedCategoryCount = Object.values(wrapper.vm.loadedNavigations).length;

        await wrapper.vm.$nextTick();

        wrapper.vm.$refs.navigationTree.openTreeById = jest.fn();

        HeyFrame.Store.get('swCategoryDetail').navigation = toLoadNavigations[10];

        await flushPromises();

        expect(Object.values(wrapper.vm.loadedNavigations)).toHaveLength(
            initialLoadedCategoryCount + Object.keys(toLoadNavigations).length,
        );
        expect(wrapper.vm.$refs.navigationTree.openTreeById).toHaveBeenCalled();
    });
});
