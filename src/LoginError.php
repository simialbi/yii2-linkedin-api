<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use yii\base\BaseObject;

/**
 * Class LoginError
 * @package simialbi\yii2\linkedin
 *
 * @property string $name
 * @property string $description
 */
class LoginError extends BaseObject
{
    /**
     * @var string name
     */
    protected $_name;

    /**
     * @var string description
     */
    protected $_description;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Name: %s, Description: %s', $this->getName(), $this->getDescription());
    }
}