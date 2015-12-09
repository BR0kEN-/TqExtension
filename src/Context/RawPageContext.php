<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

// Contexts.
use Drupal\DrupalExtension\Context\RawDrupalContext;

// Exceptions.
use WebDriver\Exception\NoSuchElement;

// Helpers.
use Behat\Mink\Element\NodeElement;

class RawPageContext extends RawDrupalContext
{
    /**
     * @var NodeElement
     */
    private static $workingElement;

    /**
     * @return NodeElement
     */
    public function getWorkingElement()
    {
        if (null === self::$workingElement) {
            $this->setWorkingElement($this->getBodyElement());
        }

        return self::$workingElement;
    }

    /**
     * @param NodeElement $element
     */
    public function setWorkingElement(NodeElement $element)
    {
        self::$workingElement = $element;
    }

    public function unsetWorkingElement()
    {
        self::$workingElement = null;
    }

    /**
     * @param string $selector
     *
     * @return NodeElement|null
     */
    public function findByCss($selector)
    {
        return $this->getWorkingElement()
            ->find(empty($this->getDrupalParameter('region_map')[$selector]) ? 'css' : 'region', $selector);
    }

    /**
     * @param string $selector
     *
     * @return NodeElement|null
     */
    public function findField($selector)
    {
        $selector = ltrim($selector, '#');
        $element = $this->getWorkingElement();
        $field = $element->findField($selector);

        if (null === $field) {
            foreach ($this->findLabels($selector) as $forAttribute => $label) {
                // We trying to find an ID with "-upload" suffix, because some
                // image inputs in Drupal are suffixed by it.
                foreach ([$forAttribute, "$forAttribute-upload"] as $elementID) {
                    $field = $element->findById($elementID);

                    if (null !== $field) {
                        return $field;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * @param string $selector
     *
     * @return NodeElement
     */
    public function findButton($selector)
    {
        $button = $this->getWorkingElement()->findButton($selector);

        if (null === $button) {
            // @todo Improve button selector.
            return $this->findByInaccurateText('(//button | //input)', $selector);
        }

        return $button;
    }

    /**
     * @param string $text
     *
     * @return NodeElement|null
     */
    public function findByText($text)
    {
        return $this->findByInaccurateText('//*', $text);
    }

    /**
     * @param string $locator
     *   Element locator. Can be inaccurate text, inaccurate field label, CSS selector or region name.
     *
     * @throws NoSuchElement
     *
     * @return NodeElement
     */
    public function findElement($locator)
    {
        return $this->findByCss($locator)
            ?: $this->findField($locator)
                ?: $this->findButton($locator)
                    ?: $this->findByText($locator);
    }

    /**
     * Find all field labels by text.
     *
     * @param string $text
     *   Label text.
     *
     * @return NodeElement[]
     */
    public function findLabels($text)
    {
        $labels = [];

        foreach ($this->findByInaccurateText('//label[@for]', $text, true) as $label) {
            $labels[$label->getAttribute('for')] = $label;
        }

        return $labels;
    }

    /**
     * @return NodeElement
     */
    public function getBodyElement()
    {
        return $this->getSession()->getPage()->find('css', 'body');
    }

    /**
     * @param NodeElement $element
     * @param string $attribute
     * @param string $value
     *
     * @return NodeElement|null
     */
    public function getParentWithAttribute($element, $attribute, $value = '')
    {
        $attribute = empty($value) ? "@$attribute" : "contains(@$attribute, '$value')";

        return $element->find('xpath', "/ancestor::*[$attribute]");
    }

    /**
     * @param string $element
     *   HTML element name.
     * @param string $text
     *   Element text.
     * @param bool $all
     *   Find all or only first.
     *
     * @return NodeElement|NodeElement[]|null
     */
    private function findByInaccurateText($element, $text, $all = false)
    {
        return $this->getWorkingElement()->{'find' . ($all ? 'All' : '')}(
            'xpath',
            "{$element}[text()[starts-with(., '$text')]]"
        );
    }

    /**
     * @param string $selector
     *   Element selector.
     * @param mixed $element
     *   Existing element or null.
     *
     * @throws NoSuchElement
     */
    public function throwNoSuchElementException($selector, $element)
    {
        if (null === $element) {
            throw new NoSuchElement(sprintf('Cannot find an element by "%s" selector.', $selector));
        }
    }

    /**
     * @param string $locator
     * @param string $selector
     *
     * @throws \RuntimeException
     * @throws NoSuchElement
     *
     * @return NodeElement
     */
    public function element($locator, $selector)
    {
        $map = [
            'button' => 'Button',
            'field' => 'Field',
            'text' => 'ByText',
            'css' => 'ByCss',
            '*' => 'Element',
        ];

        if (!isset($map[$locator])) {
            throw new \RuntimeException(sprintf('Locator "%s" was not specified.'));
        }

        $element = $this->{'find' . $map[$locator]}($selector);
        $this->throwNoSuchElementException($selector, $element);

        return $element;
    }
}
