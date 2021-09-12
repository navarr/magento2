<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cache frontend decorator that attaches no additional responsibility to a decorated instance.
 * To be used as an ancestor for concrete decorators to conveniently override only methods of interest.
 */
namespace Magento\Framework\Cache\Frontend\Decorator;

use Exception;
use Psr\SimpleCache\CacheException;

class Bare implements \Magento\Framework\Cache\FrontendInterface, \Psr\SimpleCache\CacheInterface
{
    /**
     * Cache frontend instance to delegate actual cache operations to
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_frontend;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $frontend)
    {
        $this->_frontend = $frontend;
    }

    /**
     * Set frontend
     *
     * @param \Magento\Framework\Cache\FrontendInterface $frontend
     * @return $this
     */
    protected function setFrontend(\Magento\Framework\Cache\FrontendInterface $frontend)
    {
        $this->_frontend = $frontend;
        return $this;
    }

    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    protected function _getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        return $this->_getFrontend()->test($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        try {
            return !!$this->test($key);
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return $this->_getFrontend()->load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        try {
            return $this->load($key) ?? $default;
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->_getFrontend()->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        if ($ttl instanceof \DateInterval) {
            // Convert acceptable DateInterval into seconds
            $now = new \DateTimeImmutable();
            $afterTtl = $now->add($ttl);
            $nowSeconds = $now->format('U');
            $thenSeconds = $afterTtl->format('U');
            $ttl = $thenSeconds - $nowSeconds;
        }
        try {
            return $this->save($value, $key, [], $ttl);
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $success = true;
        foreach ($values as $key => $value) {
            $success = $success && $this->set($key, $value, $ttl);
        }
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return $this->_getFrontend()->remove($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        return $this->_getFrontend()->clean($mode, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend()
    {
        return $this->_getFrontend()->getBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->_getFrontend()->getLowLevelFrontend();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        try {
          return $this->remove($key);
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            return $this->clean();
        } catch (Exception $e) {
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $success && $this->delete($key);
        }
        return $success;
    }
}
