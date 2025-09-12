/**
 * @sw-package inventory
 */

import template from './sw-seo-main-category.html.twig';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['repositoryFactory'],

    emits: [
        'main-category-add',
        'main-category-remove',
    ],

    props: {
        currentChannelId: {
            type: String,
            required: false,
            default: null,
        },
        categories: {
            type: Array,
            required: true,
        },
        mainCategories: {
            type: Array,
            required: true,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        allowEdit: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },
        overwriteLabel: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            mainCategoryForChannel: null,
        };
    },

    computed: {
        mainCategoryRepository() {
            return this.repositoryFactory.create('main_category');
        },

        isHeadlessChannel() {
            if (HeyFrame.Store.get('swSeoUrl').channelCollection === null) {
                return true;
            }

            const channel = HeyFrame.Store.get('swSeoUrl').channelCollection.find((entry) => {
                return entry.id === this.currentChannelId;
            });

            // from Defaults.php
            return this.currentChannelId !== null && channel.typeId === 'f183ee5650cf4bdb8a774337575067a6';
        },

        selectedCategory() {
            return this.mainCategoryForChannel !== null ? this.mainCategoryForChannel.categoryId : null;
        },
    },

    watch: {
        currentChannelId() {
            this.refreshMainCategoryForChannel();
        },
        mainCategories() {
            this.refreshMainCategoryForChannel();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.refreshMainCategoryForChannel();
        },
        onMainCategorySelected(categoryId) {
            if (!categoryId) {
                if (this.mainCategoryForChannel) {
                    this.$emit('main-category-remove', this.mainCategoryForChannel);
                    this.mainCategoryForChannel = null;
                }
                return;
            }

            const selectedCategory = this.categories.find((value) => {
                return value.id === categoryId;
            });

            if (this.mainCategoryForChannel !== null) {
                this.mainCategoryForChannel.category = selectedCategory;
                this.mainCategoryForChannel.categoryId = selectedCategory.id;
                return;
            }

            const mainCategory = this.mainCategoryRepository.create();
            mainCategory.channelId = this.currentChannelId;
            mainCategory.category = selectedCategory;
            mainCategory.categoryId = selectedCategory.id;
            this.$emit('main-category-add', mainCategory);
            this.refreshMainCategoryForChannel();
        },
        refreshMainCategoryForChannel() {
            const mainCategory = this.mainCategories.find((category) => {
                return category.channelId === this.currentChannelId;
            });

            if (mainCategory === undefined) {
                this.mainCategoryForChannel = null;
                return;
            }

            this.mainCategoryForChannel = mainCategory;
        },
    },
};
