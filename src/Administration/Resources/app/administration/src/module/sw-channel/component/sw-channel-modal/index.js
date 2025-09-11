/**
 * @sw-package discovery
 */

import template from './sw-channel-modal.html.twig';
import './sw-channel-modal.scss';

const { Defaults } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['repositoryFactory'],

    emits: ['modal-close'],

    data() {
        return {
            detailType: null,
            productStreamsExist: false,
            productStreamsLoading: false,
        };
    },

    computed: {
        modalTitle() {
            if (this.detailType) {
                return this.$tc(
                    'sw-channel.modal.titleDetailPrefix',
                    {
                        name: this.detailType.name,
                    },
                    0,
                );
            }

            return this.$tc('sw-channel.modal.title');
        },

        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },

        addChannelAction() {
            return {
                loading: (channelTypeId) => {
                    return this.isProductComparisonChannelType(channelTypeId) && this.productStreamsLoading;
                },

                disabled: (channelTypeId) => {
                    return this.isProductComparisonChannelType(channelTypeId) && !this.productStreamsExist;
                },
            };
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.productStreamsLoading = true;
            this.productStreamRepository.search(new Criteria(1, 1)).then((result) => {
                if (result.total > 0) {
                    this.productStreamsExist = true;
                }
                this.productStreamsLoading = false;
            });
        },

        onGridOpenDetails(detailType) {
            this.detailType = detailType;
        },

        onCloseModal() {
            this.$emit('modal-close');
        },

        onAddChannel(id) {
            this.onCloseModal();

            if (id) {
                this.$router.push({
                    name: 'sw.channel.create',
                    params: { typeId: id },
                });
            }
        },

        openRoute(route) {
            this.onCloseModal();

            this.$router.push(route);
        },

        isProductComparisonChannelType(channelTypeId) {
            return channelTypeId === Defaults.productComparisonTypeId;
        },
    },
};
