<?php declare(strict_types=1);

namespace SynergiTech\ChromePDF\Test\Constraint;

use SplObjectStorage;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * Constraint that asserts that the array contains a nested key
 * with a value that matches the specified constraint
 */
final class ArrayHasKeyValue extends Constraint
{
    /**
     * @var array
     */
    private $key;

    /**
     * @var Constraint
     */
    private $valueConstraint;

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(array $key, ?Constraint $valueConstraint = null)
    {
        $this->key = $key;
        $this->valueConstraint = $valueConstraint;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        $str = 'contains ' . implode("->", $this->key);
        if ($this->valueConstraint) {
            $str .= ' and the value ' . $this->valueConstraint->toString();
        }
        return $str;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        $array = $other;
        $parts = $this->key;
        $last = array_pop($parts);

        foreach ($parts as $part) {
            if (!isset($array[$part]) || !is_array($array[$part])) {
                return false;
            }
            $array = $array[$part];
        }

        if (!isset($array[$last])) {
            return false;
        }

        if ($this->valueConstraint) {
            $constraintPassed = $this->valueConstraint->evaluate($array[$last], '', true);
            if (!$constraintPassed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            'an array %s',
            $this->toString()
        );
    }
}
