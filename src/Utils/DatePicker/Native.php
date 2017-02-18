<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\DatePicker;

// @todo Implement support. See example in "misc/date.js" of Drupal 8.
class Native extends DatePickerBase
{
    /**
     * {@inheritdoc}
     */
    public function isDateAvailable()
    {
        throw new \RuntimeException(sprintf('Method "%s" is not implemented.', __METHOD__));
    }

    /**
     * {@inheritdoc}
     */
    public function setDate()
    {
        throw new \RuntimeException(sprintf('Method "%s" is not implemented.', __METHOD__));
    }

    /**
     * {@inheritdoc}
     */
    public function isDateSelected()
    {
        throw new \RuntimeException(sprintf('Method "%s" is not implemented,', __METHOD__));
    }
}
