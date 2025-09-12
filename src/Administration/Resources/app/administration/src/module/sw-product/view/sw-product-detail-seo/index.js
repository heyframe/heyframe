/*
 * @sw-package inventory
 */

import template from './sw-product-detail-seo.html.twig';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'feature',
        'acl',
        'repositoryFactory',
    ],

    data() {
        return {
            currentChannelId: undefined,
        };
    },

    computed: {
        product() {
            return HeyFrame.Store.get('swProductDetail').product;
        },

        parentProduct() {
            return HeyFrame.Store.get('swProductDetail').parentProduct;
        },

        isLoading() {
            return HeyFrame.Store.get('swProductDetail').isLoading;
        },

        categories() {
            if (this.product.categories.length > 0) {
                return this.product.categories;
            }

            return this.parentProduct.categories ?? [];
        },

        mainCategoryRepository() {
            return this.repositoryFactory.create('main_category');
        },

        parentMainCategory() {
            if (this.parentProduct.mainCategories && this.currentChannelId) {
                return this.parentProduct.mainCategories.find((category) => {
                    return category.channelId === this.currentChannelId;
                });
            }

            return null;
        },

        productMainCategory: {
            get() {
                return this.product.mainCategories.find((category) => {
                    return category.channelId === this.currentChannelId;
                });
            },
            set(newMainCategory) {
                if (!newMainCategory) {
                    this.product.mainCategories = this.product.mainCategories.filter((category) => {
                        return category.channelId !== this.currentChannelId;
                    });
                    return;
                }

                const newEntity = this.mainCategoryRepository.create();
                newEntity.productId = this.product.id;
                newEntity.categoryId = newMainCategory.categoryId;
                newEntity.channelId = newMainCategory.channelId;

                if (newMainCategory.category) {
                    newEntity.category = newMainCategory.category;
                }

                this.onRemoveMainCategory(newMainCategory);
                this.onAddMainCategory(newEntity);
            },
        },
    },

    methods: {
        onAddMainCategory(mainCategory) {
            if (this.product.mainCategories) {
                this.product.mainCategories.push(mainCategory);
            }
        },

        onRemoveMainCategory(mainCategory) {
            if (!this.product.mainCategories) {
                return;
            }

            this.product.mainCategories = this.product.mainCategories.filter((item) => {
                return item.channelId !== mainCategory.channelId;
            });
        },

        onChangeChannel(currentChannelId) {
            this.currentChannelId = currentChannelId;
        },
    },
};
