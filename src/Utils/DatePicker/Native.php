<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\DatePicker;

class Native extends DatePickerBase
{
    /**
     * @var string
     */
    private $dateFormat = '';
    /**
     * @var string
     */
    private $dateTime = '';
    /**
     * @var string
     */
    private $dateFormatted = '';

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->dateFormat = $this->context->executeJsOnElement($this->element, "return jQuery({{ELEMENT}}).data('drupalDateFormat')");

        if (empty($this->dateFormat)) {
            throw new \RuntimeException('Unknown date format.');
        }

        $this->dateTime = strtotime($this->date);
        $this->dateFormatted = date($this->dateFormat, $this->dateTime);
    }

    /**
     * {@inheritdoc}
     */
    public function isDateAvailable()
    {
        $ranges = [];

        foreach (['min', 'max'] as $range) {
            $value = $this->element->getAttribute($range);
            // If no range was set then use the original date as its value.
            $ranges[$range] = null === $value ? $this->dateTime : strtotime($value);
        }

        if ($this->dateTime < $ranges['min'] || $this->dateTime > $ranges['max']) {
            throw new \Exception(sprintf('The "%s" is not available for choosing.', $this->date));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDate()
    {
        $this->context->executeJsOnElement($this->element, "jQuery({{ELEMENT}}).val('$this->dateFormatted')");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDateSelected()
    {
        $value = $this->context->executeJsOnElement($this->element, "return jQuery({{ELEMENT}}).val()");

        self::debug(['Comparing "%s" with "%s".'], [$value, $this->dateFormatted]);

        if ($value !== $this->dateFormatted) {
            throw new \Exception(sprintf('DatePicker contains the "%s" but should "%s".', $value, $this->dateFormatted));
        }

        return $this;
    }
}
