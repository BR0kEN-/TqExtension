<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;

interface TqContextInterface extends SnippetAcceptingContext
{
    /**
     * Set context parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function setTqParameters(array $parameters);
}
