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
        $this->fetcher = new FetchField('users', 'language');
    }

    public function test()
    {
        self::assertSame(DRUPAL_CORE > 7 ? 'en' : '', $this->fetcher->condition('uid', 1)->execute());
    }
}
