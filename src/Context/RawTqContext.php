<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context;

// Contexts.
use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\DrupalExtension\Context as DrupalContexts;
// Exceptions.
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\DebugExtension\Debugger;
// Helpers.
use WebDriver\Session;
use Drupal\Driver\DrushDriver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
// Utils.
use Drupal\TqExtension\Utils\Tags;

/**
 * @see RawTqContext::__call()
 *
 * @method User\UserContext getUserContext()
 * @method Node\NodeContext getNodeContext()
 * @method Form\FormContext getFormContext()
 * @method Email\EmailContext getEmailContext()
 * @method Drush\DrushContext getDrushContext()
 * @method Wysiwyg\WysiwygContext getWysiwygContext()
 * @method Redirect\RedirectContext getRedirectContext()
 * @method TqContext getTqContext()
 * @method DrupalContexts\MinkContext getMinkContext()
 * @method DrupalContexts\DrupalContext getDrupalContext()
 * @method DrupalContexts\MessageContext getMessageContext()
 * @method \Drupal\Component\Utility\Random getRandom()
 */
class RawTqContext extends RawPageContext implements TqContextInterface
{
    use Debugger;
    use Tags;

    /**
     * Parameters of TqExtension.
     *
     * @var array
     */
    private $parameters = [];
    /**
     * @var string
     */
    protected static $pageUrl = '';

    /**
     * @param string $method
     * @param array $arguments
     *
     * @throws \Exception
     * @throws ContextNotFoundException
     *   When context class cannot be loaded.
     *
     * @return SnippetAcceptingContext
     */
    public function __call($method, array $arguments)
    {
        $environment = $this->getEnvironment();
        // @example
        // The "getFormContext" method is not declared and his name will be split by capital
        // letters, creating an array with three items: "get", "Form" and "Context".
        list(, $base, $context) = preg_split('/(?=[A-Z])/', $method);

        foreach ([
            [$this->getTqParameter('namespace'), 'Context', $base],
            ['Drupal', 'DrupalExtension', 'Context'],
        ] as $class) {
            $class[] = "$base$context";
            $class = implode('\\', $class);

            if ($environment->hasContextClass($class)) {
                return $environment->getContext($class);
            }
        }

        throw new \Exception(sprintf('Method %s does not exist', $method));
    }

