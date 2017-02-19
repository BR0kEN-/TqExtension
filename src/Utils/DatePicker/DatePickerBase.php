<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\DatePicker;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
// Helpers.
use Behat\Mink\Session;
use Behat\Mink\Element\NodeElement;
use Behat\DebugExtension\Debugger;

abstract class DatePickerBase implements DatePickerInterface
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
     * @var Session
     */
    protected $session;
    /**
     * @var string
     */
    protected $date = '';

    /**
     * @param RawTqContext $context
     *   Behat context.
     * @param Session $session
     *   Behat session.
     * @param NodeElement $element
     *   Element selector.
     * @param string $date
     *   Human-readable date.
     */
    public function __construct(RawTqContext $context, Session $session, NodeElement $element, $date)
    {
        $this->context = $context;
        $this->session = $session;
        $this->element = $element;
        $this->date = $date;

        $this->initialize();
    }

    abstract public function initialize();
}
