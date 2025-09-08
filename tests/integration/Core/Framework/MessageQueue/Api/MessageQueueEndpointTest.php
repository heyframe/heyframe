<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\MessageQueue\Api;

use HeyFrame\Core\Framework\Increment\IncrementGatewayRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class MessageQueueEndpointTest extends TestCase
{
    use AdminFunctionalTestBehaviour;
    use IntegrationTestBehaviour;

    public function testEndpoint(): void
    {
        $gatewayRegistry = static::getContainer()->get('heyframe.increment.gateway.registry');

        $gateway = $gatewayRegistry->get(IncrementGatewayRegistry::MESSAGE_QUEUE_POOL);

        $gateway->reset('message_queue_stats', 'foo');
        $gateway->reset('message_queue_stats', 'bar');
        $gateway->increment('message_queue_stats', 'foo');
        $gateway->increment('message_queue_stats', 'bar');
        $gateway->increment('message_queue_stats', 'bar');

        $url = '/api/_info/queue.json';
        $client = $this->getBrowser();
        $client->request('GET', $url);

        static::assertSame(200, $client->getResponse()->getStatusCode());

        /** @var string $response */
        $response = $client->getResponse()->getContent();

        $entries = json_decode($response, true, 512, \JSON_THROW_ON_ERROR);

        $mapped = [];
        foreach ($entries as $entry) {
            $mapped[$entry['name']] = $entry['size'];
        }

        static::assertArrayHasKey('foo', $mapped);
        static::assertSame(1, $mapped['foo']);
        static::assertArrayHasKey('bar', $mapped);
        static::assertSame(2, $mapped['bar']);
    }
}
