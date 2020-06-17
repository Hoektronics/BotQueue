<?php

namespace Tests\Helpers;

use PHPUnit\Framework\Constraint\Constraint;

class SubsetConstraint extends Constraint
{
    /**
     * @var array
     */
    private $expected;

    public function __construct($expected)
    {
        $this->expected = $expected;
    }

    protected function matches($other): bool
    {
        $diff = array_diff($this->expected, $other);

        return count($diff) == 0;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'has a subset of '.$this->exporter()->export($this->expected);
    }
}
