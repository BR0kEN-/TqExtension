<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Redirect;

// Helpers.
use Behat\Gherkin\Node\TableNode;
use Behat\Mink as Mink;

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
        $seconds = 0;
        $wait = $this->getTqParameter('wait_for_redirect');

        $this->consoleOutput('comment', 4, ['Waiting %s seconds for redirect...'], $wait);

        while ($wait >= $seconds++) {
            $url = $this->unTrailingSlashIt($this->getSession()->getCurrentUrl());
            sleep(1);

            if ($url != $this->pageUrl) {
                if (isset($page)) {
                    $page = $this->unTrailingSlashIt($page);

                    if (!in_array($url, [$page, $this->getBaseUrl() . "/$page"])) {
                        continue;
                    }
                }

                return;
            }
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
                $fails[] = $this->unTrailingSlashIt($path);
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
     *
     * @throws \Exception
     *
     * @Given /^I am on the "([^"]*)" page$/
     * @Given /^(?:|I )visit the "([^"]*)" page$/
     */
    public function visitPage($path)
    {
        if (!$this->assertStatusCode($path, 200)) {
            throw new \Exception(sprintf('The page "%s" is not accessible!', $path));
        }

        $this->visitPath($path);
    }
}
