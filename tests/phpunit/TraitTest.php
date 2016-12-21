<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\Tests\TqExtension;

/**
 * Class TraitTest.
 *
 * @package Drupal\Tests\TqExtension
 */
abstract class TraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fully-qualified namespace of trait.
     */
    const FQN = '';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|object
     */
    protected $target;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = $this->getMockForTrait(static::FQN);
    }
}
