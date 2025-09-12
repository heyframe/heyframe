/**
 * @sw-package discovery
 */
import template from './sw-channel-switch.html.twig';

const { debug } = HeyFrame.Utils;

/**
 * @private
 * @description
 * Renders a sales channel switcher.
 * @status ready
 * @example-type code-only
 * @component-example
 * <sw-channel-switch></sw-channel-switch>
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    emits: ['change-channel-id'],

    props: {
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
        // eslint-disable-next-line vue/require-default-prop
        abortChangeFunction: {
            type: Function,
            required: false,
        },
        // eslint-disable-next-line vue/require-default-prop
        saveChangesFunction: {
            type: Function,
            required: false,
        },
        label: {
            type: String,
            required: false,
            default: '',
        },
    },

    data() {
        return {
            channelId: '',
            lastChannelId: '',
            newChannelId: '',
            showUnsavedChangesModal: false,
        };
    },

    methods: {
        onChange(id) {
            this.channelId = id;
            this.newChannelId = id;

            this.checkAbort();
        },
        checkAbort() {
            // Check if abort function exists und reset the select field if the change should be aborted
            if (typeof this.abortChangeFunction === 'function') {
                if (
                    this.abortChangeFunction({
                        oldChannelId: this.lastChannelId,
                        newChannelId: this.channelId,
                    })
                ) {
                    this.showUnsavedChangesModal = true;
                    this.channelId = this.lastChannelId;
                    this.$refs.channelSelect.loadSelected();
                    return;
                }
            }

            this.emitChange();
        },
        emitChange() {
            this.lastChannelId = this.channelId;

            this.$emit('change-channel-id', this.channelId);
        },
        onCloseChangesModal() {
            this.showUnsavedChangesModal = false;
            this.newChannelId = '';
        },
        onClickSaveChanges() {
            let save = {};
            // Check if save function exists and wait for it before changing the channel
            if (typeof this.saveChangesFunction === 'function') {
                save = this.saveChangesFunction();
            } else {
                debug.warn('sw-channel-switch', 'You need to implement an own save function to save the changes!');
            }
            return Promise.resolve(save).then(() => {
                this.changeToNewChannel();
                this.onCloseChangesModal();
            });
        },
        onClickRevertUnsavedChanges() {
            this.changeToNewChannel();
            this.onCloseChangesModal();
        },
        changeToNewChannel(channelId) {
            if (channelId) {
                this.newChannelId = channelId;
            }
            this.channelId = this.newChannelId;
            this.newChannelId = '';
            this.$refs.channelSelect.loadSelected();
            this.emitChange();
        },
    },
};
