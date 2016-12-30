<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\TqExtension\Context\TqContext;

// @codingStandardsIgnoreStart
abstract class DrupalKernelPlaceholderBase
// @codingStandardsIgnoreEnd
{
    /**
     * Version-related implementation of @BeforeFeature hook for TqContext.
     *
     * @param BeforeFeatureScope $scope
     *
     * @see TqContext::beforeFeature()
     */
    abstract public static function beforeFeature(BeforeFeatureScope $scope);

    abstract public static function t($string, array $args = [], array $options = []);

    abstract public static function arg();

    abstract public static function formatString($string, array $args = []);

    abstract public static function tokenReplace($text, array $data = [], array $options = []);

    abstract public static function sitePath();

    abstract public static function jsonEncode($data);

    abstract public static function setCurrentUser($user);

    /**
     * Locate user ID by its name.
     *
     * @param string $username
     *
     * @return int
     */
    abstract public static function getUidByName($username);

    /**
     * @param int $user_id
     */
    abstract public static function deleteUser($user_id);

    /**
     * @param string $table
     * @param string $alias
     * @param array $options
     *
     * @return object
     */
    abstract public static function selectQuery($table, $alias = null, array $options = []);

    /**
     * @param string $entityType
     * @param string $bundle
     *
     * @return array[]
     *   An associative array where key - machine-name of a field and
     *   value - an array with two keys: "label" and "required".
     */
    abstract public static function getFieldDefinitions($entityType, $bundle);

    /**
     * @param string $entityType
     * @param int $id
     *
     * @return object
     */
    abstract public static function entityLoad($entityType, $id);

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return bool
     */
    abstract public static function entityHasField($entity, $fieldName);

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return mixed
     */
    abstract public static function entityFieldValue($entity, $fieldName);

    /**
     * Switching the mail system.
     *
     * @param bool $useTesting
     *   Whether testing or standard mail system should be used.
     */
    abstract public static function switchMailSystem($useTesting);

    /**
     * Get a list of emails, collected by testing mail system.
     *
     * @return array
     */
    abstract public static function getEmailMessages();

    /**
     * @param string $contentType
     *
     * @return bool
     */
    abstract public static function isContentTypeExists($contentType);

    /**
     * @param string $file
     *   Existing file from "src/JavaScript" without ".js" extension.
     * @param bool $delete
     *   Whether injection should be deleted.
     */
    abstract public static function injectCustomJavascript($file, $delete = false);

    public static function drupalGetFilename($type, $name, $filename = null)
    {
        return drupal_get_filename($type, $name, $filename);
    }

    public static function fileUnmanagedDelete($path)
    {
        return file_unmanaged_delete($path);
    }

    public static function fileUnmanagedCopy($source, $destination = null, $replace = FILE_EXISTS_RENAME)
    {
        return file_unmanaged_copy($source, $destination, $replace);
    }
}
