<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Redirect;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

class RawRedirectContext extends RawTqContext
{
    /**
     * @param string $path
     *   Relative URL.
     * @param string|int $code
     *   HTTP response code.
     *
     * @return bool
     */
    public function assertStatusCode($path, $code)
    {
        // The "Goutte" session should be used because it provide the request status codes.
        $this->visitPath($path, 'goutte');
        $responseCode = $this->getSession('goutte')->getStatusCode();

        self::debug(['HTTP code is: %s'], [$responseCode]);

        return $responseCode == $code;
    }
}
