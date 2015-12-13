<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils;

class DatabaseManager
{
    /**
     * @var string
     */
    private $credentials = '-u%s -p%s';
    /**
     * Name of original database.
     *
     * @var string
     */
    private $originalName = '';
    /**
     * Name of temporary database that will store data from original.
     *
     * @var string
     */
    private $newName = '';
    /**
     * Name of an object where this class is called.
     *
     * @var string
     */
    private $callee;

    /**
     * @param string $connection
     *   Database connection name (key in $databases array from settings.php).
     * @param string $callee
     *   Must be the value of "self::class" of callee object.
     */
    public function __construct($connection, $callee)
    {
        if (!defined('DRUPAL_ROOT') || !function_exists('conf_path')) {
            throw new \RuntimeException('Drupal is not bootstrapped.');
        }

        if (!class_exists($callee)) {
            throw new \InvalidArgumentException(sprintf('An object of "%s" type does not exist.', $callee));
        }

        /** @var array $databases */
        require sprintf('%s/%s/settings.php', DRUPAL_ROOT, conf_path());

        if (empty($databases[$connection])) {
            throw new \InvalidArgumentException(sprintf('The "%s" database connection does not exist.', $connection));
        }

        $info = $databases[$connection]['default'];

        $this->callee = $callee;
        $this->originalName = $info['database'];
        $this->newName = "tqextension_$this->originalName";
        $this->credentials = sprintf($this->credentials, $info['username'], $info['password']);

        foreach (['drop', 'create'] as $action) {
            $this->exec("mysql $this->credentials -e '$action database $this->newName;'");
        }

        $this->exec("mysqldump $this->credentials $this->originalName | mysql $this->credentials $this->newName");
    }

    /**
     * Restore original database.
     */
    public function __destruct()
    {
        $this->exec("mysqldump $this->credentials $this->newName | mysql $this->credentials $this->originalName");
    }

    /**
     * Executes a shell command.
     *
     * @param string $command
     */
    private function exec($command)
    {
        $command = vsprintf($command, array_slice(func_get_args(), 1));

        if (method_exists($this->callee, 'debug')) {
            call_user_func([$this->callee, 'debug'], [$command]);
        }

        shell_exec($command);
    }
}
