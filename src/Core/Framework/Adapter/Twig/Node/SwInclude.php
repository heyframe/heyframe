<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Node;

use HeyFrame\Core\Framework\Adapter\Twig\Extension\NodeExtension;
use HeyFrame\Core\Framework\Log\Package;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\IncludeNode;

/**
 * @internal
 */
#[Package('framework')]
#[YieldReady]
class SwInclude extends IncludeNode
{
    protected function addGetTemplate(Compiler $compiler): void
    {
        $compiler
            ->write("((function () use (\$context, \$blocks) {\n")
            ->indent()
                ->write('$finder = $this->env->getExtension(\'' . NodeExtension::class . '\')->getFinder();')->raw("\n\n")
                ->write('$includeTemplate = $finder->find(')
                        ->subcompile($this->getNode('expr'))
                ->raw(");\n\n")
                ->write('return $this->load(')
                    ->raw('$includeTemplate ?? null, ')
                    ->repr($this->getTemplateLine())
                ->raw(");\n")
            ->outdent()
            ->write('})())');
    }
}
