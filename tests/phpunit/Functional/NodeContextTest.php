<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\Tests\TqExtension\Functional;

/**
 * Class NodeContextTest.
 *
 * @package Drupal\Tests\TqExtension\Functional
 */
class NodeContextTest extends BehatTest
{
    public function test()
    {
        $this->runFeaturesGroup('NodeContext');
    }
}
