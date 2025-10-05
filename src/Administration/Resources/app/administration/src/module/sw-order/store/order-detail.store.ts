/**
 * @sw-package checkout
 */
import type { ContextState } from '../../../app/composables/use-context';

interface OrderAddressId {
    type: string;
    edited: boolean;
}

const swOrderDetailStore = HeyFrame.Store.register({
    id: 'swOrderDetail',

    state() {
        return {
            order: null as EntitySchema.order | null,
            loading: {
                order: false, // live version id
                recalculation: false, // custom version id
                states: false,
            },
            editing: false,
            savedSuccessful: false,
            versionContext: null as ContextState['api'] | null,
        };
    },

    getters: {
        isLoading: (state) => {
            return Object.values(state.loading).some((loadState) => loadState);
        },

        isEditing: (state) => {
            return state.editing;
        },
    },

    actions: {
        setLoading(value: [keyof typeof this.loading, boolean]) {
            const name = value[0];
            const data = value[1];

            // check for use from .js files
            if (typeof data !== 'boolean') {
                return;
            }
            this.loading[name] = data;
        },
    },
});

/**
 * @private
 */
export default swOrderDetailStore;

/**
 * @private
 */
export type SwOrderDetailStore = ReturnType<typeof swOrderDetailStore>;
