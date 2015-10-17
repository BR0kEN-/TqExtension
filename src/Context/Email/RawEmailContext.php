<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Email;

// Contexts.
use Drupal\TqExtension\Utils\Imap;
use Drupal\TqExtension\Context\RawTqContext;

class RawEmailContext extends RawTqContext
{
    use Imap;

    private $messages = [];
    private $email = '';

    /**
     * @param string $to
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getEmailMessages($to = '')
    {
        // Update address for checking.
        if (!empty($to) && $this->email !== $to) {
            $this->email = $to;
        }

        if (empty($this->messages[$this->email])) {
            $messages = $this->hasTag('imap')
              ? $this->getMessagesViaImap($this->email)
              : $this->getMessagesFromDb();

            if (empty($messages)) {
                throw new \RuntimeException(sprintf('The message for "%s" was not sent.', $this->email));
            }

            foreach ($messages as &$message) {
                if ($message['to'] === $this->email) {
                    $message['links'] = $this->parseLinksText($message['body']);
                }
            }

            $this->messages[$this->email] = $messages;
        }

        // The debug messages may differ due to testing testing mode:
        // Drupal mail system collector or IMAP protocol.
        $this->debug([var_export($this->messages[$this->email], true)]);

        return $this->messages[$this->email];
    }

    /**
     * @param string $email
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getAccount($email)
    {
        $accounts = $this->getTqParameter('email_accounts');

        if (empty($accounts[$email])) {
            throw new \InvalidArgumentException(sprintf(
                'An account for "%s" email address is not defined. Available addresses: "%s".',
                $email,
                implode(', ', array_keys($accounts))
            ));
        }

        return $accounts[$email];
    }

    public function parseLinksText($string)
    {
        $dom = new \DOMDocument;
        $links = [];

        // @todo Find a way to do the same nicer.
        @$dom->loadHTML($string);

        /* @var \DOMElement $link */
        foreach ((new \DOMXPath($dom))->query('//a[@href]') as $link) {
            $links[$link->textContent] = $link->getAttribute('href');
        }

        if (empty($links)) {
            preg_match_all('/((?:http(?:s)?|www)[^\s]+)/i', $string, $matches);

            if (!empty($matches[1])) {
                $links = $matches[1];
            }
        }

        return $links;
    }

    private function getMessagesViaImap($email)
    {
        $account = $this->getAccount($email);
        $timeout = $this->getTqParameter('wait_for_email');

        $this->setConnection($email, $account['imap'], $account['username'], $account['password']);

        if ($timeout > 0) {
            $this->consoleOutput('comment', 4, ['Waiting %s seconds for letter...'], $timeout);
            sleep($timeout);
        }

        return $this->getMessages($email);
    }

    private function getMessagesFromDb()
    {
        // We can't use variable_get() because Behat has another bootstrapped
        // variable $conf that is not updated from cURL bootstrapped Drupal instance.
        $result = db_select('variable', 'v')
            ->fields('v', ['value'])
            ->condition('name', 'drupal_test_email_collector')
            ->execute()
            ->fetchField();

        return empty($result) ? [] : unserialize($result);
    }
}
