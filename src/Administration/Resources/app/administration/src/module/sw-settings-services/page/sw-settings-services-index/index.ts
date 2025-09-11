import { mapState } from 'pinia';
import useSession from 'src/app/composables/use-session';
import { useHeyFrameServicesStore } from '../../store/heyframe-services.store';
import template from './sw-settings-services-index.html.twig';
import './sw-settings-services-index.scss';
import type { ServiceDescription } from '../../service/heyframe-services.service';
import extractError from '../../composables/extract-error';

import SwSettingsServicesHero from '../../component/sw-settings-services-hero';
import SwSettingsServicesGrantPermissionsCard from '../../component/sw-settings-services-grant-permissions-card';
import SwSettingsServicesRevokePermissionsModal from '../../component/sw-settings-services-revoke-permissions-modal';
import SwSettingsServicesDeactivateModal from '../../component/sw-settings-services-deactivate-modal';
import SwSettingsServicesServiceCard from '../../component/sw-settings-services-service-card';

type SwSettingsPageData = {
    grantPermissionsCardBackground: string;
    services: ServiceDescription[];
    suspended: boolean;
    loadingError: string;
};

/**
 * @sw-package framework
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    name: 'sw-settings-services-index',

    template,

    inject: ['acl'],

    components: {
        SwSettingsServicesHero,
        SwSettingsServicesGrantPermissionsCard,
        SwSettingsServicesRevokePermissionsModal,
        SwSettingsServicesDeactivateModal,
        SwSettingsServicesServiceCard,
    },

    data(): SwSettingsPageData {
        const assetFilter = HeyFrame.Filter.getByName('asset');

        return {
            grantPermissionsCardBackground: assetFilter(
                '/administration/administration/static/img/services/grant-permissions-background.svg',
            ),
            services: [],
            suspended: true,
            loadingError: '',
        };
    },

    computed: {
        ...mapState(useHeyFrameServicesStore, [
            'config',
            'currentRevision',
            'consentGiven',
        ]),
    },

    created() {
        const heyframeServicesService = HeyFrame.Service('heyframeServicesService');
        const serviceRegistryClient = HeyFrame.Service('serviceRegistryClient');
        const heyframeServicesStore = useHeyFrameServicesStore();
        const sessionStore = useSession();

        Promise.all([
            this.reloadServices(),
            heyframeServicesService.getServicesContext().then((servicesConsent) => {
                heyframeServicesStore.config = servicesConsent;
            }),
            serviceRegistryClient
                .getCurrentRevision(sessionStore.currentLocale.value ?? 'en-GB')
                .then((serviceRevisions) => {
                    heyframeServicesStore.revisions = serviceRevisions;
                }),
        ])
            .then(() => {
                this.suspended = false;
            })
            .catch((exception) => {
                const errorMessage = extractError(exception);

                HeyFrame.Store.get('notification').createNotification({
                    variant: 'critical',
                    title: this.$t('global.default.error'),
                    message: errorMessage,
                });
            });
    },

    methods: {
        async activateServices() {
            try {
                const heyframeServicesService = HeyFrame.Service('heyframeServicesService');
                const heyframeServicesStore = useHeyFrameServicesStore();

                heyframeServicesStore.config = await heyframeServicesService.enableAllServices();

                HeyFrame.Store.get('notification').createNotification({
                    title: this.$t('sw-settings-services.index.services-enabled'),
                    variant: 'positive',
                    message: this.$t('sw-settings-services.index.services-scheduled'),
                });
            } catch (exceptionResponse) {
                HeyFrame.Store.get('notification').createNotification({
                    title: this.$t('global.default.error'),
                    variant: 'critical',
                    message: extractError(exceptionResponse),
                    autoClose: false,
                });
            }
        },

        async reloadServices() {
            try {
                const heyframeServicesService = HeyFrame.Service('heyframeServicesService');

                this.services = await heyframeServicesService.getInstalledServices();
            } catch (exception) {
                this.loadingError = extractError(exception);

                HeyFrame.Store.get('notification').createNotification({
                    variant: 'critical',
                    title: this.$t('global.default.error'),
                    message: this.$t('sw-settings-services.exception.service-list'),
                    autoClose: false,
                });

                this.services = [];
            }
        },
    },
});
