import type { AxiosResponse } from 'axios';
import type {
    CalculatedPrice,
    Cart,
    CartError,
    ContextSwitchParameters,
    LineItem,
    PromotionCodeTag,
    ChannelContext,
} from '../order.types';

/**
 * @sw-package checkout
 */

const { Service } = HeyFrame;

function filterEmptyLineItems(items: LineItem[]) {
    return items.filter((item) => item.label === '');
}

function reverseLineItems(items: LineItem[]) {
    return items.slice().reverse();
}

function mergeEmptyAndExistingLineItems(emptyLineItems: LineItem[], lineItems: LineItem[]) {
    // Reverse the lineItems so the newly added are at the top for better UX
    reverseLineItems(lineItems);

    return [
        ...emptyLineItems,
        ...lineItems,
    ];
}

interface SwOrderState {
    cart: Cart;
    disabledAutoPromotion: boolean;
    promotionCodes: PromotionCodeTag[];
    defaultChannel: Entity<'channel'> | null;
    context: ChannelContext;
    customer: Entity<'customer'> | null;
}

const swOrderStore = HeyFrame.Store.register({
    id: 'swOrder',

    state: (): SwOrderState => ({
        customer: null,
        defaultChannel: null,
        cart: {
            token: null,
            lineItems: [],
            price: {
                totalPrice: null,
            },
            deliveries: [],
        } as unknown as Cart,
        context: {
            token: '',
            customer: null,
            paymentMethod: {
                translated: {
                    distinguishableName: '',
                },
            } as Entity<'payment_method'>,
            shippingMethod: {
                translated: {
                    name: '',
                },
            } as Entity<'shipping_method'>,
            currency: {
                isoCode: 'EUR',
                symbol: '€',
                totalRounding: {
                    decimals: 2,
                },
            } as Entity<'currency'>,
            channel: {
                id: '',
            } as Entity<'channel'>,
            context: {
                currencyId: '',
                languageIdChain: [],
            },
        },
        promotionCodes: [],
        disabledAutoPromotion: false,
    }),

    getters: {
        isCustomerActive(state: SwOrderState): boolean {
            return !!state?.context.customer?.active;
        },

        isCartTokenAvailable(state: SwOrderState): boolean {
            return !!state?.cart?.token;
        },

        currencyId(state: SwOrderState): string {
            return state?.context.context.currencyId ?? '';
        },

        invalidPromotionCodes(state: SwOrderState): PromotionCodeTag[] {
            return state.promotionCodes.filter((item) => item.isInvalid);
        },

        cartErrors(state: SwOrderState): CartError[] {
            return state?.cart?.errors ?? null;
        },
    },

    actions: {
        setCustomer(customer: Entity<'customer'> | null) {
            this.context.customer = customer;
            this.customer = customer;
        },

        setDefaultChannel(channel: Entity<'channel'> | null) {
            this.defaultChannel = channel;
        },

        setCartToken(token: string) {
            this.cart.token = token;
        },

        setCart(cart: Cart) {
            const emptyLineItems = filterEmptyLineItems(this.cart.lineItems);
            this.cart = cart;
            this.cart.lineItems = mergeEmptyAndExistingLineItems(emptyLineItems, this.cart.lineItems);
        },

        setCartLineItems(lineItems: LineItem[]) {
            this.cart.lineItems = lineItems;
        },

        setCurrency(currency: Entity<'currency'>) {
            this.context.currency = currency;
        },

        setContext(context: ChannelContext) {
            this.context = context;
        },

        setPromotionCodes(promotionCodes: PromotionCodeTag[]) {
            this.promotionCodes = promotionCodes;
        },

        removeEmptyLineItem(emptyLineItemKey: string) {
            this.cart.lineItems = this.cart.lineItems.filter((item) => item.id !== emptyLineItemKey);
        },

        removeInvalidPromotionCodes() {
            this.promotionCodes = this.promotionCodes.filter((item) => !item.isInvalid);
        },

        setDisabledAutoPromotion(disabledAutoPromotion: boolean) {
            this.disabledAutoPromotion = disabledAutoPromotion;
        },

        selectExistingCustomer({ customer }: { customer: Entity<'customer'> | null }) {
            this.setCustomer(customer);
            this.setDefaultChannel(customer?.channel ?? null);
        },

        createCart({ channelId }: { channelId: string }) {
            return (
                Service('cartStoreService')
                    .createCart(channelId)
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                    .then((response: AxiosResponse): string => {
                        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                        const token = response.data.token as string;
                        this.setCartToken(token);
                        return token;
                    })
                    .then((contextToken) => {
                        return Service('contextStoreService')
                            .getChannelContext(channelId, contextToken)
                            .then((response: AxiosResponse) => this.setContext(response.data as ChannelContext));
                    })
            );
        },

        getCart({ channelId, contextToken }: { channelId: string; contextToken: string }) {
            if (`${contextToken}`.length !== 32) {
                throw new Error('Invalid context token');
            }

            return Promise.all([
                Service('cartStoreService')
                    .getCart(channelId, contextToken)
                    .then((response: AxiosResponse) => this.setCart(response.data as Cart)),
                Service('contextStoreService')
                    .getChannelContext(channelId, contextToken)
                    .then((response: AxiosResponse) => this.setContext(response.data as ChannelContext)),
            ]);
        },

        cancelCart({ channelId, contextToken }: { channelId: string; contextToken: string }) {
            if (`${contextToken}`.length !== 32) {
                throw new Error('Invalid context token');
            }

            return Service('cartStoreService')
                .cancelCart(channelId, contextToken)
                .then(() => this.$reset());
        },

        updateCustomerContext({
            customerId,
            channelId,
            contextToken,
        }: {
            customerId: string;
            channelId: string;
            contextToken: string;
        }) {
            return Service('contextStoreService').updateCustomerContext(customerId, channelId, contextToken);
        },

        updateOrderContext({
            context,
            channelId,
            contextToken,
        }: {
            context: ContextSwitchParameters;
            channelId: string;
            contextToken: string;
        }) {
            return Service('contextStoreService').updateContext(context, channelId, contextToken);
        },

        getContext({ channelId, contextToken }: { channelId: string; contextToken: string }) {
            return Service('contextStoreService').getChannelContext(channelId, contextToken);
        },

        saveOrder({ channelId, contextToken }: { channelId: string; contextToken: string }) {
            return Service('checkoutStoreService').checkout(channelId, contextToken);
        },

        removeLineItems({
            channelId,
            contextToken,
            lineItemKeys,
        }: {
            channelId: string;
            contextToken: string;
            lineItemKeys: string[];
        }) {
            return Service('cartStoreService')
                .removeLineItems(channelId, contextToken, lineItemKeys)
                .then((response: AxiosResponse) => this.setCart(response.data as Cart));
        },

        saveLineItem({
            channelId,
            contextToken,
            item,
        }: {
            channelId: string;
            contextToken: string;
            item: LineItem;
        }) {
            return Service('cartStoreService')
                .saveLineItem(channelId, contextToken, item)
                .then((response: AxiosResponse) => this.setCart(response.data as Cart));
        },

        saveMultipleLineItems({
            channelId,
            contextToken,
            items,
        }: {
            channelId: string;
            contextToken: string;
            items: LineItem[];
        }) {
            return Service('cartStoreService')
                .addMultipleLineItems(channelId, contextToken, items)
                .then((response: AxiosResponse) => this.setCart(response.data as Cart));
        },

        addPromotionCode({
            channelId,
            contextToken,
            code,
        }: {
            channelId: string;
            contextToken: string;
            code: string;
        }): Promise<void> {
            return Service('cartStoreService')
                .addPromotionCode(channelId, contextToken, code)
                .then((response) => this.setCart(response.data as Cart));
        },

        modifyShippingCosts({
            channelId,
            contextToken,
            shippingCosts,
        }: {
            channelId: string;
            contextToken: string;
            shippingCosts: CalculatedPrice;
        }) {
            return (
                Service('cartStoreService')
                    ?.modifyShippingCosts(channelId, contextToken, shippingCosts)
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                    .then((response: AxiosResponse) => this.setCart(response.data.data as Cart))
            );
        },

        remindPayment({ orderTransactionId }: { orderTransactionId: string }) {
            return Service('orderStateMachineService').transitionOrderTransactionState(orderTransactionId, 'remind');
        },
    },
});

/**
 * @private
 */
export default swOrderStore;

/**
 * @private
 */
export type SwOrderStore = ReturnType<typeof swOrderStore>;

/**
 * @private
 */
export type { SwOrderState };
