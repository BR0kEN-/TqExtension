<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Form;

// Exceptions.
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Behat\Mink\Exception\ElementNotFoundException;
use WebDriver\Exception\NoSuchElement;

// Helpers.
use Behat\Gherkin\Node\TableNode;
use WebDriver\Service\CurlService;
use Behat\Mink\Element\NodeElement;
use Drupal\TqExtension\EntityDrupalWrapper;

class FormContext extends RawFormContext
{
    /**
     * @param string $value
     *   Typed text.
     * @param string $selector
     *   Selector of the field.
     * @param int $option
     *   An option number. Will be selected from loaded variants.
     *
     * @throws \InvalidArgumentException
     *   When $option is less than zero.
     * @throws NoSuchElement
     *   When autocomplete list was not loaded.
     * @throws \RuntimeException
     *   When neither option was not loaded.
     * @throws \OverflowException
     *   When $option is more than variants are available.
     * @throws \Exception
     *   When value was not changed.
     *
     * @Then /^(?:|I )typed "([^"]*)" in the "([^"]*)" field and chose (\d+) option from autocomplete variants$/
     *
     * @form @javascript
     */
    public function choseOptionFromAutocompleteVariants($value, $selector, $option)
    {
        if (!$option) {
            throw new \InvalidArgumentException(sprintf(
                'An option that will be chosen expected as positive number, but was got the: %s',
                $option
            ));
        }

        $field = $this->findField($selector);
        $this->throwNoSuchElementException($selector, $field);

        // Syn - a Standalone Synthetic Event Library, provided by Selenium.
        $this->executeJsOnElement($field, "Syn.type({{ELEMENT}}, '$value')");
        $this->waitAjaxAndAnimations();

        $autocomplete = $field->getParent()->findById('autocomplete');
        $this->throwNoSuchElementException('#autocomplete', $autocomplete);

        $options = count($autocomplete->findAll('css', 'li'));

        if ($options < 1) {
            throw new \RuntimeException('Neither option was not loaded.');
        }

        if ($option > $options) {
            throw new \OverflowException(sprintf(
                'You can not select an option %s, as there are only %d.',
                $option,
                $options
            ));
        }

        for ($i = 0; $i < $option; $i++) {
            // 40 - down
            $field->keyDown(40);
            $field->keyUp(40);
        }

        // 13 - enter
        $field->keyDown(13);
        $field->keyUp(13);

        if ($field->getValue() == $value) {
            throw new \Exception(sprintf('The value of "%s" field was not changed.', $selector));
        }
    }

    /**
     * Use the current user data for filling fields.
     *
     * @example
     * Then I fill "First name" with value of field "First name" of current user
     * And fill "field_last_name[und][0]" with value of field "field_user_last_name" of current user
     *
     * @param string $field
     *   The name of field to fill in. HTML Label, name or ID can be user as selector.
     * @param string $user_field
     *   The name of field from which the data will taken. Drupal label or machine name can be used as selector.
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws NoSuchElement
     *   When field cannot be found.
     *
     * @Then /^(?:I )fill "([^"]*)" with value of field "([^"]*)" of current user$/
     *
     * @form @api
     */
    public function fillInWithValueOfFieldOfCurrentUser($field, $user_field)
    {
        if ($this->user && !$this->user->uid) {
            throw new \Exception('Anonymous user have no fields');
        }

        $entity = new EntityDrupalWrapper('user');
        $wrapper = $entity->getWrapper(user_load($this->user->uid));
        $user_field = $entity->getFieldNameByLocator($user_field);

        if (empty($wrapper->$user_field)) {
            throw new \InvalidArgumentException(sprintf('User entity has no "%s" field.', $user_field));
        }

        $value = $wrapper->{$user_field}->value();

        if (empty($value)) {
            throw new \UnexpectedValueException('The value of "%s" field is empty.', $user_field);
        }

        $this->fillField($field, $value);
    }

    /**
     * @param string $action
     *   Can be "check" or "uncheck".
     * @param TableNode $checkboxes
     *   Table with one row of checkboxes selectors.
     *
     * @example
     * I uncheck the boxes:
     *   | Consumer Products  |
     *   | Financial Services |
     *
     * @example
     * I check the boxes:
     *   | Consumer Products  |
     *   | Financial Services |
     *
     * @Given /^(?:|I )(?:|un)check the boxes:/
     *
     * @form
     */
    public function checkboxAction($action, TableNode $checkboxes)
    {
        $minkContext = $this->getMinkContext();

        foreach ($checkboxes->getRows() as $checkbox) {
            $minkContext->{trim($action) . 'Option'}(reset($checkbox));
        }
    }

    /**
     * This method was defined and used instead of "assertSelectRadioById",
     * because the field label can contain too long value and better to use
     * another selector instead of label.
     *
     * @see MinkContext::assertSelectRadioById()
     *
     * @param string $customized
     *   Can be an empty string or " customized".
     * @param string $selector
     *   Field selector.
     *
     * @throws NoSuchElement
     *   When radio button was not found.
     * @throws \Exception
     *
     * @Given /^(?:|I )check the(| customized) "([^"]*)" radio button$/
     *
     * @form
     */
    public function radioAction($customized, $selector)
    {
        $field = $this->getWorkingElement()->findField($selector);
        $customized = (bool) $customized;

        if ($field !== null && !$customized) {
            $field->selectOption($field->getAttribute('value'));
            return;
        }

        // Find all labels of a radio button or only first, if it is not custom.
        foreach ($this->findLabels($selector) as $label) {
            // Check a custom label for visibility.
            if ($customized && !$label->isVisible()) {
                continue;
            }

            $label->click();
            return;
        }

        $this->throwNoSuchElementException($selector, $field);
    }

