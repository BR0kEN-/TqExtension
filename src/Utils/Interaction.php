<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

// Helpers.
use Symfony\Component\Console\Output\ConsoleOutput;

trait Interaction
{
    /**
     * @var string[]
     */
    private static $tags = [];
    /**
     * @var ConsoleOutput
     */
    private static $consoleOutput;

    /**
     * @param array $strings
     */
    public static function debug(array $strings)
    {
        if (self::hasTag('debug')) {
            array_unshift($strings, '<question>DEBUG:</question>');
            call_user_func_array(['self', 'consoleOutput'], array_merge(
                ['comment', 4, $strings],
                array_slice(func_get_args(), 1))
            );
        }
    }

    /**
     * @param string $type
     *   Could be "comment", "info", "question" or "error".
     * @param int $indent
     *   Number of spaces.
     * @param array $strings
     *   Paragraphs.
     * @param string ...
     *   Any replacement argument for "sprintf()".
     *
     * @link http://symfony.com/doc/current/components/console/introduction.html#coloring-the-output
     */
    public static function consoleOutput($type, $indent, array $strings)
    {
        if (!isset(self::$consoleOutput)) {
            self::$consoleOutput = new ConsoleOutput;
        }

        $indent = implode(' ', array_fill_keys(range(0, $indent), ''));
        $arguments = func_get_args();
        // Remove the "indent" and "strings" parameters from an array with arguments.
        unset($arguments[1], $arguments[2]);

        // Replace the "type" argument by message that will be printed.
        $arguments[0] = "$indent<$type>" . implode(PHP_EOL . "</$type>$indent<$type>", $strings) . "</$type>";

        self::$consoleOutput->writeln(call_user_func_array('sprintf', $arguments));
    }

    /**
     * @param string $tag
     *   The name of tag.
     *
     * @return bool
     *   Indicates the state of tag existence in a feature and/or scenario.
     */
    public static function hasTag($tag)
    {
        return isset(self::$tags[$tag]);
    }

    /**
     * @param string $tag
     *   The name of tag.
     * @param string $default
     *   Default value, if tag does not exist or empty.
     *
     * @return string
     *   Tag value or an empty string.
     */
    public static function getTag($tag, $default = '')
    {
        return empty(self::$tags[$tag]) ? $default : self::$tags[$tag];
    }

    /**
     * @param string[] $tags
     */
    public static function collectTags(array $tags)
    {
        foreach ($tags as $tag) {
            $values = explode(':', $tag);
            $value = '';

            if (count($values) > 1) {
                list($tag, $value) = $values;
            }

            self::$tags[strtolower($tag)] = $value;
        }
    }
}
