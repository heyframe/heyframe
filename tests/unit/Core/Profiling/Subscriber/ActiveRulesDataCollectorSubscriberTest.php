<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Profiling\Subscriber;

use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Routing\Event\ChannelContextResolvedEvent;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Profiling\Subscriber\ActiveRulesDataCollectorSubscriber;
use HeyFrame\Core\System\Channel\ChannelContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(ActiveRulesDataCollectorSubscriber::class)]
class ActiveRulesDataCollectorSubscriberTest extends TestCase
{
    public function testEvents(): void
    {
        static::assertSame(
            [
                ChannelContextResolvedEvent::class => 'onContextResolved',
            ],
            ActiveRulesDataCollectorSubscriber::getSubscribedEvents()
        );
    }

    public function testDataCollection(): void
    {
        $ruleId = Uuid::randomHex();

        $channelContext = $this->createMock(ChannelContext::class);
        $context = new Context(new SystemSource(), [$ruleId]);
        $channelContext->method('getContext')->willReturn($context);
        $event = new ChannelContextResolvedEvent($channelContext, Uuid::randomHex());

        $activeRule = new RuleEntity();
        $activeRule->setId($ruleId);
        $activeRule->setName('Demo rule');
        $activeRule->setPriority(100);

        $ruleRepository = $this->createMock(EntityRepository::class);
        $ruleRepository
            ->method('search')
            ->willReturn(new EntitySearchResult(
                'rule',
                1,
                new RuleCollection([$activeRule]),
                null,
                new Criteria(),
                Context::createDefaultContext()
            ));

        $subscriber = new ActiveRulesDataCollectorSubscriber($ruleRepository);
        $subscriber->onContextResolved($event);
        $subscriber->collect(new Request(), new Response());

        $data = $subscriber->getData();

        static::assertSame(1, $subscriber->getMatchingRuleCount());
        static::assertArrayHasKey($ruleId, $data);

        $rule = $data[$ruleId];
        static::assertInstanceOf(RuleEntity::class, $rule);
        static::assertSame(100, $rule->getPriority());
        static::assertSame('Demo rule', $rule->getName());

        $subscriber->reset();

        static::assertSame(0, $subscriber->getMatchingRuleCount());
    }

    public function testEmptyRuleIds(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);
        $context = new Context(new SystemSource(), []);
        $channelContext->method('getContext')->willReturn($context);
        $event = new ChannelContextResolvedEvent($channelContext, Uuid::randomHex());

        $ruleRepository = $this->createMock(EntityRepository::class);
        $ruleRepository
            ->expects($this->never())
            ->method('search');

        $subscriber = new ActiveRulesDataCollectorSubscriber($ruleRepository);
        $subscriber->onContextResolved($event);
        $subscriber->collect(new Request(), new Response());
    }

    public function testTemplate(): void
    {
        static::assertSame('@Profiling/Collector/rules.html.twig', ActiveRulesDataCollectorSubscriber::getTemplate());
    }
}
