<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\DatePicker;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

final class DatePicker extends DatePickerBase
{
    /**
     * @var DatePickerBase
     */
    private $datePicker;

    /**
     * {@inheritdoc}
     */
    public function __construct(RawTqContext $context, $selector, $date)
    {
        parent::__construct($context, $selector, $date);

        if (null === $this->execute('jQuery.fn.datepicker')) {
            throw new \RuntimeException('jQuery DatePicker is not available on the page.');
        }

        // Drupal 8 will use native "date" field if available.
        $class = $this->execute('Modernizr && Modernizr.inputtypes.date') ? Native::class : JQuery::class;
        $this->datePicker = new $class($context, $selector, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function setDate()
    {
        return $this->datePicker->{__FUNCTION__}();
    }

    /**
     * {@inheritdoc}
     */
    public function isDateSelected()
    {
        return $this->datePicker->{__FUNCTION__}();
    }

    /**
     * {@inheritdoc}
     */
    public function isDateAvailable()
    {
        return $this->datePicker->{__FUNCTION__}();
    }
}
