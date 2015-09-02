<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Wysiwyg;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

// Exceptions.
use WebDriver\Exception\NoSuchElement;

class RawWysiwygContext extends RawTqContext
{
    protected $wysiwyg = '';
    protected $selector = '';

    /**
     * Get the editor instance for use in Javascript.
     *
     * @param string $selector
     *   Any selector of a form field.
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @throws NoSuchElement
     *
     * @return string
     *   A Javascript expression representing the WYSIWYG instance.
     */
    public function getWysiwygInstance($selector = '')
    {
        if (empty($selector) && empty($this->wysiwyg)) {
            throw new \RuntimeException('No such editor was not selected.');
        }

        $this->selector = $selector ?: $this->wysiwyg;
        $field = $this->findField($this->selector);

        $this->throwNoSuchElementException($this->selector, $field);
        $id = $field->getAttribute('id');

        $instance = "CKEDITOR.instances['$id']";
        $session = $this->getSession();

        if (!$session->evaluateScript("return !!$instance")) {
            throw new \Exception(sprintf(
                'The editor "%s" was not found on the page %s',
                $id,
                $session->getCurrentUrl()
            ));
        }

        return $instance;
    }

    /**
     * @param string $method
     *   WYSIWYG editor method.
     * @param string|array $arguments
     *   Arguments for method of WYSIWYG editor.
     * @param string $selector
     *   Editor selector.
     *
     * @throws \Exception
     *   Throws an exception if the editor does not exist.
     *
     * @return string
     *   Result of JS evaluation.
     */
    public function executeWysiwygMethod($method, $arguments = '', $selector = '')
    {
        if ($arguments && is_array($arguments)) {
            $arguments = "'" . implode("','", $arguments) . "'";
        }

        $editor = $this->getWysiwygInstance($selector);
        return $this->getSession()->evaluateScript("$editor.$method($arguments);");
    }
}
