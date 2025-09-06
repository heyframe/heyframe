<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Flow\Aggregate\FlowTemplate\FlowTemplateDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('after-sales')]
class Migration1659257296GenerateFlowTemplateDataFromEventAction extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1659257296;
    }

    public function update(Connection $connection): void
    {
        $existingFlowTemplates = $this->getExistingFlowTemplates($connection);

        $eventActions = $this->getDefaultEventActions();

        $flowTemplates = [];
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        foreach ($eventActions as $eventAction) {
            $templateName = $this->getEventFullNameByEventName($eventAction['event_name']);

            if (\in_array($templateName, $existingFlowTemplates, true)) {
                continue;
            }

            $flowTemplate = [
                'id' => Uuid::randomBytes(),
                'name' => $templateName,
                'created_at' => $createdAt,
            ];

            $flowTemplate['config'] = json_encode([
                'eventName' => $eventAction['event_name'],
                'description' => null,
                'customFields' => null,
            ], \JSON_THROW_ON_ERROR);

            $flowTemplates[] = $flowTemplate;
        }

        $queue = new MultiInsertQueryQueue($connection);

        foreach ($flowTemplates as $flowTemplate) {
            $queue->addInsert(FlowTemplateDefinition::ENTITY_NAME, $flowTemplate);
        }

        $queue->execute();
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param array<string, string> $mailTemplateData
     *
     * @return array<string, mixed>
     */
    private function getConfigData(array $mailTemplateData): array
    {
        $config = [];
        foreach ($mailTemplateData as $key => $value) {
            $key = lcfirst(implode('', array_map('ucfirst', explode('_', $key))));
            $config[$key] = $value;
        }

        $config['recipient'] = ['data' => [], 'type' => 'default'];

        return $config;
    }

    /**
     * @return array<array<string, string>>
     */
    private function getDefaultEventActions(): array
    {
        return [
            [
                'event_name' => 'checkout.order.placed',
            ],
            [
                'event_name' => 'checkout.customer.register',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.paid_partially',
            ],
            [
                'event_name' => 'state_enter.order_delivery.state.returned',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.refunded',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.paid',
            ],
            [
                'event_name' => 'state_enter.order.state.in_progress',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.refunded_partially',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.open',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.cancelled',
            ],
            [
                'event_name' => 'state_enter.order.state.cancelled',
            ],
            [
                'event_name' => 'state_enter.order_transaction.state.reminded',
            ],
            [
                'event_name' => 'state_enter.order.state.completed',
            ],
        ];
    }

    private function getEventFullNameByEventName(string $eventName): string
    {
        $listEventName = [
            'checkout.customer.before.login' => 'Customer has requested login',
            'checkout.customer.changed-payment-method' => 'Customer changes payment',
            'checkout.customer.deleted' => 'Customer account deleted',
            'checkout.customer.double_opt_in_guest_order' => 'Guest account registered with double opt-in',
            'checkout.customer.double_opt_in_registration' => 'Customer account registered with double opt-in',
            'checkout.customer.login' => 'Customer logs on',
            'checkout.customer.logout' => 'Customer logs off',
            'checkout.customer.register' => 'Customer account registered',
            'checkout.order.placed' => 'Order placed',
            'state_enter.order.state.cancelled' => 'Order enters status cancelled',
            'state_enter.order.state.completed' => 'Order enters status completed',
            'state_enter.order.state.in_progress' => 'Order enters status in progress',
            'state_enter.order.state.open' => 'Order enters status open',
            'state_enter.order_transaction.state.authorized' => 'Payment enters status authorised',
            'state_enter.order_transaction.state.cancelled' => 'Payment enters status cancelled',
            'state_enter.order_transaction.state.chargeback' => 'Payment enters status refunded',
            'state_enter.order_transaction.state.failed' => 'Payment enters status failed',
            'state_enter.order_transaction.state.in_progress' => 'Payment enters status in progress',
            'state_enter.order_transaction.state.open' => 'Payment enters status open',
            'state_enter.order_transaction.state.paid' => 'Payment enters status paid',
            'state_enter.order_transaction.state.paid_partially' => 'Payment enters status partially paid',
            'state_enter.order_transaction.state.refunded' => 'Payment enters status refunded',
            'state_enter.order_transaction.state.refunded_partially' => 'Payment enters status partially refunded',
            'state_enter.order_transaction.state.reminded' => 'Payment enters status reminder sent',
            'state_leave.order.state.cancelled' => 'Order leaves status cancelled',
            'state_leave.order.state.completed' => 'Order leaves status completed',
            'state_leave.order.state.in_progress' => 'Order leaves status in progress',
            'state_leave.order.state.open' => 'Order leaves status open',
            'state_leave.order_transaction.state.authorized' => 'Payment leaves status authorised',
            'state_leave.order_transaction.state.cancelled' => 'Payment leaves status cancelled',
            'state_leave.order_transaction.state.chargeback' => 'Payment leaves status refunded',
            'state_leave.order_transaction.state.failed' => 'Payment leaves status failed',
            'state_leave.order_transaction.state.in_progress' => 'Payment leaves status in progress',
            'state_leave.order_transaction.state.open' => 'Payment leaves status open',
            'state_leave.order_transaction.state.paid' => 'Payment leaves status paid',
            'state_leave.order_transaction.state.paid_partially' => 'Payment leaves status partially paid',
            'state_leave.order_transaction.state.refunded' => 'Payment leaves status refunded',
            'state_leave.order_transaction.state.refunded_partially' => 'Payment leaves status partially refunded',
            'state_leave.order_transaction.state.reminded' => 'Payment leaves status reminder sent',
        ];

        if (\array_key_exists($eventName, $listEventName)) {
            return $listEventName[$eventName];
        }

        return $eventName;
    }

    /**
     * @return string[]
     */
    private function getExistingFlowTemplates(Connection $connection): array
    {
        /** @var string[] $flowTemplates */
        $flowTemplates = $connection->fetchFirstColumn('SELECT DISTINCT name FROM flow_template');

        return array_unique(array_filter($flowTemplates));
    }
}
