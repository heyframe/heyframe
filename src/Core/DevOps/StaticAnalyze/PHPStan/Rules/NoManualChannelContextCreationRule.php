<?php

declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\Generator;
use HeyFrame\Tests\Unit\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGeneratorTest;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * This PHPStan rule prevents the manual creation of a `ChannelContext`.
 * It checks if the `ChannelContext` or any of its children are created manually.
 * Usually it should be sufficient to use the `ChannelContextFactory` or the `Generator::generateChannelContext` method.
 *
 * @internal
 *
 * @implements Rule<New_>
 */
#[Package('framework')]
class NoManualChannelContextCreationRule implements Rule
{
    /**
     * @var list<class-string>
     */
    private static array $allowedClassesWhichCanCreateChannelContext = [
        ChannelContextFactory::class,
        Generator::class,
        EntityCacheKeyGeneratorTest::class, // A bit complicated to refactor this test
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof New_) {
            return [];
        }

        $class = $node->class;
        if (!$class instanceof Name) {
            return [];
        }

        $className = $class->toString();
        if (!$this->isChannelContextOrChild($className)) {
            return [];
        }

        $currentClass = $scope->getClassReflection();
        if ($currentClass && \in_array($currentClass->getName(), self::$allowedClassesWhichCanCreateChannelContext, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Manual creation of `HeyFrame\Core\System\Channel\ChannelContext` is not allowed.')
                ->identifier('heyframe.noManualChannelContextCreation')
                ->addTip('Use `HeyFrame\Core\System\Channel\Context\ChannelContextFactory` or `HeyFrame\Core\Test\Generator::generateChannelContext` instead.')
                ->build(),
        ];
    }

    private function isChannelContextOrChild(string $className): bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $class = $this->reflectionProvider->getClass($className);
        if ($class->getName() === ChannelContext::class) {
            return true;
        }

        return $class->is(ChannelContext::class);
    }
}
