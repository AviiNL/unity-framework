<?php
namespace Unity\Component\Security;

use Unity\Component\Service\Service;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class Session extends Service
{
    private $manager;

    public function __construct()
    {
        $this->setName('session')
             ->addDependency('session-manager');
    }

    /**
     * @param SessionManager $manager
     */
    protected function configure(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Kills the entire session. Use with caution!
     */
    public function kill()
    {
        $this->manager->destroy($this->manager->getSessionId());
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->manager->getStorage()->set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $default
     */
    public function get($key, $default = null)
    {
        return $this->manager->getStorage()->get($key, $default);
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->manager->getStorage()->delete($key);
    }
}
