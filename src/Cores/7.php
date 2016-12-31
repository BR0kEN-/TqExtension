<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\TqExtension\Utils\Database\FetchField;

// @codingStandardsIgnoreStart
final class DrupalKernelPlaceholder extends DrupalKernelPlaceholderBase
  // @codingStandardsIgnoreEnd
{
    /**
     * {@inheritdoc}
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        // Set to "false", because the administration menu will not be rendered.
        // @see https://www.drupal.org/node/2023625#comment-8607207
        variable_set('admin_menu_cache_client', false);
    }

    /**
     * {@inheritdoc}
     */
    public static function t($string, array $args = [], array $options = [])
    {
        return t($string, $args, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function arg()
    {
        return arg();
    }

    /**
     * {@inheritdoc}
     */
    public static function formatString($string, array $args = [])
    {
        return format_string($string, $args);
    }

    /**
     * {@inheritdoc}
     */
    public static function tokenReplace($text, array $data = [], array $options = [])
    {
        return token_replace($text, $data, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function sitePath()
    {
        return conf_path();
    }

    /**
     * {@inheritdoc}
     */
    public static function jsonEncode($data)
    {
        return drupal_json_encode($data);
    }

    /**
     * {@inheritdoc}
     *
     * @param \stdClass $user
     */
    public static function setCurrentUser($user)
    {
        $GLOBALS['user'] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public static function setCurrentPath($path)
    {
        $_GET['q'] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public static function getUidByName($username)
    {
        return (int) (new FetchField('users', 'uid'))
            ->condition('name', $username)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public static function deleteUser($user_id)
    {
        user_delete($user_id);
    }

    /**
     * {@inheritdoc}
     *
     * @return \SelectQuery
     */
    public static function selectQuery($table, $alias = null, array $options = [])
    {
        return db_select($table, $alias, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function getFieldDefinitions($entityType, $bundle)
    {
        $definitions = [];

        foreach (field_info_instances($entityType, $bundle) as $name => $definition) {
            $definitions[$name] = [
                'label' => $definition['label'],
                'required' => $definition['required'],
            ];
        }

        return $definitions;
    }

    /**
     * {@inheritdoc}
     */
    public static function entityLoad($entityType, $id)
    {
        return entity_metadata_wrapper($entityType, $id);
    }

    /**
     * {@inheritdoc}
     *
     * @param \EntityDrupalWrapper $entity
     */
    public static function entityHasField($entity, $fieldName)
    {
        return isset($entity->{$fieldName});
    }

    /**
     * {@inheritdoc}
     *
     * @param \EntityDrupalWrapper $entity
     */
    public static function entityFieldValue($entity, $fieldName)
    {
        return $entity->{$fieldName}->value();
    }

    /**
     * {@inheritdoc}
     */
    public static function switchMailSystem($useTesting)
    {
        static $original = [
            'default-system' => 'DefaultMailSystem',
        ];

        if ($useTesting) {
            // Store original mail system to restore it after scenario.
            $original = variable_get('mail_system', $original);
            // Set the mail system for testing. It will store an emails in
            // "drupal_test_email_collector" Drupal variable instead of sending.
            $value = array_merge($original, [
                'default-system' => 'TestingMailSystem',
            ]);
        } else {
            // Bring back the original mail system.
            $value = $original;
            // Flush the email buffer to be able to reuse it from scratch.
            // @see \TestingMailSystem
            variable_set('drupal_test_email_collector', []);
        }

        variable_set('mail_system', $value);
    }

    /**
     * {@inheritdoc}
     */
    public static function getEmailMessages()
    {
        // We can't use variable_get() because Behat has another bootstrapped
        // variable $conf that is not updated from cURL bootstrapped Drupal instance.
        $result = (new FetchField('variable', 'value'))
            ->condition('name', 'drupal_test_email_collector')
            ->execute();

        return empty($result) ? [] : unserialize($result);
    }

    /**
     * {@inheritdoc}
     */
    public static function getContentTypeName($contentType)
    {
        if (isset(node_type_get_types()[$contentType])) {
            return $contentType;
        }

        return (string) (new FetchField('node_type', 'type'))
            ->condition('name', $contentType)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public static function injectCustomJavascript($file, $delete = false)
    {
        $file .= '.js';
        $modulePath = static::drupalGetFilename('module', 'system');
        $destination = dirname($modulePath) . '/' . $file;
        $injection = "\ndrupal_add_js('$destination', array('every_page' => TRUE));";

        if ($delete) {
            static::fileUnmanagedDelete($destination);

            $search = $injection;
            $replace = '';
        } else {
            static::fileUnmanagedCopy(
                str_replace('Context', 'JavaScript', __DIR__) . '/' . $file,
                $destination,
                FILE_EXISTS_REPLACE
            );

            $search = 'system_add_module_assets();';
            $replace = $search . $injection;
        }

        file_put_contents($modulePath, str_replace($search, $replace, file_get_contents($modulePath)));
    }
}
