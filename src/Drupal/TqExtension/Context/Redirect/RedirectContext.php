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
     * Initial URL of the page.
     *
     * @var string
     */
    private $startUrl = '';

    /**
     * @BeforeStep
     */
    public function beforeShouldBeRedirected()
    {
        $this->startUrl = $this->unTrailingSlashIt($this->getSession()->getCurrentUrl());
    }

    /**
     * @param string $page
     *   Expected page URL.
     *
     * @throws \Exception
     * @throws \OverflowException
     *
     * @Then /^(?:|I )should be redirected(?:| on "([^"]*)")$/
     *
     * @redirect
     */
    public function shouldBeRedirected($page = null)
    {
        $seconds = 0;
        $wait = $this->getTqParameter('wait_for_redirect');

        $this->consoleOutput('comment', 4, ['Waiting %s seconds for redirect...'], $wait);

        while ($wait >= $seconds++) {
            $url = $this->unTrailingSlashIt($this->getSession()->getCurrentUrl());
            sleep(1);

            if ($url != $this->startUrl) {
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
     * @param string $not
     * @param TableNode $paths
     *
     * @throws \Exception
     *
     * @Given /^user should(| not) have an access to the following pages$/
     *
     * @redirect
     */
    public function checkUserAccessToPages($not, TableNode $paths)
    {
        $code = empty($not) ? 200 : 403;
        $result = [];

        foreach (array_keys($paths->getRowsHash()) as $path) {
            if (!$this->assertStatusCode($path, $code)) {
                $result[] = $this->unTrailingSlashIt($path);
            }
        }

        if (!empty($result)) {
            throw new \Exception(sprintf(
                'The following paths: "%s" are %s accessible!',
                implode(', ', $result),
                $not ? '' : 'not'
            ));
        }
    }

    /**
     * This step should be used instead of "I am at" if page should be checked
     * for accessibility before visiting.
     *
     * Also, this step can be replaced by:
     * @example
     * Given user should have an access to the following pages
     *   | page/url |
     * Then I am at "page/url"
     *
     * @param string $path
     *
     * @throws \Exception
     *
     * @Given /^I am on the "([^"]*)" page$/
     * @Given /^(?:|I )visit the "([^"]*)" page$/
     *
     * @redirect
     */
    public function iAmOnThePage($path)
    {
        if (!$this->assertStatusCode($path, 200)) {
            throw new \Exception(sprintf('The page "%s" is not accessible!', $path));
        }

        $this->visitPath($path);
    }
}
