<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use yii\web\HeaderCollection;

/**
 * A request manager builds a request.
 *
 * @package simialbi\yii2\linkedin
 *
 * @property-write \yii\httpclient\Client $client
 */
interface RequestManagerInterface
{
    /**
     * Send a request.
     *
     * @param string $method
     * @param string $uri
     * @param HeaderCollection|null $headers
     * @param string|array|null $body
     *
     * @return \yii\httpclient\Response
     *
     * @throws LinkedInTransferException
     */
    public function sendRequest($method, $uri, $headers = null, $body = null);

    /**
     * @param \yii\httpclient\Client|array $client
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setClient($client);
}