import template from './sw-admin-menu.html.twig';
import './sw-admin-menu.scss';
import {MtText} from '@heyframe-ag/meteor-component-library';
import {
    PopoverRoot,
    PopoverTrigger,
    PopoverPortal,
    PopoverContent,
    RovingFocusItem,
    RovingFocusGroup
} from 'reka-ui';

const {Criteria} = HeyFrame.Data;
import {motion} from 'motion-v';

const MODULES = [
    {
        id: 'dashboard',
        name: '首页',
        icon: 'dashboard',
        to: 'sw.dashboard.index',
        match(route) {
            return route.name === 'sw.dashboard.index' ? 'exact' : 'none';
        }
    },
    {
        id: 'products',
        name: '产品',
        icon: 'tag',
        to: 'sw.product.index',
        match(route) {
            if (route.name.startsWith('sw.product.stream')) {
                return 'none';
            }

            return route.name.startsWith('sw.product') ? 'exact' : 'none';
        },
        children: [
            {
                id: 'reviews',
                name: '评价',
                to: 'sw.review.index',
                match(route) {
                    return route.name.startsWith('sw.review') ? 'exact' : 'none';
                }
            },
            {
                id: 'categories',
                name: '分类',
                to: 'sw.category.index',
                match(route) {
                    return route.name.startsWith('sw.category') ? 'exact' : 'none';
                }
            },
            {
                id: 'properties',
                name: '属性',
                to: 'sw.property.index',
                match(route) {
                    return route.name.startsWith('sw.property') ? 'exact' : 'none';
                }
            },
        ]
    },
    {
        id: 'orders',
        name: '订单',
        icon: 'shopping-bag',
        to: 'sw.order.index',
        match(route) {
            return route.name.startsWith('sw.order') ? 'exact' : 'none';
        }
    },
    {
        id: 'customers',
        name: '客户',
        icon: 'users',
        to: 'sw.customer.index',
        match(route) {
            return route.name.startsWith('sw.customer') ? 'exact' : 'none';
        }
    },
    {
        id: 'content',
        name: '内容',
        icon: 'image-text',
        to: 'sw.cms.index',
        match(route) {
            return route.name.startsWith('sw.cms') ? 'exact' : 'none';
        },
        children: [
            {
                id: 'themes',
                name: '主题',
                to: 'sw.theme.manager.index',
                match(route) {
                    return route.name.startsWith('sw.theme.manager') ? 'exact' : 'none';
                }
            },
            {
                id: 'navigation',
                name: '导航',
                to: 'sw.navigation.index',
                match(route) {
                    return route.name.startsWith('sw.navigation') ? 'exact' : 'none';
                }
            }
        ]
    },
    {
        id: 'automations',
        name: '流程',
        to: 'sw.flow.index.flows',
        icon: 'bolt',
        match(route) {
            return route.name.startsWith('sw.flow') ? 'exact' : 'none';
        },
        children: [
            {
                id: 'rule',
                name: '规则',
                to: 'sw.settings.rule.index',
                match(route) {
                    return route.name.startsWith('sw.settings.rule') ? 'exact' : 'none';
                }
            }
        ]
    },
        {
        id: 'marketing',
        name: '营销',
        icon: 'megaphone',
        to: 'sw.promotion.v2.index',
        match(route) {
            return route.name.startsWith('sw.promotion.v2') ? 'exact' : 'none';
        }
    },
    {
        id: 'extensions',
        name: '扩展',
        icon: 'puzzle-piece',
        to: 'sw.extension.my-extensions.listing',
        match(route) {
            return route.name.startsWith('sw.extension.my-extensions') ? 'exact' : 'none';
        },
    },
    {
        id: 'settings',
        name: '设置',
        icon: 'cog',
        to: 'sw.settings.index',
        match(route) {
            return route.name.startsWith('sw.settings') ? 'exact' : 'none';
        }
    },
];

/**
 * @sw-package framework
 *
 * @private
 */
export default {
    template,

    components: {
        MtText,
        PopoverRoot,
        PopoverContent,
        PopoverTrigger,
        PopoverPortal,
        RovingFocusGroup,
        RovingFocusItem,
        MotionDiv: motion.div
    },

    inject: [
        'repositoryFactory',
        'menuService',
        'loginService',
    ],

    data() {
        return {
            showAccountMenu: false,
            isDarkMode: false,
            channels: [],
            MODULES
        };
    },

    watch: {
        isDarkMode: {
            handler(newValue) {
                // The code below disables all css transitions during the theme change
                //   See more: https://paco.me/writing/disable-theme-transitions
                const css = document.createElement('style')
                css.type = 'text/css'
                css.appendChild(
                    document.createTextNode(
                        `* {
   -webkit-transition: none !important;
   -moz-transition: none !important;
   -o-transition: none !important;
   -ms-transition: none !important;
   transition: none !important;
}`
                    ),
                );
                document.head.appendChild(css)

                if (newValue) {
                    document.documentElement.dataset.theme = 'dark';
                } else {
                    document.documentElement.dataset.theme = 'light';
                }

                // Re-enables all css transitions
                const _ = window.getComputedStyle(css).opacity
                document.head.removeChild(css)
            },
            immediate: true
        }
    },

    created() {
        this.channelRepository.search(this.channelCriteria).then((response) => {
            this.channels = response;
        });
    },

    computed: {
        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        channelCriteria() {
            const criteria = new Criteria(1, 7);

            criteria.addIncludes({
                channel: [
                    'name',
                    'type',
                    'active',
                    'translated',
                    'domains',
                ],
                channel_type: ['iconName'],
                channel_domain: [
                    'url',
                    'languageId',
                ],
            });

            criteria.addSorting(Criteria.sort('channel.name', 'ASC'));
            criteria.addAssociation('type');
            criteria.addAssociation('domains');

            return criteria;
        },
    },

    methods: {
        isChannelSelected(channelId) {
            const isChannelRoute = this.$route.name?.startsWith('sw.channel.');
            if (!isChannelRoute) return false;

            return this.$route.params?.id === channelId;
        },

        signOut() {
            this.loginService.logout();
            HeyFrame.Store.get('session').removeCurrentUser();
            HeyFrame.Store.get('notification').clearGrowlNotificationsForCurrentUser();
            HeyFrame.Store.get('notification').clearNotificationsForCurrentUser();

            this.$router.push({
                name: 'sw.login.index',
            });
        },
    }
};
