<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

use Drupal\TqExtension\Cores\DrupalKernelPlaceholder;

final class EntityDrupalWrapper
{
    /**
     * Entity type.
     *
     * @var string
     */
    private $type = '';
    /**
     * Entity bundle.
     *
     * @var string
     */
    private $bundle = '';
    /**
     * Entity object.
     *
     * @var object
     */
    private $entity;
    /**
     * @var array
     */
    private $fields = [
        'locators' => [],
        'required' => [],
    ];

    /**
     * @param string $entityType
     * @param string $bundle
     */
    public function __construct($entityType, $bundle = '')
    {
        $this->type = $entityType;

        if (empty($bundle)) {
            $this->bundle = $this->type;
        }

        // The fields in "locators" array stored by machine name of a field and duplicated by field label.
        foreach (DrupalKernelPlaceholder::getFieldDefinitions($this->type, $this->bundle) as $name => $definition) {
            $this->fields['locators'][$definition['label']] = $this->fields['locators'][$name] = $name;

            if ($definition['required']) {
                $this->fields['required'][$name] = $definition['label'];
            }
        }
    }

    public function load($id)
    {
        if (null === $this->entity) {
            $this->entity = entity_metadata_wrapper($this->type, DrupalKernelPlaceholder::entityLoad($this->type, $id));
        }

        return $this->entity;
    }

    public function hasField($fieldName)
    {
        return DrupalKernelPlaceholder::entityHasField(
            $this->getEntity(),
            $this->getFieldNameByLocator($fieldName)
        );
    }

    public function getFieldValue($fieldName)
    {
        return DrupalKernelPlaceholder::entityFieldValue(
            $this->getEntity(),
            $this->getFieldNameByLocator($fieldName)
        );
    }

    /**
     * @param string $field_name
     *   Machine name or label of a field.
     *
     * @return string
     */
    public function getFieldNameByLocator($field_name)
    {
        return isset($this->fields['locators'][$field_name]) ? $this->fields['locators'][$field_name] : '';
    }

    /**
     * @return array[]
     */
    public function getRequiredFields()
    {
        return $this->fields['required'];
    }

    /**
     * @return object
     */
    protected function getEntity()
    {
        if (null === $this->entity) {
            throw new \RuntimeException('You have to load an entity before getting it.');
        }

        return $this->entity;
    }
}
