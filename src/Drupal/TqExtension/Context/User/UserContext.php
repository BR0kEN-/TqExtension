<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\User;

// Helpers.
use Behat\Gherkin\Node\TableNode;
use Drupal\TqExtension\EntityDrupalWrapper;

class UserContext extends RawUserContext
{
    /**
     * @example
     * Then I am logged in as a user with "CRM Client" role and filled fields
     *   | Full name                | Sergey Bondarenko |
     *   | Position                 | Developer         |
     *   | field_crm_user_company   | FFW               |
     *
     * @param string $roles
     *   User roles, separated by comma.
     * @param TableNode $fields
     *   | Field machine name or label | Value |
     *
     * @throws \EntityMetadataWrapperException
     *   When user object cannot be saved.
     * @throws \Exception
     *   When required fields are not filled.
     *
     * @Given /^(?:|I am )logged in as a user with "([^"]*)" role(?:|s)(?:| and filled fields)$/
     *
     * @user @api
     */
    public function createDrupalUser($roles, TableNode $fields = null)
    {
        $user = $this->createUserWithRoles($roles);

        if ($fields !== null) {
            $entity = new EntityDrupalWrapper('user');
            $wrapper = $entity->getWrapper(user_load($user->uid));
            $required = $entity->getRequiredFields();

            // Fill fields. Field can be found by name or label.
            foreach ($fields->getRowsHash() as $field_name => $value) {
                $field_info = $entity->getFieldInfo($field_name);

                if (empty($field_info)) {
                    continue;
                }

                $field_name = $field_info['field_name'];

                switch ($field_info['type']) {
                    case 'taxonomy_term_reference':
                        // Try to find taxonomy term by it name.
                        $terms = taxonomy_term_load_multiple([], ['name' => $value]);

                        if (empty($terms)) {
                            throw new \InvalidArgumentException(sprintf('Taxonomy term "%s" does no exist.', $value));
                        }

                        $value = key($terms);
                        break;
                }

                $wrapper->{$field_name}->set($value);

                // Remove field from $required if it was there and filled.
                if (isset($required[$field_name])) {
                    unset($required[$field_name]);
                }
            }

            // Throw an exception when one of required fields was not filled.
            if (!empty($required)) {
                throw new \Exception(sprintf(
                    'The following fields "%s" are required and has not filled.',
                    implode('", "', $required)
                ));
            }

            $wrapper->save();
        }

        $this->loginUser();
    }

    /**
     * @param TableNode $credentials
     *   | username | BR0kEN |
     *   | password | p4sswd |
     *
     * @throws \Exception
     *   When user cannot be authorized.
     *
     * @Given /^(?:|I )am logged in with credentials:/
     *
     * @user
     */
    public function loginWithCredentials(TableNode $credentials)
    {
        $this->fillLoginForm($credentials->getRowsHash());
    }

    /**
     * This step must be used instead of "I am an anonymous user" because it
     * has more strict checking for authenticated user.
     *
     * @Given /^I am unauthorized user$/
     *
     * @user
     */
    public function logoutDrupalUser()
    {
        $this->logoutUser();
    }
}
