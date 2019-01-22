<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;


use yii\helpers\ArrayHelper;

/**
 * Class ArrayStorage
 * @package simialbi\yii2\linkedin
 */
class ArrayStorage extends BaseDataStorage
{
    /**
     * @var array Inline Storage
     */
    private $_storage = [];

    /**
     * Stores the given ($key, $value) pair, so that future calls to
     * getPersistentData($key) return $value. This call may be in another request.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        ArrayHelper::setValue($this->_storage, $key, $value);
    }

    /**
     * Get the data for $key, persisted by BaseFacebook::setPersistentData().
     *
     * @param string $key The key of the data to retrieve
     *
     * @return mixed
     */
    public function get($key)
    {
        return ArrayHelper::getValue($this->_storage, $key);
    }

    /**
     * Clear the data with $key from the persistent storage.
     *
     * @param string $key
     */
    public function clear($key)
    {
        ArrayHelper::remove($this->_storage, $key);
    }
}