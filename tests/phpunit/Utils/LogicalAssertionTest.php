<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

namespace Drupal\Tests\TqExtension\Utils;

use Drupal\TqExtension\Utils\LogicalAssertion;

/**
 * Class LogicalAssertionTest.
 *
 * @package Drupal\Tests\TqExtension\Utils
 *
 * @coversDefaultClass \Drupal\TqExtension\Utils\LogicalAssertion
 */
class LogicalAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogicalAssertion
     */
    private $logicalAssertion;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->logicalAssertion = $this->getMockForTrait(LogicalAssertion::class);
    }

    /**
     * @covers ::assertion
     * @dataProvider dataset
     *
     * @param mixed $value
     * @param bool $negate
     * @param int $expected
     */
    public function testAssertion($value, $negate, $expected)
    {
        $this->assertSame($expected, $this->logicalAssertion->assertion($value, $negate));
    }

    /**
     * @dataProvider
     */
    public function dataset()
    {
        return [
            // Value is equalled to "true" and not negated.
            [1, false, 0],
            // Value is equalled to "true" and negated.
            [1, true, 1],
            // Value is equalled to "false" and not negated.
            [0, false, 2],
            // Value is equalled to "false" and negated.
            [0, true, 0],
        ];
    }
}
