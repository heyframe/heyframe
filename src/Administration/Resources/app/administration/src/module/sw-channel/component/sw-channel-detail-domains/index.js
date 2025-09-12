/**
 * @sw-package discovery
 */

import template from './sw-channel-detail-domains.html.twig';
import './sw-channel-detail-domains.scss';

const { Mixin, Context } = HeyFrame;
const { Criteria } = HeyFrame.Data;
const { HeyFrameError } = HeyFrame.Classes;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        // eslint-disable-next-line vue/require-prop-types
        channel: {
            required: true,
        },

        disableEdit: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            currentDomain: null,
            currentDomainBackup: {
                url: null,
                language: null,
                languageId: null,
                currency: null,
                currencyId: null,
                snippetSet: null,
                snippetSetId: null,
            },
            isLoadingDomains: false,
            deleteDomain: null,
            sortBy: 'url',
            sortDirection: 'ASC',
            error: null,
            isEditingDomain: false,
        };
    },

    computed: {
        domainRepository() {
            return this.repositoryFactory.create(this.channel.domains.entity, this.channel.domains.source);
        },

        currentDomainModalTitle() {
            if (!this.isEditingDomain) {
                return this.$t('sw-channel.detail.titleCreateDomain');
            }

            return this.$t('sw-channel.detail.titleEditDomain', {
                name: this.unicodeUriFilter(this.currentDomainBackup.url),
            });
        },

        currentDomainModalButtonText() {
            if (!this.isEditingDomain) {
                return this.$t('sw-channel.detail.buttonAddDomain');
            }
            return this.$t('sw-channel.detail.buttonEditDomain');
        },

        snippetSetCriteria() {
            return new Criteria(1, 25).addSorting(Criteria.sort('name', 'ASC'));
        },

        channelFilterCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addAssociation('channels').addSorting(Criteria.sort('name', 'ASC'));

            return criteria.addFilter(Criteria.equals('channels.id', this.channel.id));
        },

        currencyCriteria() {
            return new Criteria(1, 25).addSorting(Criteria.sort('name', 'ASC'));
        },

        hreflangLocalisationOptions() {
            return [
                {
                    name: this.$tc('sw-channel.detail.hreflang.domainSettings.byIso'),
                    value: false,
                    helpText: this.$tc('sw-channel.detail.hreflang.domainSettings.byIsoHelpText'),
                },
                {
                    name: this.$tc('sw-channel.detail.hreflang.domainSettings.byAbbreviation'),
                    value: true,
                    helpText: this.$tc('sw-channel.detail.hreflang.domainSettings.byAbbreviationHelpText'),
                },
            ];
        },

        disabled() {
            return (
                !this.currentDomain ||
                !this.currentDomain.currencyId ||
                !this.currentDomain.snippetSetId ||
                !this.currentDomain.url ||
                !this.currentDomain.languageId ||
                this.disableEdit ||
                this.error !== null
            );
        },

        sortedDomains() {
            const domains = [...this.channel.domains];

            return this.localSortDomains(domains);
        },
    },

    methods: {
        sortColumns(column) {
            if (this.sortBy === column.dataIndex) {
                // If the same column, that is already being sorted, is clicked again, change direction
                this.sortDirection = this.sortDirection === 'ASC' ? 'DESC' : 'ASC';
            } else {
                // We clicked on a new column to be sorted, therefore set the sort field and the direction to 'ASC'
                this.sortBy = column.dataIndex;
                this.sortDirection = 'ASC';
            }
        },

        unicodeUriFilter(uri) {
            const unicodeUriFilter = HeyFrame.Filter.getByName('unicodeUri');
            return unicodeUriFilter(uri);
        },

        localSortDomains(domains) {
            domains.sort((a, b) => {
                const valA = this.getSortValue(a, this.sortBy).toString();
                const valB = this.getSortValue(b, this.sortBy).toString();

                const compareVal = valA.localeCompare(valB, undefined, {
                    numeric: true,
                    sensitivity: 'base',
                });

                if (this.sortDirection === 'ASC') {
                    return compareVal;
                }

                return compareVal * -1;
            });

            return domains;
        },

        getSortValue(val, column) {
            // Removes 'Id' from fields like 'languageId', so we're accessing 'language' instead
            column = column.replace('Id', '');

            if (val.hasOwnProperty(column) && typeof val[column] === 'object' && val[column].hasOwnProperty('name')) {
                return val[column].name;
            }

            return val[column];
        },

        onInput() {
            this.error = null;
        },

        async verifyUrl(domain) {
            return !(this.domainExistsLocal(domain) || (await this.domainExistsInDatabase(domain.url)));
        },

        domainExistsLocal(currentDomain) {
            return (
                this.channel.domains.filter(
                    (domain) => domain.id !== currentDomain.id && domain.url === currentDomain.url,
                ).length > 0
            );
        },

        isOriginalUrl(url) {
            return url === this.currentDomainBackup.url;
        },

        async domainExistsInDatabase(url) {
            const globalDomainRepository = this.repositoryFactory.create(this.channel.domains.entity);
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('url', url));

            const items = await globalDomainRepository.search(criteria);

            if (items.total === 0) {
                return false;
            }

            // Edge case: Delete domain, which is in database already, and then try to re-add it.
            // In that case a database entry is still available, yet locally it's not available anymore.
            // We don't want to prevent re-adding this domain in that case.
            return items.first().channelId !== this.channel.id;
        },

        setCurrentDomainBackup(domain) {
            this.currentDomainBackup = {
                url: domain.url,
                language: domain.language,
                languageId: domain.languageId,
                currency: domain.currency,
                currencyId: domain.currencyId,
                snippetSet: domain.snippetSet,
                snippetSetId: domain.snippetSetId,
            };
        },

        resetCurrentDomainToBackup() {
            this.currentDomain.url = this.currentDomainBackup.url;
            this.currentDomain.language = this.currentDomainBackup.language;
            this.currentDomain.languageId = this.currentDomainBackup.languageId;
            this.currentDomain.currency = this.currentDomainBackup.currency;
            this.currentDomain.currencyId = this.currentDomainBackup.currencyId;
            this.currentDomain.snippetSet = this.currentDomainBackup.snippetSet;
            this.currentDomain.snippetSetId = this.currentDomainBackup.snippetSetId;
        },

        setInitialCurrency(domain) {
            const currency = this.channel.currencies.first();
            domain.currency = currency;
            domain.currencyId = currency.id;
            this.currentDomain = domain;
        },

        setInitialLanguage(domain) {
            const language = this.channel.languages.first();
            domain.language = language;
            domain.languageId = language.id;
            this.currentDomain = domain;
        },

        onClickOpenCreateDomainModal() {
            const domain = this.domainRepository.create(Context.api);

            this.setCurrentDomainBackup(domain);

            if (this.channel.currencies.length === 1) {
                this.setInitialCurrency(domain);
            }

            if (this.channel.languages.length === 1) {
                this.setInitialLanguage(domain);
            }

            domain.hreflangUseOnlyLocale = false;

            this.currentDomain = domain;
            this.isEditingDomain = false;
        },

        async onClickAddNewDomain() {
            if (this.isOriginalUrl(this.currentDomain.url)) {
                this.currentDomain = null;
                return;
            }

            if (!(await this.verifyUrl(this.currentDomain))) {
                this.error = new HeyFrameError({
                    code: 'DUPLICATED_URL',
                });

                return;
            }

            if (!this.isEditingDomain) {
                this.channel.domains.add(this.currentDomain);
            }

            this.currentDomain = null;
            this.isEditingDomain = false;
        },

        onClickEditDomain(domain) {
            this.currentDomain = domain;
            this.setCurrentDomainBackup(this.currentDomain);
            this.isEditingDomain = true;
        },

        onCloseCreateDomainModal() {
            this.resetCurrentDomainToBackup();
            this.currentDomain = null;
            this.isEditingDomain = false;
        },

        onClickDeleteDomain(domain) {
            if (domain.isNew()) {
                this.onConfirmDeleteDomain(domain);
            } else {
                this.deleteDomain = domain;
            }
        },

        onConfirmDeleteDomain(domain) {
            if (domain.productExports.length > 0) {
                this.createNotificationError({
                    message: this.$tc(
                        'sw-channel.detail.messageDeleteDomainError',
                        {
                            url: this.unicodeUriFilter(domain.url),
                        },
                        0,
                    ),
                });

                this.deleteDomain = null;

                return;
            }

            this.deleteDomain = null;

            this.$nextTick(() => {
                this.channel.domains.remove(domain.id);
            });
        },

        onCloseDeleteDomainModal() {
            this.deleteDomain = null;
        },

        onLanguageSelect(id) {
            this.onOptionSelect('language', this.channel.languages.get(id));
        },

        onCurrencySelect(id) {
            this.onOptionSelect('currency', this.channel.currencies.get(id));
        },

        onOptionSelect(name, entity) {
            this.currentDomain[name] = entity;
        },

        getDomainColumns() {
            return [
                {
                    property: 'url',
                    dataIndex: 'url',
                    label: this.$t('sw-channel.detail.columnDomainUrl'),
                    allowResize: false,
                    primary: true,
                    inlineEdit: true,
                },
                {
                    property: 'languageId',
                    dataIndex: 'languageId',
                    label: this.$t('sw-channel.detail.columnDomainLanguage'),
                    allowResize: false,
                    inlineEdit: false,
                },
                {
                    property: 'snippetSetId',
                    dataIndex: 'snippetSetId',
                    label: this.$t('sw-channel.detail.columnDomainSnippetSet'),
                    allowResize: false,
                    inlineEdit: false,
                },
                {
                    property: 'currencyId',
                    dataIndex: 'currencyId',
                    label: this.$t('sw-channel.detail.columnDomainCurrency'),
                    allowResize: false,
                    inlineEdit: false,
                },
            ];
        },
    },
};
