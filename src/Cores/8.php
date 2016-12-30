<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Serialization\Json;
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
    }

    /**
     * {@inheritdoc}
     */
    public static function t($string, array $args = [], array $options = [])
    {
        return (string) new TranslatableMarkup($string, $args, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function arg()
    {
        return explode('/', \Drupal::service('path.current')->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public static function formatString($string, array $args = [])
    {
        return (string) new FormattableMarkup($string, $args);
    }

    /**
     * {@inheritdoc}
     */
    public static function tokenReplace($text, array $data = [], array $options = [])
    {
        return \Drupal::token()->replace($text, $data, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function sitePath()
    {
        return \Drupal::service('site.path');
    }

    /**
     * {@inheritdoc}
     */
    public static function jsonEncode($data)
    {
        return Json::encode($data);
    }

    /**
     * {@inheritdoc}
     *
     * @param AccountInterface $user
     */
    public static function setCurrentUser($user)
    {
        \Drupal::currentUser()->setAccount($user);
    }

    /**
     * {@inheritdoc}
     */
    public static function getUidByName($username)
    {
        return (int) (new FetchField('users_field_data', 'uid'))
            ->condition('name', $username)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public static function deleteUser($user_id)
    {
        $user_storage = \Drupal::entityTypeManager()->getStorage('user');
        $user_storage->delete([$user_storage->load($user_id)]);
    }

    /**
     * {@inheritdoc}
     *
     * @return Select
     */
    public static function selectQuery($table, $alias = null, array $options = [])
    {
        return \Drupal::database()->select($table, $alias, $options);
    }

    /**
     * {@inheritdoc}
     */
    public static function getFieldDefinitions($entityType, $bundle)
    {
        $definitions = [];

        /** @var FieldDefinitionInterface $definition */
        foreach (\Drupal::service('entity_field.manager')->getFieldDefinitions($entityType, $bundle) as $name => $definition) {
            $definitions[$name] = [
                'label' => (string) $definition->getLabel(),
                'required' => $definition->isRequired(),
            ];
        }

        return $definitions;
    }

    /**
     * {@inheritdoc}
     */
    public static function entityLoad($entityType, $id)
    {
        return \Drupal::entityTypeManager()->getStorage($entityType)->load($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param FieldableEntityInterface $entity
     */
    public static function entityHasField($entity, $fieldName)
    {
        return $entity->hasField($fieldName);
    }

    /**
     * {@inheritdoc}
     *
     * @param FieldableEntityInterface $entity
     */
    public static function entityFieldValue($entity, $fieldName)
    {
        return $entity->get($fieldName)->value;
    }

    /**
     * {@inheritdoc}
     */
    public static function switchMailSystem($useTesting)
    {
        static $original = 'php_mail';

        $systemMail = \Drupal::configFactory()
          ->getEditable('system.mail');

        if ($useTesting) {
            // Store original mail system to restore it after scenario.
            $original = $systemMail->get('interface.default') ?: $original;
            // Set the mail system for testing. It will store an emails in
            // "system.test_mail_collector" Drupal state instead of sending.
            $value = 'test_mail_collector';
        } else {
            // Bring back the original mail system.
            $value = $original;
            // Flush the email buffer to be able to reuse it from scratch.
            // @see \Drupal\Core\Mail\Plugin\Mail\TestMailCollector
            \Drupal::state()->set('system.test_mail_collector', []);
        }

        $systemMail->set('interface.default', $value)->save(true);
    }

    /**
     * {@inheritdoc}
     */
    public static function getEmailMessages()
    {
        return \Drupal::state()->get('system.test_mail_collector') ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public static function isContentTypeExists($contentType)
    {
        return (bool) \Drupal::entityTypeManager()->getStorage('node_type')->load($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public static function injectCustomJavascript($file, $delete = false)
    {
        // @todo
    }
}
