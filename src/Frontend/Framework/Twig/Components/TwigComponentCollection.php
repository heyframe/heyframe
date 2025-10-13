<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig\Components;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<TwigComponent>
 */
#[Package('framework')]
class TwigComponentCollection extends Collection
{
    public function __construct(iterable $elements = [])
    {
        parent::__construct();

        foreach ($elements as $element) {
            $this->validateType($element);

            $this->set($element->getName(), $element);
        }
    }

    public function add($element): void
    {
        $this->validateType($element);
        $this->set($element->getName(), $element);
    }

    protected function getExpectedClass(): string
    {
        return TwigComponent::class;
    }
}
