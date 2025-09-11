/**
 * @sw-package discovery
 */

import template from './sw-channel-create.html.twig';

const utils = HeyFrame.Utils;

const insertIdIntoRoute = (to, from, next) => {
    if (to.name.includes('sw.sales.channel.create') && !to.params.id) {
        to.params.id = utils.createId();
    }

    next();
};

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    beforeRouteEnter: insertIdIntoRoute,

    beforeRouteUpdate: insertIdIntoRoute,

    inject: ['systemConfigApiService'],

    computed: {
        allowSaving() {
            return this.acl.can('channel.creator');
        },
    },

    methods: {
        createdComponent() {
            if (!this.$route.params.typeId) {
                return;
            }

            if (!HeyFrame.Store.get('context').isSystemDefaultLanguage) {
                HeyFrame.Store.get('context').resetLanguageToDefault();
            }

            this.channel = this.channelRepository.create();
            this.channel.typeId = this.$route.params.typeId;
            this.channel.active = false;

            this.setMeasurementUnits()
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-channel.detail.messageMeasurementUnitsSetError'),
                    });
                })
                .finally(() => {
                    this.$super('createdComponent');
                });
        },

        async setMeasurementUnits() {
            const measurementUnits = await this.getMeasurementUnits();

            this.channel.measurementUnits = {
                system: measurementUnits['core.measurementUnits.system'],
                units: {
                    length: measurementUnits['core.measurementUnits.length'],
                    weight: measurementUnits['core.measurementUnits.weight'],
                },
            };
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({
                name: 'sw.sales.channel.detail',
                params: { id: this.channel.id },
            });
        },

        onSave() {
            this.$super('onSave');
        },

        getMeasurementUnits() {
            return this.systemConfigApiService.getValues('core.measurementUnits');
        },
    },
};
