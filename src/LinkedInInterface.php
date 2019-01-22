<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use yii\base\InvalidConfigException;
use yii\httpclient\Client;

interface LinkedInInterface
{
    /**
     * Is the current user authenticated?
     *
     * @return bool
     *
     * @throws LinkedInTransferException
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function isAuthenticated();

    /**
     * Make an API call. Read about what calls that are possible here: https://developer.linkedin.com/docs/rest-api.
     *
     * Example:
     * $linkedIn->api('GET', '/v1/people/~:(id,firstName,lastName,headline)');
     *
     * The options:
     * - body: the body of the request
     * - format: the format you are using to send the request
     * - headers: array with headers to use
     * - query: query parameters to the request
     *
     * @param string $method This is the HTTP verb
     * @param string $resource everything after the domain in the URL.
     * @param array $options See the readme for option description.
     *
     * @return array
     *
     * @throws LinkedInTransferException
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function api($method, $resource, array $options = []);

    /**
     * Get a login URL where the user can put his/hers LinkedIn credentials and authorize the application.
     *
     * The options:
     * - redirect_uri: the url to go to after a successful login
     * - scope: comma (or space) separated list of requested extended permissions
     *
     * @param array $options Provide custom parameters
     *
     * @return string The URL for the login flow
     *
     * @throws InvalidConfigException
     */
    public function getLoginUrl($options = []);

    /**
     * See docs for LinkedIn::api().
     *
     * @param string $resource
     * @param array $options
     *
     * @return mixed
     *
     * @throws LinkedInTransferException
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function get($resource, array $options = []);

    /**
     * See docs for LinkedIn::api().
     *
     * @param string $resource
     * @param array $options
     *
     * @return mixed
     *
     * @throws LinkedInTransferException
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function post($resource, array $options = []);

    /**
     * Clear the data storage. This will forget everything about the user and authentication process.
     */
    public function clearStorage();

    /**
     * If the user has canceled the login we will return with an error.
     *
     * @return bool
     */
    public function hasError();

    /**
     * Returns a LoginError or null.
     *
     * @return LoginError|null
     */
    public function getError();

    /**
     * Get the last response. This will always return a PSR-7 response no matter of the data type used.
     *
     * @return \yii\httpclient\Response|null
     */
    public function getLastResponse();

    /**
     * Returns an access token. If we do not have one in memory, try to fetch one from a *code* in the $_REQUEST.
     *
     * @return AccessToken|null The access token of null if the access token is not found
     *
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccessToken();

    /**
     * If you have stored a previous access token in a storage (database) you could set it here. After setting an
     * access token you have to make sure to verify it is still valid by running LinkedIn::isAuthenticated.
     *
     * @param string|AccessToken|array $accessToken
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setAccessToken($accessToken);

    /**
     * Set a URL generator.
     *
     * @param UrlGeneratorInterface|array $urlGenerator
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setUrlGenerator($urlGenerator);

    /**
     * Set a data storage.
     *
     * @param DataStorageInterface|array $storage
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setStorage($storage);

    /**
     * Set a http client.
     *
     * @param Client|array $client
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setClient($client);
}