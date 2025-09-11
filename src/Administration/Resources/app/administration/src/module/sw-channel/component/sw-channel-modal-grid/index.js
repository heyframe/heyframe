/**
 * @sw-package discovery
 */

import template from './sw-channel-modal-grid.html.twig';
import './sw-channel-modal-grid.scss';

const { Defaults } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['repositoryFactory'],

    emits: [
        'grid-channel-add',
        'grid-detail-open',
    ],

    props: {
        productStreamsExist: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },

        productStreamsLoading: {
            type: Boolean,
            required: false,
            default: false,
        },

        addChannelAction: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            channelTypes: [],
            isLoading: false,
            total: 0,
        };
    },

    computed: {
        channelTypeRepository() {
            return this.repositoryFactory.create('channel_type');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.isLoading = true;
            const context = {
                ...HeyFrame.Context.api,
                languageId: HeyFrame.Store.get('session').languageId,
            };
            this.channelTypeRepository.search(new Criteria(1, 500), context).then((response) => {
                this.total = response.total;
                this.channelTypes = response;
                this.isLoading = false;
            });
        },

        onAddChannel(id) {
            this.$emit('grid-channel-add', id);
        },

        onOpenDetail(id) {
            const detailType = this.channelTypes.find((channelType) => channelType.id === id);
            this.$emit('grid-detail-open', detailType);
        },

        isProductComparisonChannelType(channelTypeId) {
            return channelTypeId === Defaults.productComparisonTypeId;
        },
    },
};
