<?php
/**
 * @author Sergey Bondarenko, <sb@firstvector.org>
 */
namespace Drupal\TqExtension\Context\Redirect;

// Contexts.
use Drupal\TqExtension\Context\RawTqContext;

class RawRedirectContext extends RawTqContext
{
    /**
     * @param string $path
     *   Relative URL.
     * @param int $code
     *   HTTP response code.
     *
     * @return bool
     */
    public function assertStatusCode($path, $code)
    {
        // The "Goutte" session should be used because it provide the request status codes.
        $this->visitPath($this->unTrailingSlashIt($path), 'goutte');

        return $this->getSession('goutte')->getStatusCode() === $code;
    }
}
