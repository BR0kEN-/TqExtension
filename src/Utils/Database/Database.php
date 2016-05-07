<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Utils\Database;

// Helpers.
use Behat\DebugExtension\Debugger;

class Database
{
    use Debugger;

    /**
     * MySQL and MySQLDump login arguments.
     *
     * @var string
     */
    private $credentials = '-u%s -p%s -h%s -P%s';
    /**
     * Name of original database.
     *
     * @var string
     */
    private $source = '';
    /**
     * Name of temporary database that will store data from original.
     *
     * @var string
     */
    private $temporary = '';
    /**
     * Indicates that DB was cloned.
     *
     * @var bool
     */
    private $cloned = false;

    /**
     * @param string $connection
     *   Database connection name (key in $databases array from settings.php).
     */
    public function __construct($connection)
    {
        if (!defined('DRUPAL_ROOT') || !function_exists('conf_path')) {
            throw new \RuntimeException('Drupal is not bootstrapped.');
        }

        $databases = [];

        require sprintf('%s/%s/settings.php', DRUPAL_ROOT, conf_path());

        if (empty($databases[$connection])) {
            throw new \InvalidArgumentException(sprintf('The "%s" database connection does not exist.', $connection));
        }

        $db = $databases[$connection]['default'];

        foreach (['port' => 3306, 'host' => '127.0.0.1'] as $option => $default) {
            if (empty($db[$option])) {
                $db[$option] = $default;
            }
        }

        self::debug([__CLASS__]);

        $this->source = $db['database'];
        $this->temporary = "tqextension_$this->source";
        $this->credentials = sprintf($this->credentials, $db['username'], $db['password'], $db['host'], $db['port']);
    }

    /**
     * Clone a database.
     */
    public function __clone()
    {
        // Drop and create temporary DB and copy source into it.
        $this->copy($this->source, $this->temporary);
        $this->cloned = true;
    }

    /**
     * Restore original database.
     */
    public function __destruct()
    {
        if ($this->cloned) {
            // Drop and create source DB and copy temporary into it.
            $this->copy($this->temporary, $this->source);
            // Kill temporary DB.
            $this->drop($this->temporary);
        }
    }

    /**
     * @param string $name
     *   Name of the database to check.
     *
     * @return bool
     *   Checking state.
     */
    public function exist($name)
    {
        return !empty($this->exec("mysql -e 'show databases' | grep '^$name$'"));
    }

    /**
     * @param string $name
     *   Name of the database to drop.
     */
    public function drop($name)
    {
        if ($this->exist($name)) {
            $this->exec("mysql -e '%s database $name;'", __FUNCTION__);
        }
    }

    /**
     * @param string $name
     *   Name of the database to create.
     */
    public function create($name)
    {
        if (!$this->exist($name)) {
            $this->exec("mysql -e '%s database $name;'", __FUNCTION__);
        }
    }

    /**
     * @param string $source
     *   Source DB name.
     * @param string $destination
     *   Name of the new DB.
     */
    public function copy($source, $destination)
    {
        $this->drop($destination);
        $this->create($destination);
        $this->exec("mysqldump $source | mysql $destination");
    }

    /**
     * Executes a shell command.
     *
     * @param string $command
     *   Command to execute.
     *
     * @return string
     *   Result of a shell command.
     */
    private function exec($command)
    {
        // Adding credentials after "mysql" and "mysqldump" commands.
        $command = preg_replace(
            '/(mysql(?:dump)?)/',
            "\\1 $this->credentials",
            vsprintf($command, array_slice(func_get_args(), 1))
        );

        self::debug(['%s'], [$command]);

        return trim(shell_exec($command));
    }
}
