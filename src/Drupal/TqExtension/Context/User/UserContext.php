<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\User;

// Helpers.
use Behat\Gherkin\Node\TableNode;

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
     * @Given /^(?:|I am )logged in as a user with "([^"]*)" role(?:|s)(?:| and filled fields:)$/
     *
     * @user @api
     */
    public function loginCreatedUser($roles, TableNode $fields = null)
    {
        $this->createDrupalUser($roles, $fields);
        $this->loginUser();
    }

    /**
     * @see loginCreatedUser()
     *
     * @Then /^(?:|I )create a user with "([^"]*)" role(?:|s)(?:| and filled fields:)$/
     *
     * @user @api
     */
    public function createDrupalUser($roles, TableNode $fields = null)
    {
        $this->createUserWithRoles($roles, null !== $fields ? $fields->getRowsHash() : []);
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
