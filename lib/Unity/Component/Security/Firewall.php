<?php
namespace Unity\Component\Security;

use Unity\Component\HTTP\Request;

use Unity\Component\Parameter\Parameters;

use Unity\Component\Annotation\AnnotationReader;
use Unity\Component\Event\EventListener;
use Unity\Component\Controller\Controller;
use Unity\Component\Event\EventManager;
use Unity\Component\Service\Service;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Firewall extends Service
{

    /**
     * @var AnnotationReader
     */
    private $annotation_reader;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var UserManager
     */
    private $user_manager;

    /**
     * @var Request
     */
    private $request;

    public function __construct()
    {
        $this->setName('firewall')
             ->addDependency('event-manager')
             ->addDependency('annotation-reader')
             ->addDependency('session')
             ->addDependency('parameters')
             ->addDependency('user-manager')
             ->addDependency('request');
    }

    /**
     * @param EventManager $em
     * @param Dispatcher $dispatcher
     * @param Session $session
     */
    protected function configure(EventManager $em, AnnotationReader $annotation_reader, Session $session, Parameters $parameters, UserManager $user_manager, Request $request)
    {
        $this->annotation_reader = $annotation_reader;
        $this->session           = $session;
        $this->parameters        = $parameters;
        $this->user_manager      = $user_manager;
        $this->request           = $request;

        $em->getEvent('dispatcher.on_route')->bind(function(EventListener $event) {

            if (null === ($controller = $event->getParameter('controller'))) {
                return;
            }
            // Is there a secure annotation on the controller?
            $controller_secure = $this->annotation_reader->getClass($controller)->getAnnotation('Secure');
            if ($controller_secure instanceof Secure && !$this->isAuthorized($controller_secure)) {
                if (!$this->user_manager->getUser() instanceof SecurityUser) {
                    $this->session->set('login_redirect_url', $this->request->getRequestUri());
                    header('location: ' . $this->parameters->getParameter('framework.firewall.login_url'));
                } else {
                    header('location: ' . $this->parameters->getParameter('framework.firewall.denied_url'));
                }
                exit;
            }

            if (null === ($method = $event->getParameter('method'))) {
                return;
            }

            // Is there a secure annotation on the method ?
            $method_secure = $this->annotation_reader->getMethod($controller, $method)->getAnnotation('Secure');
            if ($method_secure instanceof Secure && !$this->isAuthorized($method_secure)) {
                if (!$this->user_manager->getUser() instanceof SecurityUser) {
                    $this->session->set('login_redirect_url', $this->request->getRequestUri());
                    header('location: ' . $this->parameters->getParameter('framework.firewall.login_url'));
                } else {
                    header('location: ' . $this->parameters->getParameter('framework.firewall.denied_url'));
                }
                exit;
            }
        }, 100);
    }

    private function isAuthorized(Secure $secure)
    {
        if ($this->user_manager->getUser() instanceof SecurityUser) {
            $roles = $this->user_manager->getUser()->getSecurityRoles();
            if (empty($roles)) {
                return true;
            }
            foreach ($roles as $role) {
                if ($secure->hasRoleAccess($role)) {
                    return true;
                }
            }
        }
        return false;
    }
}
