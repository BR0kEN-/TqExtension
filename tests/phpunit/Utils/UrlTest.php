<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\Tests\TqExtension\Utils;

use Drupal\TqExtension\Utils\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param string $baseUrl
     * @param string $path
     * @param string $expected
     */
    public function test($baseUrl, $path, $expected)
    {
        static::assertSame($expected, (string) new Url($baseUrl, $path));
    }

    public function dataProvider()
    {
        return [
            // Relative URL.
            [
                'http://example.com/',
                'path/to/page',
                'http://example.com/path/to/page',
            ],
            // Relative URL with facing slash.
            [
                'http://example.com/',
                '/path/to/page',
                'http://example.com/path/to/page',
            ],
            // Absolute URL.
            [
                'http://example.com/',
                'http://example.com/path/to/page',
                'http://example.com/path/to/page',
            ],
            // User, password, path.
            [
                'http://user:pass@example.com',
                'path/to/page',
                'http://user:pass@example.com/path/to/page',
            ],
            // User, password, path, query, fragment.
            [
                'http://user:pass@example.com',
                'path/to/page?test=value#fragment',
                'http://user:pass@example.com/path/to/page?test=value#fragment',
            ],
            // User, password, port, path, query, fragment.
            [
                'http://user:pass@example.com:8080',
                'path/to/page?test=value#fragment',
                'http://user:pass@example.com:8080/path/to/page?test=value#fragment',
            ],
        ];
    }
}
