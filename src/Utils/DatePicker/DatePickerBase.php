<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\DatePicker;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
// Helpers.
use Behat\DebugExtension\Debugger;
use Behat\Mink\Element\NodeElement;

abstract class DatePickerBase
{
    use Debugger;

    /**
     * @var RawTqContext
     */
    protected $context;
    /**
     * @var NodeElement
     */
    protected $element;
    /**
     * @var string
     */
    protected $date = '';

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
        $this->element = $this->context->element('*', $selector);
        $this->date = self::jsDate($date);
    }

    /**
     * @param string $javascript
     *   JS code for execution.
     *
     * @return mixed
     *   Result of JS execution.
     */
    protected function execute($javascript)
    {
        return $this->context->executeJs("return $javascript;");
    }

    /**
     * @param string $date
     *   The string to parse.
     *
     * @return string
     */
    protected static function jsDate($date)
    {
        return sprintf("new Date('%s')", date('c', strtotime($date)));
    }

    /**
     * @throws \Exception
     *   When date is not available for selection.
     *
     * @return static
     */
    abstract public function isDateAvailable();

    /**
     * @return static
     */
    abstract public function setDate();

    /**
     * @throws \Exception
     *   When date is not selected.
     *
     * @return static
     */
    abstract public function isDateSelected();
}
