<?php
namespace Unity\Component\Security;

use Unity\Component\Service\Service;
use Unity\Component\Parameter\Parameters;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class UserManager extends Service
{
    private $session;
    private $parameters;
    private $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('user-manager')
             ->addDependency('session')
             ->addDependency('parameters');
    }

    /**
     * @param Session $session
     */
    protected function configure(Session $session, Parameters $parameters)
    {
        $this->session    = $session;
        $this->parameters = $parameters;
    }

    /**
     * @param string $username
     * @param string $password
     * @return \Unity\Component\Security\User
     */
    public function login($username, $password)
    {
        $user = $this->getAuthenticationService()->login($username, $password);
        if (!$user instanceof SecurityUser) {
            throw new \RuntimeException(sprintf(
                'User object %s must be an instance of %s, given by AuthenticationService %s.',
                get_class($user), 'SecurityUser', get_class($this->getAuthenticationService())
            ));
        }
        return $user;
    }

    public function logout()
    {
        return $this->getAuthenticationService()->logout();
    }

    /**
     * @return \Unity\Component\Security\User
     */
    public function getUser()
    {
        return $this->getAuthenticationService()->getUser();
    }

    /**
     * @return AuthenticationServiceInterface
     */
    private function getAuthenticationService()
    {
        if ($this->service instanceof AuthenticationServiceInterface) {
            return $this->service;
        }
        $service = $this->getService($this->parameters->getParameter('framework.security.authentication_service'));
        if (!$service instanceof AuthenticationServiceInterface) {
            throw new \RuntimeException(sprintf(
                    'Service %s must implement AuthenticationServiceInterface.', get_class($service)));
        }
        return ($this->service = $service);
    }
}
