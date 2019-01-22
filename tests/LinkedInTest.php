<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace yiiunit\extensions\linkedin;

use Yii;

class LinkedInTest extends TestCase
{
    /**
     * @var \simialbi\yii2\linkedin\LinkedIn
     */
    private $_api;

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();

        $this->_api = Yii::$app->get('linkedIn');
    }
}