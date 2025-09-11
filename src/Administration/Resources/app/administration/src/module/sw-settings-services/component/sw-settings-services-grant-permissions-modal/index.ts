/**
 * @sw-package framework
 */
import { MtModal, MtModalRoot, MtModalAction, MtModalClose } from '@heyframe-ag/meteor-component-library';
import useSession from 'src/app/composables/use-session';
import template from './sw-settings-services-grant-permissions-modal.html.twig';
import './sw-settings-services-grant-permissions-modal.scss';
import { useHeyFrameServicesStore } from '../../store/heyframe-services.store';
import extractErrorMessage from '../../composables/extract-error';
import { grantPermissions } from '../../composables/permissions';

/**
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    name: 'sw-settings-services-grant-permissions-modal',
    template,

    components: {
        MtModal,
        MtModalRoot,
        MtModalAction,
        MtModalClose,
    },

    data() {
        const assetFilter = HeyFrame.Filter.getByName('asset');

        return {
            grantPermissionsBackground: assetFilter(
                '/administration/administration/static/img/services/grant-permissions-background.svg',
            ),
            isLoading: false,
        };
    },

    computed: {
        feedbackLink() {
            return useHeyFrameServicesStore().currentRevision?.links['docs-url'] ?? '';
        },

        showGrantPermissionsModal: {
            get() {
                return useHeyFrameServicesStore().showGrantPermissionsModal;
            },
            set(value: boolean) {
                useHeyFrameServicesStore().showGrantPermissionsModal = value;
            },
        },
    },

    methods: {
        prepareRevisions(isOpen: boolean) {
            this.showGrantPermissionsModal = isOpen;

            if (this.showGrantPermissionsModal && !this.feedbackLink) {
                HeyFrame.Service('serviceRegistryClient')
                    .getCurrentRevision(useSession().currentLocale.value as string)
                    .then((revisions) => {
                        useHeyFrameServicesStore().revisions = revisions;
                    })
                    .catch(() => {});
            }
        },

        async grantPermissions(done: () => void) {
            try {
                this.isLoading = true;

                await grantPermissions();
            } catch (exception) {
                HeyFrame.Store.get('notification').createNotification({
                    variant: 'critical',
                    title: this.$t('global.default.error'),
                    message: extractErrorMessage(exception),
                });
            } finally {
                this.isLoading = false;
                done();
            }
        },
    },
});
