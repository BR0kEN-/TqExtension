<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

namespace Drupal\Tests\TqExtension\Utils;

use Drupal\TqExtension\Utils\Tags;

/**
 * Class TagsTest.
 *
 * @package Drupal\Tests\TqExtension\Utils
 */
class TagsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tags
     */
    private $tags;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->tags = $this->getMockForTrait(Tags::class);
    }

    /**
     * @test
     */
    public function collectTags()
    {
        $this->tags->collectTags(['JavaScript', 'WYSIWYG', 'wysiwyg:CKEditor']);

        self::assertAttributeCount(2, 'tags', $this->tags);
    }

    /**
     * @test
     */
    public function hasTag()
    {
        $this->collectTags();

        self::assertTrue($this->tags->hasTag('javascript'));
        self::assertTrue($this->tags->hasTag('wysiwyg'));

        self::assertFalse($this->tags->hasTag('JavaScript'));
        self::assertFalse($this->tags->hasTag('WYSIWYG'));
    }

    /**
     * @test
     */
    public function getTag()
    {
        $this->collectTags();

        self::assertSame('CKEditor', $this->tags->getTag('wysiwyg'));
    }
}
