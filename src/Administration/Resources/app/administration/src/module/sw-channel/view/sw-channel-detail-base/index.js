/**
 * @sw-package discovery
 */

import template from './sw-channel-detail-base.html.twig';
import './sw-channel-detail-base.scss';

const { Component, Mixin, Context, Defaults } = HeyFrame;
const { Criteria } = HeyFrame.Data;
const domUtils = HeyFrame.Utils.dom;
const HeyFrameError = HeyFrame.Classes.HeyFrameError;
const utils = HeyFrame.Utils;

const { mapPropertyErrors } = Component.getComponentHelper();

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'channelService',
        'productExportService',
        'repositoryFactory',
        'knownIpsService',
        'acl',
    ],

    emits: [
        'template-selected',
        'template-modal-close',
        'template-modal-confirm',
        'invalid-file-name',
        'valid-file-name',
        'access-key-changed',
        'domain-changed',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    props: {
        // eslint-disable-next-line vue/require-prop-types
        channel: {
            required: true,
        },

        productExport: {
            // type: Entity
            type: Object,
            required: true,
        },

        // eslint-disable-next-line vue/require-default-prop
        frontendChannelCriteria: {
            type: Criteria,
            required: false,
        },

        customFieldSets: {
            type: Array,
            required: true,
        },

        isLoading: {
            type: Boolean,
            default: false,
        },

        productComparisonAccessUrl: {
            type: String,
            default: '',
        },

        templateOptions: {
            type: Array,
            default: () => [],
        },

        showTemplateModal: {
            type: Boolean,
            default: false,
        },

        templateName: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            showDeleteModal: false,
            defaultSnippetSetId: '71a916e745114d72abafbfdc51cbd9d0',
            isLoadingDomains: false,
            deleteDomain: null,
            frontendDomains: [],
            selectedStorefrontChannel: null,
            invalidFileName: false,
            isFileNameChecking: false,
            disableGenerateByCronjob: false,
            knownIps: [],
            mainCategoriesCollection: null,
            footerCategoriesCollection: null,
            serviceCategoriesCollection: null,
        };
    },

    computed: {
        secretAccessKeyFieldType() {
            return this.showSecretAccessKey ? 'text' : 'password';
        },

        isStorefront() {
            return this.channel?.typeId === Defaults.frontendChannelTypeId;
        },

        isDomainAware() {
            const domainAware = [
                Defaults.frontendChannelTypeId,
                Defaults.apiChannelTypeId,
            ];
            return domainAware.includes(this.channel.typeId);
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        isProductComparison() {
            return this.channel && this.channel.typeId === Defaults.productComparisonTypeId;
        },

        isHeadlessChannel() {
            return this.channel?.typeId === Defaults.apiChannelTypeId;
        },

        paymentMethodCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        countryCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('position', 'ASC'));
            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        languageCriteria() {
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('active', true));

            return criteria;
        },

        disabledCountries() {
            return this.channel?.countries?.filter((country) => country.active === false) ?? [];
        },

        disabledCountryVariant() {
            return this.disabledCountries.find((country) => country.id === this.channel.countryId)
                ? 'attention'
                : 'info';
        },

        disabledPaymentMethods() {
            return this.channel?.paymentMethods?.filter((paymentMethod) => paymentMethod.active === false) ?? [];
        },

        disabledPaymentMethodVariant() {
            return this.disabledPaymentMethods.find(
                (paymentMethod) => paymentMethod.id === this.channel.paymentMethodId,
            )
                ? 'attention'
                : 'info';
        },

        disabledShippingMethods() {
            return this.channel?.shippingMethods?.filter((shippingMethod) => shippingMethod.active === false) ?? [];
        },

        disabledShippingMethodVariant() {
            return this.disabledShippingMethods.find(
                (shippingMethod) => shippingMethod.id === this.channel.shippingMethodId,
            )
                ? 'attention'
                : 'info';
        },

        unservedLanguages() {
            return (
                this.channel.languages?.filter(
                    (language) =>
                        (this.channel.domains?.filter((domain) => domain.languageId === language.id) || []).length ===
                        0,
                ) ?? []
            );
        },

        unservedLanguageVariant() {
            return this.unservedLanguages.find((language) => language.id === this.channel.languageId)
                ? 'attention'
                : 'info';
        },

        frontendDomainsLoaded() {
            return this.frontendDomains.length > 0;
        },

        domainRepository() {
            return this.repositoryFactory.create(this.channel.domains.entity, this.channel.domains.source);
        },

        globalDomainRepository() {
            return this.repositoryFactory.create('channel_domain');
        },

        productExportRepository() {
            return this.repositoryFactory.create('product_export');
        },

        mainNavigationCriteria() {
            const criteria = new Criteria(1, 10);
            return criteria.addFilter(
                Criteria.equalsAny('type', [
                    'page',
                    'folder',
                ]),
            );
        },

        getIntervalOptions() {
            return [
                {
                    id: 0,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.0'),
                },
                {
                    id: 120,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.120'),
                },
                {
                    id: 300,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.300'),
                },
                {
                    id: 600,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.600'),
                },
                {
                    id: 900,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.900'),
                },
                {
                    id: 1800,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.1800'),
                },
                {
                    id: 3600,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.3600'),
                },
                {
                    id: 7200,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.7200'),
                },
                {
                    id: 14400,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.14400'),
                },
                {
                    id: 28800,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.28800'),
                },
                {
                    id: 43200,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.43200'),
                },
                {
                    id: 86400,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.86400'),
                },
                {
                    id: 172800,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.172800'),
                },
                {
                    id: 259200,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.259200'),
                },
                {
                    id: 345600,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.345600'),
                },
                {
                    id: 432000,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.432000'),
                },
                {
                    id: 518400,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.518400'),
                },
                {
                    id: 604800,
                    label: this.$tc('sw-channel.detail.productComparison.intervalLabels.604800'),
                },
            ];
        },

        getFileFormatOptions() {
            return [
                {
                    id: 'csv',
                    label: this.$tc('sw-channel.detail.productComparison.fileFormatLabels.csv'),
                },
                {
                    id: 'xml',
                    label: this.$tc('sw-channel.detail.productComparison.fileFormatLabels.xml'),
                },
            ];
        },

        getEncodingOptions() {
            return [
                {
                    id: 'ISO-8859-1',
                    label: 'ISO-8859-1',
                },
                {
                    id: 'UTF-8',
                    label: 'UTF-8',
                },
            ];
        },

        invalidFileNameError() {
            if (this.invalidFileName && !this.isFileNameChecking) {
                this.$emit('invalid-file-name');
                return new HeyFrameError({
                    code: 'DUPLICATED_PRODUCT_EXPORT_FILE_NAME',
                });
            }

            this.$emit('valid-file-name');
            return null;
        },

        helpTextTaxCalculation() {
            const link = {
                name: 'sw.settings.tax.index',
            };

            return this.$tc(
                'sw-channel.detail.helpTextTaxCalculation.label',
                {
                    link: `<sw-internal-link
                           :router-link=${JSON.stringify(link)}
                           :inline="true">
                           ${this.$tc('sw-channel.detail.helpTextTaxCalculation.linkText')}
                      </sw-internal-link>`,
                },
                0,
            );
        },

        taxCalculationTypeOptions() {
            return [
                {
                    value: 'horizontal',
                    name: this.$tc('sw-channel.detail.taxCalculation.horizontalName'),
                    description: this.$tc('sw-channel.detail.taxCalculation.horizontalDescription'),
                },
                {
                    value: 'vertical',
                    name: this.$tc('sw-channel.detail.taxCalculation.verticalName'),
                    description: this.$tc('sw-channel.detail.taxCalculation.verticalDescription'),
                },
            ];
        },

        maintenanceIpAllowlist: {
            get() {
                // eslint-disable-next-line inclusive-language/use-inclusive-words
                return this.channel.maintenanceIpWhitelist ?? [];
            },
            set(value) {
                // eslint-disable-next-line inclusive-language/use-inclusive-words
                this.channel.maintenanceIpWhitelist = value;
            },
        },

        ...mapPropertyErrors('channel', [
            'name',
            'customerGroupId',
            'navigationCategoryId',
        ]),

        ...mapPropertyErrors('productExport', [
            'productStreamId',
            'encoding',
            'fileName',
            'fileFormat',
            'channelDomainId',
            'currencyId',
        ]),

        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        mainCategoryCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('id', this.channel.navigationCategoryId || null));

            return criteria;
        },

        footerCategoryCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('id', this.channel.footerCategoryId || null));

            return criteria;
        },

        serviceCategoryCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('id', this.channel.serviceCategoryId || null));

            return criteria;
        },

        mainCategories() {
            return this.mainCategoriesCollection ? this.mainCategoriesCollection : [];
        },

        footerCategories() {
            return this.footerCategoriesCollection ? this.footerCategoriesCollection : [];
        },

        serviceCategories() {
            return this.serviceCategoriesCollection ? this.serviceCategoriesCollection : [];
        },

        navigationCategoryPlaceholder() {
            return this.channel.navigationCategoryId ? '' : this.$tc('sw-category.base.link.categoryPlaceholder');
        },

        footerCategoryPlaceholder() {
            return this.channel.footerCategoryId ? '' : this.$tc('sw-category.base.link.categoryPlaceholder');
        },

        serviceCategoryPlaceholder() {
            return this.channel.serviceCategoryId ? '' : this.$tc('sw-category.base.link.categoryPlaceholder');
        },

        channelFavoritesService() {
            return HeyFrame.Service('channelFavorites');
        },

        currencyCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        shippingMethodCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addSorting(Criteria.sort('name', 'ASC'));

            return criteria;
        },

        /**
         * @deprecated tag:v6.8.0 - Will be removed, because the filter is unused
         */
        dateFilter() {
            return HeyFrame.Filter.getByName('date');
        },

        cliCommand() {
            if (this.channel.productExports === undefined || this.channel.productExports.length === 0) {
                return '';
            }

            // eslint-disable-next-line max-len
            return `php bin/console product-export:generate ${this.channel.productExports[0].frontendChannelId} ${this.channel.productExports[0].id}`;
        },

        templateSelectOptions() {
            return this.templateOptions.map((templateOption) => {
                return {
                    value: templateOption.name,
                    id: templateOption.name,
                    label: this.$tc(templateOption.translationKey),
                };
            });
        },
    },

    watch: {
        'productExport.fileName'() {
            this.onChangeFileName();
        },
        channel() {
            this.createCategoryCollections();
        },
    },

    created() {
        this.knownIpsService.getKnownIps().then((ips) => {
            this.knownIps = ips;
        });

        this.createCategoryCollections();
    },

    methods: {
        onGenerateKeys() {
            this.channelService
                .generateKey()
                .then((response) => {
                    this.channel.accessKey = response.accessKey;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-channel.detail.messageAPIError'),
                    });
                });
        },

        onGenerateProductExportKey(displaySaveNotification = true) {
            this.productExportService
                .generateKey()
                .then((response) => {
                    this.productExport.accessKey = response.accessKey;
                    this.$emit('access-key-changed');

                    if (displaySaveNotification) {
                        this.createNotificationInfo({
                            message: this.$tc('sw-channel.detail.productComparison.messageAccessKeyChanged'),
                        });
                    }
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-channel.detail.messageAPIError'),
                    });
                });
        },

        onToggleActive() {
            if (this.channel.active !== true || this.isProductComparison || this.isHeadlessChannel) {
                return;
            }

            const criteria = new Criteria(1, 25);
            criteria.addAssociation('themes');

            this.channelRepository.get(this.$route.params.id.toLowerCase(), Context.api, criteria).then((entity) => {
                if (entity.extensions.themes !== undefined && entity.extensions.themes.length >= 1) {
                    return;
                }

                this.channel.active = false;
                this.createNotificationError({
                    message: this.$tc(
                        'sw-channel.detail.messageActivateWithoutThemeError',
                        {
                            name: this.channel.name || this.placeholder(this.channel, 'name'),
                        },
                        0,
                    ),
                });
            });
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete() {
            this.showDeleteModal = false;

            this.$nextTick(() => {
                this.deleteChannel(this.channel.id);
                this.$router.push({ name: 'sw.dashboard.index' });
            });
        },

        deleteChannel(channelId) {
            this.channelRepository.delete(channelId, Context.api).then(() => {
                HeyFrame.Utils.EventBus.emit('sw-channel-detail-base-channel-change');
                this.channelFavoritesService.refresh();
            });
        },

        async copyToClipboard() {
            try {
                await domUtils.copyStringToClipboard(this.channel.accessKey);
                this.createNotificationSuccess({
                    message: this.$tc('global.sw-field.notification.notificationCopySuccessMessage'),
                });
            } catch (err) {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('global.sw-field.notification.notificationCopyFailureMessage'),
                });
            }
        },

        onStorefrontSelectionChange(frontendChannelId) {
            this.channelRepository.get(frontendChannelId).then((entity) => {
                this.channel.languageId = entity.languageId;
                this.channel.currencyId = entity.currencyId;
                this.channel.paymentMethodId = entity.paymentMethodId;
                this.channel.shippingMethodId = entity.shippingMethodId;
                this.channel.countryId = entity.countryId;
                this.channel.navigationCategoryId = entity.navigationCategoryId;
                this.channel.navigationCategoryVersionId = entity.navigationCategoryVersionId;
                this.channel.customerGroupId = entity.customerGroupId;
            });
        },

        onStorefrontDomainSelectionChange(frontendChannelDomainId) {
            this.globalDomainRepository.get(frontendChannelDomainId).then((entity) => {
                this.productExport.channelDomain = entity;
                this.productExport.currencyId = entity.currencyId;
                this.$emit('domain-changed');
            });
        },

        loadStorefrontDomains(frontendChannelId) {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(Criteria.equals('channelId', frontendChannelId));

            this.globalDomainRepository.search(criteria).then((searchResult) => {
                this.frontendDomains = searchResult;
            });
        },

        onChangeFileName() {
            this.isFileNameChecking = true;
            this.onChangeFileNameDebounce();
        },

        onChangeFileNameDebounce: utils.debounce(function executeChange() {
            if (!this.productExport) {
                return;
            }

            if (typeof this.productExport.fileName !== 'string' || this.productExport.fileName.trim() === '') {
                this.invalidFileName = false;
                this.isFileNameChecking = false;
                return;
            }

            const criteria = new Criteria(1, 1);
            criteria.addFilter(
                Criteria.multi('AND', [
                    Criteria.equals('fileName', this.productExport.fileName),
                    Criteria.not('AND', [
                        Criteria.equals('id', this.productExport.id),
                    ]),
                ]),
            );

            this.productExportRepository
                .search(criteria)
                .then(({ total }) => {
                    this.invalidFileName = total > 0;
                    this.isFileNameChecking = false;
                })
                .catch(() => {
                    this.invalidFileName = true;
                    this.isFileNameChecking = false;
                });
        }, 500),

        changeInterval(value) {
            this.productExport.interval = value;

            this.disableGenerateByCronjob = this.productExport.interval === 0;
            this.productExport.generateByCronjob = !this.disableGenerateByCronjob;
        },

        createCategoryCollections() {
            if (!this.channel) {
                return;
            }

            this.createCategoriesCollection(this.mainCategoryCriteria, 'mainCategoriesCollection');
            this.createCategoriesCollection(this.footerCategoryCriteria, 'footerCategoriesCollection');
            this.createCategoriesCollection(this.serviceCategoryCriteria, 'serviceCategoriesCollection');
        },

        async createCategoriesCollection(criteria, collectionName) {
            this[collectionName] = await this.categoryRepository.search(criteria, HeyFrame.Context.api);
        },

        onMainSelectionAdd(item) {
            this.channel.navigationCategoryId = item.id;
        },

        onMainSelectionRemove() {
            this.channel.navigationCategoryId = null;
        },

        onFooterSelectionAdd(item) {
            this.channel.footerCategoryId = item.id;
        },

        onFooterSelectionRemove() {
            this.channel.footerCategoryId = null;
        },

        onServiceSelectionAdd(item) {
            this.channel.serviceCategoryId = item.id;
        },

        onServiceSelectionRemove() {
            this.channel.serviceCategoryId = null;
        },

        buildDisabledPaymentAlert(snippet, collection, property = 'name') {
            const route = { name: 'sw.settings.payment.overview' };
            const routeData = this.$router.resolve(route);

            const data = {
                separatedList: collection
                    .map((item) => `<span>${item.translated[property].replaceAll('|', '&vert;')}</span>`)
                    .join(', '),
                paymentSettingsLink: routeData.href,
            };

            return this.$t(snippet, data, collection.length);
        },

        buildDisabledShippingAlert(snippet, collection, property = 'name') {
            const data = {
                name: collection.first().translated[property].replaceAll('|', '&vert;'),
                addition:
                    collection.length > 2
                        ? this.$t('sw-channel.detail.warningDisabledAddition', { amount: collection.length - 1 }, 1)
                        : collection.last().translated[property].replaceAll('|', '&vert;'),
            };

            return this.$t(snippet, data, collection.length);
        },

        buildUnservedLanguagesAlert(snippet, collection, property = 'name') {
            const data = {
                list: collection.map((item) => item[property]).join(', '),
            };

            return this.$t(snippet, data, collection.length);
        },

        isFavorite() {
            return this.channelFavoritesService.isFavorite(this.channel.id);
        },

        validateMaintenanceIpCidr(term) {
            return utils.string.isValidIp(term) || utils.string.isValidCidr(term);
        },
    },
};
