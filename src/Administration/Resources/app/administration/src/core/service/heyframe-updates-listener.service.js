const { Application } = HeyFrame;

/**
 * @sw-package framework
 *
 * @module core/service/heyframe-updates-listener
 */

/**
 *
 * @memberOf module:core/service/heyframe-updates-listener
 * @method addHeyFrameUpdatesListener
 * @param loginService
 * @param serviceContainer
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default function addHeyFrameUpdatesListener(loginService, serviceContainer) {
    /** @var {String} localStorage token */
    let applicationRoot = null;

    loginService.addOnLoginListener(() => {
        if (!HeyFrame.Service('acl').can('system.core_update')) {
            return;
        }

        serviceContainer.updateService
            .checkForUpdates()
            .then((response) => {
                if (response.version) {
                    createUpdatesAvailableNotification(response);
                }
            })
            .catch();
    });

    function createUpdatesAvailableNotification(response) {
        const cancelLabel = getApplicationRootReference().$tc('global.default.cancel');
        const updateLabel = getApplicationRootReference().$tc(
            'global.notification-center.heyframe-updates-listener.updateNow',
        );

        const notification = {
            title: getApplicationRootReference().$t(
                'global.notification-center.heyframe-updates-listener.updatesAvailableTitle',
                {
                    version: response.version,
                },
            ),
            message: getApplicationRootReference().$t(
                'global.notification-center.heyframe-updates-listener.updatesAvailableMessage',
                {
                    version: response.version,
                },
            ),
            variant: 'info',
            growl: true,
            system: true,
            actions: [
                {
                    label: updateLabel,
                    route: { name: 'sw.settings.heyframe.updates.wizard' },
                },
                {
                    label: cancelLabel,
                },
            ],
            autoClose: false,
        };

        HeyFrame.Store.get('notification').createNotification(notification);
    }

    function getApplicationRootReference() {
        if (!applicationRoot) {
            applicationRoot = Application.getApplicationRoot();
        }

        return applicationRoot;
    }
}
