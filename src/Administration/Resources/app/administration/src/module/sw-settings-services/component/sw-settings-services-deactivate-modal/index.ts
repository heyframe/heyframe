import { MtModalRoot, MtModal, MtModalTrigger, MtModalAction, MtModalClose } from '@heyframe-ag/meteor-component-library';
import template from './sw-settings-services-deactivate-modal.html.twig';
import './sw-settings-services-deactivate-modal.scss';
import extractError from '../../composables/extract-error';

/**
 * @sw-package framework
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    name: 'sw-settings-services-deactivate-modal',
    template,

    components: {
        MtModalRoot,
        MtModal,
        MtModalAction,
        MtModalTrigger,
        MtModalClose,
    },

    props: {
        feedbackLink: {
            type: String,
        },
    },

    data() {
        return {
            isLoading: false,
        };
    },

    methods: {
        async disableServices(done: () => void) {
            this.isLoading = true;

            try {
                const heyframeServicesService = HeyFrame.Service('heyframeServicesService');

                await heyframeServicesService.disableAllServices();

                window.location.reload();
            } catch (exceptionResponse) {
                HeyFrame.Store.get('notification').createNotification({
                    title: this.$t('global.default.error'),
                    variant: 'critical',
                    message: extractError(exceptionResponse),
                    autoClose: false,
                });
            } finally {
                this.isLoading = false;
            }

            done();
        },
    },
});
