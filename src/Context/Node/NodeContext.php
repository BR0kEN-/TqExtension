<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Node;

use Drupal\TqExtension\Context\RawTqContext;

class NodeContext extends RawTqContext
{
    /**
     * @When /^I (visit|view|edit) "([^"]+)" node of type "([^"]+)"$/
     */
    public function iVisitNodePageOfType($operation, $title, $type) {
        if ($operation == 'visit') {
            $operation = 'view';
        }
        $query = new \EntityFieldQuery();
        $result = $query
            ->entityCondition('entity_type', 'node')
            // @todo: Add support for CT label.
            ->entityCondition('bundle', strtolower($type))
            ->propertyCondition('title', $title)
            ->range(0, 1)
            ->execute();

        if (empty($result['node'])) {
            $params = array(
                '@title' => $title,
                '@type' => $type,
            );
            throw new \Exception(format_string("Node @title of @type not found.", $params));
        }

        $nid = key($result['node']);
        $this->getSession()->visit($this->locatePath('/node/' . $nid . '/' . $operation));
    }

    /**
     * @When I edit this node
     */
    public function iEditThisNode()
    {
        if (preg_match("@node/(\d+)@", $_GET['q'], $matches)) {
            $this->getSession()->visit($this->locatePath('/node/' . $matches[1] . '/edit'));
        }
        else {
            throw new \Exception("You're not currently on a node page.");
        }
    }
}
