/**
 * @sw-package checkout
 */

import type { UserInfo } from 'src/core/service/api/store.api.service';
import type { Extension } from '../service/extension-store-action.service';
import type { MappedError } from '../service/extension-error-handler.service';

/**
 * @private
 */
export interface HeyFrameExtensionsState {
    search: {
        page: number;
        limit: number;
        rating: $TSFixMe;
        sorting: $TSFixMe;
        term: null | string;
        filter: $TSFixMe;
    };
    extensionListing: Extension[];
    categoriesLanguageId: string | null;
    myExtensions: {
        loading: boolean;
        data: Extension[];
    };
    userInfo: UserInfo | null;
}

type SearchValue<T, K extends keyof T> = {
    key: K;
    value: T[K];
};

const heyframeExtensionsStore = HeyFrame.Store.register({
    id: 'heyframeExtensions',

    state: () =>
        ({
            search: {
                page: 1,
                limit: 12,
                rating: null,
                sorting: null,
                term: null,
                filter: {},
            },
            extensionListing: [],
            categoriesLanguageId: null,
            myExtensions: {
                loading: true,
                data: [],
            },
            userInfo: null,
            heyframeId: null,
            loginStatus: false,
            licensedExtensions: {
                loading: false,
                data: [],
            },
            totalPlugins: 0,
            plugins: null,
        }) as HeyFrameExtensionsState,

    actions: {
        setSearchValue<K extends keyof HeyFrameExtensionsState['search']>({
            key,
            value,
        }: SearchValue<HeyFrameExtensionsState['search'], K>) {
            this.search.page = 1;
            this.search[key] = value;
        },

        loadMyExtensions() {
            this.myExtensions.loading = true;
        },

        setLoading(value: boolean = true) {
            this.myExtensions.loading = value;
        },

        setMyExtensions(myExtensions: Extension[]) {
            this.myExtensions.data = myExtensions;
            this.myExtensions.loading = false;
        },

        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        pluginErrorsMapped(_mappedError: MappedError[]) {
            /* nth */
        },
    },
});

/**
 * @private
 */
export type HeyFrameExtensionsStore = ReturnType<typeof heyframeExtensionsStore>;

/**
 * @sw-package checkout
 * @private
 */
export default heyframeExtensionsStore;
