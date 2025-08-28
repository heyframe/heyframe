<?php declare(strict_types=1);

namespace HeyFrame\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use HeyFrame\Core\Framework\Log\Package;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * This rule checks if attribute classes are declared final.
 *
 * @implements Rule<Class_>
 *
 * @internal
 */
#[Package('framework')]
class AttributeFinalRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Class_) {
            return [];
        }

        // Check if class has the #[\Attribute] attribute
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if ($attribute->name->toString() === 'Attribute') {
                    if (!$node->isFinal()) {
                        return [
                            RuleErrorBuilder::message('Attribute classes must be declared final.')
                                ->identifier('heyframe.attributeNotFinal')
                                ->build(),
                        ];
                    }
                }
            }
        }

        return [];
    }
}
