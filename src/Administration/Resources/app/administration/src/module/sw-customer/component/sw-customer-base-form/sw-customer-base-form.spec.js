import { mount } from '@vue/test-utils';

/**
 * @sw-package checkout
 */

const customer = {
    id: '1',
    email: null,
    boundChannelId: null,
    vatIds: [
        '9f8f091c-db81-4ef3-862c-9c554a34cdc4',
    ],
};

async function createWrapper() {
    return mount(await wrapTestComponent('sw-customer-base-form', { sync: true }), {
        props: {
            customer,
        },
        global: {
            stubs: {
                'sw-container': await wrapTestComponent('sw-container'),
                'sw-entity-single-select': true,
                'sw-text-field': true,
                'sw-email-field': true,
                'sw-datepicker': true,
                'sw-entity-tag-select': true,
                'sw-single-select': true,
            },
        },
    });
}

describe('module/sw-customer/page/sw-customer-base-form', () => {
    it('should display the account type switcher', async () => {
        const wrapper = await createWrapper();
        const accountTypeSelect = wrapper.find('.sw-customer-base-form__account-type-select');
        expect(accountTypeSelect.exists()).toBeTruthy();
    });
});
