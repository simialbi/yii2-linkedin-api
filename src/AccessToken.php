<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use yii\base\BaseObject;

/**
 * Class AccessToken
 * @package simialbi\yii2\linkedin
 *
 * @property string $token
 * @property \DateTime $expiresAt
 */
class AccessToken extends BaseObject
{
    /**
     * @var null|string
     */
    private $_token;
    /**
     * @var \DateTime
     */
    private $_expiresAt;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->expiresAt !== null) {
            if (!($this->expiresAt instanceof \DateTime)) {
                try {
                    $this->expiresAt = new \DateTime(sprintf('+%dseconds', $this->expiresAt));
                } catch (\Exception $e) {
                    $this->expiresAt = null;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_token ?: '';
    }

    /**
     * Does a token string exist?
     *
     * @return bool
     */
    public function hasToken()
    {
        return !empty($this->_token);
    }

    /**
     * @param \DateTime $_expiresAt
     */
    public function setExpiresAt($_expiresAt = null)
    {
        $this->_expiresAt = $_expiresAt;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->_expiresAt;
    }

    /**
     * @param null|string $_token
     */
    public function setToken($_token)
    {
        $this->_token = $_token;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->_token;
    }
}