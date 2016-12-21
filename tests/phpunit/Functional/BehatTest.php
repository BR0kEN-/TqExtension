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
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        chdir(dirname(CONFIG_FILE));
    }

    /**
     * @param string $feature
     *   The name of file without extension and path to "features" folder.
     */
    protected function runFeature($feature)
    {
        $code = 0;
        $file = "features/$feature.feature";

        if (file_exists($file)) {
            system("../../bin/behat --no-colors $file", $code);
            self::assertSame(0, $code);
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
        $dir = "features/$group";
        $files = glob("$dir/*.feature");

        if (empty($files)) {
            self::fail(sprintf('No features exists in "%s/%s" directory.', getcwd(), $dir));
        } else {
            foreach ($files as $file) {
                $this->runFeature(str_replace(['features/', '.feature'], '', $file));
            }
        }
    }
}
