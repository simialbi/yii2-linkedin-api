<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

/**
 * Class RequestManager
 * @package simialbi\yii2\linkedin
 *
 */
class RequestManager extends Component implements RequestManagerInterface
{
    /**
     * @var Client
     */
    private $_client;

    /**
     * {@inheritdoc}
     */
    public function sendRequest($method, $uri, $headers = null, $body = null)
    {
        try {
            $request = $this->getClient()
                ->createRequest()
                ->setMethod($method)
                ->setUrl($uri)
                ->setHeaders($headers)
                ->setData($body);

            return $this->getClient()->send($request);
        } catch (InvalidConfigException $e) {
            throw new LinkedInTransferException(
                'Error while requesting data from LinkedIn.com: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (Exception $e) {
            throw new LinkedInTransferException(
                'Error while requesting data from LinkedIn.com: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setClient($client)
    {
        if (is_array($client)) {
            $this->_client = Yii::createObject($client);
        } else {
            $this->_client = $client;
        }
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        if ($this->_client === null) {
            $this->_client = new Client([
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ]
            ]);
        }
        return $this->_client;
    }
}