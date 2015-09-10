<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Email;

// Helpers.
use Behat\Gherkin\Node\TableNode;

class EmailContext extends RawEmailContext
{
    const PARSE_STRING = '(.+?)';

    private $originalMailSystem = [
        'default-system' => 'DefaultMailSystem',
    ];

    /**
     * @param string $to
     * @param string $type
     *   Can be "was sent" or "contains".
     * @param TableNode $values
     *   | subject | New email letter   |
     *   | body    | The body of letter |
     *   | from    | admin@example.com  |
     *
     * @throws \Exception
     *
     * @Given /^(?:|I )check that email for "([^"]*)" (was sent|contains:)$/
     * @Then /^also check that email contains:$/
     */
    public function checkEmail($to = '', $type = '', TableNode $values = null)
    {
        $emptyData = null === $values;

        if ('contains' === $type && $emptyData) {
            throw new \RuntimeException('At leas one field should be specified.');
        }

        if (!$emptyData) {
            $rows = $values->getRowsHash();

            foreach ($this->getEmailMessages($to) as $message) {
                $failed = [];

                $this->debug([var_export($message, true)]);

                foreach ($rows as $field => $value) {
                    if (empty($message[$field]) || strpos($message[$field], $value) === false) {
                        $failed[$field] = $value;
                    }
                }

                if (!empty($failed)) {
                    $exception = [];

                    foreach ($failed as $field => $value) {
                        $exception[] = sprintf('The "%s" field has not contain the "%s" value.', $field, $value);
                    }

                    throw new \Exception(implode(PHP_EOL, $exception));
                }
            }
        }
    }

    /**
     * @param string $link
     * @param string $to
     *
     * @Given /^(?:|I )click on link "([^"]*)" in email(?:| that was sent on "([^"]*)")$/
     */
    public function clickLink($link, $to = '')
    {
        foreach ($this->getEmailMessages($to) as $message) {
            if (!isset($message['links'][$link])) {
                $link = array_search($link, $message['links']);
            }

            if (isset($message['links'][$link])) {
                $this->visitPath($message['links'][$link]);
            }
        }
    }

    /**
     * @param string $to
     *
     * @throws \Exception
     *   When parameter "parse_mail_callback" was not specified.
     * @throws \InvalidArgumentException
     *   When parameter "parse_mail_callback" is not callable.
     * @throws \WebDriver\Exception\NoSuchElement
     *   When "Log in" button cannot be found on the page.
     * @throws \RuntimeException
     *   When credentials cannot be parsed or does not exist.
     *
     * @Given /^(?:|I )login with credentials that was sent on (?:"([^"]*)"|email)$/
     */
    public function loginWithCredentialsThatWasSentByEmail($to = '')
    {
        /**
         * Function must return an associative array with two keys: "username" and "password". The
         * value of each key should be a string with placeholder that will be replaced with user
         * login and password from an account. In testing, placeholders will be replaced by regular
         * expressions for parse the message that was sent.
         *
         * @example
         * @code
         * function mail_account_strings($name, $pass) {
         *     return array(
         *       'username' => t('Username: !mail', array('!mail' => $name)),
         *       'password' => t('Password: !pass', array('!pass' => $pass)),
         *     );
         * }
         *
         * // Drupal module.
         * function hook_mail($key, &$message, $params) {
         *     switch ($key) {
         *         case 'account':
         *             $message['subject'] = t('Website Account');
         *             $message['body'][] = t('You can login on the site using next credentials:');
         *             $message['body'] += mail_account_strings($params['mail'], $params['pass']);
         *         break;
         *     }
         * }
         *
         * // Behat usage.
         * mail_account_strings('(.+?)', '(.+?)');
         * @endcode
         *
         * @var callable $callback
         */
        $param = 'email_account_strings';
        $callback = $this->getTqParameter($param);

        if (empty($callback)) {
            throw new \Exception(sprintf(
                'The parameter "%s" does not specified in "behat.yml" for "%s" context.',
                $param,
                __CLASS__
            ));
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('The value of "%s" parameter is not callable.', $param));
        }

        $regexps = call_user_func($callback, self::PARSE_STRING, self::PARSE_STRING);
        $userContext = $this->getUserContext();

        foreach ($this->getEmailMessages($to) as $message) {
            if (!empty($message['body'])) {
                $matches = [];

                foreach (explode("\n", $message['body']) as $string) {
                    foreach ($regexps as $name => $regexp) {
                        if (!empty($regexp) && preg_match("/^$regexp$/i", $string, $match)) {
                            $matches[$name] = $match[1];
                        }
                    }
                }

                if (!empty($matches['username']) && !empty($matches['password'])) {
                    $userContext->fillLoginForm($matches);
                    break;
                }
            }
        }

        if (!$userContext->isLoggedIn()) {
            throw new \RuntimeException(
                'Failed to login because email does not contain user credentials or they are was not parsed correctly.'
            );
        }
    }

    /**
     * @BeforeScenario @email&&@api&&~@imap
     */
    public function beforeScenarioEmailApi()
    {
        $this->consoleOutput('comment', 2, [
            "Sending messages will be tested by storing them in a database instead of sending.",
            "This is the good choice, because you testing the application, not web-server.\n",
        ]);

        // Store original mail system to restore it after scenario.
        $this->originalMailSystem = variable_get('mail_system', $this->originalMailSystem);
        $this->setDrupalVariables([
            // Set the mail system for testing. It will store an emails in
            // "drupal_test_email_collector" Drupal variable instead of sending.
            'mail_system' => ['default-system' => 'TestingMailSystem'],
        ]);
    }

    /**
     * @AfterScenario @email&&@api&&~@imap
     */
    public function afterScenarioEmailApi()
    {
        $this->setDrupalVariables([
            // Bring back the original mail system.
            'mail_system' => $this->originalMailSystem,
            // Flush the email buffer, allowing us to reuse this step
            // definition to clear existing mail.
            'drupal_test_email_collector' => [],
        ]);
    }

    /**
     * @BeforeScenario @email&&@imap
     */
    public function beforeScenarioEmailImap()
    {
        $this->consoleOutput('comment', 2, [
            "Sending messages will be tested via IMAP protocol. You'll need to know, that the message",
            "simply cannot be delivered due to incorrect server configuration or third-party service",
            "problems. Would be better if you'll test this functionality using the <info>@api</info>.\n",
        ]);

        // Restore original mail system.
        $this->afterScenarioEmailApi();
    }

    /**
     * @AfterScenario @email&&@imap
     */
    public function afterScenarioEmailImap()
    {
        $this->closeConnections();
    }
}
