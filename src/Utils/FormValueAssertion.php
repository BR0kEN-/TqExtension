<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

// Helpers.
use Behat\Mink\Element\NodeElement;

class FormValueAssertion
{
    /**
     * @var RawTqContext
     */
    private $context;
    /**
     * @var string
     *   Field selector.
     */
    private $selector = '';
    /**
     * @var NodeElement
     *   Found element.
     */
    private $element;
    /**
     * @var string
     *   Expected value.
     */
    private $expected = '';
    /**
     * @var string
     *   Field element value.
     */
    private $value = '';
    /**
     * @var string
     *   Tag name of found element.
     */
    private $tag = '';
    /**
     * @var bool
     *   Negate the condition.
     */
    private $not = false;

    /**
     * @param RawTqContext $context
     *   Behat context.
     * @param string $selector
     *   Field selector.
     * @param bool $not
     *   Negate the condition.
     * @param string $expected
     *   Expected value.
     */
    public function __construct(RawTqContext $context, $selector, $not, $expected = '')
    {
        $this->not = (bool) $not;
        $this->context = $context;
        $this->selector = $selector;
        $this->expected = $expected;

        $this->element = $this->context->element('field', $selector);
        $this->value = $this->element->getValue();
        $this->tag = $this->element->getTagName();
    }

    /**
     * Check value in inputs and text areas.
     */
    public function textual()
    {
        $this->restrictElements([
            'textarea' => [],
            'input' => [],
        ]);

        $this->context->debug([
            "Expected: $this->expected",
            "Value: $this->value",
            "Tag: $this->tag",
        ]);

        $this->assert(trim($this->expected) === $this->value);
    }

    /**
     * Ensure option is selected.
     */
    public function selectable()
    {
        $this->restrictElements(['select' => []]);
        $data = [$this->value, $this->element->find('xpath', "//option[@value='$this->value']")->getText()];

        $this->context->debug([
            "Expected: $this->expected",
            "Value: %s",
            "Tag: $this->tag",
        ], implode(' => ', $data));

        $this->assert(in_array($this->expected, $data), 'selected');
    }

    /**
     * Ensure that checkbox/radio button is checked.
     */
    public function checkable()
    {
        $this->restrictElements(['input' => ['radio', 'checkbox']]);

        if (!in_array($this->element->getAttribute('type'), ['radio', 'checkbox'])) {
            throw new \RuntimeException('Element cannot be checked.');
        }

        $this->context->debug([$this->element->getOuterHtml()]);
        $this->assert($this->element->isChecked(), 'checked');
    }

    /**
     * @param string[] $allowedElements
     *   Element machine names.
     */
    private function restrictElements(array $allowedElements)
    {
        // Match element tag with allowed.
        if (!isset($allowedElements[$this->tag])) {
            throw new \RuntimeException("Tag is not allowed: $this->tag.");
        }

        $types = $allowedElements[$this->tag];

        // Restrict by types only if they are specified.
        if (!empty($types)) {
            $type = $this->element->getAttribute('type');

            if (!in_array($type, $types)) {
                throw new \RuntimeException(sprintf('Type "%s" is not allowed for "%s" tag', $type, $this->tag));
            }
        }
    }

    /**
     * @param bool $value
     *   Value for checking.
     * @param string $word
     *   A word for default message (e.g. "checked", "selected", etc).
     *
     * @throws \Exception
     */
    private function assert($value, $word = '')
    {
        if ($value) {
            if ($this->not) {
                throw new \Exception(
                    empty($word)
                    ? 'Field contain a value, but should not.'
                    : "Element is $word, but should not be."
                );
            }
        } else {
            if (!$this->not) {
                throw new \Exception(
                    empty($word)
                    ? 'Field does not contain a value.'
                    : "Element is not $word."
                );
            }
        }
    }
}
