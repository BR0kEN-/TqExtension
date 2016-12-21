<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

/**
 * Trait LogicalAssertion.
 *
 * @package Drupal\TqExtension\Utils
 */
trait LogicalAssertion
{
    /**
     * @param mixed $value
     * @param bool $not
     *   Negate the condition.
     */
    public static function assertion($value, $not)
    {
        $not = (bool) $not;

        if ($value) {
            if ($not) {
                // Value is found, but should not be.
                return 1;
            }
        } else {
            if (!$not) {
                // Value is not found, but should be.
                return 2;
            }
        }

        return 0;
    }
}
