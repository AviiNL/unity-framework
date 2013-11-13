<?php
namespace Unity\Component\Security\Exception;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class InvalidPasswordException extends \Exception
{
    /**
     * @param string $username
     */
    public function __construct($username)
    {
        parent::__construct(sprintf(
            'The password for user %s is invalid.', $username
        ));
    }
}
