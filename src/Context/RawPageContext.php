<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

// Contexts.
use Drupal\DrupalExtension\Context\RawDrupalContext;
// Exceptions.
use WebDriver\Exception\NoSuchElement;
// Helpers.
use Behat\Mink\Element\NodeElement;
// Utils.
use Drupal\TqExtension\Utils\XPath;

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
     * @return NodeElement
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

        return $element->findField($selector);
    }

    /**
     * @param string $selector
     *
     * @return NodeElement
     */
    public function findButton($selector)
    {
        $element = $this->getWorkingElement();

        // Search inside of: "id", "name", "title", "alt" and "value" attributes.
        return $element->findButton($selector)
            ?: (new XPath\InaccurateText('//button', $element))->text($selector)->find();
    }

    /**
     * @param string $text
     *
     * @return NodeElement|null
     */
    public function findByText($text)
    {
        return (new XPath\InaccurateText('//*', $this->getWorkingElement()))->text($text)->find();
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
        $xpath = new XPath\InaccurateText('//label[@for]', $this->getWorkingElement());
        $labels = [];

        foreach ($xpath->text($text)->findAll() as $label) {
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

        $selector = t($selector);
        $element = $this->{'find' . $map[$locator]}($selector);
        $this->throwNoSuchElementException($selector, $element);

        return $element;
    }
}
