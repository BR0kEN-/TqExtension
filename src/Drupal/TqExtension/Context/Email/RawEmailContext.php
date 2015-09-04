<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Email;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;
use Drupal\TqExtension\Utils\Imap;

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
        if (!empty($to) && $this->email !== $to) {
            $this->email = $to;
        }

        if (empty($this->messages[$this->email])) {
            $messages = $this->hasTag('imap') ? $this->getMessagesViaImap($this->email) : $this->getMessagesFromDb();

            foreach ($messages as &$message) {
                if ($message['to'] === $this->email) {
                    $message['links'] = $this->parseLinksText($message['body']);
                }
            }

            $this->messages[$this->email] = $messages;
        }

        if (empty($this->messages[$this->email])) {
            throw new \RuntimeException(sprintf('The message for "%s" was not sent.', $this->email));
        }

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
                'An account "%s" is not defined. Available accounts: "%s".',
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
        $wait = $this->getTqParameter('wait_for_email');

        $this->setConnection($email, $account['imap'], $account['username'], $account['password']);

        if ($wait > 0) {
            $this->consoleOutput('comment', 4, ['Waiting %s seconds for letter...'], $wait);

            sleep($wait);
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
