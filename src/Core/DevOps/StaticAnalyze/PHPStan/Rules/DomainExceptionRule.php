<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Configuration;
use HeyFrame\Core\Framework\Adapter\Cache\ReverseProxy\FastlyReverseProxyGateway;
use HeyFrame\Core\Framework\Adapter\Cache\ReverseProxy\ReverseProxyException;
use HeyFrame\Core\Framework\Adapter\Cache\ReverseProxy\VarnishReverseProxyGateway;
use HeyFrame\Core\Framework\Framework;
use HeyFrame\Core\Framework\FrameworkException;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Kernel;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 *
 * @implements Rule<Throw_>
 */
#[Package('framework')]
class DomainExceptionRule implements Rule
{
    use InTestClassTrait;

    /**
     * @var list<string>
     */
    private const VALID_SUB_DOMAINS = [
        'Cart',
        'Payment',
        'Order',
    ];

    /**
     * @var list<string>
     */
    private const EXCLUDED_NAMESPACES = [
        'HeyFrame\Core\DevOps\StaticAnalyze\\',
    ];

    /**
     * @var array<string, string>
     */
    private const REMAPPED_DOMAINS = [
        Kernel::class => FrameworkException::class,
        Framework::class => FrameworkException::class,
        VarnishReverseProxyGateway::class => ReverseProxyException::class,
        FastlyReverseProxyGateway::class => ReverseProxyException::class,
    ];

    /**
     * @var array<string>
     */
    private array $validExceptionClasses;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly Configuration $configuration,
    ) {
        // see src/Core/DevOps/StaticAnalyze/PHPStan/extension.neon for the default config
        $this->validExceptionClasses = $this->configuration->getAllowedNonDomainExceptions();
    }

    public function getNodeType(): string
    {
        return Throw_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isInTestClass($scope) || !$scope->isInClass()) {
            return [];
        }

        if (!$node instanceof Throw_) {
            return [];
        }

        if ($node->expr instanceof StaticCall) {
            return $this->validateDomainExceptionClass($node->expr, $scope);
        }

        if (!$node->expr instanceof New_) {
            return [];
        }

        $namespace = $scope->getNamespace();
        if (\is_string($namespace)) {
            foreach (self::EXCLUDED_NAMESPACES as $excludedNamespace) {
                if (\str_starts_with($namespace, $excludedNamespace)) {
                    return [];
                }
            }
        }

        \assert($node->expr->class instanceof Name);
        $exceptionClass = $node->expr->class->toString();

        if (\in_array($exceptionClass, $this->validExceptionClasses, true)) {
            return [];
        }

        // Allow InvalidArgumentException in commands to validate user input
        if ($scope->getClassReflection()->is(Command::class) && $exceptionClass === 'InvalidArgumentException') {
            return [];
        }

        return [
            RuleErrorBuilder::message('Throwing new exceptions within classes are not allowed. Please use domain exception pattern. See https://github.com/heyframe/platform/blob/v6.4.20.0/adr/2022-02-24-domain-exceptions.md')
                ->identifier('heyframe.domainException')
                ->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function validateDomainExceptionClass(StaticCall $node, Scope $scope): array
    {
        \assert($node->class instanceof Name);
        $exceptionClass = $node->class->toString();

        if (!\str_starts_with($exceptionClass, 'HeyFrame\\Core\\')) {
            return [];
        }

        $exception = $this->reflectionProvider->getClass($exceptionClass);
        if (!$exception->is(HttpException::class)) {
            return [
                RuleErrorBuilder::message(\sprintf('Domain exception class %s has to extend the \HeyFrame\Core\Framework\HttpException class', $exceptionClass))
                    ->identifier('heyframe.domainException')
                    ->build(),
            ];
        }

        $reflection = $scope->getClassReflection();
        \assert($reflection !== null);
        if (!\str_starts_with($reflection->getName(), 'HeyFrame\\Core\\')) {
            return [];
        }

        if ($this->isRemapped($reflection->getName(), $exceptionClass)) {
            return [];
        }

        $parts = \explode('\\', $reflection->getName());

        $domain = $parts[2] ?? '';
        $sub = $parts[3] ?? '';

        $acceptedClasses = [
            \sprintf('HeyFrame\\Core\\%s\\%s\\%sException', $domain, $sub, $sub),
            \sprintf('HeyFrame\\Core\\%s\\%sException', $domain, $domain),
        ];

        foreach ($acceptedClasses as $expected) {
            if ($exceptionClass === $expected || $exception->is($expected)) {
                return [];
            }
        }

        // Is it in a subdomain?
        if (isset($parts[5]) && \in_array($parts[4], self::VALID_SUB_DOMAINS, true)) {
            $expectedSub = \sprintf('\\%s\\%sException', $parts[4], $parts[4]);
            if (\str_starts_with(strrev($exceptionClass), strrev($expectedSub))) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(\sprintf('Expected domain exception class %s, got %s', $acceptedClasses[0], $exceptionClass))
                ->identifier('heyframe.domainException')
                ->build(),
        ];
    }

    private function isRemapped(string $source, string $exceptionClass): bool
    {
        if (!\array_key_exists($source, self::REMAPPED_DOMAINS)) {
            return false;
        }

        return self::REMAPPED_DOMAINS[$source] === $exceptionClass;
    }
}
