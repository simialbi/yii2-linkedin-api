<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

/**
 * Interface AuthenticatorInterface
 * @package simialbi\yii2\linkedin
 *
 * @property DataStorageInterface $storage
 *
 * @property-write RequestManagerInterface $requestManager
 * @property-write string $appId
 * @property-write string $appSecret
 */
interface AuthenticatorInterface
{
    /**
     * Tries to get a new access token from data storage or code. If it fails, it will return null.
     *
     * @param LinkedInUrlGeneratorInterface|array $urlGenerator
     *
     * @return AccessToken|null A valid user access token, or null if one could not be fetched.
     *
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function fetchNewAccessToken($urlGenerator);

    /**
     * Generate a login url.
     *
     * @param LinkedInUrlGeneratorInterface|array $urlGenerator
     * @param array $options
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginUrl($urlGenerator, $options = []);

    /**
     * Clear the storage.
     */
    public function clearStorage();

    /**
     * @param DataStorageInterface|array $storage
     * @throws \yii\base\InvalidConfigException
     */
    public function setStorage($storage);

    /**
     * @return DataStorageInterface
     */
    public function getStorage();

    /**
     * @param RequestManagerInterface|array $requestManager
     * @throws \yii\base\InvalidConfigException
     */
    public function setRequestManager($requestManager);

    /**
     * @param string $appId
     */
    public function setAppId($appId);

    /**
     * @param string $appSecret
     */
    public function setAppSecret($appSecret);
}