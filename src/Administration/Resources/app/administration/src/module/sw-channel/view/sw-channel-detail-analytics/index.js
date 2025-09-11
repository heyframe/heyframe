/**
 * @sw-package discovery
 */

import template from './sw-channel-detail-analytics.html.twig';

import './sw-channel-detail-analytics.scss';

const { Context } = HeyFrame;

/**
 * @private
 */
export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    props: {
        isLoading: {
            type: Boolean,
            default: false,
        },

        // eslint-disable-next-line vue/require-prop-types
        channel: {
            required: true,
        },
    },

    watch: {
        channel() {
            this.createAnalyticsData();
        },
    },

    created() {
        this.createAnalyticsData();
    },

    methods: {
        createAnalyticsData() {
            if (this.channel && !this.channel.analytics) {
                const repository = this.repositoryFactory.create('channel_analytics');
                this.channel.analytics = repository.create(Context.api);
            }
        },
    },
};
