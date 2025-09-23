<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\Event\BeforeCartMergeEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartMergedEvent;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Event\ChannelContextRestoredEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('framework')]
class CartRestorer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractChannelContextFactory $factory,
        private readonly ChannelContextPersister $contextPersister,
        private readonly CartService $cartService,
        private readonly CartRuleLoader $cartRuleLoader,
        private readonly AbstractCartPersister $cartPersister,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * This function restores the context by the given token. If a context with this token doesn't exist, the context will
     * create with the customer id in the payload, but not in the main customerId table column.
     * So, the context is not directly referenced to the customer and will not be loaded, if the normal restore-function is used.
     *
     * @internal
     */
    public function restoreByToken(string $token, string $customerId, ChannelContext $currentContext): ChannelContext
    {
        $customerPayload = $this->contextPersister->load(
            $token,
            $currentContext->getChannelId(),
        );

        if (empty($customerPayload) || !empty($customerPayload['permissions'])) {
            return $this->replaceContextToken($customerId, $currentContext, $token);
        }

        $customerContext = $this->factory->create($customerPayload['token'], $currentContext->getChannelId(), $customerPayload);
        if ($customerPayload['expired'] ?? false) {
            $customerContext = $this->replaceContextToken($customerId, $customerContext, $token);
        }

        return $this->enrichCustomerContext($customerContext, $currentContext, $currentContext->getToken(), $customerId);
    }

    /**
     * This function restores the context by the given customer id. If a context with this customer id doesn't exist, the context will
     * create with the customer id in the main customerId table column.
     * So, the context is directly referenced to the customer.
     */
    public function restore(string $customerId, ChannelContext $currentContext): ChannelContext
    {
        $customerPayload = $this->contextPersister->load(
            $currentContext->getToken(),
            $currentContext->getChannelId(),
            $customerId
        );

        if (empty($customerPayload) || !empty($customerPayload['permissions']) || !($customerPayload['expired'] ?? false) && $customerPayload['token'] === $currentContext->getToken()) {
            return $this->replaceContextToken($customerId, $currentContext);
        }

        $customerContext = $this->factory->create($customerPayload['token'], $currentContext->getChannelId(), $customerPayload);
        if ($customerPayload['expired'] ?? false) {
            $customerContext = $this->replaceContextToken($customerId, $customerContext);
        }

        if (!$customerContext->getDomainId()) {
            $customerContext->setDomainId($currentContext->getDomainId());
        }

        return $this->enrichCustomerContext($customerContext, $currentContext, $currentContext->getToken(), $customerId);
    }

    private function mergeCart(Cart $customerCart, Cart $guestCart, ChannelContext $customerContext): Cart
    {
        $mergeableLineItems = $guestCart->getLineItems()->filter(fn (LineItem $item) => ($item->getQuantity() > 0 && $item->isStackable()) || !$customerCart->has($item->getId()));

        $this->eventDispatcher->dispatch(new BeforeCartMergeEvent(
            $customerCart,
            $mergeableLineItems,
            $customerContext
        ));

        $errors = $customerCart->getErrors();
        $customerCart->setErrors(new ErrorCollection());

        $customerCartClone = clone $customerCart;
        $customerCart->setErrors($errors);
        $customerCartClone->setErrors($errors);

        $mergedCart = $this->cartService->add($customerCart, $mergeableLineItems->getElements(), $customerContext);

        $this->eventDispatcher->dispatch(new CartMergedEvent($mergedCart, $customerContext, $customerCartClone));

        return $mergedCart;
    }

    private function replaceContextToken(?string $customerId, ChannelContext $currentContext, ?string $newToken = null): ChannelContext
    {
        $originalToken = $newToken;
        if ($newToken === null) {
            $newToken = $this->contextPersister->replace($currentContext->getToken(), $currentContext);
        } else {
            // Prevent duplicate key RDBMS errors in case the new token exists and has permissions attached.
            $this->cartPersister->delete($newToken, $currentContext);
            $this->cartPersister->replace($currentContext->getToken(), $newToken, $currentContext);
        }

        $currentContext->assign([
            'token' => $newToken,
        ]);

        $this->contextPersister->save(
            $newToken,
            [
                'customerId' => $customerId,
                'billingAddressId' => null,
                'shippingAddressId' => null,
                'permissions' => [],
            ],
            $currentContext->getChannelId(),
            ($originalToken === null) ? $customerId : null,
        );

        $this->updateImpersonation($currentContext);

        return $currentContext;
    }

    private function deleteGuestContext(ChannelContext $guestContext, string $customerId): void
    {
        $this->cartService->deleteCart($guestContext);
        $this->contextPersister->delete($guestContext->getToken(), $guestContext->getChannelId(), $customerId);
    }

    private function updateImpersonation(ChannelContext $context): void
    {
        $request = $this->requestStack->getMainRequest();

        if (!$request?->hasSession()) {
            return;
        }

        $session = $request->getSession();

        if (!$context->getImitatingUserId()) {
            $session->remove(PlatformRequest::ATTRIBUTE_IMITATING_USER_ID);
        } else {
            $session->set(PlatformRequest::ATTRIBUTE_IMITATING_USER_ID, $context->getImitatingUserId());
        }
    }

    private function enrichCustomerContext(
        ChannelContext $customerContext,
        ChannelContext $currentContext,
        string $token,
        string $customerId
    ): ChannelContext {
        if (!$customerContext->getDomainId()) {
            $customerContext->setDomainId($currentContext->getDomainId());
        }

        $guestCart = $this->cartService->getCart($token, $currentContext);
        $customerCart = $this->cartService->getCart($customerContext->getToken(), $customerContext);
        $cartsAreIdentical = $token === $customerContext->getToken();

        if ($guestCart->getLineItems()->count() > 0 && !$cartsAreIdentical) {
            $restoredCart = $this->mergeCart($customerCart, $guestCart, $customerContext);
        } else {
            $restoredCart = $this->cartService->recalculate($customerCart, $customerContext);
        }

        $restoredCart->addErrors(...array_values($guestCart->getErrors()->getPersistent()->getElements()));

        $this->deleteGuestContext($currentContext, $customerId);

        if ($currentContext->getImitatingUserId() !== $customerContext->getImitatingUserId()) {
            $customerContext->setImitatingUserId($currentContext->getImitatingUserId());
            $this->updateImpersonation($customerContext);
        }

        $errors = $restoredCart->getErrors();
        $result = $this->cartRuleLoader->loadByToken($customerContext, $restoredCart->getToken());

        $cartWithErrors = $result->getCart();
        $cartWithErrors->setErrors($errors);
        $this->cartService->setCart($cartWithErrors);

        $this->eventDispatcher->dispatch(new ChannelContextRestoredEvent($customerContext, $currentContext));

        return $customerContext;
    }
}
