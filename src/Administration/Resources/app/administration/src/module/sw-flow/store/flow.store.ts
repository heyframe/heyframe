/**
 * @sw-package after-sales
 */
const { Service } = HeyFrame;
const { EntityCollection } = HeyFrame.Data;
const { types } = HeyFrame.Utils;

type Flow = Entity<'flow'>;
type Sequence = Entity<'flow_sequence'>;
type Sequences = EntityCollection<'flow_sequence'>;

type EntityActions =
    | 'ADD_ORDER_TAG'
    | 'REMOVE_ORDER_TAG'
    | 'ADD_CUSTOMER_TAG'
    | 'SET_ORDER_CUSTOM_FIELD'
    | 'REMOVE_CUSTOMER_TAG'
    | 'SET_CUSTOMER_CUSTOM_FIELD'
    | 'ADD_ORDER_AFFILIATE_AND_CAMPAIGN_CODE'
    | 'SET_CUSTOMER_GROUP_CUSTOM_FIELD'
    | 'ADD_CUSTOMER_AFFILIATE_AND_CAMPAIGN_CODE';

type EntityActionName = (typeof HeyFrame.Constants.FLOW.ACTION)[EntityActions];

const swFlowStore = HeyFrame.Store.register('swFlow', {
    state: () => ({
        flow: {
            eventName: '',
            sequences: [] as unknown as Sequences,
        } as Flow,
        originFlow: {} as Flow,
        triggerEvent: {} as Event,
        invalidSequences: [],
        stateMachineState: [],
        documentTypes: [],
        mailTemplates: [],
        customFieldSets: [],
        customFields: [],
        customerGroups: [],
        restrictedRules: [],
        originAvailableActions: [] as string[],
    }),

    getters: {
        sequences(state) {
            return state.flow.sequences;
        },

        hasFlowChanged(state) {
            const flow = {
                ...state.flow,
                sequences: Array.from(state.flow.sequences as EntityCollection<'flow_sequence'>).filter((item) => {
                    if (item.actionName || item.ruleId) {
                        return Object.assign(item, {});
                    }

                    return false;
                }),
            };

            return !types.isEqual(state.originFlow, flow);
        },

        isSequenceEmpty(state) {
            if (!state.flow.sequences?.length) {
                return true;
            }

            const firstSequence = state.flow.sequences[0];
            return !firstSequence.actionName && !firstSequence.ruleId;
        },

        mailTemplateIds(state) {
            return (
                state.flow.sequences
                    ?.filter((item) => item.actionName === Service('flowBuilderService').getActionName('MAIL_SEND'))
                    .map((item: Sequence) => (item.config as { mailTemplateId?: string })?.mailTemplateId) ?? []
            );
        },

        customFieldSetIds(state) {
            const service = Service('flowBuilderService');
            return (
                state.flow.sequences
                    ?.filter(
                        (item) =>
                            item.actionName === service.getActionName('SET_CUSTOMER_CUSTOM_FIELD') ||
                            item.actionName === service.getActionName('SET_ORDER_CUSTOM_FIELD') ||
                            item.actionName === service.getActionName('SET_CUSTOMER_GROUP_CUSTOM_FIELD'),
                    )
                    .map((item) => (item.config as { customFieldSetId?: string })?.customFieldSetId) ?? []
            );
        },

        customFieldIds(state) {
            const service = Service('flowBuilderService');
            return (
                state.flow.sequences
                    ?.filter(
                        (item) =>
                            item.actionName === service.getActionName('SET_CUSTOMER_CUSTOM_FIELD') ||
                            item.actionName === service.getActionName('SET_ORDER_CUSTOM_FIELD') ||
                            item.actionName === service.getActionName('SET_CUSTOMER_GROUP_CUSTOM_FIELD'),
                    )
                    .map((item) => (item.config as { customFieldId?: string })?.customFieldId) ?? []
            );
        },

        actionGroups() {
            return Service('flowBuilderService').getGroups();
        },
    },

    actions: {
        setFlow(flow: Flow & { config?: Flow }) {
            this.flow = flow;

            if (flow.config) {
                this.flow.description = flow.config.description;
                this.flow.sequences = flow.config.sequences;
                this.flow.eventName = flow.config.eventName;
            }
        },

        setOriginFlow(flow: Flow) {
            const clonedFlow = HeyFrame.Utils.object.cloneDeep(flow);

            if (!flow.sequences) {
                this.originFlow = clonedFlow;
                return;
            }

            const sequences = new EntityCollection(
                flow.sequences.source,
                flow.sequences.entity,
                HeyFrame.Context.api,
                null,
                [],
            );

            flow.sequences.forEach((item) => {
                sequences.add(HeyFrame.Utils.object.cloneDeep(item) as Sequence);
            });

            clonedFlow.sequences = sequences;
            this.originFlow = clonedFlow;
        },

        setEventName(eventName: string) {
            this.flow.eventName = eventName;
        },

        setSequences(sequences: Sequences) {
            this.flow.sequences = sequences;
        },

        addSequence(sequence: Sequence) {
            if (this.flow.sequences instanceof EntityCollection) {
                this.flow.sequences.add(sequence);
                return;
            }

            (this.flow.sequences as Sequence[])?.push(sequence);
        },

        removeSequences(sequenceIds: string[]) {
            sequenceIds.forEach((sequenceId) => {
                if (this.flow.sequences instanceof EntityCollection) {
                    this.flow.sequences?.remove(sequenceId);
                } else {
                    this.flow.sequences = (this.flow.sequences as Sequence[])?.filter(
                        (sequence) => sequence.id !== sequenceId,
                    ) as Sequences;
                }
            });
        },

        updateSequence(params: Partial<Sequence> & { id: string }) {
            const sequences = this.flow.sequences;
            if (!sequences) return;

            const sequenceIndex = sequences.findIndex((el) => el.id === params.id);
            const sequence = sequences[sequenceIndex];
            if (!sequence) return;

            const updatedSequence = Object.assign(sequence, params);

            this.flow.sequences = new EntityCollection(sequences.source, sequences.entity, HeyFrame.Context.api, null, [
                ...sequences.slice(0, sequenceIndex),
                updatedSequence,
                ...sequences.slice(sequenceIndex + 1),
            ]);
        },

        removeCurrentFlow() {
            this.flow = {
                eventName: '',
                sequences: [] as unknown as Sequences,
            } as Flow;
        },

        removeInvalidSequences() {
            this.invalidSequences = [];
        },

        removeTriggerEvent() {
            this.triggerEvent = {} as Event;
        },

        resetFlowState() {
            this.removeCurrentFlow();
            this.removeInvalidSequences();
            this.removeTriggerEvent();
        },

        setRestrictedRules(id: string) {
            void Service('ruleConditionDataProviderService')
                .getRestrictedRules(`flowTrigger.${id}`)
                .then((result) => {
                    this.setRestrictedRules(result?.[0]);
                });
        },
    },
});

/**
 * @private
 */
export default swFlowStore;

/**
 * @private
 */
export type SwFlowStore = ReturnType<typeof swFlowStore>;
