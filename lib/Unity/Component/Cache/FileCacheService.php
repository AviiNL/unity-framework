<?php
namespace Unity\Component\Cache;


use Unity\Component\Kernel\Kernel;
use Unity\Component\Service\Service;
use Unity\Component\Parameter\Parameters;
use Unity\Component\Kernel\FileNotFoundException;

/**
 * @author Harold Iedema <harold@iedema.me>
 */
class FileCacheService extends Service
{
    private $cache_dir;
    private $cache_max_age = 86400;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setName('file-cache')
             ->addDependency('kernel')
             ->addDependency('parameters');
    }

    /**
     * @param Kernel $kernel
     * @param Parameters $parameters
     * @throws \RuntimeException
     * @throws FileNotFoundException
     */
    protected function configure(Kernel $kernel, Parameters $parameters)
    {
        if (null === ($dir = $parameters->getParameter('app_root_dir', null))) {
            throw new \RuntimeException('app_root_dir not defined in Parameters');
        }
        if (!file_exists($dir)) {
            throw new FileNotFoundException($dir);
        }

        if (!file_exists($dir . '/cache')) {
            if (false === (mkdir($dir . '/cache', 0777))) {
                throw new \RuntimeException(
                        'Unable to create cache directory "' . $dir . '/cache".');
            }
        }
        $this->cache_dir = $dir . '/cache';

        foreach (glob($this->cache_dir . '/*') as $file) {
            if (filemtime($file) > time() + $this->cache_max_age) {
                unlink($file);
            }
        }
    }

    /**
     * Checks if a given file has a cached version. Pass extra data as a string
     * to $cache_data if your resource is manipulated in any way to create a
     * specific cached version of that.
     *
     * @param string $file
     * @param string $cache_data
     */
    public function isValid($file, $cache_data)
    {
        $handle = $this->getCacheHandle($file, $cache_data);
        if (!file_exists($this->cache_dir . DIRECTORY_SEPARATOR . $handle) ||
            filemtime($this->cache_dir . DIRECTORY_SEPARATOR . $handle) > time() + $this->cache_max_age) {
            return false;
        }
        return true;
    }

    /**
     * Returns a cached version of the resource.
     *
     * @param string $file
     * @param string $cache_data
     * @return boolean|string
     */
    public function getCachedVersion($file, $cache_data)
    {
        $handle = $this->getCacheHandle($file, $cache_data);
        if (!file_exists($this->cache_dir . DIRECTORY_SEPARATOR . $handle) ||
                filemtime($this->cache_dir . DIRECTORY_SEPARATOR . $handle) > time() + $this->cache_max_age) {
            return false;
        }
        return $this->cache_dir . DIRECTORY_SEPARATOR . $handle;
    }

    /**
     * Creates a cached version of a resource and returns the absolute path to
     * the file.
     *
     * @param string $file
     * @param string $cache_data
     * @param string $source
     * @return string
     */
    public function createCachedVersion($file, $cache_data, $source)
    {
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $this->getCacheHandle($file, $cache_data);
        file_put_contents($cache_file, $source);
        return $cache_file;
    }

    /**
     * Creates a cache handle for the given file.
     *
     * @param string $file
     * @param string $cache_data
     */
    private function getCacheHandle($file, $cache_data)
    {
        return md5(dirname($file))
                                   . '-' . md5(json_encode($cache_data))
                                   . '-' . filesize($file)
                                   . '-' . basename($file);
    }
}