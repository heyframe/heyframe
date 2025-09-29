import template from './sw-navigation-link-settings.html.twig';
import './sw-navigation-link-settings.scss';

const { Criteria } = HeyFrame.Data;

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'repositoryFactory',
    ],

    props: {
        navigation: {
            type: Object,
            required: true,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            categoriesCollection: [],
            linkHasProtocol: false,
        };
    },

    computed: {
        linkTypeValues() {
            return [
                {
                    value: 'external',
                    label: this.$t('sw-navigation.base.link.type.external'),
                },
                {
                    value: 'internal',
                    label: this.$t('sw-navigation.base.link.type.internal'),
                },
            ];
        },

        entityValues() {
            return [
                {
                    value: 'navigation',
                    label: this.$t('global.entities.navigation'),
                },
                {
                    value: 'product',
                    label: this.$t('global.entities.product'),
                },
                {
                    value: 'landing_page',
                    label: this.$t('global.entities.landing_page'),
                },
            ];
        },

        mainType: {
            get() {
                if (this.isExternal || !this.navigation.linkType) {
                    return this.navigation.linkType;
                }

                return 'internal';
            },

            set(value) {
                if (value === 'external') {
                    this.navigation.internalLink = null;
                } else {
                    this.navigation.externalLink = null;
                }

                this.navigation.linkType = value;
            },
        },

        isExternal() {
            return this.navigation.linkType === 'external';
        },

        isInternal() {
            return !!this.navigation.linkType && this.navigation.linkType !== 'external';
        },

        productCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');

            return criteria;
        },

        navigationCriteria() {
            return new Criteria(1, null);
        },

        internalLinkCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('id', this.navigation.internalLink));

            return criteria;
        },

        navigationRepository() {
            return this.repositoryFactory.create('navigation');
        },

        navigationLinkPlaceholder() {
            return this.navigation.internalLink ? '' : this.$t('sw-navigation.base.link.navigationPlaceholder');
        },

        allowedCategoryTypes() {
            return ['page'];
        },

        navigationLinkHelpText() {
            return this.$t('sw-navigation.base.link.navigationHelpText', {
                types: this.allowedCategoryTypes
                    .map((type) => {
                        return this.$t(`sw-navigation.base.general.types.${type}`);
                    })
                    .join(', '),
            });
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.navigation.linkType && this.navigation.externalLink) {
                this.navigation.linkType = 'external';
            }

            this.linkHasProtocol = this.navigation.externalLink?.startsWith('http') || this.navigation.externalLink === null;
            this.createCategoryCollection();
        },

        changeEntity() {
            if (!this.navigation.linkType) {
                this.navigation.linkType = 'internal';
            }

            this.navigation.internalLink = null;
        },

        createCategoryCollection() {
            this.navigationRepository.search(this.internalLinkCriteria, HeyFrame.Context.api).then((result) => {
                this.categoriesCollection = result;
            });
        },

        onSelectionAdd(item) {
            this.navigation.internalLink = item.id;
        },

        onSelectionRemove() {
            this.navigation.internalLink = null;
        },
    },
};
