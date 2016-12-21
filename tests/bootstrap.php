<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Drupal configuration.
define('DRUPAL_CORE', getenv('DRUPAL_CORE') ?: '7');
define('DRUPAL_ROOT', __DIR__ . '/drupal_tqextension_phpunit_' . DRUPAL_CORE);
define('DRUPAL_HOST', '127.0.0.1:1349');
define('DRUPAL_USER', 'admin');
define('DRUPAL_PASS', 'admin');
// Drush configuration.
define('DRUSH_BINARY', realpath('./bin/drush'));
// Database configuration.
define('DRUPAL_DB_HOST', '127.0.0.1');
define('DRUPAL_DB_USER', 'root');
define('DRUPAL_DB_PASS', '');
define('DRUPAL_DB_NAME', basename(DRUPAL_ROOT));
// Behat configuration.
define('CONFIG_FILE', __DIR__ . '/behat/behat.yml');
// Routing configuration.
define('ROUTER_URL', 'https://gist.githubusercontent.com/shawnachieve/4592ea196d1c8519e3b6/raw/0fbf62e5f6ac6eca09f013d8ff9b080f0da50dbb/.ht_router.php');
define('ROUTER_FILE', DRUPAL_ROOT . '/' . basename(ROUTER_URL));

if (!in_array(DRUPAL_CORE, [7, 8])) {
    exit(sprintf("The %s version of Drupal core does not supported.\n", DRUPAL_CORE));
}

/**
 * Execute Drush command.
 *
 * @param string $command
 *   Drush command with arguments.
 * @param array $arguments
 *   Values for placeholders in first parameter.
 *
 * @return string
 */
function drush($command, array $arguments = [])
{
    return shell_exec(sprintf('%s %s -r %s -y', DRUSH_BINARY, vsprintf($command, $arguments), DRUPAL_ROOT));
}

/**
 * Prepare configuration file for Behat.
 *
 * @param bool $restore
 *   Replace placeholders by actual data or restore original file.
 *
 * @return bool
 */
function behat_config($restore = false)
{
    $arguments = [
        '<DRUPAL_HOST>' => DRUPAL_HOST,
        '<DRUPAL_PATH>' => DRUPAL_ROOT,
    ];

    if ($restore) {
        $arguments = array_flip($arguments);
    }

    return file_put_contents(CONFIG_FILE, strtr(file_get_contents(CONFIG_FILE), $arguments));
}

// Drop and create database.
(new Drupal\TqExtension\Utils\Database\Operator(DRUPAL_DB_USER, DRUPAL_DB_PASS, DRUPAL_DB_HOST))
    ->clear(DRUPAL_DB_NAME);

// Download Drupal and rename the folder.
if (!file_exists(DRUPAL_ROOT)) {
    drush('dl drupal-%s --drupal-project-rename=%s --destination=%s', [
        DRUPAL_CORE,
        DRUPAL_DB_NAME,
        dirname(DRUPAL_ROOT),
    ]);
}

// Install Drupal.
drush('si standard --db-url=mysql://%s:%s@%s/%s --account-name=%s --account-pass=%s', [
    DRUPAL_DB_USER,
    DRUPAL_DB_PASS,
    DRUPAL_DB_HOST,
    DRUPAL_DB_NAME,
    DRUPAL_USER,
    DRUPAL_PASS,
]);

if (!file_exists(ROUTER_FILE)) {
    $index = sprintf('%s/index.php', DRUPAL_ROOT);

    shell_exec(sprintf('wget -O %s %s', ROUTER_FILE, ROUTER_URL));
    file_put_contents($index, str_replace('getcwd()', "'" . DRUPAL_ROOT . "'", file_get_contents($index)));
}

// Run built-in PHP web-server.
$processId = shell_exec(sprintf(
    'php -S %s -t %s %s >/dev/null 2>&1 & echo $!',
    DRUPAL_HOST,
    DRUPAL_ROOT,
    ROUTER_FILE
));

// Bootstrap Drupal to make available an API.
$_SERVER['REMOTE_ADDR'] = 'localhost';

switch (DRUPAL_CORE) {
    case 7:
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
        break;

    case 8:
        $autoloader = require_once DRUPAL_ROOT . '/autoload.php';
        $kernel = new DrupalKernel('prod', $autoloader);
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
        break;
}

// Initialize Behat configuration.
behat_config();

register_shutdown_function(function () use ($processId) {
    shell_exec("kill $processId > /dev/null 2>&1");
    behat_config(true);
});
