import template from './sw-channel-detail.html.twig';

const { Component } = HeyFrame;

/**
 * @package discovery
 */
Component.override('sw-channel-detail', {
    template,

    inject: [
        'themeService',
    ],

    methods: {
        getLoadChannelCriteria() {
            const criteria = this.$super('getLoadChannelCriteria');

            criteria.addAssociation('themes');

            return criteria;
        },

        async onSave() {
            this.isLoading = true;
            await this.assignChannelTheme();
            await this.$super('onSave');
        },

        async assignChannelTheme() {
            const originThemeId = this.channel.getOrigin().extensions?.themes?.[0]?.id;
            const newThemeId = this.channel.extensions?.themes?.[0]?.id;

            if (originThemeId === newThemeId) {
                return;
            }

            try {
                await this.themeService.assignTheme(newThemeId, this.channel.id);
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-theme-manager.general.messageSaveError')
                });
            }
        },
    },
});
