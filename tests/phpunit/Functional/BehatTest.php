<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\Tests\TqExtension\Functional;

/**
 * Class BehatTest.
 *
 * @package Drupal\Tests\TqExtension\Functional
 */
abstract class BehatTest extends \PHPUnit_Framework_TestCase
{
    const FEATURE_PATH = 'tests/behat/features';

    /**
     * @param string $feature
     *   The name of file without extension and path to "features" folder.
     */
    protected function runFeature($feature)
    {
        $code = 0;
        $feature .= '.feature';
        $file = static::FEATURE_PATH . "/$feature";

        if (file_exists($file)) {
            system(sprintf(
                "cd %s && ../../bin/behat --no-colors %s/$feature",
                dirname(static::FEATURE_PATH),
                basename(static::FEATURE_PATH)
            ), $code);
            self::assertTrue(0 === $code);
        } else {
            self::fail(sprintf('File "%s/%s" does not exists!', getcwd(), $file));
        }
    }

    /**
     * @param string $group
     *   The name of directory inside of "features" folder.
     */
    protected function runFeaturesGroup($group)
    {
        $dir = static::FEATURE_PATH . "/$group";
        $files = glob("$dir/*.feature");

        if (empty($files)) {
            self::fail(sprintf('No features exists in "%s/%s" directory.', getcwd(), $dir));
        } else {
            foreach ($files as $file) {
                $this->runFeature(str_replace([static::FEATURE_PATH, '.feature'], '', $file));
            }
        }
    }
}
