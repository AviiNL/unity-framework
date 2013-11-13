<?php
namespace Unity\Component\Security;

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

    /**
     * @var YamlService
     */
    private $yaml_service   = null;

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
             ->addDependency('parameters')
             ->addDependency('yaml');
    }

    /**
     * @param Parameters $parameters
     */
    protected function configure(Parameters $parameters, YamlService $yaml_service)
    {
        $this->yaml_service = $yaml_service;

        if (null === ($this->storage_path = $parameters->getParameter('framework.session.storage_path'))) {
            throw new ParameterNotFoundException('framework.session.storage_path');
        }
        if (null === ($this->storage_time = $parameters->getParameter('framework.session.storage_time'))) {
            throw new ParameterNotFoundException('framework.session.storage_time');
        }
        if (null === ($this->session_prefix = $parameters->getParameter('framework.session.file_prefix'))) {
            throw new ParameterNotFoundException('framework.session.file_prefix');
        }

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
        if (isset($_COOKIE[$name])) {
            // Refresh cookie
            $this->session_id = $_COOKIE[$name];
        } else {
            $this->session_id = session_id();
        }
        setcookie($name, $this->session_id, time() + $this->storage_time);
    }

    /**
     * @param string $session_id
     */
    public function read($session_id)
    {
        $data   = [];

        if (file_exists($this->getStorageFile())) {
            $data = $this->yaml_service->parseFile($this->getStorageFile());
            if (isset($data['session_id']) && $data['session_id'] != $this->session_id) {
                throw new \RuntimeException('Session id mismatch from storage! Possible security breach.');
            }
        }
        $this->storage = new Container($data);
        if (!file_exists($this->getStorageFile())) {
            $this->storage->set('session_id', $this->session_id);
        }
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $data = Yaml::dump($this->storage->toArray());
        file_put_contents($this->getStorageFile(), $data);
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
        echo '<b>' . __METHOD__ . '</b><br>';
        var_dump(func_get_args()); echo '<br>';
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
