<?php
namespace Unity\Component\Security\Exception;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class InvalidUsernameException extends \Exception
{
    /**
     * @param string $username
     */
    public function __construct($username)
    {
        parent::__construct(sprintf(
            'The user %s does not exist.', $username
        ));
    }
}
