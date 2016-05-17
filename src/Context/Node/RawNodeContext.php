<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Node;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
// Utils.
use Drupal\TqExtension\Utils\Database\FetchField;
use Drupal\TqExtension\Utils\BaseEntity;

class RawNodeContext extends RawTqContext
{
    use BaseEntity;

    /**
     * {@inheritdoc}
     */
    protected function entityType()
    {
        return 'node';
    }

    /**
     * @param string $title
     *   Inaccurate title of a node.
     * @param string $contentType
     *   Content type. Could be a title of content type.
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function getIdByArguments($title, $contentType = '')
    {
        $nid = new FetchField('node', 'nid');
        $nid->condition('title', "$title%", 'like');

        // Try to recognize node type by its title if content type specified and does not exist.
        if ('' !== $contentType && !isset(node_type_get_types()[$contentType])) {
            $contentType = (new FetchField('node_type', 'type'))
                ->condition('name', $contentType)
                ->execute();

            if ('' === $contentType) {
                throw new \InvalidArgumentException('Content type with such name does not exist!');
            }

            $nid->condition('type', $contentType);
        }

        return $nid->execute();
    }
}
