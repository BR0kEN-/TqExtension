<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

final class EntityDrupalWrapper
{
    /**
     * The Drupal entity name.
     *
     * @var string
     */
    private $entity = '';
    /**
     * The Drupal entity bundle.
     *
     * @var string
     */
    private $bundle = '';
    /**
     * @var array
     */
    private $fields = [
        'locators' => [],
        'required' => [],
    ];
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @param string $entity
     * @param string $bundle
     */
    public function __construct($entity, $bundle = '')
    {
        $this->entity = $entity;

        if (empty($bundle)) {
            $this->bundle = $this->entity;
        }

        // The fields in "locators" array stored by machine name of a field and duplicates by field label.
        foreach (field_info_instances($this->entity, $this->bundle) as $field_name => $definition) {
            $this->fields['locators'][$definition['label']] = $this->fields['locators'][$field_name] = $field_name;

            if ($definition['required']) {
                $this->fields['required'][$field_name] = $definition['label'];
            }

            $this->instances[$field_name] = $definition;
        }
    }

    /**
     * Load the Drupal entity wrapper.
     *
     * @see entity_metadata_wrapper()
     *
     * @param mixed $data
     * @param array $info
     *
     * @return \EntityDrupalWrapper
     */
    public function wrapper($data = null, array $info = [])
    {
        return entity_metadata_wrapper($this->entity, $data, $info);
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
     * @param string $field_name
     *   Machine name or label of a field.
     *
     * @return array[]
     *   Drupal field definition.
     */
    public function getFieldInfo($field_name)
    {
        $field_name = $this->getFieldNameByLocator($field_name);

        return empty($field_name) ? [] : field_info_field($field_name);
    }

    /**
     * @param string $field_name
     *   Machine name or label of a field.
     *
     * @return array[]
     *   Drupal field definition.
     */
    public function getFieldInstance($field_name)
    {
        $field_name = $this->getFieldNameByLocator($field_name);

        return empty($this->instances[$field_name]) ? [] : $this->instances[$field_name];
    }
}
