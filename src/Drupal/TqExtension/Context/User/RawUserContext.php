<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\User;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

class RawUserContext extends RawTqContext
{
    /**
     * @param string $roles
     *   Necessary user roles separated by comma.
     *
     * @return \stdClass
     */
    public function createUserWithRoles($roles)
    {
        $user = $this->createTestUser();
        $driver = $this->getDriver();

        foreach (array_map('trim', explode(',', $roles)) as $role) {
            if (!in_array(strtolower($role), ['authenticated', 'authenticated user'])) {
                // Only add roles other than 'authenticated user'.
                $driver->userAddRole($user, $role);
            }
        }

        return $user;
    }

    /**
     * @throws \Exception
     */
    public function loginUser()
    {
        $this->logoutUser();

        if (!$this->user) {
            throw new \Exception('Tried to login without a user.');
        }

        $this->fillLoginForm([
            'username' => $this->user->name,
            'password' => $this->user->pass,
        ]);
    }

    /**
     * @param array $props
     *   An array with two keys: "username" and "password". Both of them are required.
     * @param string $message
     *   An error message, that will be thrown when user cannot be authenticated.
     *
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     *   When one of a fields cannot be not found.
     * @throws \Exception
     *   When login process failed.
     * @throws \WebDriver\Exception\NoSuchElement
     *   When log in button cannot be found.
     */
    public function fillLoginForm(array $props, $message = '')
    {
        $this->visitPath('/user/login');
        $formContext = $this->getFormContext();

        foreach (['username', 'password'] as $prop) {
            $formContext->fillField($this->getDrupalText($prop . '_field'), $props[$prop]);
        }

        $this->getWorkingElement()->pressButton($this->getDrupalText('log_in'));

        if (!$this->isLoggedIn()) {
            if (empty($message)) {
                $message = sprintf(
                    'Failed to login as a user "%s" with password "%s".',
                    $props['username'],
                    $props['password']
                );
            }

            throw new \Exception($message);
        }
    }

    /**
     * Cookies are set when at least one page of the site has been visited. This
     * action done in "beforeScenario" hook of TqContext.
     *
     * @see TqContext::beforeScenario()
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        $cookieName = session_name();
        $cookie = $this->getSession()->getCookie($cookieName);

        if ($cookie !== null) {
            $this->getSession('goutte')->setCookie($cookieName, $cookie);

            return true;
        }

        return false;
    }

    public function logoutUser()
    {
        if ($this->isLoggedIn()) {
            $this->logout();
        }
    }

    /**
     * @param array $data
     *   Additional data for user account.
     *
     * @return \stdClass
     */
    public function createTestUser(array $data = [])
    {
        $random = $this->getRandom();
        $username = $random->name(8);
        $user = $data + [
            'name' => $username,
            'pass' => $random->name(16),
            'mail' => "$username@example.com",
        ];

        $user = (object) $user;
        $this->userCreate($user);

        return $user;
    }
}
