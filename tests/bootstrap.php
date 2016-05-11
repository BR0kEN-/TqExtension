<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */

global $argv;

define('DRUPAL_PATH', __DIR__ . '/drupal_tqextension_phpunit');
define('DRUPAL_HOST', '127.0.0.1:1349');

define('DRUPAL_USER', 'admin');
define('DRUPAL_PASS', 'admin');

define('DRUPAL_DB_HOST', '127.0.0.1');
define('DRUPAL_DB_USER', 'root');
define('DRUPAL_DB_PASS', '');
define('DRUPAL_DB_NAME', basename(DRUPAL_PATH));

define('ROUTER_PATH', DRUPAL_PATH . '/router.php');
define('CONFIG_PATH', __DIR__ . '/behat/behat.yml');

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
    return shell_exec(sprintf('./bin/drush %s -r %s -y', vsprintf($command, $arguments), DRUPAL_PATH));
}

/**
 * Prepare configuration file for Behat.
 *
 * @param bool $restore
 *   Replace placeholders by actual data or restore original file.
 *
 * @return bool
 */
function prepare_config($restore = false)
{
    $arguments = [
      '<DRUPAL_HOST>' => DRUPAL_HOST,
      '<DRUPAL_PATH>' => DRUPAL_PATH,
    ];

    if ($restore) {
        $arguments = array_flip($arguments);
    }

    return file_put_contents(CONFIG_PATH, strtr(file_get_contents(CONFIG_PATH), $arguments));
}

$argsline = strtolower(implode($argv));

// Run Drupal-related tasks when a test suite directly specified.
if (strpos($argsline, 'functional') !== false || strpos($argsline, 'testsuite') === false) {
    // Drop and create database.
    (new Drupal\TqExtension\Utils\Database\Operator(DRUPAL_DB_USER, DRUPAL_DB_PASS, DRUPAL_DB_HOST))
        ->clear(DRUPAL_DB_NAME);

    // Download Drupal and rename the folder.
    if (!file_exists(DRUPAL_PATH)) {
        drush('dl drupal-7 --drupal-project-rename=%s --destination=%s', [
            DRUPAL_DB_NAME,
            dirname(DRUPAL_PATH),
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

    // Create router for built-in web-server.
    file_put_contents(
        ROUTER_PATH,
        "
        <?php
        \$url = parse_url(\$_SERVER['REQUEST_URI']);

        if (file_exists('.' . \$url['path'])) {
            // Serve requested resource as-is.
            return false;
        }

        \$_GET['q'] = trim(\$url['path'], '/');
        require 'index.php';
        "
    );

    // Run built-in PHP web-server.
    $processId = shell_exec(sprintf(
        'php -S %s -t %s %s >/dev/null 2>&1 & echo $!',
        DRUPAL_HOST,
        DRUPAL_PATH,
        ROUTER_PATH
    ));

    chdir(dirname(CONFIG_PATH));
    prepare_config();

    register_shutdown_function(function () use ($processId) {
        shell_exec("kill $processId");
        prepare_config(true);
    });
}
