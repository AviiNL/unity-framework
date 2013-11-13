<?php
namespace Unity\Component\Security;

use Unity\Component\Service\Service;
use Unity\Component\Parameter\Parameters;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
interface AuthenticationServiceInterface
{
    /**
     * Returns the user object of the currently signed in user or FALSE
     * if there is no signed-in user at this moment.
     *
     * @return User
     */
    public function getUser();

    /**
     * Authenticates a user by the given username and password and
     * returns the corresponding User object once the login procedure
     * is successful.
     *
     * The Repository service is responsible for throwing the approriate
     * exceptions from Unity\Component\Security\Exception according to
     * the context. E.g. InvalidUsernameException or InvalidPasswordException.
     *
     * @param string $username
     * @param string $password
     * @return User
     */
    public function login($username, $password);

    /**
     * Kills the session for the signed-in user (if any).
     */
    public function logout();
}
