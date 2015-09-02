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
     * @param string $contextNamespace
     */
    public function __construct(array $parameters, $contextNamespace)
    {
        $this->parameters = $parameters;
        $this->parameters['context_namespace'] = $contextNamespace;
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
