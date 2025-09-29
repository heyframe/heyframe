/**
 * @sw-package discovery
 */

import { mount } from '@vue/test-utils';

const navigationIdMock = 'CATEGORY_MOCK_ID';

async function createWrapper(navigationType) {
    HeyFrame.Store.get('swCategoryDetail').$reset();
    HeyFrame.Store.get('swCategoryDetail').navigation = {
        id: navigationIdMock,
    };
    HeyFrame.Store.get('swCategoryDetail').isCategoryColumn = true;

    HeyFrame.Store.unregister('cmsPage');
    HeyFrame.Store.register({
        id: 'cmsPage',
        state: () => ({
            currentPage: undefined,
        }),
    });

    return mount(await wrapTestComponent('sw-navigation-view', { sync: true }), {
        global: {
            stubs: {
                'sw-card-view': {
                    template: '<div class="sw-card-view"><slot /></div>',
                },
                'sw-language-info': {
                    template: '<div class="sw-language-info"></div>',
                    props: ['entityDescription'],
                },
                'sw-tabs': {
                    template: '<div class="sw-tabs"><slot /></div>',
                },
                'sw-tabs-item': {
                    template: '<div class="sw-tabs-item"><slot /></div>',
                    props: [
                        'route',
                        'title',
                    ],
                },
                'router-view': {
                    template: '<div class="router-view"></div>',
                    props: ['isLoading'],
                },
            },
            mocks: {
                placeholder: (entity, field, fallbackSnippet) => {
                    return {
                        entity,
                        field,
                        fallbackSnippet,
                    };
                },
            },
            provide: {},
        },
        props: {
            isLoading: false,
            type: navigationType,
        },
    });
}

describe('src/module/sw-navigation/component/sw-navigation-view', () => {
    it('should display static snippets and position-identifiers', async () => {
        const wrapper = await createWrapper();

        expect(wrapper.getComponent('.sw-navigation-view').attributes('position-identifier')).toBe('sw-navigation-view');
        expect(wrapper.getComponent('.sw-language-info').props('entityDescription')).toStrictEqual({
            entity: {
                id: 'CATEGORY_MOCK_ID',
            },
            fallbackSnippet: 'sw-manufacturer.detail.textHeadline',
            field: 'name',
        });

        expect(wrapper.getComponent('[role="banner"]').props('variant')).toBe('info');
        expect(wrapper.get('.swag-navigation-view__column-info-header').text()).toBe('sw-navigation.view.columnInfoHeader');
        expect(wrapper.get('.swag-navigation-view__column-info-content').text()).toBe('sw-navigation.view.columnInfo');

        expect(wrapper.get('.sw-navigation-detail-page__tabs').attributes('position-identifier')).toBe('sw-navigation-view');
    });

    function checkGeneralTab(generalTab) {
        expect(generalTab.props()).toStrictEqual({
            route: { name: 'sw.navigation.detail.base' },
            title: 'sw-navigation.view.general',
        });
        expect(generalTab.text()).toBe('sw-navigation.view.general');
    }

    function checkProductTab(productTab) {
        expect(productTab.props()).toStrictEqual({
            route: { name: 'sw.navigation.detail.products' },
            title: 'sw-navigation.view.products',
        });
        expect(productTab.text()).toBe('sw-navigation.view.products');
    }

    function checkCustomEntityTab(customEntityTab) {
        expect(customEntityTab.props()).toStrictEqual({
            route: { name: 'sw.navigation.detail.customEntity' },
            title: 'sw-navigation.view.customEntity',
        });
        expect(customEntityTab.text()).toBe('sw-navigation.view.customEntity');
    }

    function checkCmsTab(cmsTab) {
        expect(cmsTab.props()).toStrictEqual({
            route: { name: 'sw.navigation.detail.cms' },
            title: 'sw-navigation.view.cms',
        });
        expect(cmsTab.text()).toBe('sw-navigation.view.cms');
    }

    function checkSeoTab(seoTab) {
        expect(seoTab.props()).toStrictEqual({
            route: { name: 'sw.navigation.detail.seo' },
            title: 'sw-navigation.view.seo',
        });
        expect(seoTab.text()).toBe('sw-navigation.view.seo');
    }

    it('should display the tabs for the `page` navigation type', async () => {
        const wrapper = await createWrapper('page');

        const generalTab = wrapper.getComponent('.sw-navigation-detail__tab-base');
        checkGeneralTab(generalTab);

        const productTab = wrapper.getComponent('.sw-navigation-detail__tab-products');
        checkProductTab(productTab);

        const customEntityTab = wrapper.getComponent('.sw-navigation-detail__tab-custom-entity');
        expect(customEntityTab.isVisible()).toBe(false);

        const cmsTab = wrapper.getComponent('.sw-navigation-detail__tab-cms');
        checkCmsTab(cmsTab);

        const seoTab = wrapper.getComponent('.sw-navigation-detail__tab-seo');
        checkSeoTab(seoTab);
    });

    it('should display the tabs for the `folder` navigation type', async () => {
        const wrapper = await createWrapper('folder');

        const generalTab = wrapper.getComponent('.sw-navigation-detail__tab-base');
        checkGeneralTab(generalTab);

        const productTab = wrapper.getComponent('.sw-navigation-detail__tab-products');
        expect(productTab.isVisible()).toBe(false);

        const customEntityTab = wrapper.getComponent('.sw-navigation-detail__tab-custom-entity');
        expect(customEntityTab.isVisible()).toBe(false);

        const cmsTab = wrapper.getComponent('.sw-navigation-detail__tab-cms');
        expect(cmsTab.isVisible()).toBe(false);

        const seoTab = wrapper.getComponent('.sw-navigation-detail__tab-seo');
        expect(seoTab.isVisible()).toBe(false);
    });

    it('should display the tabs for the `link` navigation type', async () => {
        const wrapper = await createWrapper('link');

        const generalTab = wrapper.getComponent('.sw-navigation-detail__tab-base');
        checkGeneralTab(generalTab);

        const productTab = wrapper.getComponent('.sw-navigation-detail__tab-products');
        expect(productTab.isVisible()).toBe(false);

        const customEntityTab = wrapper.getComponent('.sw-navigation-detail__tab-custom-entity');
        expect(customEntityTab.isVisible()).toBe(false);

        const cmsTab = wrapper.getComponent('.sw-navigation-detail__tab-cms');
        expect(cmsTab.isVisible()).toBe(false);

        const seoTab = wrapper.getComponent('.sw-navigation-detail__tab-seo');
        expect(seoTab.isVisible()).toBe(false);
    });

    it('should display the tabs for the `custom_entity` navigation type', async () => {
        const wrapper = await createWrapper('custom_entity');

        const generalTab = wrapper.getComponent('.sw-navigation-detail__tab-base');
        checkGeneralTab(generalTab);

        const productTab = wrapper.getComponent('.sw-navigation-detail__tab-products');
        expect(productTab.isVisible()).toBe(false);

        const customEntityTab = wrapper.getComponent('.sw-navigation-detail__tab-custom-entity');
        checkCustomEntityTab(customEntityTab);

        const cmsTab = wrapper.getComponent('.sw-navigation-detail__tab-cms');
        checkCmsTab(cmsTab);

        const seoTab = wrapper.getComponent('.sw-navigation-detail__tab-seo');
        checkSeoTab(seoTab);
    });
});
