<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Drush;

class DrushContext extends RawDrushContext
{
    /**
     * @Given /^(?:|I )login with one time link$/
     *
     * @drush @api
     */
    public function loginWithOneTimeLink()
    {
        $userContext = $this->getUserContext();
        $userContext->logoutUser();

        $user = $userContext->createTestUser();
        // Care about not-configured Drupal installations, when
        // the "$base_url" variable is not set in "settings.php".
        // Also, remove the last underscore symbol from link for
        // prevent opening the page for reset the password;
        $link = rtrim($this->getOneTimeLoginLink($user->name), '_');
        $this->visitPath($link);

        $text = t('You have just used your one-time login link.');
        if (!preg_match("/$text|$user->name/i", $this->getWorkingElement()->getText())) {
            throw new \Exception(sprintf('Cannot login with one time link: "%s"', $link));
        }
    }
}
