<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use HeyFrame\Core\Content\Product\Channel\Listing\Processor\CompositeListingProcessor;
use HeyFrame\Core\Framework\Log\Package;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @internal
 *
 * @implements Rule<InClassNode>
 */
#[Package('framework')]
class AbstractClassUsageRule implements Rule
{
    use InTestClassTrait;

    private const SKIP = [
        CompositeListingProcessor::class,
    ];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isInTestClass($scope) || !$scope->isInClass()) {
            return [];
        }

        $class = $scope->getClassReflection();
        if (!$class->hasConstructor()) {
            return [];
        }

        $constructors = $class->getConstructor()->getVariants();
        $errors = [];
        foreach ($constructors as $constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                foreach ($parameter->getType()->getObjectClassReflections() as $parameterClass) {
                    if (\in_array($parameterClass->getName(), self::SKIP, true)) {
                        continue;
                    }

                    if (!$parameterClass->hasMethod('getDecorated')) {
                        continue;
                    }

                    if ($parameterClass->isAbstract()) {
                        continue;
                    }

                    $errors[] = RuleErrorBuilder::message(\sprintf(
                        'Decoration error: Parameter %s $%s of %s is using the decoration pattern, but non-abstract constructor parameter is used.',
                        $parameterClass->getName(),
                        $parameter->getName(),
                        $class->getName()
                    ))
                        ->identifier('heyframe.decorationPattern')
                        ->build();
                }
            }
        }

        return $errors;
    }
}
