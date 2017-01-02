<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Cores;

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Drupal\TqExtension\Context\TqContext;

class DrupalKernelPlaceholder
{
    /**
     * Version-related implementation of @BeforeFeature hook for TqContext.
     *
     * @param BeforeFeatureScope $scope
     *
     * @see TqContext::beforeFeature()
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $string
     * @param array $arguments
     * @param array $options
     *
     * @return string
     */
    public static function t($string, array $arguments = [], array $options = [])
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $string
     * @param array $arguments
     *
     * @return string
     */
    public static function formatString($string, array $arguments = [])
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @return string[]
     */
    public static function arg()
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $text
     *   Input text.
     * @param array $data
     *   Data for replacements.
     * @param array $options
     *   Replacement configuration.
     *
     * @return string
     *   Processed text.
     */
    public static function tokenReplace($text, array $data = [], array $options = [])
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public static function sitePath()
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public static function jsonEncode($data)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @return object
     */
    public static function getCurrentUser()
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    public static function setCurrentUser($user)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    public static function setCurrentPath($path)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * Locate user ID by its name.
     *
     * @param string $username
     *
     * @return int
     */
    public static function getUidByName($username)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param int $user_id
     */
    public static function deleteUser($user_id)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $table
     * @param string $alias
     * @param array $options
     *
     * @return object
     */
    public static function selectQuery($table, $alias = null, array $options = [])
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $entityType
     * @param string $bundle
     *
     * @return array[]
     *   An associative array where key - machine-name of a field and
     *   value - an array with two keys: "label" and "required".
     */
    public static function getFieldDefinitions($entityType, $bundle)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * Get information about database connections.
     *
     * Impossible to use $GLOBALS['databases'] in Drupal 8 since {@link https://www.drupal.org/node/2176621}.
     *
     * @param string $connection
     *   Connection name.
     *
     * @return array[]
     */
    public static function getDatabaseConnectionInfo($connection)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $entityType
     * @param int $id
     *
     * @return object|null
     */
    public static function entityLoad($entityType, $id)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return bool
     */
    public static function entityHasField($entity, $fieldName)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param object $entity
     * @param string $fieldName
     *
     * @return mixed
     */
    public static function entityFieldValue($entity, $fieldName)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * Switching the mail system.
     *
     * @param bool $useTesting
     *   Whether testing or standard mail system should be used.
     */
    public static function switchMailSystem($useTesting)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * Get a list of emails, collected by testing mail system.
     *
     * @return array
     */
    public static function getEmailMessages()
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * Check existence of the content type by its machine name or title.
     *
     * @param string $contentType
     *   Machine name or title of the content type.
     *
     * @return string
     *   Machine name.
     */
    public static function getContentTypeName($contentType)
    {
        return self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $file
     *   Existing file from "src/JavaScript" without ".js" extension.
     * @param bool $delete
     *   Whether injection should be deleted.
     */
    public static function injectCustomJavascript($file, $delete = false)
    {
        self::requireContext(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public static function drupalGetFilename($type, $name, $filename = null)
    {
        return drupal_get_filename($type, $name, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public static function fileUnmanagedDelete($path)
    {
        return file_unmanaged_delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public static function fileUnmanagedCopy($source, $destination = null, $replace = FILE_EXISTS_RENAME)
    {
        return file_unmanaged_copy($source, $destination, $replace);
    }

    /**
     * Require method execution from context.
     *
     * @param string $method
     *   The name of method.
     * @param array $arguments
     *   Method's arguments.
     *
     * @return mixed
     */
    private static function requireContext($method, array $arguments)
    {
        $context = str_replace('Kernel', DRUPAL_CORE, __CLASS__);

        if (method_exists($context, $method)) {
            return call_user_func_array([$context, $method], $arguments);
        }

        throw new \BadMethodCallException(sprintf('Method "%s" is not implemented in "%s".', $method, $context));
    }
}
