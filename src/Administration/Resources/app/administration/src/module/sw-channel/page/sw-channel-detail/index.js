/**
 * @sw-package discovery
 */

import template from './sw-channel-detail.html.twig';

const { Mixin, Context, Defaults } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'exportTemplateService',
        'acl',
        'feature',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
    },

    data() {
        return {
            channel: null,
            isLoading: false,
            customFieldSets: [],
            isSaveSuccessful: false,
            productComparison: {
                newProductExport: null,
                productComparisonAccessUrl: null,
                invalidFileName: false,
                templateOptions: [],
                templates: null,
                templateName: null,
                showTemplateModal: false,
                selectedTemplate: null,
            },
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.channel, 'name');
        },

        productExport() {
            if (this.channel && this.channel.productExports.first()) {
                return this.channel.productExports.first();
            }

            if (this.productComparison.newProductExport) {
                return this.productComparison.newProductExport;
            }

            // eslint-disable-next-line vue/no-side-effects-in-computed-properties
            this.productComparison.newProductExport = this.productExportRepository.create();
            // eslint-disable-next-line vue/no-side-effects-in-computed-properties
            this.productComparison.newProductExport.interval = 0;
            // eslint-disable-next-line vue/no-side-effects-in-computed-properties
            this.productComparison.newProductExport.generateByCronjob = false;

            return this.productComparison.newProductExport;
        },

        isStorefront() {
            if (!this.channel) {
                return this.$route.params.typeId === Defaults.storefrontChannelTypeId;
            }

            return this.channel.typeId === Defaults.storefrontChannelTypeId;
        },

        isProductComparison() {
            if (!this.channel) {
                return this.$route.params.typeId === Defaults.productComparisonTypeId;
            }

            return this.channel.typeId === Defaults.productComparisonTypeId;
        },

        isHeadless() {
            if (!this.channel) {
                return this.$route.params.typeId === Defaults.apiChannelTypeId;
            }

            return this.channel.typeId === Defaults.apiChannelTypeId;
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        channelAnalyticsRepository() {
            return this.repositoryFactory.create('channel_analytics');
        },

        customFieldRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        productExportRepository() {
            return this.repositoryFactory.create('product_export');
        },

        storefrontChannelCriteria() {
            const criteria = new Criteria(1, 25);

            return criteria.addFilter(Criteria.equals('typeId', Defaults.storefrontChannelTypeId));
        },

        tooltipSave() {
            if (!this.allowSaving) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.allowSaving,
                    showOnDisabledElements: true,
                };
            }

            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },

        allowSaving() {
            return this.acl.can('channel.editor');
        },
    },

    watch: {
        '$route.params.id'() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            HeyFrame.ExtensionAPI.publishData({
                id: 'sw-channel-detail__channel',
                path: 'channel',
                scope: this,
            });
            this.loadEntityData();
            this.loadProductExportTemplates();
        },

        loadEntityData() {
            if (!this.$route.params.id) {
                return;
            }

            if (this.$route.params.typeId) {
                return;
            }

            if (this.channel) {
                this.channel = null;
            }

            this.loadChannel();
            this.loadCustomFieldSets();
        },

        loadChannel() {
            this.isLoading = true;
            this.channelRepository
                .get(this.$route.params.id.toLowerCase(), Context.api, this.getLoadChannelCriteria())
                .then((entity) => {
                    this.channel = entity;

                    // eslint-disable-next-line inclusive-language/use-inclusive-words
                    if (!this.channel.maintenanceIpWhitelist) {
                        // eslint-disable-next-line inclusive-language/use-inclusive-words
                        this.channel.maintenanceIpWhitelist = [];
                    }

                    this.generateAccessUrl();

                    this.isLoading = false;
                });
        },

        getLoadChannelCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addAssociation('paymentMethods');
            criteria.addAssociation('shippingMethods');
            criteria.addAssociation('countries');
            criteria.getAssociation('currencies').addSorting(Criteria.sort('name', 'ASC'));
            criteria.addAssociation('domains');
            criteria
                .getAssociation('languages')
                .addSorting(Criteria.sort('name', 'ASC'))
                .addFilter(Criteria.equals('active', true));
            criteria.addAssociation('analytics');

            criteria.addAssociation('productExports');
            criteria.addAssociation('productExports.channelDomain.channel');

            criteria.getAssociation('domains.language').addSorting(Criteria.sort('name', 'ASC'));
            criteria.getAssociation('domains.snippetSet').addSorting(Criteria.sort('name', 'ASC'));
            criteria.addAssociation('domains.currency');
            criteria.addAssociation('domains.productExports');

            return criteria;
        },

        onTemplateSelected(templateName) {
            if (this.productComparison.templates === null || this.productComparison.templates[templateName] === undefined) {
                return;
            }

            this.productComparison.selectedTemplate = this.productComparison.templates[templateName];
            const contentChanged = Object.keys(this.productComparison.selectedTemplate).some((value) => {
                return this.productExport[value] !== this.productComparison.selectedTemplate[value];
            });

            if (!contentChanged) {
                return;
            }

            this.productComparison.showTemplateModal = true;
        },

        onTemplateModalClose() {
            this.productComparison.selectedTemplate = null;
            this.productComparison.templateName = null;
            this.productComparison.showTemplateModal = false;
        },

        onTemplateModalConfirm() {
            Object.keys(this.productComparison.selectedTemplate).forEach((value) => {
                this.productExport[value] = this.productComparison.selectedTemplate[value];
            });
            this.onTemplateModalClose();

            this.createNotificationInfo({
                message: this.$tc('sw-channel.detail.productComparison.templates.message.template-applied-message'),
            });
        },

        loadCustomFieldSets() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(Criteria.equals('relations.entityName', 'channel'));
            criteria.getAssociation('customFields').addSorting(Criteria.sort('config.customFieldPosition', 'ASC', true));

            this.customFieldRepository.search(criteria, Context.api).then((searchResult) => {
                this.customFieldSets = searchResult;
            });
        },

        generateAccessUrl() {
            if (!this.productExport.channelDomain) {
                this.productComparison.productComparisonAccessUrl = '';
                return;
            }

            const domainUrl = this.productExport.channelDomain.url.replace(/\/+$/g, '');
            // eslint-disable-next-line max-len
            this.productComparison.productComparisonAccessUrl = `${domainUrl}/front-api/product-export/${this.productExport.accessKey}/${this.productExport.fileName}`;
        },

        loadProductExportTemplates() {
            this.productComparison.templateOptions = Object.values(
                this.exportTemplateService.getProductExportTemplateRegistry(),
            );
            this.productComparison.templates = this.exportTemplateService.getProductExportTemplateRegistry();
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        setInvalidFileName(invalidFileName) {
            this.productComparison.invalidFileName = invalidFileName;
        },

        async onSave() {
            this.isLoading = true;

            this.isSaveSuccessful = false;
            if (this.isProductComparison && !this.channel.productExports.length) {
                this.channel.productExports.add(this.productExport);
            }

            const analyticsId = this.updateAnalytics();

            try {
                await this.channelRepository.save(this.channel, Context.api);

                if (analyticsId && !this.channel?.analytics?.trackingId) {
                    await this.channelAnalyticsRepository.delete(analyticsId, Context.api);
                }

                this.isLoading = false;
                this.isSaveSuccessful = true;

                HeyFrame.Utils.EventBus.emit('sw-channel-detail-channel-change');
                this.loadEntityData();
            } catch (error) {
                this.isLoading = false;

                this.createNotificationError({
                    message: this.$tc(
                        'sw-channel.detail.messageSaveError',
                        {
                            name: this.channel.name || this.placeholder(this.channel, 'name'),
                        },
                        0,
                    ),
                });
            }
        },

        updateAnalytics() {
            const analyticsId = this.channel.analyticsId;
            if (analyticsId && !this.channel?.analytics?.trackingId) {
                this.channel.analyticsId = null;
                delete this.channel.analytics;
            }

            return analyticsId;
        },

        abortOnLanguageChange() {
            return this.channelRepository.hasChanges(this.channel);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },
    },
};