    /**
     * @param string $selector
     * @param string $value
     *
     * @throws NoSuchElement
     *
     * @When /^(?:|I )fill "([^"]*)" with "([^"]*)"$/
     *
     * @form
     */
    public function fillField($selector, $value)
    {
        $field = $this->findField($selector);

        $this->throwNoSuchElementException($selector, $field);
        $field->setValue($value);
    }

    /**
     * @param TableNode $fields
     *   | Field locator | Value |
     *
     * @throws NoSuchElement
     *
     * @When /^(?:|I )fill the following:$/
     *
     * @form
     */
    public function fillFields(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->fillField($field, $value);
        }
    }

    /**
     * @param string $file
     *   Path to a file. Relative to the directory specified in "files_path" in behat.yml.
     * @param string $selector
     *   Field selector (label|id|name).
     *
     * @throws \Exception
     * @throws NoSuchElement
     *
     * @Given /^(?:|I )attach file "([^"]*)" to "([^"]*)"$/
     *
     * @form
     */
    public function attachFile($file, $selector)
    {
        $filesPath = $this->getMinkParameter('files_path');

        if (!$filesPath) {
            throw new \Exception('The "files_path" Mink parameter was not configured.');
        }

        $file = rtrim(realpath($filesPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

        if (!is_file($file)) {
            throw new \InvalidArgumentException(sprintf('The "%s" file does not exist.', $file));
        }

        $field = $this->findField($selector);

        $this->throwNoSuchElementException($selector, $field);
        $field->attachFile($file);
    }

    /**
     * @param string $selector
     * @param TableNode $values
     *
     * @throws ElementNotFoundException
     * @throws \Exception
     * @throws NoSuchElement
     *
     * @Given /^(?:|I )select the following in "([^"]*)" hierarchical select:$/
     *
     * @form @javascript
     */
    public function setValueForHierarchicalSelect($selector, TableNode $values)
    {
        $element = $this->getWorkingElement();
        // Try to selects by wrapper ID.
        $wrapper = $element->findById($selector);

        if ($wrapper !== null) {
            $labels = $wrapper->findAll('xpath', '//label[@for]');
        } else {
            $labels = $this->findLabels($selector);
        }

        if (empty($labels)) {
            throw new \Exception('No one hierarchical select was found.');
        }

        /* @var NodeElement $label */
        $label = reset($labels);
        $parent = $label->getParent();

        foreach (array_keys($values->getRowsHash()) as $i => $value) {
            /* @var NodeElement[] $selects */
            $selects = [];

            /* @var NodeElement $select */
            foreach ($parent->findAll('css', 'select') as $select) {
                if ($select->isVisible()) {
                    $selects[] = $select;
                }
            }

            if (!isset($selects[$i])) {
                throw new \InvalidArgumentException(sprintf(
                    'The value "%s" was specified for select "%s" but it does not exist.',
                    $value,
                    $i
                ));
            }

            $selects[$i]->selectOption($value);
            $this->waitAjaxAndAnimations();
        }
    }

    /**
     * Check that an image was uploaded and can be viewed on the page.
     *
     * @throws \Exception
     * @throws FileNotFoundException
     *
     * @Then /^(?:|I )should see the thumbnail$/
     *
     * @form
     */
    public function iShouldSeeTheThumbnail()
    {
        $thumb = false;

        foreach (array('media-thumbnail', 'image-preview') as $classname) {
            if ($thumb) {
                break;
            }

            $thumb = $this->findByCss(".$classname img");
        }

        if (!$thumb) {
            throw new \Exception('An expected image tag was not found.');
        }

        $file = explode('?', $thumb->getAttribute('src'));
        $file = reset($file);

        $curl = new CurlService();
        list(, $info) = $curl->execute('GET', $file);

        if (empty($info) || strpos($info['content_type'], 'image/') === false) {
            throw new FileNotFoundException(sprintf('%s did not return an image', $file));
        }
    }

    /**
     * Check that the page have no error messages and fields - error classes.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @Then /^(?:|I )should see no errors$/
     *
     * @form
     */
    public function iShouldSeeNoErrors()
    {
        $selector = $this->getDrupalSelector('error_message_selector');

        if (empty($selector)) {
            throw new \InvalidArgumentException('The "error_message_selector" in behat.yml is not configured.');
        }

        $session = $this->getSession();
        $page = $session->getPage();
        $errors = $page->find('css', $this->getDrupalSelector('error_message_selector'));

        // Some modules are inserted an empty container for errors before
        // they are arise. The "Clientside Validation" - one of them.
        if ($errors) {
            $text = $errors->getText();

            if (!empty($text)) {
                throw new \RuntimeException(sprintf(
                    'The page "%s" contains following error messages: "%s"',
                    $session->getCurrentUrl(),
                    $text
                ));
            }
        }

        /* @var \Behat\Mink\Element\NodeElement $formElement */
        foreach ($page->findAll('css', 'input, select, textarea') as $formElement) {
            if ($formElement->hasClass('error')) {
                throw new \Exception(sprintf('Element "#%s" has an error class.', $formElement->getAttribute('id')));
            }
        }
    }
}
