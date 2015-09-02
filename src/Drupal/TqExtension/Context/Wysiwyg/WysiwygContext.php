<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Wysiwyg;

// Helpers.
use Behat\Gherkin\Node\TableNode;

/**
 * @todo Add TinyMCE support.
 */
class WysiwygContext extends RawWysiwygContext
{
    /**
     * @param string $selector
     *
     * @Given /^(?:|I )work with "([^"]*)" WYSIWYG editor$/
     *
     * @wysiwyg @javascript
     */
    public function workWithEditor($selector)
    {
        $this->wysiwyg = $selector;
    }

    /**
     * @AfterScenario @wysiwyg
     */
    public function unsetWysiwyg()
    {
        $this->wysiwyg = '';
    }

    /**
     * @param string $text
     * @param string $selector
     *
     * @throws \Exception
     *   When editor was not found.
     *
     * @Given /^(?:|I )fill "([^"]*)" in (?:|"([^"]*)" )WYSIWYG editor$/
     *
     * @wysiwyg @javascript
     */
    public function setData($text, $selector = '')
    {
        $this->executeWysiwygMethod(__FUNCTION__, [$text], $selector);
    }

    /**
     * @param string $text
     * @param string $selector
     *
     * @throws \Exception
     *   When editor was not found.
     *
     * @When /^(?:|I )type "([^"]*)" in (?:|"([^"]*)" )WYSIWYG editor$/
     *
     * @wysiwyg @javascript
     */
    public function insertText($text, $selector = '')
    {
        $this->executeWysiwygMethod(__FUNCTION__, [$text], $selector);
    }

    /**
     * @param string $condition
     * @param string $text
     * @param string $selector
     *
     * @throws \Exception
     *   When editor was not found.
     * @throws \RuntimeException
     *
     * @Then /^(?:|I )should(| not) see "([^"]*)" in (?:|"([^"]*)" )WYSIWYG editor$/
     *
     * @wysiwyg @javascript
     */
    public function getData($condition, $text, $selector = '')
    {
        $condition = (bool) $condition;

        if (strpos($this->executeWysiwygMethod(__FUNCTION__, '', $selector), $text) === $condition) {
            throw new \RuntimeException(sprintf(
                'The text "%s" was %s found in the "%s" WYSIWYG editor.',
                $text,
                $condition ? '' : 'not',
                $this->selector
            ));
        }
    }

    /**
     * @param TableNode $fields
     *   | Editor locator | Value |
     *
     * @Then /^(?:|I )fill in following WYSIWYG editors:$/
     *
     * @wysiwyg @javascript
     */
    public function fillInMultipleEditors(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $editor => $value) {
            $this->setData($value, $editor);
        }
    }
}
