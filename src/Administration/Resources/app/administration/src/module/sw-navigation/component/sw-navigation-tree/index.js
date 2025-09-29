import template from './sw-navigation-tree.html.twig';
import './sw-navigation-tree.scss';

const { Criteria } = HeyFrame.Data;

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'syncService',
    ],

    emits: [
        'navigation-checked-elements-count',
        'unsaved-changes',
    ],

    mixins: ['notification'],

    props: {
        navigationId: {
            type: String,
            required: false,
            default: null,
        },

        currentLanguageId: {
            type: String,
            required: true,
        },

        allowEdit: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },

        allowCreate: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },

        allowDelete: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },
    },

    data() {
        return {
            loadedNavigations: {},
            translationContext: 'sw-navigation',
            linkContext: 'sw.navigation.detail',
            isLoadingInitialData: true,
            loadedParentIds: [],
            sortable: this.allowEdit,
        };
    },

    computed: {
        categoriesToDelete() {
            return HeyFrame.Store.get('swCategoryDetail').categoriesToDelete;
        },

        navigationRepository() {
            return this.repositoryFactory.create('navigation');
        },

        navigation() {
            return HeyFrame.Store.get('swCategoryDetail').navigation;
        },

        categories() {
            return Object.values(this.loadedNavigations);
        },

        disableContextMenu() {
            if (!this.allowEdit) {
                return true;
            }

            return this.currentLanguageId !== HeyFrame.Context.api.systemLanguageId;
        },

        contextMenuTooltipText() {
            if (!this.allowEdit) {
                return this.$tc('sw-privileges.tooltip.warning');
            }

            return null;
        },

        criteria() {
            return new Criteria(1, 500)
                .addAssociation('navigationChannels')
                .addAssociation('footerChannels')
                .addAssociation('serviceChannels');
        },

        criteriaWithChildren() {
            const parentCriteria = Criteria.fromCriteria(this.criteria).setLimit(1);
            parentCriteria.associations.push({
                association: 'children',
                criteria: Criteria.fromCriteria(this.criteria),
            });

            return parentCriteria;
        },

        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },
    },

    watch: {
        categoriesToDelete(value) {
            if (value === undefined) {
                return;
            }

            this.$refs.navigationTree.onDeleteElements(value);

            HeyFrame.Store.get('swCategoryDetail').categoriesToDelete = undefined;
        },

        allowEdit(value) {
            this.sortable = value;
        },

        navigation(newVal, oldVal) {
            // load data when path is available
            if (!oldVal && this.isLoadingInitialData) {
                this.openInitialTree();
                return;
            }

            // back to index
            if (newVal === null) {
                return;
            }

            // reload after save
            if (oldVal && newVal.id === oldVal.id) {
                const affectedCategoryIds = [
                    newVal.id,
                    ...oldVal.navigationChannels.map((Channel) => Channel.navigationCategoryId),
                    ...oldVal.footerChannels.map((Channel) => Channel.footerCategoryId),
                    ...oldVal.serviceChannels.map((Channel) => Channel.serviceCategoryId),
                ];

                const criteria = Criteria.fromCriteria(this.criteria).setIds(
                    affectedCategoryIds.filter((value, index, self) => {
                        return value !== null && self.indexOf(value) === index;
                    }),
                );

                this.navigationRepository.search(criteria).then((categories) => {
                    this.addNavigations(categories);
                });

                return;
            }

            this.loadActiveCategory().then(() => {
                this.$refs.navigationTree.openTreeById();
            });
        },

        currentLanguageId() {
            this.openInitialTree();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (this.navigation !== null) {
                this.openInitialTree();
            }

            if (!this.navigationId) {
                this.loadRootNavigations().finally(() => {
                    this.isLoadingInitialData = false;
                });
            }
        },

        openInitialTree() {
            this.isLoadingInitialData = true;
            this.loadedNavigations = {};
            this.loadedParentIds = [];

            this.loadRootNavigations().then(() => {
                if (!this.navigation || this.navigation.path === null) {
                    this.isLoadingInitialData = false;
                    return Promise.resolve();
                }

                return this.loadActiveCategory().then(() => {
                    this.isLoadingInitialData = false;
                });
            });
        },

        loadActiveCategory() {
            if (!this.navigation || this.navigation.path === null || this.navigation.id in this.loadedNavigations) {
                return Promise.resolve();
            }

            const parentIds = this.navigation.path.split('|').filter((id) => !!id);
            const parentPromises = [];

            parentIds.forEach((id) => {
                const promise = this.navigationRepository
                    .get(id, HeyFrame.Context.api, this.criteriaWithChildren)
                    .then((result) => {
                        this.addNavigations([
                            result,
                            ...result.children,
                        ]);
                    });
                parentPromises.push(promise);
            });

            return Promise.all(parentPromises);
        },

        onUpdatePositions: HeyFrame.Utils.debounce(function onUpdatePositions({ draggedItem, oldParentId, newParentId }) {
            if (draggedItem.children.length > 0) {
                draggedItem.children.forEach((child) => {
                    this.removeFromStore(child.id);
                });
                this.loadedParentIds = this.loadedParentIds.filter((id) => id !== draggedItem.id);
            }

            this.syncSiblings({ parentId: newParentId }).then(() => {
                if (oldParentId !== newParentId) {
                    this.syncSiblings({ parentId: oldParentId }).then(() => {
                        this.syncProducts(draggedItem.id);
                    });
                }

                this.sortable = this.allowEdit;
            });
        }, 400),

        syncProducts(navigationId) {
            const criteria = new Criteria(1, 50);
            criteria.addFilter(
                Criteria.multi('or', [
                    Criteria.equals('categoriesRo.id', navigationId),
                    Criteria.equals('categories.id', navigationId),
                ]),
            );

            return this.productRepository.iterateIds(criteria, this.indexProducts);
        },

        indexProducts(ids) {
            const headers = this.productRepository.buildHeaders();

            const initContainer = HeyFrame.Application.getContainer('init');
            const httpClient = initContainer.httpClient;

            return httpClient.post('/_action/index-products', { ids }, { headers });
        },

        checkedElementsCount(count) {
            this.$emit('navigation-checked-elements-count', count);
        },

        async deleteCheckedItems(checkedItems) {
            const ids = Object.keys(checkedItems);

            const hasNavigationNavigations = ids.some((id) => {
                return (
                    this.loadedNavigations[id]?.navigationChannels !== null &&
                    this.loadedNavigations[id]?.navigationChannels.length > 0
                );
            });

            if (hasNavigationNavigations) {
                this.createNotificationError({
                    message: this.$tc('sw-navigation.general.errorNavigationEntryPointMultiple'),
                });

                const categories = ids.map((id) => {
                    return this.loadedNavigations[id];
                });

                // reload to remove selection
                ids.forEach((deleted) => {
                    delete this.loadedNavigations[deleted];
                });
                this.$nextTick(() => {
                    this.addNavigations(categories);
                });

                return;
            }

            await this.navigationRepository.syncDeleted(ids, HeyFrame.Context.api);

            const categories = ids.map((id) => this.loadedNavigations[id]);

            await this.fixSortingForNavigations(categories);

            ids.forEach((id) => {
                this.removeFromStore(id);
            });
        },

        onDeleteCategory({ data: navigation, children, checked }) {
            if (navigation.isNew()) {
                delete this.loadedNavigations[navigation.id];
                return Promise.resolve();
            }

            if (this.isErrorNavigationEntryPoint(navigation)) {
                // remove delete flags
                navigation.isDeleted = false;
                if (children.length > 0) {
                    children.forEach((child) => {
                        child.data.isDeleted = false;
                    });
                }

                // reinsert navigation in sorting because the tree
                // already overwrites the afterCategoryId of the following navigation
                const next = this.getNextCategory(navigation);

                if (next) {
                    next.afterCategoryId = navigation.id;
                }

                // reload after changes
                this.loadedNavigations = { ...this.loadedNavigations };

                this.createNotificationError({
                    message: this.entryPointWarningMessage(navigation),
                });
                return Promise.resolve();
            }

            return this.navigationRepository.delete(navigation.id).then(async () => {
                this.removeFromStore(navigation.id);

                if (navigation.parentId !== null) {
                    const updatedParent = await this.navigationRepository.get(
                        navigation.parentId,
                        HeyFrame.Context.api,
                        this.criteria,
                    );
                    this.addCategory(updatedParent);
                }

                await this.fixSortingForNavigations([navigation], true);

                if (navigation.id === this.navigationId) {
                    this.$router.push({ name: 'sw.navigation.index' });
                }

                if (checked === true) {
                    this.$refs.navigationTree.checkedElementsCount -= 1;
                    this.$emit('navigation-checked-elements-count', this.$refs.navigationTree.checkedElementsCount);
                }
            });
        },

        fixSortingForNavigations(categories, isSorted = false) {
            const categoriesToBeChanged = [];

            categories.forEach((navigation) => {
                // We need the second parameter, because the value of `afterCategoryId` of the actual next navigation
                // is either updated already in case of `onDeleteCategory`, but not in case of `deleteCheckedItems`
                const nextCategory = this.getNextCategory(navigation, isSorted ? 'afterCategoryId' : 'id');

                if (!nextCategory) {
                    return;
                }

                nextCategory.afterCategoryId = navigation.afterCategoryId;

                if (categories.find((item) => item.id === nextCategory.id)) {
                    return;
                }

                categoriesToBeChanged.push(nextCategory);
            });

            return this.navigationRepository.saveAll(categoriesToBeChanged);
        },

        getNextCategory(navigation, key = 'id') {
            return Object.values(this.loadedNavigations).find((item) => {
                return item.parentId === navigation.parentId && item.afterCategoryId === navigation[key];
            });
        },

        changeCategory(navigation) {
            const route = {
                name: 'sw.navigation.detail',
                params: { id: navigation.id },
            };
            if (this.navigation && this.navigationRepository.hasChanges(this.navigation)) {
                this.$emit('unsaved-changes', route);
            } else {
                this.$router.push(route);
            }
        },

        onGetTreeItems(parentId) {
            if (this.loadedParentIds.includes(parentId)) {
                return Promise.resolve();
            }

            const criteria = Criteria.fromCriteria(this.criteria);
            criteria.addFilter(Criteria.equals('parentId', parentId));
            // in case the criteria has been altered to search specific ids e.g. by dragndrop position change
            // reset all ids so categories can be found solely by parentId
            criteria.setIds([]);

            return this.navigationRepository.search(criteria).then((children) => {
                this.addNavigations(children);
                this.loadedParentIds.push(parentId);
            });
        },

        getChildrenFromParent(parentId) {
            return this.onGetTreeItems(parentId);
        },

        loadRootNavigations() {
            const criteria = Criteria.fromCriteria(this.criteria).addFilter(Criteria.equals('parentId', null));

            return this.navigationRepository.search(criteria).then((result) => {
                this.addNavigations(result);
            });
        },

        createNewElement(contextItem, parentId, name = '') {
            this.sortable = false;

            if (!parentId && contextItem) {
                parentId = contextItem.parentId;
            }
            const newCategory = this.createNewCategory(name, parentId);
            this.addCategory(newCategory);
            return newCategory;
        },

        createNewCategory(name, parentId) {
            const newCategory = this.navigationRepository.create();

            newCategory.name = name;
            newCategory.parentId = parentId;
            newCategory.childCount = 0;
            newCategory.active = false;
            newCategory.visible = true;

            newCategory.save = () => {
                return this.navigationRepository.save(newCategory).then(() => {
                    const criteria = Criteria.fromCriteria(this.criteria).setIds(
                        [
                            newCategory.id,
                            parentId,
                        ].filter((id) => id !== null),
                    );
                    this.navigationRepository.search(criteria).then((categories) => {
                        this.addNavigations(categories);

                        this.sortable = this.allowEdit;
                    });
                });
            };

            return newCategory;
        },

        syncSiblings({ parentId }) {
            const siblings = this.categories.filter((navigation) => {
                return navigation.parentId === parentId;
            });

            return this.navigationRepository
                .sync(siblings)
                .then(() => {
                    this.loadedParentIds = this.loadedParentIds.filter((id) => id !== parentId);
                    return this.getChildrenFromParent(parentId);
                })
                .then(() => {
                    this.navigationRepository.get(parentId, HeyFrame.Context.api, this.criteria).then((parent) => {
                        this.addCategory(parent);
                    });
                });
        },

        addCategory(navigation) {
            if (!navigation) {
                return;
            }

            this.loadedNavigations[navigation.id] = navigation;
        },

        addNavigations(categories) {
            categories.forEach((navigation) => {
                this.loadedNavigations[navigation.id] = navigation;
            });
        },

        removeFromStore(id) {
            const deletedIds = this.getDeletedIds(id);
            this.loadedParentIds = this.loadedParentIds.filter((loadedId) => {
                return !deletedIds.includes(loadedId);
            });

            deletedIds.forEach((deleted) => {
                delete this.loadedNavigations[deleted];
            });
        },

        getDeletedIds(idToDelete) {
            const idsToDelete = [idToDelete];
            Object.keys(this.loadedNavigations).forEach((id) => {
                const currentCategory = this.loadedNavigations[id];
                if (currentCategory.parentId === idToDelete) {
                    idsToDelete.push(...this.getDeletedIds(id));
                }
            });
            return idsToDelete;
        },

        getCategoryUrl(navigation) {
            return this.$router.resolve({
                name: this.linkContext,
                params: { id: navigation.id },
            }).href;
        },

        isHighlighted({ data: navigation }) {
            return (
                (navigation.navigationChannels !== null && navigation.navigationChannels.length > 0) ||
                (navigation.serviceChannels !== null && navigation.serviceChannels.length > 0) ||
                (navigation.footerChannels !== null && navigation.footerChannels.length > 0)
            );
        },

        isErrorNavigationEntryPoint(navigation) {
            const { navigationChannels, serviceChannels, footerChannels } = navigation;

            return [
                navigationChannels,
                serviceChannels,
                footerChannels,
            ].some((navigation) => navigation !== null && navigation?.length > 0);
        },

        entryPointWarningMessage(navigation) {
            const { serviceChannels, footerChannels } = navigation;

            if (serviceChannels !== null && serviceChannels?.length > 0) {
                return this.$tc(
                    'sw-navigation.general.errorNavigationEntryPoint',
                    {
                        entryPointLabel: this.$tc('sw-navigation.base.entry-point-card.types.labelServiceNavigation'),
                    },
                    0,
                );
            }

            if (footerChannels !== null && footerChannels?.length > 0) {
                return this.$tc(
                    'sw-navigation.general.errorNavigationEntryPoint',
                    {
                        entryPointLabel: this.$tc('sw-navigation.base.entry-point-card.types.labelFooterNavigation'),
                    },
                    0,
                );
            }

            return this.$tc(
                'sw-navigation.general.errorNavigationEntryPoint',
                {
                    entryPointLabel: this.$tc('sw-navigation.base.entry-point-card.types.labelMainNavigation'),
                },
                0,
            );
        },
    },
};
