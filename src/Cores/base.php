<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\TqExtension\Context\TqContext;

// @codingStandardsIgnoreStart
interface DrupalKernelPlaceholderInterface
// @codingStandardsIgnoreEnd
{
    /**
     * Version-related implementation of @BeforeFeature hook for TqContext.
     *
     * @param BeforeFeatureScope $scope
     *
     * @see TqContext::beforeFeature()
     */
    public static function beforeFeature(BeforeFeatureScope $scope);

    public static function t($string, array $args = [], array $options = []);

    public static function arg();

    public static function formatString($string, array $args = []);

    public static function tokenReplace($text, array $data = [], array $options = []);

    public static function sitePath();

    public static function jsonEncode($data);

    public static function setCurrentUser($user);

    public static function setCurrentPath($path);

    /**
     * Locate user ID by its name.
     *
     * @param string $username
     *
     * @return int
     */
    public static function getUidByName($username);

    /**
     * @param int $user_id
     */
    public static function deleteUser($user_id);

    /**
     * @param string $table
     * @param string $alias
     * @param array $options
     *
     * @return object
     */
    public static function selectQuery($table, $alias = null, array $options = []);

    /**
     * @param string $entityType
     * @param string $bundle
     *
     * @return array[]
     *   An associative array where key - machine-name of a field and
     *   value - an array with two keys: "label" and "required".
     */
    public static function getFieldDefinitions($entityType, $bundle);

    /**
     * @param string $entityType
     * @param int $id
     *
     * @return object
     */
    public static function entityLoad($entityType, $id);

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return bool
     */
    public static function entityHasField($entity, $fieldName);

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return mixed
     */
    public static function entityFieldValue($entity, $fieldName);

    /**
     * Switching the mail system.
     *
     * @param bool $useTesting
     *   Whether testing or standard mail system should be used.
     */
    public static function switchMailSystem($useTesting);

    /**
     * Get a list of emails, collected by testing mail system.
     *
     * @return array
     */
    public static function getEmailMessages();

    /**
     * Check existence of the content type by its machine name or title.
     *
     * @param string $contentType
     *   Machine name or title of the content type.
     *
     * @return string
     *   Machine name.
     */
    public static function getContentTypeName($contentType);

    /**
     * @param string $file
     *   Existing file from "src/JavaScript" without ".js" extension.
     * @param bool $delete
     *   Whether injection should be deleted.
     */
    public static function injectCustomJavascript($file, $delete = false);
}

// @codingStandardsIgnoreStart
abstract class DrupalKernelPlaceholderBase implements DrupalKernelPlaceholderInterface
// @codingStandardsIgnoreEnd
{
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
