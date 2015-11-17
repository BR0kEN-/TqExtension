<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Reader\EnvironmentReader;
use Behat\Behat\Context\Reader\ContextReader;
use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Environment\UninitializedContextEnvironment;

final class TqContextReader implements EnvironmentReader
{
    /**
     * @var ContextReader[]
     */
    private $contextReaders = [];
    /**
     * @var Callee[]
     */
    private $callees = [];

    /**
     * Register context loaders.
     *
     * @see TqExtension::process
     *
     * @param ContextReader[] $contextReaders
     */
    public function __construct(array $contextReaders)
    {
        $this->contextReaders = $contextReaders;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironment(Environment $environment)
    {
        return $environment instanceof ContextEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function readEnvironmentCallees(Environment $environment)
    {
        if ($environment instanceof UninitializedContextEnvironment) {
            // Read all TqExtension contexts.
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS)
            ) as $path => $object) {
                $namespace = ltrim(str_replace(__DIR__, '', $path), DIRECTORY_SEPARATOR);

                // Allow names which not starts from "Raw" and ends by "Context.php".
                if (strrpos($namespace, 'Context.php') !== false && strpos($namespace, 'Raw') !== 0) {
                    $class = str_replace('/', '\\', __NAMESPACE__ . '/' . rtrim($namespace, '.php'));

                    if (!$environment->hasContextClass($class)) {
                        $environment->registerContextClass($class);
                    }
                }
            }
        }

        if (empty($this->callees)) {
            // Read all step definitions from available contexts.
            foreach ($environment->getContextClasses() as $class) {
                foreach ($this->contextReaders as $loader) {
                    $this->callees = array_merge($this->callees, $loader->readContextCallees($environment, $class));
                }
            }
        }

        return $this->callees;
    }
}
