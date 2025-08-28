<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Customer\DataAbstractionLayer\CustomerIndexingMessage;
use HeyFrame\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Api\Context\ChannelApiSource;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelException;
use HeyFrame\Core\System\Channel\Context\ChannelContextRestorer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('after-sales')]
class CustomerFlowEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ChannelContextRestorer $restorer,
        private readonly EntityIndexer $customerIndexer,
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_WRITTEN_EVENT => 'onCustomerWritten',
        ];
    }

    public function onCustomerWritten(EntityWrittenEvent $event): void
    {
        $context = $event->getContext();
        if ($context->getSource() instanceof ChannelApiSource) {
            return;
        }

        $payloads = $event->getPayloads();

        foreach ($payloads as $payload) {
            try {
                if (!empty($payload['createdAt'])) {
                    $this->dispatchCustomerRegisterEvent($payload['id'], $event);
                }
            } catch (ChannelException $exception) {
                if ($exception->getErrorCode() !== ChannelException::CHANNEL_LANGUAGE_NOT_AVAILABLE_EXCEPTION) {
                    throw $exception;
                }

                if ($context->getSource() instanceof AdminApiSource && \is_string($payload['id'])) {
                    $this->connection->delete('customer', ['id' => Uuid::fromHexToBytes($payload['id'])]);
                }

                throw $exception;
            }
        }
    }

    private function dispatchCustomerRegisterEvent(string $customerId, EntityWrittenEvent $event): void
    {
        $context = $event->getContext();

        $channelContext = $this->restorer->restoreByCustomer($customerId, $context);
        $message = new CustomerIndexingMessage([$customerId]);
        $this->customerIndexer->handle($message);
        if (!$customer = $channelContext->getCustomer()) {
            return;
        }

        $customerCreated = new CustomerRegisterEvent(
            $channelContext,
            $customer
        );

        $this->dispatcher->dispatch($customerCreated);
    }
}