    /**
     * @param array $variables
     *   An associative array where key is a variable name and a value - value.
     */
    public static function setDrupalVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            variable_set($name, $value);
        }
    }

    /**
     * Get selector by name.
     *
     * @param string $name
     *   Selector name from the configuration file.
     *
     * @return string
     *   CSS selector.
     *
     * @throws \Exception
     *   If selector does not exits.
     */
    public function getDrupalSelector($name)
    {
        $selectors = $this->getDrupalParameter('selectors');

        if (!isset($selectors[$name])) {
            throw new \Exception(sprintf('No such selector configured: %s', $name));
        }

        return $selectors[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getDrupalText($name)
    {
        // Make text selectors translatable.
        return t(parent::getDrupalText($name));
    }

    /**
     * @param string $site
     *   Drupal site folder.
     *
     * @return string
     *   URL to files directory.
     */
    public function getFilesUrl($site = 'default')
    {
        return $this->locatePath("sites/$site/files");
    }

    /**
     * @param string $text
     *   JS code for processing.
     *
     * @return self
     */
    protected function processJavaScript(&$text)
    {
        $text = str_replace(['$'], ['jQuery'], $text);

        return $this;
    }

    /**
     * @return InitializedContextEnvironment
     */
    public function getEnvironment()
    {
        return $this->getDrupal()->getEnvironment();
    }

    /**
     * @return Selenium2Driver
     */
    public function getSessionDriver()
    {
        return $this->getSession()->getDriver();
    }

    /**
     * @return Session
     */
    public function getWebDriverSession()
    {
        return $this->getSessionDriver()->getWebDriverSession();
    }

    /**
     * @todo Remove this when DrupalExtension will be used Mink >=1.6 and use $this->getSession->getWindowNames();
     *
     * @return string[]
     */
    public function getWindowNames()
    {
        return $this->getWebDriverSession()->window_handles();
    }

    /**
     * @param NodeElement $element
     * @param string $script
     *
     * @example
     * $this->executeJsOnElement($this->element('*', 'Meta tags'), 'return jQuery({{ELEMENT}}).text();');
     * $this->executeJsOnElement($this->element('*', '#menu'), '{{ELEMENT}}.focus();');
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function executeJsOnElement(NodeElement $element, $script)
    {
        $session = $this->getWebDriverSession();
        // We need to trigger something with "withSyn" method, because, otherwise an element won't be found.
        $element->focus();

        $this->processJavaScript($script);
        self::debug([$script]);

        return $session->execute([
            'script' => str_replace('{{ELEMENT}}', 'arguments[0]', $script),
            'args' => [['ELEMENT' => $session->element('xpath', $element->getXpath())->getID()]],
        ]);
    }

    /**
     * @param string $javascript
     *   JS code for execution.
     * @param array $args
     *   Placeholder declarations.
     *
     * @return mixed
     */
    public function executeJs($javascript, array $args = [])
    {
        $javascript = format_string($javascript, $args);

        $this->processJavaScript($javascript);
        self::debug([$javascript]);

        return $this->getSession()->evaluateScript($javascript);
    }

    /**
     * @param string $file
     *   Existing file from "src/JavaScript" without ".js" extension.
     * @param bool $delete
     *   Whether injection should be deleted.
     */
    protected static function injectCustomJavascript($file, $delete = false)
    {
        $file .= '.js';
        $modulePath = drupal_get_filename('module', 'system');
        $destination = dirname($modulePath) . '/' . $file;
        $injection = "\ndrupal_add_js('$destination', array('every_page' => TRUE));";

        if ($delete) {
            file_unmanaged_delete("$destination");

            $search = $injection;
            $replace = '';
        } else {
            file_unmanaged_copy(
                str_replace('Context', 'JavaScript', __DIR__) . '/' . $file,
                $destination,
                FILE_EXISTS_REPLACE
            );

            $search = 'system_add_module_assets();';
            $replace = $search . $injection;
        }

        file_put_contents($modulePath, str_replace($search, $replace, file_get_contents($modulePath)));
    }

    /**
     * Check JS events in step definition.
     *
     * @param StepScope $event
     *
     * @return int
     */
    public static function isStepImpliesJsEvent(StepScope $event)
    {
        return preg_match('/(follow|press|click|submit)/i', $event->getStep()->getText());
    }

    /**
     * @return DrushDriver
     */
    public function getDrushDriver()
    {
        return $this->getDriver('drush');
    }

    /**
     * Wait for all AJAX requests and jQuery animations.
     */
    public function waitAjaxAndAnimations()
    {
        $this->getSession()
             ->wait(1000, "window.__behatAjax === false && !jQuery(':animated').length && !jQuery.active");
    }

    /**
     * {@inheritdoc}
     */
    public function setTqParameters(array $parameters)
    {
        if (empty($this->parameters)) {
            $this->parameters = $parameters;
        }
    }

    /**
     * @param string $name
     *   The name of parameter from behat.yml.
     *
     * @return mixed
     */
    public function getTqParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function locatePath($path = '')
    {
        // Obtain base URL when path is empty, or not starts from "//" or "http".
        if (empty($path) || strpos($path, '//') !== 0 && strpos($path, 'http') !== 0) {
            $path = rtrim($this->getMinkParameter('base_url'), '/') . '/' . ltrim($path, '/');
        }

        $url = parse_url($path);

        if (!isset($url['host'])) {
            throw new \InvalidArgumentException(sprintf('Incorrect URL: %s', func_get_arg(0)));
        }

        // When URL starts from "//" the "scheme" key will not exists.
        if (isset($url['scheme'])) {
            // Check scheme.
            if (!in_array($url['scheme'], ['http', 'https'])) {
                throw new \InvalidArgumentException(sprintf('%s is not valid scheme.', $url['scheme']));
            }

            $path = $url['scheme'] . ':';
        } else {
            // Process "//" at the start.
            $path = '';
        }

        $path .= '//';

        if (isset($url['user'], $url['pass'])) {
            // Encode special characters in username and password. Useful
            // when some item contain something like "@" symbol.
            foreach (['user' => ':', 'pass' => '@'] as $part => $suffix) {
                $path .= rawurlencode($url[$part]) . $suffix;
            }
        }

        $path .= $url['host'];

        // Append additional URL components.
        foreach (['port' => ':', 'path' => '', 'query' => '?', 'fragment' => '#'] as $part => $prefix) {
            if (isset($url[$part])) {
                $path .= $prefix . $url[$part];
            }
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->locatePath($this->getSession()->getCurrentUrl());
    }
}
