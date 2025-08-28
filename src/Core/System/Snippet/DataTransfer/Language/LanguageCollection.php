<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\DataTransfer\Language;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @internal
 *
 * @extends Collection<Language>
 */
#[Package('discovery')]
class LanguageCollection extends Collection
{
    /**
     * @param list<Language> $elements
     */
    public function __construct(
        iterable $elements = [],
    ) {
        parent::__construct($elements);

        foreach ($elements as $element) {
            $this->set($element->locale, $element);
        }
    }

    /**
     * @param array-key|null $key
     * @param Language $element
     */
    public function set($key, $element): void
    {
        $this->validateType($element);

        $this->elements[$key] = $element;
    }

    protected function getExpectedClass(): string
    {
        return Language::class;
    }
}
