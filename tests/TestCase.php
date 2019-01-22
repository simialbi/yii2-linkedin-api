<?php

namespace yiiunit\extensions\linkedin;

use Yii;
use yii\di\Container;
use yii\helpers\ArrayHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor'
        ], $config));
    }

    /**
     * Populates Yii::$app with a new web application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array $config
     * @param string $appClass
     */
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
                'linkedIn' => [
                    'class' => 'simialbi\yii2\linkedin\LinkedIn',
                    'appSecret' => '987654321',
                    'appId' => '123456789',
                    'authenticator' => [
                        'class' => 'simialbi\yii2\linkedin\Authenticator',
                        'appSecret' => '987654321',
                        'appId' => '123456789',
                        'requestManager' => [
                            'class' => 'simialbi\yii2\linkedin\RequestManager'
                        ],
                        'storage' => [
                            'class' => 'simialbi\yii2\linkedin\ArrayStorage'
                        ]
                    ]
                ]
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}