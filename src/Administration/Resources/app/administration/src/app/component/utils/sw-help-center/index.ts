import template from './sw-help-center.html.twig';
import './sw-help-center.scss';

/**
 * @description Displays an icon and a link to the help sidebar
 *
 * @sw-package framework
 *
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    computed: {
        showHelpSidebar(): boolean {
            return HeyFrame.Store.get('adminHelpCenter').showHelpSidebar;
        },

        showShortcutModal(): boolean {
            return HeyFrame.Store.get('adminHelpCenter').showShortcutModal;
        },
    },

    watch: {
        showShortcutModal(value) {
            const shortcutModal = this.$refs.shortcutModal as {
                onOpenShortcutOverviewModal: () => void;
            };

            if (!shortcutModal) {
                return;
            }

            if (value === false) {
                this.setFocusToSidebar();

                return;
            }

            shortcutModal.onOpenShortcutOverviewModal();
        },
    },

    methods: {
        openHelpSidebar(): void {
            HeyFrame.Store.get('adminHelpCenter').showHelpSidebar = true;
        },

        openShortcutModal(): void {
            HeyFrame.Store.get('adminHelpCenter').showShortcutModal = true;
        },

        closeShortcutModal(): void {
            HeyFrame.Store.get('adminHelpCenter').showShortcutModal = false;
        },

        setFocusToSidebar(): void {
            const helpSidebar = this.$refs.helpSidebar as {
                setFocusToSidebar: () => void;
            };

            if (!helpSidebar) {
                return;
            }

            helpSidebar.setFocusToSidebar();
        },
    },
});
