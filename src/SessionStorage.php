<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use Yii;
use yii\base\InvalidConfigException;

class SessionStorage extends BaseDataStorage
{
    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!Yii::$app->has('session')) {
            throw new InvalidConfigException('App needs to have a session component to use session storage');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->validateKey($key);

        Yii::$app->session->set($this->getStorageKeyId($key), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $this->validateKey($key);

        return Yii::$app->session->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($key)
    {
        $this->validateKey($key);

        return Yii::$app->session->remove($key);
    }
}