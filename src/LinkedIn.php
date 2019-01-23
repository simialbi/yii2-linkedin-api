<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\HeaderCollection;

/**
 * Class LinkedIn lets you talk to LinkedIn api.
 *
 * When a new user arrives and want to authenticate here is whats happens:
 * 1. You redirect him to whatever url getLoginUrl() returns.
 * 2. The user logs in on www.linkedin.com and authorize your application.
 * 3. The user returns to your site with a *code* in the the $_REQUEST.
 * 4. You call isAuthenticated() or getAccessToken()
 * 5. If we don't have an access token (only a *code*), getAccessToken() will call fetchNewAccessToken()
 * 6. fetchNewAccessToken() gets the *code* from the $_REQUEST and calls getAccessTokenFromCode()
 * 7. getAccessTokenFromCode() makes a request to www.linkedin.com and exchanges the *code* for an access token
 * 8. When you have the access token you should store it in a database and/or query the API.
 * 9. When you make a second request to the API we have the access token in memory, so we don't go through all these
 *    authentication steps again.
 *
 * @package simialbi\yii2\linkedin
 *
 * @property AuthenticatorInterface $authenticator
 * @property-read \yii\httpclient\Response $lastResponse
 * @property-write string $appId
 * @property-write string $appSecret
 */
class LinkedIn extends Component implements LinkedInInterface
{
    /**
     * @var string
     */
    private $_appId;
    /**
     * @var string
     */
    private $_appSecret;

    /**
     * The OAuth access token received in exchange for a valid authorization
     * code.  null means the access token has yet to be determined.
     *
     * @var AccessToken
     */
    protected $accessToken = null;
    /**
     * @var \yii\httpclient\Response
     */
    private $_lastResponse;
    /**
     * @var RequestManager
     */
    private $_requestManager;
    /**
     * @var Authenticator
     */
    private $_authenticator;
    /**
     * @var UrlGeneratorInterface
     */
    private $_urlGenerator;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->_authenticator)) {
            $this->_requestManager = new RequestManager();
            $this->_authenticator = new Authenticator([
                'requestManager' => $this->_requestManager,
                'appId' => $this->_appId,
                'appSecret' => $this->_appSecret
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        $accessToken = $this->getAccessToken();
        if ($accessToken === null) {
            return false;
        }
        $user = $this->api(
            'GET',
            '/v1/people/~:(id,firstName,lastName)',
            ['format' => 'json', 'response_data_type' => 'array']
        );
        return !empty($user['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function api($method, $resource, array $options = [])
    {
        // Add access token to the headers
        $initialHeaders = ArrayHelper::remove($options, 'headers', []);
        $body = ArrayHelper::remove($options, 'body');
        $headers = new HeaderCollection();
        foreach ($initialHeaders as $name => $value) {
            $headers->add($name, $value);
        }
        $headers->set('Authorization', 'Bearer ' . (string)$this->getAccessToken());

        if ($body) {
            $body = Json::htmlEncode($options['json']);
        }

        // Generate an url
        $url = $this->getUrlGenerator()->getUrl(
            'api',
            $resource,
            isset($options['query']) ? $options['query'] : []
        );
        $this->_lastResponse = $this->getRequestManager()->sendRequest($method, $url, $headers, $body);

        return $this->_lastResponse->isOk ? $this->_lastResponse->data : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl($options = [])
    {
        $urlGenerator = $this->getUrlGenerator();
        // Set redirect_uri to current URL if not defined
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $urlGenerator->getCurrentUrl();
        }

        return $this->getAuthenticator()->getLoginUrl($urlGenerator, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function get($resource, array $options = [])
    {
        return $this->api('GET', $resource, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function post($resource, array $options = [])
    {
        return $this->api('POST', $resource, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function clearStorage()
    {
        $this->getAuthenticator()->clearStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function hasError()
    {
        return (null === Yii::$app->request->get('error', Yii::$app->request->getBodyParam('error')));
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        if ($this->hasError()) {
            return new LoginError([
                'name' => Yii::$app->request->get('error', Yii::$app->request->getBodyParam('error')),
                'description' => Yii::$app->request->get(
                    'error_description',
                    Yii::$app->request->getBodyParam('error_description')
                )
            ]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        if ($this->accessToken === null) {
            if (null !== ($newAccessToken = $this->getAuthenticator()->fetchNewAccessToken($this->getUrlGenerator()))) {
                $this->setAccessToken($newAccessToken);
            }
        }
        // return the new access token or null if none found
        return $this->accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->accessToken = new AccessToken([
                'token' => $accessToken
            ]);
        } elseif (is_array($accessToken)) {
            $this->accessToken = Yii::createObject($accessToken);
        } else {
            $this->accessToken = $accessToken;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlGenerator($urlGenerator)
    {
        if (is_array($urlGenerator)) {
            $this->_urlGenerator = Yii::createObject($urlGenerator);
        } else {
            $this->_urlGenerator = $urlGenerator;
        }
    }

    /**
     * @return UrlGeneratorInterface
     */
    protected function getUrlGenerator()
    {
        if ($this->_urlGenerator === null) {
            $this->_urlGenerator = new UrlGenerator();
        }

        return $this->_urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorage($storage)
    {
        $this->getAuthenticator()->setStorage($storage);
    }

    /**
     * {@inheritdoc}
     */
    public function setClient($client)
    {
        $this->getRequestManager()->setClient($client);
    }

    /**
     * @return RequestManager
     */
    protected function getRequestManager()
    {
        return $this->_requestManager;
    }

    /**
     * @return Authenticator
     */
    public function getAuthenticator()
    {
        return $this->_authenticator;
    }

    /**
     * Authenticator setter
     *
     * @param AuthenticatorInterface|array $authenticator
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function setAuthenticator($authenticator)
    {
        if (is_array($authenticator)) {
            $this->_authenticator = Yii::createObject($authenticator);
        } else {
            $this->_authenticator = $authenticator;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAppId($appId)
    {
        $this->_appId = $appId;
    }

    /**
     * {@inheritdoc}
     */
    public function setAppSecret($appSecret)
    {
        $this->_appSecret = $appSecret;
    }
}