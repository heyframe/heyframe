/**
 * @sw-package inventory
 */

import template from './sw-seo-url-template-card.html.twig';
import './sw-seo-url-template-card.scss';

const { Mixin } = HeyFrame;
const { mapCollectionPropertyErrors } = HeyFrame.Component.getComponentHelper();
const EntityCollection = HeyFrame.Data.EntityCollection;
const Criteria = HeyFrame.Data.Criteria;
const utils = HeyFrame.Utils;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'seoUrlTemplateService',
        'repositoryFactory',
    ],

    mixins: [Mixin.getByName('notification')],

    data() {
        return {
            defaultSeoUrlTemplates: null,
            seoUrlTemplates: null,
            seoUrlPreviewCriteria: {},
            isLoading: true,
            debouncedPreviews: {},
            previewLoadingStates: {},
            errorMessages: {},
            previews: {},
            noEntityError: [],
            variableStores: {},
            seoUrlTemplateRepository: {},
            channelId: null,
            channels: [],
            selectedProperty: null,
        };
    },

    computed: {
        ...mapCollectionPropertyErrors('seoUrlTemplates', ['template']),

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        channelIsHeadless() {
            const currentChannel = this.channels.find((entity) => {
                return entity.id === this.channelId;
            });

            if (!currentChannel) {
                return false;
            }

            // from Defaults.php
            return currentChannel.typeId === 'f183ee5650cf4bdb8a774337575067a6';
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.seoUrlTemplateRepository = this.repositoryFactory.create('seo_url_template');
            this.seoUrlTemplates = new EntityCollection(
                this.seoUrlTemplateRepository.route,
                this.seoUrlTemplateRepository.schema.entity,
                HeyFrame.Context.api,
                new Criteria(1, 25),
            );

            this.defaultSeoUrlTemplates = new EntityCollection(
                this.seoUrlTemplateRepository.route,
                this.seoUrlTemplateRepository.schema.entity,
                HeyFrame.Context.api,
                new Criteria(1, 25),
            );

            this.seoUrlPreviewCriteria['frontend.navigation.page'] = new Criteria(1, 25).addFilter(
                Criteria.not('and', [Criteria.equals('path', null)]),
            );

            this.fetchChannels();
            this.fetchSeoUrlTemplates();
        },
        fetchSeoUrlTemplates(channelId = null) {
            const criteria = new Criteria(1, 25);

            if (!channelId) {
                channelId = null;
            }
            criteria.addFilter(Criteria.equals('channelId', channelId));

            this.isLoading = true;

            this.seoUrlTemplateRepository.search(criteria).then((response) => {
                response.forEach((entity) => {
                    if (!this.seoUrlTemplates.has(entity.id)) {
                        this.seoUrlTemplates.add(entity);
                    }
                });

                if (!channelId) {
                    // Save the defaults as blueprint for creating dynamically new entities
                    response.forEach((entity) => {
                        if (!this.defaultSeoUrlTemplates.has(entity)) {
                            this.defaultSeoUrlTemplates.add(entity);
                        }
                    });
                } else {
                    this.createSeoUrlTemplatesFromDefaultRoutes(channelId);
                }
                this.isLoading = false;

                this.seoUrlTemplates.forEach((seoUrlTemplate) => {
                    // Fetch preview / validate seo url template
                    this.fetchSeoUrlPreview(seoUrlTemplate);

                    // Create stores for the possible variables
                    if (!this.variableStores.hasOwnProperty(seoUrlTemplate.id)) {
                        this.seoUrlTemplateService.getContext(seoUrlTemplate).then((data) => {
                            this.createVariableOptions(seoUrlTemplate.id, data);
                        });
                    }
                });
            });
        },
        createSeoUrlTemplatesFromDefaultRoutes(channelId) {
            // Iterate over the default seo url templates and create new entities for the actual sales channel
            // if they do not exist
            this.defaultSeoUrlTemplates.forEach((defaultEntity) => {
                const entityAlreadyExists = this.seoUrlTemplates.some((entity) => {
                    return entity.routeName === defaultEntity.routeName && entity.channelId === channelId;
                });

                if (!entityAlreadyExists) {
                    const entity = this.seoUrlTemplateRepository.create();
                    entity.routeName = defaultEntity.routeName;
                    entity.channelId = channelId;
                    entity.entityName = defaultEntity.entityName;
                    entity.template = null;
                    this.seoUrlTemplates.add(entity);
                }
            });
        },
        createVariableOptions(id, data) {
            const storeOptions = [];

            Object.entries(data).forEach(
                ([
                    property,
                    value,
                ]) => {
                    storeOptions.push({ name: `${property}` });

                    if (value instanceof Object) {
                        Object.keys(value).forEach((innerProperty) => {
                            storeOptions.push({
                                name: `${property}.${innerProperty}`,
                            });
                        });
                    }
                },
            );

            this.variableStores.id = storeOptions;
        },
        getVariableOptions(id) {
            if (this.variableStores.hasOwnProperty(id)) {
                return this.variableStores[id];
            }
            return false;
        },
        getLabel(seoUrlTemplate) {
            const routeName = seoUrlTemplate.routeName.replace(/\./g, '-');
            if (this.$tc(`sw-seo-url-template-card.routeNames.${routeName}`)) {
                return this.$tc(`sw-seo-url-template-card.routeNames.${routeName}`);
            }

            return seoUrlTemplate.routeName;
        },
        getPlaceholder(seoUrlTemplate) {
            if (!seoUrlTemplate.channelId) {
                return null;
            }

            const defaultEntity = Object.values(this.defaultSeoUrlTemplates).find((entity) => {
                return entity.routeName === seoUrlTemplate.routeName;
            });

            return defaultEntity.template;
        },
        onClickSave() {
            const hasError = Object.keys(this.errorMessages).some((key) => {
                return this.errorMessages[key] !== null;
            });

            if (hasError) {
                this.createSaveErrorNotification();
                return;
            }

            this.seoUrlTemplates.forEach((entry) => {
                if (entry.template === null) {
                    this.seoUrlTemplates.remove(entry.id);
                }
            });

            this.seoUrlTemplateRepository
                .sync(this.seoUrlTemplates)
                .then(() => {
                    this.seoUrlTemplates = new EntityCollection(
                        this.seoUrlTemplateRepository.route,
                        this.seoUrlTemplateRepository.schema.entity,
                        HeyFrame.Context.api,
                        new Criteria(1, 25),
                    );
                    this.fetchSeoUrlTemplates(this.channelId);
                    this.createSaveSuccessNotification();
                })
                .catch(() => {
                    this.createSaveErrorNotification();
                });
        },
        createSaveErrorNotification() {
            const titleSaveSuccess = this.$tc('global.default.error');
            const messageSaveSuccess = this.$tc('sw-seo-url-template-card.general.messageSaveError');

            this.createNotificationError({
                title: titleSaveSuccess,
                message: messageSaveSuccess,
            });
        },
        createSaveSuccessNotification() {
            const titleSaveSuccess = this.$tc('global.default.success');
            const messageSaveSuccess = this.$tc('sw-seo-url-template-card.general.messageSaveSuccess');

            this.createNotificationSuccess({
                title: titleSaveSuccess,
                message: messageSaveSuccess,
            });
        },

        onSelectInput(propertyName, entity) {
            if (propertyName === null) {
                return;
            }
            const templateValue = entity.template ? `${entity.template}/` : '';
            entity.template = `${templateValue}{{ ${propertyName} }}`;
            this.fetchSeoUrlPreview(entity);
        },
        onInput(entity) {
            this.debouncedPreviewSeoUrlTemplate(entity);
        },
        debouncedPreviewSeoUrlTemplate(entity) {
            if (!this.debouncedPreviews[entity.id]) {
                this.debouncedPreviews[entity.id] = utils.debounce(() => {
                    if (entity.template && entity.template !== '') {
                        this.fetchSeoUrlPreview(entity);
                    } else {
                        this.setErrorMessagesForEntity(entity);
                    }
                }, 400);
            } else {
                this.setErrorMessagesForEntity(entity);
            }

            this.debouncedPreviews[entity.id]();
        },
        setErrorMessagesForEntity(entity, value = null) {
            // eslint-disable-next-line no-lonely-if
            this.errorMessages[entity.id] = value;
        },
        fetchSeoUrlPreview(entity) {
            this.previewLoadingStates[entity.id] = true;

            const criteria = this.seoUrlPreviewCriteria[entity.routeName]
                ? this.seoUrlPreviewCriteria[entity.routeName]
                : new Criteria(1, 25);
            entity.criteria = criteria.parse();
            this.seoUrlTemplateService
                .preview(entity)
                .then((response) => {
                    this.noEntityError = this.noEntityError.filter((elem) => {
                        return elem !== entity.id;
                    });

                    this.previews[entity.id] = response;

                    if (response === null) {
                        this.noEntityError.push(entity.id);
                    } else {
                        this.setErrorMessagesForEntity(entity);
                    }
                    this.previewLoadingStates[entity.id] = false;
                })
                .catch((err) => {
                    this.setErrorMessagesForEntity(entity, err.response.data.errors[0].detail);

                    this.previews[entity.id] = [];

                    this.previewLoadingStates[entity.id] = false;
                });
        },
        fetchChannels() {
            this.channelRepository.search(new Criteria(1, 25)).then((response) => {
                this.channels = response;
            });
        },
        onChannelChanged(channelId) {
            this.channelId = channelId;
            this.fetchSeoUrlTemplates(channelId);
        },
        getTemplatesForChannel(channelId) {
            return this.seoUrlTemplates.filter((templateEntity) => {
                return templateEntity.channelId === channelId;
            });
        },
    },
};
