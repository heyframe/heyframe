import Criteria from '@heyframe-ag/meteor-admin-sdk/es/data/Criteria';
import template from './sw-navigation-detail-custom-entity.html.twig';
import './sw-navigation-detail-custom-entity.scss';

const { Utils } = HeyFrame;
const EXTENSION_POSTFIX = 'SwNavigations';

/**
 * @private
 * @sw-package inventory
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    data() {
        return {
            navigationCustomEntityProperty: '',
        };
    },

    props: {
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    computed: {
        customEntityAssignments(): EntityCollection<'custom_entity'> | undefined {
            return this.navigation?.extensions?.[`${this.navigationCustomEntityProperty}${EXTENSION_POSTFIX}`] as
                | EntityCollection<'custom_entity'>
                | undefined;
        },

        customEntityColumns(): {
            dataIndex: string;
            property: string;
            label: string;
        }[] {
            return [
                {
                    dataIndex: 'cmsAwareTitle',
                    property: 'cmsAwareTitle',
                    label: this.$tc('sw-navigation.base.customEntity.instanceAssignment.title'),
                },
            ];
        },

        navigation(): Entity<'navigation'> | null {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            return HeyFrame.Store.get('swCategoryDetail').navigation as Entity<'navigation'> | null;
        },

        customEntityCriteria(): Criteria {
            return new Criteria(1, 10).addFilter(Criteria.contains('flags', 'cms-aware'));
        },

        sortingCriteria(): Criteria {
            return new Criteria(1, 10).addSorting(Criteria.sort('cmsAwareTitle', 'ASC'));
        },

        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },

    created(): void {
        void this.fetchCustomEntityName();
    },

    methods: {
        onAssignmentChange(customEntityAssignments: EntityCollection<'custom_entity'>): void {
            const navigationExtensions = this.navigation?.extensions;
            if (!navigationExtensions) {
                return;
            }

            navigationExtensions[`${this.navigationCustomEntityProperty}${EXTENSION_POSTFIX}`] = customEntityAssignments;
        },

        onEntityChange(id: string, entity?: Entity<'custom_entity'>) {
            if (!this.navigation) {
                return;
            }

            this.navigation.customEntityTypeId = id;

            this.navigationCustomEntityProperty = Utils.string.camelCase(entity?.name ?? '');
        },

        async fetchCustomEntityName(): Promise<void> {
            if (!this.navigation?.customEntityTypeId) {
                return;
            }

            const customEntityRepository = this.repositoryFactory.create('custom_entity');
            const customEntity = await customEntityRepository.get(this.navigation.customEntityTypeId);

            if (!customEntity) {
                return;
            }

            this.navigationCustomEntityProperty = Utils.string.camelCase(customEntity.name);
        },
    },
});
