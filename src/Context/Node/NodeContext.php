<?php
/**
 * @author Cristina Eftimita, <eftimitac@gmail.com>
 */
namespace Drupal\TqExtension\Context\Node;

class NodeContext extends RawNodeContext
{
    /**
     * @When /^I (visit|view|edit) "([^"]+)" node of type "([^"]+)"$/
     */
    public function visitNode($operation, $title, $type) {
        if ('visit' === $operation) {
             $operation = 'view';
        }
        $nid = $this->getNodeIdByTitle($title, $type);

        $this->getRedirectContext()->visitPage("node/$nid/$operation");
    }

    /**
     * @When /^(?:|I )edit (?:this|current|the) node$/
     */
    public function editNode()
    {
        $args = arg();
        if (count($args) < 2 || ('node' !== $args[0] || $args[1] <= 0)) {
            throw new \RuntimeException(sprintf('Page "%s" is not a node. Unable to edit.', static::$pageUrl));
        }

        $this->getRedirectContext()->visitPage('node/' . $args[1] . '/edit');
    }
}
