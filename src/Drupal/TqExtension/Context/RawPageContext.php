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
        return $this
            ->getWorkingElement()
            ->find(empty($this->getDrupalParameter('region_map')[$selector]) ? 'css' : 'region', $selector);
    }

    /**
     * @param string $selector
     *
     * @return NodeElement|null
     */
    public function findField($selector)
    {
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
     * @return NodeElement
     * @throws NoSuchElement
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
     * @param string $sessionName
     *
     * @return NodeElement|null
     */
    public function getBodyElement($sessionName = null)
    {
        return $this->getSession($sessionName)->getPage()->find('css', 'body');
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
}
