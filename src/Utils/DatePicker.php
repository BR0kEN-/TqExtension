<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
// Helpers.
use Behat\DebugExtension\Debugger;
use Behat\Mink\Element\NodeElement;

class DatePicker
{
    use Debugger;

    const DATE_ADJUSTER = 'window.__behatDatePickerDateAdjuster';

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

        if (null === $this->execute('jQuery.fn.datepicker')) {
            throw new \RuntimeException('jQuery DatePicker is not available on the page.');
        }

        $this->element = $this->context->element('*', $selector);
        $this->date = self::jsDate($date);
    }

    /**
     * @return self
     */
    public function setDate()
    {
        // @todo setDate will not work if browser window is not active.
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
        $initial = $this->execute($this->date);

        // By some reasons DatePicker could not return a date using "getDate" method
        // and we'll try to use it from input value directly. An issue could occur
        // after saving the form and/or reloading the page.
        if (empty($value)) {
            $value = $this->execute(self::jsDate($this->element->getValue()));
        } else {
            $value = $this->adjustTimezone($value);
        }

        self::debug(['Comparing "%s" with "%s".'], [$value, $initial]);

        if ($value !== $initial) {
            throw new \Exception(sprintf('DatePicker contains the "%s" but should "%s".', $value, $initial));
        }

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return self
     */
    public function isDateAvailable()
    {
        // @todo For now, any out of scope variable inside of "beforeShowDay" method will be undefined.
        // @link https://github.com/refactoror/SelBlocks/issues/5#issuecomment-68511965
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

    /**
     * Adjust timezone of date returned by DatePicker.
     *
     * @param string $date
     *   Date returned by "jQuery('#selector').datepicker('getDate')".
     *
     * @return string
     *   Date with correct timezone.
     *
     * @link http://stackoverflow.com/a/31560608
     */
    private function adjustTimezone($date)
    {
        $session = $this->context->getSession();

        $session->executeScript(sprintf(
            '%s=%s;%1$s.setMinutes(%1$s.getMinutes()-%1$s.getTimezoneOffset());delete %1$s;',
            self::DATE_ADJUSTER,
            self::jsDate($date)
        ));

        return $session->evaluateScript(self::DATE_ADJUSTER);
    }

    /**
     * @param string $date
     *   The string to parse.
     *
     * @return string
     */
    private static function jsDate($date)
    {
        return sprintf("new Date('%s')", date('c', strtotime($date)));
    }
}
