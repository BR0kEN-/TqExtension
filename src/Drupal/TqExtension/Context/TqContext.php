<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

// Helpers.
use Behat\Behat\Hook\Scope;

class TqContext extends RawTqContext
{
    /**
     * The name and working element of main window.
     *
     * @var array
     */
    private $mainWindow = [];

    /**
     * Supports switching between the two windows only.
     *
     * @Given /^(?:|I )switch to opened window$/
     * @Then /^(?:|I )switch back to main window$/
     */
    public function iSwitchToWindow()
    {
        $windows = $this->getWindowNames();

        // If the window was not switched yet, then store it name and working element for future switching back.
        if (empty($this->mainWindow)) {
            $this->mainWindow['name'] = array_shift($windows);
            $this->mainWindow['element'] = $this->getWorkingElement();

            $window = reset($windows);
        } else {
            $window = $this->mainWindow['name'];
            $element = $this->mainWindow['element'];

            $this->mainWindow = [];
        }

        $this->getSession()->switchToWindow($window);
        $this->setWorkingElement(isset($element) ? $element : $this->getBodyElement());
    }

    /**
     * @Given /^(?:|I )switch to CKFinder window$/
     *
     * @javascript
     */
    public function iSwitchToCKFinderWindow()
    {
        $this->iSwitchToWindow();
        $this->executeJsOnElement(
            $this->element('css', 'iframe'),
            "{{ELEMENT}}.setAttribute('id', 'behat_ckfinder');"
        );
        $this->iSwitchToAnIframe('behat_ckfinder');
    }

    /**
     * @param string $name
     *   An iframe name (null for switching back).
     *
     * @Given /^(?:|I )switch to an iframe "([^"]*)"$/
     * @Then /^(?:|I )switch back from an iframe$/
     */
    public function iSwitchToAnIframe($name = null)
    {
        $this->getSession()->switchToIFrame($name);
    }

    /**
     * Open the page with specified resolution.
     *
     * @param string $width_height
     *   String that satisfy the condition "<WIDTH>x<HEIGHT>".
     *
     * @example
     * Given I should use the "1280x800" resolution
     *
     * @Given /^(?:|I should )use the "([^"]*)" screen resolution$/
     */
    public function useScreenResolution($width_height)
    {
        list($width, $height) = explode('x', $width_height);

        $this->getSessionDriver()->resizeWindow((int) $width, (int) $height);
    }

    /**
     * @param string $action
     *   The next actions can be: "press", "click", "double click" and "right click".
     * @param string $selector
     *   CSS, inaccurate text or selector name from behat.yml can be used.
     *
     * @throws \WebDriver\Exception\NoSuchElement
     *   When element was not found.
     *
     * @Given /^(?:|I )((?:|(?:double|right) )click|press) on "([^"]*)"$/
     */
    public function pressElement($action, $selector)
    {
        $this->beforeStep();
        // 1. Get the action, divide string by spaces and put it parts into an array.
        // 2. Apply the "ucfirst" function for each array element.
        // 3. Make string from an array.
        // 4. Set the first letter of a string to lower case.
        $this->element('*', $selector)->{lcfirst(implode(array_map('ucfirst', explode(' ', $action))))}();
    }

    /**
     * @Given /^(?:|I )wait until AJAX is finished$/
     *
     * @javascript
     */
    public function waitUntilAjaxIsFinished()
    {
        $this->waitAjaxAndAnimations();
    }

    /**
     * @param string $selector
     *   CSS selector or region name.
     *
     * @Then /^(?:|I )work with elements in "([^"]*)"(?:| region)$/
     */
    public function workWithElementsInRegion($selector)
    {
        $this->setWorkingElement($this->element('css', $selector));
    }

    /**
     * @Then /^I checkout to whole page$/
     */
    public function unsetWorkingElementScope()
    {
        $this->unsetWorkingElement();
    }

    /**
     * @param int $seconds
     *   Amount of seconds when nothing to happens.
     *
     * @Given /^(?:|I )wait (\d+) seconds$/
     */
    public function waitSeconds($seconds)
    {
        sleep($seconds);
    }

    /**
     * @param string $selector
     *   Text or CSS.
     *
     * @throws \Exception
     *
     * @Given /^(?:|I )scroll to "([^"]*)" element$/
     *
     * @javascript
     */
    public function scrollToElement($selector)
    {
        if (!$this->hasTag('javascript')) {
            throw new \Exception('Scrolling to an element is impossible without a JavaScript.');
        }

        $this->executeJsOnElement($this->findElement($selector), '{{ELEMENT}}.scrollIntoView(true);');
    }

    /**
     * @BeforeSuite @api
     */
    public static function beforeSuite()
    {
        self::setDrupalVariables([
            // Set to "FALSE", because the administration menu will not be rendered.
            // https://www.drupal.org/node/2023625#comment-8607207
            'admin_menu_cache_client' => false,
        ]);
    }

    /**
     * @param Scope\BeforeScenarioScope $scope
     *
     * @BeforeScenario
     */
    public function beforeScenario(Scope\BeforeScenarioScope $scope)
    {
        foreach (array_merge($scope->getFeature()->getTags(), $scope->getScenario()->getTags()) as $tag) {
            $values = explode(':', $tag);
            $value = '';

            if (count($values) > 1) {
                list($tag, $value) = $values;
            }

            self::$tags[strtolower($tag)] = $value;
        }

        // Any page should be visited due to using jQuery and checking the cookies.
        $this->visitPath('/');
        // By "Goutte" session we need to visit any page to be able to set a cookie
        // for this session and use it for checking request status codes.
        $this->visitPath('/', 'goutte');
    }

    /**
     * Set the jQuery handlers for "start" and "finish" events of AJAX queries.
     * In each method can be used the "waitAjaxAndAnimations" method for check
     * that AJAX was finished.
     *
     * @see RawTqContext::waitAjaxAndAnimations()
     *
     * @BeforeScenario @javascript
     */
    public function beforeScenarioJS()
    {
        $javascript = '';

        foreach (['Start' => 'true', 'Complete' => 'false'] as $name => $state) {
            $javascript .= "jQuery(document).bind('ajax$name', function() {window.__behatAjax=$state;});";
        }

        $this->getSession()->executeScript($javascript);
    }

    /**
     * IMPORTANT! The "BeforeStep" hook should not be tagged, because steps has no tags!
     *
     * @BeforeStep
     */
    public function beforeStep(Scope\BeforeStepScope $step = null)
    {
        if (null !== $step) {
            $this->pageUrl = $this->unTrailingSlashIt($this->getSession()->getCurrentUrl());
        }
    }

    /**
     * IMPORTANT! The "AfterStep" hook should not be tagged, because steps has no tags!
     *
     * @param Scope\AfterStepScope $step
     *
     * @AfterStep
     */
    public function afterStep(Scope\AfterStepScope $step)
    {
        // If "mainWindow" variable is not empty that means that additional window has been opened.
        // Then, if number of opened windows equals to one, we need to switch back to original window,
        // otherwise an error will occur: "Window not found. The browser window may have been closed".
        // This happens due to auto closing window by JavaScript (CKFinder does this after choosing a file).
        if (!empty($this->mainWindow) && count($this->getWindowNames()) == 1) {
            $this->iSwitchToWindow();
        }

        if ($this->hasTag('javascript') && self::isStepImpliesJsEvent($step)) {
            $this->waitAjaxAndAnimations();
        }
    }
}
