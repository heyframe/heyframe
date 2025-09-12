/**
 * @sw-package discovery
 */
import template from './sw-channel-config.html.twig';

const { Criteria } = HeyFrame.Data;

/**
 * @private
 */
export default {
    template,

    inject: [
        'systemConfigApiService',
        'repositoryFactory',
        'feature',
    ],

    emits: [
        'update:value',
        'channelChanged',
    ],

    props: {
        domain: {
            type: String,
            required: false,
            default: '',
        },
        // eslint-disable-next-line vue/require-default-prop
        value: {
            type: Object,
            required: false,
        },
        criteria: {
            type: Object,
            required: false,
            default: () => {
                return new Criteria(1, 25);
            },
        },
    },

    data() {
        return {
            allConfigs: {},
            selectedChannelId: null,
            channel: [],
        };
    },

    computed: {
        actualConfigData: {
            get() {
                return this.allConfigs[this.selectedChannelId];
            },
            set(config) {
                this.allConfigs = {
                    ...this.allConfigs,
                    [this.selectedChannelId]: config,
                };
            },
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },
    },

    watch: {
        actualConfigData: {
            handler(configData) {
                if (!configData) {
                    return;
                }

                this.$emit('update:value', configData);
            },
            deep: true,
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.channel.length) {
                this.channelRepository.search(this.criteria, HeyFrame.Context.api).then((res) => {
                    res.add({
                        id: null,
                        translated: {
                            name: this.$tc('sw-channel-switch.labelDefaultOption'),
                        },
                    });

                    this.channel = res;
                });
            }

            if (this.allConfigs[this.selectedChannelId]) {
                return;
            }

            if (this.domain && !this.actualConfigData) {
                this.readAll().then((values) => {
                    this.actualConfigData = values;
                });
            }
        },

        readAll() {
            return this.systemConfigApiService.getValues(this.domain, this.selectedChannelId);
        },

        onInput(channelId) {
            this.selectedChannelId = channelId;
            this.$emit('channelChanged');
            this.createdComponent();
        },

        save() {
            if (this.domain && this.domain.length !== 0) {
                return this.systemConfigApiService.batchSave(this.allConfigs);
            }

            return Promise.resolve(this.allConfigs);
        },
    },
};
