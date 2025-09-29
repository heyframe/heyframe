import './store';
import template from './sw-navigation-detail.html.twig';
import './sw-navigation-detail.scss';

const { Context, Mixin } = HeyFrame;
const { Criteria, ChangesetGenerator, EntityCollection } = HeyFrame.Data;
const { cloneDeep, merge } = HeyFrame.Utils.object;
const type = HeyFrame.Utils.types;

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'cmsService',
        'repositoryFactory',
        'seoUrlService',
        'systemConfigApiService',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.acl.can('navigation.editor');
            },
            method: 'onSave',
        },
        ESCAPE: 'cancelEdit',
    },

    props: {
        navigationId: {
            type: String,
            required: false,
            default: null,
        },
        landingPageId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            term: '',
            isLoading: false,
            isCustomFieldLoading: false,
            isSaveSuccessful: false,
            isMobileViewport: null,
            splitBreakpoint: 1024,
            isDisplayingLeavePageWarning: false,
            nextRoute: null,
            currentLanguageId: HeyFrame.Context.api.languageId,
            forceDiscardChanges: false,
            navigationCheckedItem: 0,
            landingPageCheckedItem: 0,
            entryPointOverwriteConfirmed: false,
            entryPointOverwriteChannels: null,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        changesetGenerator() {
            return new ChangesetGenerator();
        },

        showEmptyState() {
            return !this.navigation && !this.landingPage;
        },

        identifier() {
            return this.navigation ? this.placeholder(this.navigation, 'name') : '';
        },

        landingPageRepository() {
            return this.repositoryFactory.create('landing_page');
        },

        navigationRepository() {
            return this.repositoryFactory.create('navigation');
        },

        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },

        landingPage() {
            if (!HeyFrame.Store.get('swCategoryDetail')) {
                return {};
            }

            return HeyFrame.Store.get('swCategoryDetail').landingPage;
        },

        navigation() {
            if (!HeyFrame.Store.get('swCategoryDetail')) {
                return {};
            }

            return HeyFrame.Store.get('swCategoryDetail').navigation;
        },

        showEntryPointOverwriteModal() {
            return this.entryPointOverwriteChannels !== null && this.entryPointOverwriteChannels.length;
        },

        cmsPage() {
            return HeyFrame.Store.get('cmsPage').currentPage;
        },

        cmsPageState() {
            return HeyFrame.Store.get('cmsPage');
        },

        cmsPageId() {
            if (this.landingPage) {
                return this.landingPage.cmsPageId ?? null;
            }

            return this.navigation ? this.navigation.cmsPageId : null;
        },

        customFieldSetRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        customFieldSetCriteria() {
            const criteria = new Criteria(1, null);

            criteria.addFilter(Criteria.equals('relations.entityName', 'navigation'));

            return criteria;
        },

        customFieldSetLandingPageCriteria() {
            const criteria = new Criteria(1, null);

            criteria.addFilter(Criteria.equals('relations.entityName', 'landing_page'));

            return criteria;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        pageClasses() {
            return {
                'has--navigation': !!this.navigation,
                'is--mobile': !!this.isMobileViewport,
            };
        },

        tooltipSave() {
            if (!this.acl.can('navigation.editor')) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.acl.can('navigation.editor'),
                    showOnDisabledElements: true,
                };
            }

            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },

        landingPageTooltipSave() {
            if (!this.acl.can('landing_page.editor')) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.acl.can('landing_page.editor'),
                    showOnDisabledElements: true,
                };
            }

            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light',
            };
        },

        navigationCriteria() {
            const criteria = new Criteria(1, 1);
            criteria.getAssociation('seoUrls').addFilter(Criteria.equals('isCanonical', true));

            criteria
                .addAssociation('tags')
                .addAssociation('media')
                .addAssociation('navigationChannels.homeCmsPage.previewMedia')
                .addAssociation('serviceChannels')
                .addAssociation('footerChannels')
                .addAssociation('translations');

            return criteria;
        },

        landingPageCriteria() {
            const criteria = new Criteria(1, 1);

            criteria.addAssociation('tags');
            criteria.addAssociation('Channels');

            return criteria;
        },

        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },

    watch: {
        landingPageId() {
            this.setLandingPage();
        },

        navigationId() {
            this.setCategory();
        },

        cmsPageId() {
            if (this.isLoading) {
                return;
            }

            if (this.navigation) {
                this.cmsPageState.resetCmsPageState();
                this.getAssignedCmsPage();
            }

            if (this.landingPage) {
                this.cmsPageState.resetCmsPageState();
                this.getAssignedCmsPageForLandingPage();
            }
        },
    },

    beforeCreate() {
        HeyFrame.Store.get('cmsPage').resetCmsPageState();
    },

    created() {
        this.createdComponent();
    },

    beforeRouteLeave(to, from, next) {
        if (this.forceDiscardChanges) {
            this.forceDiscardChanges = false;
            HeyFrame.Store.get('heyframeApps').selectedIds = [];
            next();

            return;
        }

        if (!this.navigation) {
            HeyFrame.Store.get('heyframeApps').selectedIds = [];
            next();

            return;
        }

        /*
         * Generate change set for navigation and delete `id` and `versionId` to only consider actual changes.
         * A new version without changes should not trigger the navigation guard.
         */
        const { changes, deletionQueue } = this.changesetGenerator.generate(this.navigation);
        if (changes === null) {
            HeyFrame.Store.get('heyframeApps').selectedIds = [];
            next();

            return;
        }

        const keysToDelete = [
            'id',
            'versionId',
        ];
        const changedKeys = Object.keys(changes).filter((key) => !keysToDelete.includes(key));
        const hasDeletions = deletionQueue.length > 0;

        /*
         * Allow exiting the route to the `cms.page.create` route
         * when just the cmsPage assignment has been cleared.
         */
        if (
            to.name === 'sw.cms.create' &&
            changedKeys.length === 1 &&
            changedKeys[0] === 'cmsPageId' &&
            changes.cmsPageId === null &&
            !hasDeletions
        ) {
            HeyFrame.Store.get('heyframeApps').selectedIds = [];
            next();

            return;
        }

        if (changedKeys.length === 0 && !hasDeletions) {
            HeyFrame.Store.get('heyframeApps').selectedIds = [];
            next();

            return;
        }

        this.isDisplayingLeavePageWarning = true;
        this.nextRoute = to;
        next(false);
    },

    methods: {
        createdComponent() {
            HeyFrame.ExtensionAPI.publishData({
                id: 'sw-navigation-detail__navigation',
                path: 'navigation',
                scope: this,
            });

            HeyFrame.ExtensionAPI.publishData({
                id: 'sw-navigation-detail__cmsPage',
                path: 'cmsPage',
                scope: this,
            });

            this.isLoading = true;
            this.checkViewport();
            this.registerListener();

            if (this.navigationId !== null) {
                this.setCategory();

                return;
            }

            this.setLandingPage();
        },

        navigationCheckedElementsCount(count) {
            this.navigationCheckedItem = count;
        },

        landingPageCheckedElementsCount(count) {
            this.landingPageCheckedItem = count;
        },

        registerListener() {
            this.$device.onResize({
                listener: this.checkViewport,
            });
        },

        onSearch(value) {
            if (value.length === 0) {
                value = undefined;
            }
            this.term = value;
        },

        checkViewport() {
            this.isMobileViewport = this.$device.getViewportWidth() < this.splitBreakpoint;
        },

        getAssignedCmsPage() {
            if (this.cmsPageId === null) {
                return Promise.resolve(null);
            }

            const cmsPageId = this.cmsPageId;
            const criteria = new Criteria(1, 1);
            criteria.setIds([cmsPageId]);
            criteria.addAssociation('previewMedia');
            criteria.addAssociation('sections');
            criteria.getAssociation('sections').addSorting(Criteria.sort('position'));

            criteria.addAssociation('sections.blocks');
            criteria.getAssociation('sections.blocks').addSorting(Criteria.sort('position', 'ASC')).addAssociation('slots');

            return this.cmsPageRepository.search(criteria).then((response) => {
                const cmsPage = response.get(cmsPageId);

                if (cmsPageId !== this.cmsPageId) {
                    return null;
                }

                if (this.navigation.slotConfig !== null) {
                    cmsPage.sections.forEach((section) => {
                        section.blocks.forEach((block) => {
                            block.slots.forEach((slot) => {
                                if (this.navigation.slotConfig[slot.id]) {
                                    if (slot.config === null) {
                                        slot.config = {};
                                    }
                                    merge(slot.config, cloneDeep(this.navigation.slotConfig[slot.id]));
                                }
                            });
                        });
                    });
                }

                this.updateCmsPageDataMapping();
                this.cmsPageState.setCurrentPage(cmsPage);

                return this.cmsPage;
            });
        },

        updateCmsPageDataMapping() {
            this.cmsPageState.setCurrentMappingEntity('navigation');
            this.cmsPageState.setCurrentMappingTypes(this.cmsService.getEntityMappingTypes('navigation'));
            this.cmsPageState.setCurrentDemoEntity(this.navigation);
        },

        getAssignedCmsPageForLandingPage() {
            if (this.cmsPageId === null) {
                return Promise.resolve(null);
            }

            const cmsPageId = this.cmsPageId;
            const criteria = new Criteria(1, 1);
            criteria.setIds([cmsPageId]);
            criteria.addAssociation('previewMedia');
            criteria.addAssociation('sections');
            criteria.getAssociation('sections').addSorting(Criteria.sort('position'));

            criteria.addAssociation('sections.blocks');
            criteria
                .getAssociation('sections.blocks')
                .addSorting(Criteria.sort('position', 'ASC'))
                .getAssociation('slots')
                .addAssociation('translations');

            return this.cmsPageRepository.search(criteria).then((response) => {
                const cmsPage = response.get(cmsPageId);
                if (cmsPageId !== this.cmsPageId) {
                    return null;
                }

                if (this.landingPage.slotConfig !== null) {
                    cmsPage.sections.forEach((section) => {
                        section.blocks.forEach((block) => {
                            block.slots.forEach((slot) => {
                                if (this.landingPage.slotConfig[slot.id]) {
                                    if (slot.config === null) {
                                        slot.config = {};
                                    }
                                    merge(slot.config, cloneDeep(this.landingPage.slotConfig[slot.id]));
                                }
                            });
                        });
                    });
                }

                this.updateCmsPageDataMappingForLandingPage();
                this.cmsPageState.setCurrentPage(cmsPage);
                return this.cmsPage;
            });
        },

        updateCmsPageDataMappingForLandingPage() {
            this.cmsPageState.setCurrentMappingEntity('landing_page');
            this.cmsPageState.setCurrentMappingTypes(this.cmsService.getEntityMappingTypes('landing_page'));
            this.cmsPageState.setCurrentDemoEntity(this.landingPage);
        },

        async setLandingPage() {
            this.isLoading = true;

            try {
                if (this.landingPageId === null) {
                    HeyFrame.Store.get('heyframeApps').selectedIds = [];

                    HeyFrame.Store.get('swCategoryDetail').landingPage = null;
                    this.cmsPageState.resetCmsPageState();

                    return;
                }

                HeyFrame.Store.get('heyframeApps').selectedIds = [
                    this.landingPageId,
                ];
                await HeyFrame.Store.get('swCategoryDetail').loadActiveLandingPage({
                    repository: this.landingPageRepository,
                    apiContext: HeyFrame.Context.api,
                    id: this.landingPageId,
                    criteria: this.landingPageCriteria,
                });

                this.cmsPageState.resetCmsPageState();
                await this.getAssignedCmsPageForLandingPage();
                await this.loadLandingPageCustomFieldSet();
            } catch {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('global.notification.unspecifiedSaveErrorMessage'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        setCategory() {
            this.isLoading = true;

            if (this.navigationId === null) {
                HeyFrame.Store.get('heyframeApps').selectedIds = [];

                HeyFrame.Store.get('swCategoryDetail').navigation = null;
                this.cmsPageState.resetCmsPageState();
                this.isLoading = false;
                return;
            }

            HeyFrame.Store.get('heyframeApps').selectedIds = [
                this.navigationId,
            ];
            HeyFrame.Store.get('swCategoryDetail')
                .loadActiveCategory({
                    repository: this.navigationRepository,
                    apiContext: HeyFrame.Context.api,
                    id: this.navigationId,
                    criteria: this.navigationCriteria,
                })
                .then(() => {
                    this.cmsPageState.resetCmsPageState();
                    return Promise.resolve();
                })
                .then(this.getAssignedCmsPage)
                .then(this.loadCustomFieldSet)
                .then(() => {
                    this.isLoading = false;
                });
        },

        loadCustomFieldSet() {
            this.isCustomFieldLoading = true;

            return this.customFieldSetRepository
                .search(this.customFieldSetCriteria)
                .then((customFieldSet) => {
                    HeyFrame.Store.get('swCategoryDetail').customFieldSets = customFieldSet;
                })
                .finally(() => {
                    this.isCustomFieldLoading = true;
                });
        },

        loadLandingPageCustomFieldSet() {
            this.isCustomFieldLoading = true;

            return this.customFieldSetRepository
                .search(this.customFieldSetLandingPageCriteria)
                .then((customFieldSet) => {
                    HeyFrame.Store.get('swCategoryDetail').customFieldSets = customFieldSet;
                })
                .finally(() => {
                    this.isCustomFieldLoading = true;
                });
        },

        onSaveNavigations() {
            return this.navigationRepository.save(this.navigation);
        },

        openChangeModal(destination) {
            this.nextRoute = destination;
            this.isDisplayingLeavePageWarning = true;
        },

        onLeaveModalClose() {
            this.nextRoute = null;
            this.isDisplayingLeavePageWarning = false;
        },

        onLeaveModalConfirm(destination) {
            // Discard all navigation related errors that may have occurred
            HeyFrame.Store.get('error').removeApiError('navigation');

            this.forceDiscardChanges = true;
            this.isDisplayingLeavePageWarning = false;

            this.$nextTick(() => {
                this.$router.push({
                    name: destination.name,
                    params: destination.params,
                });
            });
        },

        cancelEdit() {
            this.resetCategory();
        },

        resetCategory() {
            this.$router.push({ name: 'sw.navigation.index' });
        },

        onChangeLanguage(newLanguageId) {
            this.currentLanguageId = newLanguageId;

            if (this.landingPageId !== null) {
                this.setLandingPage();
            }

            this.setCategory();
        },

        abortOnLanguageChange() {
            if (this.landingPage) {
                return this.landingPage ? this.navigationRepository.hasChanges(this.landingPage) : false;
            }

            return this.navigation ? this.navigationRepository.hasChanges(this.navigation) : false;
        },

        saveOnLanguageChange() {
            if (this.landingPage) {
                return this.onSaveLandingPage();
            }

            return this.onSave();
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        async onSave() {
            this.isSaveSuccessful = false;

            const pageOverrides = this.getCmsPageOverrides();

            if (type.isPlainObject(pageOverrides)) {
                this.navigation.slotConfig = cloneDeep(pageOverrides);
            }

            if (!this.entryPointOverwriteConfirmed) {
                this.checkForEntryPointOverwrite();
                if (this.showEntryPointOverwriteModal) {
                    return Promise.resolve();
                }
            }

            this.isLoading = true;
            await this.updateSeoUrls();

            const response = await this.systemConfigApiService.getValues('core.cms');

            this.defaultCategoryId = response['core.cms.default_navigation_cms_page'];

            if (this.navigation.cmsPageId === this.defaultCategoryId) {
                this.navigation.cmsPageId = null;
            }

            return this.navigationRepository
                .save(this.navigation, { ...HeyFrame.Context.api })
                .then(() => {
                    this.isSaveSuccessful = true;
                    this.entryPointOverwriteConfirmed = false;
                    return this.setCategory();
                })
                .catch(() => {
                    this.isLoading = false;
                    this.entryPointOverwriteConfirmed = false;

                    this.createNotificationError({
                        message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                    });
                });
        },

        checkForEntryPointOverwrite() {
            this.entryPointOverwriteChannels = new EntityCollection('/channel', 'channel', Context.api);

            this.navigation.navigationChannels.forEach((Channel) => {
                if (Channel.navigationCategoryId !== null && Channel.navigationCategoryId !== this.navigationId) {
                    this.entryPointOverwriteChannels.add(Channel);
                }
            });

            this.navigation.footerChannels.forEach((Channel) => {
                if (Channel.footerCategoryId !== null && Channel.footerCategoryId !== this.navigationId) {
                    this.entryPointOverwriteChannels.add(Channel);
                }
            });

            this.navigation.serviceChannels.forEach((Channel) => {
                if (Channel.serviceCategoryId !== null && Channel.serviceCategoryId !== this.navigationId) {
                    this.entryPointOverwriteChannels.add(Channel);
                }
            });
        },

        cancelEntryPointOverwrite() {
            this.entryPointOverwriteChannels = null;
        },

        confirmEntryPointOverwrite() {
            this.entryPointOverwriteChannels = null;
            this.entryPointOverwriteConfirmed = true;
            this.$nextTick(() => {
                this.onSave();
            });
        },

        onSaveLandingPage() {
            this.isSaveSuccessful = false;

            const pageOverrides = this.getCmsPageOverrides();

            if (type.isPlainObject(pageOverrides)) {
                this.landingPage.slotConfig = cloneDeep(pageOverrides);
            }

            if (this.landingPageId !== 'create') {
                if (this.landingPage.Channels.length === 0) {
                    this.addLandingPageChannelError();

                    return Promise.resolve();
                }
            }

            this.isLoading = true;
            return this.landingPageRepository
                .save(this.landingPage, HeyFrame.Context.api)
                .then(() => {
                    this.isSaveSuccessful = true;

                    if (this.landingPageId === 'create') {
                        this.$router.push({
                            name: 'sw.navigation.landingPageDetail',
                            params: { id: this.landingPage.id },
                        });
                        return Promise.resolve();
                    }

                    return this.setLandingPage();
                })
                .catch(() => {
                    this.isLoading = false;

                    if (this.landingPage.Channels.length === 0) {
                        this.addLandingPageChannelError();

                        return;
                    }

                    this.createNotificationError({
                        message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                    });
                });
        },

        addLandingPageChannelError() {
            const heyframeError = new HeyFrame.Classes.HeyFrameError({
                code: 'landing_page_channel_blank',
                detail: 'This value should not be blank.',
                status: '400',
            });

            HeyFrame.Store.get('error').addApiError({
                expression: `landing_page.${this.landingPage.id}.Channels`,
                error: heyframeError,
            });

            this.createNotificationError({
                message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
            });
        },

        getCmsPageOverrides() {
            if (this.cmsPage === null) {
                return null;
            }

            this.deleteSpecifcKeys(this.cmsPage.sections);

            const { changes } = this.changesetGenerator.generate(this.cmsPage);

            const slotOverrides = {};
            if (changes === null) {
                return slotOverrides;
            }

            if (type.isArray(changes.sections)) {
                changes.sections.forEach((section) => {
                    if (type.isArray(section.blocks)) {
                        section.blocks.forEach((block) => {
                            if (type.isArray(block.slots)) {
                                block.slots.forEach((slot) => {
                                    slotOverrides[slot.id] = slot.config;
                                });
                            }
                        });
                    }
                });
            }

            return slotOverrides;
        },

        deleteSpecifcKeys(sections) {
            if (!sections) {
                return;
            }

            sections.forEach((section) => {
                if (!section.blocks) {
                    return;
                }

                section.blocks.forEach((block) => {
                    if (!block.slots) {
                        return;
                    }

                    block.slots.forEach((slot) => {
                        if (!slot.config) {
                            return;
                        }

                        Object.values(slot.config).forEach((configField) => {
                            if (configField.entity) {
                                delete configField.entity;
                            }
                            if (configField.hasOwnProperty('required')) {
                                delete configField.required;
                            }
                            if (configField.type) {
                                delete configField.type;
                            }
                        });
                    });
                });
            });
        },

        updateSeoUrls() {
            if (!HeyFrame.Store.list().includes('swSeoUrl')) {
                return Promise.resolve();
            }

            const seoUrls = HeyFrame.Store.get('swSeoUrl').newOrModifiedUrls;

            return Promise.all(
                seoUrls.map((seoUrl) => {
                    if (seoUrl.seoPathInfo) {
                        seoUrl.isModified = true;
                        return this.seoUrlService.updateCanonicalUrl(seoUrl, seoUrl.languageId);
                    }

                    return Promise.resolve();
                }),
            );
        },

        onLandingPageDelete() {
            HeyFrame.Store.get('swCategoryDetail').landingPagesToDelete = null;
        },

        onCategoryDelete() {
            HeyFrame.Store.get('swCategoryDetail').categoriesToDelete = null;
        },
    },
};
