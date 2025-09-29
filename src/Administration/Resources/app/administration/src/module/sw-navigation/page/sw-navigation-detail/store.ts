import type { Entity } from '@heyframe-ag/meteor-admin-sdk/es/_internals/data/Entity';
import type CriteriaType from '@heyframe-ag/meteor-admin-sdk/es/data/Criteria';
import type Repository from '../../../../core/data/repository.data';
import type { ContextStore } from '../../../../app/store/context.store';

const { Criteria } = HeyFrame.Data;

interface LoadPayload<EntityName extends keyof EntitySchema.Entities> {
    repository: Repository<EntityName>;
    id: string;
    apiContext: ContextStore['api'];
    criteria?: CriteriaType;
}

/**
 * @sw-package inventory
 */
const swCategoryDetailStore = HeyFrame.Store.register({
    id: 'swCategoryDetail',

    state: () => ({
        landingPage: null as Entity<'landing_page'> | null,
        navigation: null as Entity<'navigation'> | null,
        isCategoryColumn: false,
        customFieldSets: [],
        landingPagesToDelete: undefined,
        categoriesToDelete: undefined,
    }),

    actions: {
        loadActiveLandingPage({ repository, id, apiContext, criteria = new Criteria(1, 25) }: LoadPayload<'landing_page'>) {
            if (id === 'create') {
                const landingPage = repository.create(apiContext);
                landingPage.cmsPageId = undefined;
                this.landingPage = landingPage;
                return Promise.resolve();
            }

            return repository.get(id, apiContext, criteria).then((landingPage) => {
                this.landingPage = landingPage;
            });
        },

        loadActiveCategory({ repository, id, apiContext, criteria = new Criteria(1, 25) }: LoadPayload<'navigation'>) {
            return repository
                .get(id, apiContext, criteria)
                .then((navigation) => {
                    if (navigation) {
                        this.isCategoryColumn = false;
                        if (navigation.parentId) {
                            const parentCriteria = new Criteria(1, 25);
                            parentCriteria.addAssociation('footerChannels');

                            return repository.get(navigation.parentId, apiContext, parentCriteria).then((parent) => {
                                navigation.parent = parent ?? undefined;

                                this.isCategoryColumn = navigation.parent?.footerChannels?.length !== 0;

                                return navigation;
                            });
                        }
                    }
                    return navigation;
                })
                .then((navigation) => {
                    this.navigation = navigation;
                });
        },
    },
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export type SwCategoryDetailStore = ReturnType<typeof swCategoryDetailStore>;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default swCategoryDetailStore;
