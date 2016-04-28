<?php
/**
 * @author Cristina Eftimita, <eftimitac@gmail.com>
 */
namespace Drupal\TqExtension\Context\Node;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

class RawNodeContext extends RawTqContext
{
    /**
     * Retrieves node nid by node title and type.
     * @param $title
     *   Node title to lookup, accepts exact match only.
     * @param $type
     *   Node type machine name.
     * @return int|null
     *   Returns node nid if found.
     * @throws \Exception
     */
    public function getNodeIdByTitle($title, $type)
    {
        $nid = db_select('node', 'n')
            ->fields('n', array('nid'))
            ->condition('n.title', $title)
            ->condition('n.type', $type)
            ->range(0, 1)
            ->execute()->fetchField();

        if (empty($nid)) {
            throw new \Exception(sprintf("Node %s of %s not found.", $title, $type));
        }
        return $nid;
    }
}
