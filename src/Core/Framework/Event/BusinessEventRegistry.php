<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

class BusinessEventRegistry
{
    /**
     * @var list<class-string>
     */
    private array $classes = [];

    /**
     * @param list<class-string> $classes
     */
    public function addClasses(array $classes): void
    {
        /** @var list<class-string> */
        $classes = array_unique(array_merge($this->classes, $classes));

        $this->classes = $classes;
    }

    /**
     * @return list<class-string>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
