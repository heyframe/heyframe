import { defineComponent } from 'vue';

/**
 * @sw-package checkout
 * @private
 */
export default HeyFrame.Mixin.register(
    'sw-extension-error',
    defineComponent({
        mixins: [HeyFrame.Mixin.getByName('notification')],

        methods: {
            showExtensionErrors(errorResponse) {
                HeyFrame.Service('extensionErrorService')
                    .handleErrorResponse(errorResponse, this)
                    .forEach((notification) => {
                        this.createNotificationError(notification);
                    });
            },
        },
    }),
);
