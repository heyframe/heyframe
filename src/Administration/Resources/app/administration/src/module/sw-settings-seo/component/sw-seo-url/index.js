/**
 * @sw-package inventory
 */

import './store';
import template from './sw-seo-url.html.twig';
import './sw-seo-url.scss';

const Criteria = HeyFrame.Data.Criteria;
const EntityCollection = HeyFrame.Data.EntityCollection;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['repositoryFactory'],

    emits: ['on-change-channel'],

    mixins: [],

    props: {
        channelId: {
            type: String,
            required: false,
            default: null,
        },

        urls: {
            type: Array,
            required: false,
            default() {
                return [];
            },
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },

        hasDefaultTemplate: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },

        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },

        resultLimit: {
            type: Number,
            required: false,
            default: 25,
        },
    },

    data() {
        return {
            currentChannelId: this.channelId,
            showEmptySeoUrlError: false,
        };
    },

    computed: {
        seoUrlCollection() {
            return HeyFrame.Store.get('swSeoUrl').seoUrlCollection;
        },

        currentSeoUrl() {
            if (!HeyFrame.Store.get('swSeoUrl')) {
                return {};
            }

            return HeyFrame.Store.get('swSeoUrl').currentSeoUrl;
        },

        defaultSeoUrl() {
            return HeyFrame.Store.get('swSeoUrl').defaultSeoUrl;
        },

        seoUrlRepository() {
            return this.repositoryFactory.create('seo_url');
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        isHeadlessChannel() {
            if (!HeyFrame.Store.get('swSeoUrl')) {
                return true;
            }

            if (HeyFrame.Store.get('swSeoUrl').channelCollection === null) {
                return true;
            }

            const channel = HeyFrame.Store.get('swSeoUrl').channelCollection.find((entry) => {
                return entry.id === this.currentChannelId;
            });

            // from Defaults.php
            return this.currentChannelId !== null && channel?.typeId === 'f183ee5650cf4bdb8a774337575067a6';
        },

        seoUrlHelptext() {
            return this.isHeadlessChannel ? this.$tc('sw-seo-url.textSeoUrlsDisallowedForHeadless') : null;
        },

        hasAdditionalSeoSlot() {
            return this.$slots.hasOwnProperty('seo-additional');
        },

        allowInput() {
            return this.hasDefaultTemplate || this.currentChannelId !== null;
        },
    },

    watch: {
        urls() {
            this.initSeoUrlCollection();
            this.refreshCurrentSeoUrl();
        },
    },

    created() {
        HeyFrame.Utils.EventBus.on('sw-product-detail-save-finish', this.clearDefaultSeoUrls);

        this.createdComponent();
    },

    beforeUnmount() {
        HeyFrame.Utils.EventBus.off('sw-product-detail-save-finish', this.clearDefaultSeoUrls);
    },

    methods: {
        createdComponent() {
            this.initChannelCollection();
            this.initSeoUrlCollection();
            if (!this.showEmptySeoUrlError) {
                this.refreshCurrentSeoUrl();
            }
        },

        initChannelCollection() {
            const channelCriteria = new Criteria(1, this.resultLimit);
            channelCriteria.addAssociation('type');

            this.channelRepository.search(channelCriteria).then((channelCollection) => {
                HeyFrame.Store.get('swSeoUrl').channelCollection = channelCollection;
            });
        },

        initSeoUrlCollection() {
            this.showEmptySeoUrlError = false;
            const seoUrlCollection = new EntityCollection(
                this.seoUrlRepository.route,
                this.seoUrlRepository.schema.entity,
                HeyFrame.Context.api,
                new Criteria(1, this.resultLimit),
            );

            const defaultSeoUrlData = this.urls.find((entityData) => {
                return entityData.channelId === null;
            });

            if (defaultSeoUrlData === undefined && (this.hasDefaultTemplate || this.urls.length <= 0)) {
                this.showEmptySeoUrlError = true;
            }

            const defaultSeoUrlEntity = this.seoUrlRepository.create();
            Object.assign(defaultSeoUrlEntity, defaultSeoUrlData);
            seoUrlCollection.add(defaultSeoUrlEntity);
            HeyFrame.Store.get('swSeoUrl').defaultSeoUrl = defaultSeoUrlEntity;

            this.urls.forEach((entityData) => {
                const entity = this.seoUrlRepository.create();
                Object.assign(entity, entityData);

                seoUrlCollection.add(entity);
            });

            if (!HeyFrame.Store.get('swSeoUrl').defaultSeoUrl) {
                this.showEmptySeoUrlError = true;
            }

            HeyFrame.Store.get('swSeoUrl').seoUrlCollection = seoUrlCollection;
            HeyFrame.Store.get('swSeoUrl').originalSeoUrls = this.urls;
            this.clearDefaultSeoUrls();
        },

        clearDefaultSeoUrls() {
            this.seoUrlCollection.forEach((entity) => {
                if (entity.id === this.defaultSeoUrl.id) {
                    return;
                }

                if (entity.seoPathInfo === this.defaultSeoUrl.seoPathInfo) {
                    entity.seoPathInfo = null;
                }
            });
        },

        refreshCurrentSeoUrl() {
            const actualLanguageId = HeyFrame.Context.api.languageId;

            const currentSeoUrl = this.seoUrlCollection.find((entity) => {
                return entity.languageId === actualLanguageId && entity.channelId === this.currentChannelId;
            });

            if (!currentSeoUrl) {
                const entity = this.seoUrlRepository.create();
                // Fetch any seo url as template, since we need to know foreignKey, pathInfo and the routeName
                const seoUrl =
                    this.seoUrlCollection.find((item) => {
                        return item.pathInfo && item.routeName && item.foreignKey;
                    }) || {};

                entity.foreignKey = this.defaultSeoUrl?.foreignKey ?? seoUrl.foreignKey;
                entity.isCanonical = true;
                entity.languageId = actualLanguageId;
                entity.channelId = this.currentChannelId;
                entity.routeName = this.defaultSeoUrl?.routeName ?? seoUrl.routeName;
                entity.pathInfo = this.defaultSeoUrl?.pathInfo ?? seoUrl.pathInfo;
                entity.isModified = true;

                this.seoUrlCollection.add(entity);

                HeyFrame.Store.get('swSeoUrl').currentSeoUrl = entity;

                return;
            }

            HeyFrame.Store.get('swSeoUrl').currentSeoUrl = currentSeoUrl;
        },
        onChannelChanged(channelId) {
            this.currentChannelId = channelId;
            this.$emit('on-change-channel', channelId);
            this.refreshCurrentSeoUrl();
        },
    },
};
