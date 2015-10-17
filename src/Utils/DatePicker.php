<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

// Helpers.
use Behat\Mink\Element\NodeElement;

class DatePicker
{
    /**
     * @var NodeElement
     */
    private $element;
    /**
     * @var string
     */
    private $date = '';
    /**
     * @var RawTqContext
     */
    private $context;

    /**
     * @param RawTqContext $context
     *   Behat context.
     * @param string $selector
     *   Element selector.
     * @param string $date
     *   Human-readable date.
     */
    public function __construct(RawTqContext $context, $selector, $date)
    {
        $this->context = $context;

        if (null === $this->execute('$.fn.datepicker')) {
            throw new \RuntimeException('jQuery DatePicker is not available on the page.');
        }

        $this->element = $this->context->element('*', $selector);
        $this->date = sprintf("new Date('%s')", date('c', strtotime($date)));
    }

    /**
     * @return self
     */
    public function setDate()
    {
        $this->datePicker([__FUNCTION__, '<date>']);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return self
     */
    public function isDateSelected()
    {
        $value = $this->datePicker(['getDate']);
        $date = $this->execute($this->date);

        $this->context->debug(["Comparing $value with $date"]);

        if ($value !== $date) {
            throw new \Exception(sprintf('DatePicker contains the "%s" but should "%s".', $value, $date));
        }

        return $this;
    }

    /**
     * @todo For now, any out of scope variable inside of "beforeShowDay" method will be undefined.
     *
     * @throws \Exception
     *
     * @return self
     */
    public function isDateAvailable()
    {
        $beforeShowDay = $this->datePicker(['option', 'beforeShowDay']);

        if (!empty($beforeShowDay) && !empty($this->execute("$beforeShowDay($this->date)")[0])) {
            throw new \Exception(sprintf('The "%s" is not available for choosing.', $this->date));
        }

        return $this;
    }

    /**
     * @param string $javascript
     *   JS code for execution.
     *
     * @return mixed
     *   Result of JS execution.
     */
    private function execute($javascript)
    {
        return $this->context->executeJs("return $javascript;");
    }

    /**
     * @param array $arguments
     *   jQuery.fn.datepicker arguments.
     *
     * @return mixed
     *   Result of JS execution.
     */
    private function datePicker(array $arguments)
    {
        return $this->context->executeJsOnElement($this->element, sprintf(
            "return jQuery({{ELEMENT}}).datepicker(%s);",
            implode(', ', array_map(function ($value) {
                return in_array($value, ['<date>']) ? $this->date : "'$value'";
            }, $arguments))
        ));
    }
}
