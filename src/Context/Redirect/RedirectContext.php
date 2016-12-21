<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Redirect;

// Helpers.
use Behat\DebugExtension\Message;
use Behat\Gherkin\Node\TableNode;

class RedirectContext extends RawRedirectContext
{
    /**
     * @param string $page
     *   Expected page URL.
     *
     * @throws \Exception
     * @throws \OverflowException
     *
     * @Then /^(?:|I )should be redirected(?:| on "([^"]*)")$/
     */
    public function shouldBeRedirected($page = null)
    {
        $wait = $this->getTqParameter('wait_for_redirect');
        $pages = [];
        $seconds = 0;

        new Message('comment', 4, ['Waiting %s seconds for redirect...'], [$wait]);

        if (isset($page)) {
            $page = trim($page, '/');
            $pages = [$page, $this->locatePath($page)];
        }

        while ($wait >= $seconds++) {
            $url = $this->getCurrentUrl();
            $raw = explode('?', $url)[0];

            self::debug([
                'Expected URLs: %s',
                'Current URL: %s',
            ], [
                implode(', ', $pages),
                $raw,
            ]);

            if ((!empty($pages) && in_array($raw, $pages)) || $url === self::$pageUrl) {
                return;
            }

            sleep(1);
        }

        throw new \OverflowException('The waiting time is over.');
    }

    /**
     * @example
     * Given user should have an access to the following pages
     *   | page/url |
     *
     * @param string $not
     * @param TableNode $paths
     *
     * @throws \Exception
     *
     * @Given /^user should(| not) have an access to the following pages:$/
     */
    public function checkUserAccessToPages($not, TableNode $paths)
    {
        $code = empty($not) ? 200 : 403;
        $fails = [];

        foreach (array_keys($paths->getRowsHash()) as $path) {
            if (!$this->assertStatusCode($path, $code)) {
                $fails[] = $path;
            }
        }

        if (!empty($fails)) {
            throw new \Exception(sprintf(
                'The following paths: "%s" are %s accessible!',
                implode(', ', $fails),
                $not ? '' : 'not'
            ));
        }
    }

    /**
     * This step should be used instead of "I am at" if page should be checked
     * for accessibility before visiting.
     *
     * Also, this step can be replaced by:
     *   Then I am at "page/url"
     *
     * @param string $path
     *   Path to visit.
     * @param string|int $code
     *   Expected HTTP status code.
     *
     * @throws \Exception
     *
     * @Given /^I am on the "([^"]*)" page(?:| and HTTP code is "([^"]*)")$/
     * @Given /^(?:|I )visit the "([^"]*)" page(?:| and HTTP code is "([^"]*)")$/
     */
    public function visitPage($path, $code = 200)
    {
        if (!$this->assertStatusCode($path, $code)) {
            throw new \Exception(sprintf('The page "%s" is not accessible!', $path));
        }

        self::debug(['Visited page: %s'], [$path]);

        $this->visitPath($path);
    }
}
