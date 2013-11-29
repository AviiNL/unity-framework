<?php
namespace Unity\Component\Security;

use Unity\Component\Event\EventListener;

use Unity\Component\Event\EventManager;

use Unity\Component\Yaml\Yaml;

use Unity\Component\Service\Service;
use Unity\Component\Container\Container;
use Unity\Component\Parameter\Parameters;
use Unity\Component\Parameter\ParameterNotFoundException;
use Unity\Component\Yaml\YamlService;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class SessionManager extends Service implements \SessionHandlerInterface
{
    /**
     * @var Container
     */
    private $storage        = null;
    private $original_stor  = null;

    /**
     * @var YamlService
     */
    private $yaml_service   = null;

    /**
     * @var EventManager
     */
    private $event_manager  = null;

    private $session_id     = null;
    private $session_prefix = null;
    private $storage_path   = null;
    private $storage_time   = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('session-manager')
             ->addDependency('yaml')
             ->addDependency('event-manager');
    }

    /**
     * @param Parameters $parameters
     */
    protected function configure(YamlService $yaml_service, EventManager $em)
    {
        $this->yaml_service  = $yaml_service;
        $this->event_manager = $em;

        $em->register(new EventListener('session.open'));
        $em->register(new EventListener('session.close'));

        $this->setOptionPrefix('framework.session')
             ->addOption('storage_path', '/tmp')
             ->addOption('storage_time', 86400)
             ->addOption('file_prefix', 'sess_');

        $this->storage_path   = $this->getOption('storage_path');
        $this->storage_time   = $this->getOption('storage_time');
        $this->session_prefix = $this->getOption('file_prefix');

        session_set_save_handler($this, true);
        session_start();
    }

    /**
     * @return \Unity\Component\Container\Container
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name)
    {
        $sess_id = session_id();
        if (isset($_COOKIE[$name])) {
            $this->session_id = $_COOKIE[$name];
        } elseif (!empty($sess_id)) {
            $this->session_id = session_id();
        } else {
            session_id(uniqid());
            $this->session_id = session_id();
        }
        setcookie($name, $this->session_id, time() + $this->storage_time);
    }

    /**
     * @param string $session_id
     */
    public function read($session_id)
    {
        $data = [];
        if (file_exists($this->getStorageFile())) {
            $data = json_decode(file_get_contents($this->getStorageFile()), true);
            if (isset($data['session_id']) && $data['session_id'] != $this->session_id) {
                throw new \RuntimeException('Session id mismatch from storage! Possible security breach.');
            }
        }
        $this->storage = new Container($data);
        $this->original_stor = new Container($data);
        if (!file_exists($this->getStorageFile())) {
            $this->storage->set('session_id', $this->session_id);
        }
        $this->event_manager->trigger('session.open');
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->event_manager->trigger('session.close');
        $data = json_encode($this->storage->toArray());
        if ($data != json_encode($this->original_stor->toArray())) {
            file_put_contents($this->getStorageFile(), $data);
        }
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function destroy($session_id)
    {
        if (file_exists($this->getStorageFile())) {
            unlink($this->getStorageFile());
            $this->container = new Container();
        }
    }

    /**
     * Returns the session id.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    private function getStorageFile()
    {
        if (!$this->session_id) {
            throw new \RuntimeException('Attempt to fetch session-storage file without a session_id!');
        }
        return rtrim($this->storage_path, DIRECTORY_SEPARATOR) .
               DIRECTORY_SEPARATOR .
               $this->session_prefix .
               $this->session_id . '.yml';
    }
}
