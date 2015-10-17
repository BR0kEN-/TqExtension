<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

use Behat\Behat\Context as Behat;

class TqContextInitializer implements Behat\Initializer\ContextInitializer
{
    /**
     * Parameters of TqExtension.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Behat\Context $context)
    {
        if ($context instanceof TqContextInterface) {
            $context->setTqParameters($this->parameters);
        }
    }
}
