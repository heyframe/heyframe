/**
 * @sw-package after-sales
 */

import template from './sw-mail-header-footer-detail.html.twig';
import './sw-mail-header-footer-detail.scss';

const { Mixin } = HeyFrame;
const { Criteria } = HeyFrame.Data;
const { warn } = HeyFrame.Utils.debug;
const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();

/**
 * @sw-package after-sales
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'entityMappingService',
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.allowSave;
            },
            method: 'onSave',
        },
        ESCAPE: 'onCancel',
    },

    data() {
        return {
            mailHeaderFooter: null,
            mailHeaderFooterId: null,
            isLoading: true,
            isSaveSuccessful: false,
            editorConfig: {
                enableBasicAutocompletion: true,
            },
            showModal: false,
            alreadyAssignedChannels: [],
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        ...mapPropertyErrors('mailHeaderFooter', [
            'name',
        ]),

        identifier() {
            return this.placeholder(this.mailHeaderFooter, 'name');
        },

        mailHeaderFooterRepository() {
            return this.repositoryFactory.create('mail_header_footer');
        },

        mailHeaderFooterCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addAssociation('channels');

            return criteria;
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        completerFunction() {
            return (function completerWrapper(entityMappingService) {
                function completerFunction(prefix) {
                    const properties = [];
                    Object.keys(
                        entityMappingService.getEntityMapping(prefix, {
                            channel: 'channel',
                        }),
                    ).forEach((val) => {
                        properties.push({
                            value: val,
                        });
                    });
                    return properties;
                }
                return completerFunction;
            })(this.entityMappingService);
        },

        allowSave() {
            return this.mailHeaderFooter && this.mailHeaderFooter.isNew()
                ? this.acl.can('mail_templates.creator')
                : this.acl.can('mail_templates.editor');
        },

        tooltipSave() {
            if (!this.allowSave) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.allowSave,
                    showOnDisabledElements: true,
                };
            }

            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },
    },

    watch: {
        '$route.params.id'() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        onClose() {
            this.showModal = false;
            this.isLoading = false;
        },

        async createdComponent() {
            if (this.$route.params.id) {
                this.mailHeaderFooterId = this.$route.params.id.toLowerCase();
                await this.loadEntityData();
            }

            this.isLoading = false;
        },

        async loadEntityData() {
            this.isLoading = true;

            this.mailHeaderFooter = await this.mailHeaderFooterRepository.get(
                this.mailHeaderFooterId,
                HeyFrame.Context.api,
                this.mailHeaderFooterCriteria,
            );

            this.isLoading = false;
        },

        abortOnLanguageChange() {
            return this.this.mailHeaderFooterRepository.hasChanges(this.mailHeaderFooter);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },

        saveFinish() {
            this.isSaveSuccessful = false;
        },

        onCancel() {
            this.$router.push({ name: 'sw.mail.template.index' });
        },

        async onSave() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            if (this.mailHeaderFooter.channels.length > 0) {
                await this.findAlreadyAssignedChannels();
            }

            if (this.alreadyAssignedChannels.length) {
                this.showModal = true;
                this.isLoading = false;

                return;
            }

            await this.confirmSave();
        },

        async confirmSave() {
            try {
                this.isLoading = true;

                await this.mailHeaderFooterRepository.save(this.mailHeaderFooter);
                await this.loadEntityData();

                this.isSaveSuccessful = true;
            } catch (error) {
                const notificationError = {
                    message: this.$tc('global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'),
                };

                this.createNotificationError(notificationError);
                warn(error);
            } finally {
                this.isLoading = false;
                this.showModal = false;
            }
        },

        async findAlreadyAssignedChannels() {
            const criteria = new Criteria(1, 25);
            const channelIds = [];

            this.mailHeaderFooter.channels.forEach((channel) => {
                channelIds.push(channel.id);
            });

            criteria.addFilter(Criteria.equalsAny('id', channelIds));

            const items = await this.channelRepository.search(criteria);
            this.alreadyAssignedChannels = items.reduce((assignedChannels, currentChannel) => {
                if (currentChannel.mailHeaderFooterId === null) {
                    return assignedChannels;
                }

                if (!this.mailHeaderFooterId) {
                    assignedChannels.push(currentChannel);
                }

                if (this.mailHeaderFooterId && currentChannel.mailHeaderFooterId !== this.mailHeaderFooterId) {
                    assignedChannels.push(currentChannel);
                }

                return assignedChannels;
            }, []);
        },
    },
};
