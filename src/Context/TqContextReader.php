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
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
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
        // Calless are needed only for initialized environment.
        if (empty($this->callees) && $environment instanceof InitializedContextEnvironment) {
            foreach ($environment->getContextClasses() as $class) {
                $this->readContextCallees($environment, $class);
            }
        }

        if ($environment instanceof UninitializedContextEnvironment) {
            /** @var \splFileInfo $object */
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS)
            ) as $path => $object) {
                $namespace = ltrim(str_replace(__DIR__, '', $path), '/');

                if (strrpos($namespace, 'Context.php') !== false && strpos($namespace, 'Raw') === false) {
                    $class = str_replace('/', '\\', __NAMESPACE__ . '/' . trim($namespace, '.php'));

                    if (!$environment->hasContextClass($class)) {
                        $environment->registerContextClass($class);
                    }
                }
            }
        }

        return $this->callees;
    }

    /**
     * Register context loaders.
     *
     * @param ContextReader $contextReader
     */
    public function registerContextReader(ContextReader $contextReader)
    {
        $this->contextReaders[] = $contextReader;
    }

    /**
     * Reads callees from a specific suite's context.
     *
     * @param ContextEnvironment $environment
     * @param string $contextClass
     *
     * @return Callee[]
     */
    private function readContextCallees(ContextEnvironment $environment, $contextClass)
    {
        foreach ($this->contextReaders as $loader) {
            $this->callees = array_merge($this->callees, $loader->readContextCallees($environment, $contextClass));
        }
    }
}
