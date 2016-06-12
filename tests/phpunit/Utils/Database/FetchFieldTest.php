<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\Tests\TqExtension\Utils\Database;

use Drupal\TqExtension\Utils\Database\FetchField;

/**
 * Class FetchFieldTest.
 *
 * @package Drupal\Tests\TqExtension\Utils\Database
 */
class FetchFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FetchField
     */
    private $fetcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->fetcher = new FetchField('variable', 'value');
    }

    public function test()
    {
        self::assertSame(
            variable_get('drupal_private_key'),
            unserialize($this->fetcher->condition('name', 'drupal_private_key')->execute())
        );
    }
}
