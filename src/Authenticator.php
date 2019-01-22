<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\HeaderCollection;

/**
 * Class Authenticator
 * @package simialbi\yii2\linkedin
 */
class Authenticator extends Component implements AuthenticatorInterface
{
    /**
     * The application ID.
     *
     * @var string
     */
    protected $_appId;

    /**
     * The application secret.
     *
     * @var string
     */
    protected $_appSecret;

    /**
     * A storage to use to store data between requests.
     *
     * @var DataStorageInterface storage
     */
    private $_storage;

    /**
     * @var RequestManagerInterface
     */
    private $_requestManager;

    /**
     * {@inheritdoc}
     */
    public function fetchNewAccessToken($urlGenerator)
    {
        if (is_array($urlGenerator)) {
            $urlGenerator = Yii::createObject($urlGenerator);
        }
        $storage = $this->getStorage();
        $code = $this->getCode();
        if ($code === null) {
            /*
             * As a fallback, just return whatever is in the persistent
             * store, knowing nothing explicit (signed request, authorization
             *  code, etc.) was present to shadow it.
             */
            return $storage->get('access_token');
        }
        try {
            $accessToken = $this->getAccessTokenFromCode($urlGenerator, $code);
        } catch (LinkedInException $e) {
            // code was bogus, so everything based on it should be invalidated.
            $storage->clearAll();
            throw $e;
        }
        $storage->set('code', $code);
        $storage->set('access_token', $accessToken);
        return $accessToken;
    }

    /**
     * Retrieves an access token for the given authorization code
     * (previously generated from www.linkedin.com on behalf of
     * a specific user). The authorization code is sent to www.linkedin.com
     * and a legitimate access token is generated provided the access token
     * and the user for which it was generated all match, and the user is
     * either logged in to LinkedIn or has granted an offline access permission.
     *
     * @param LinkedInUrlGeneratorInterface|array $urlGenerator
     * @param string $code An authorization code.
     *
     * @return AccessToken An access token exchanged for the authorization code.
     *
     * @throws LinkedInException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAccessTokenFromCode($urlGenerator, $code)
    {
        if (empty($code)) {
            throw new LinkedInException('Could not get access token: The code was empty.');
        }
        if (is_array($urlGenerator)) {
            $urlGenerator = Yii::createObject($urlGenerator);
        }
        $redirectUri = $this->getStorage()->get('redirect_uri');
        try {
            $url = $urlGenerator->getUrl('www', 'oauth/v2/accessToken');
            $headers = (new HeaderCollection())->add('Content-Type', 'application/x-www-form-urlencoded');
            $response = $this->getRequestManager()->sendRequest('POST', $url, $headers, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $this->_appId,
                'client_secret' => $this->_appSecret,
            ]);
        } catch (LinkedInTransferException $e) {
            // most likely that user very recently revoked authorization.
            // In any event, we don't have an access token, so throw an exception.
            throw new LinkedInException(
                'Could not get access token: The user may have revoked the authorization response from LinkedIn.com was empty.',
                $e->getCode(),
                $e
            );
        }
        if (!$response->isOk || empty($response->data)) {
            throw new LinkedInException('Could not get access token: The response from LinkedIn.com was empty.');
        }
        $token = new AccessToken([
            'token' => ArrayHelper::getValue($response->data, 'access_token'),
            'expiresAt' => ArrayHelper::getValue($response->data, 'expires_in')
        ]);
        if (!$token->hasToken()) {
            throw new LinkedInException('Could not get access token: The response from LinkedIn.com did not contain a token.');
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl($urlGenerator, $options = [])
    {
        if (is_array($urlGenerator)) {
            $urlGenerator = Yii::createObject($urlGenerator);
        }
        // Generate a state
        $this->establishCSRFTokenState();

        // Build request params
        $requestParams = ArrayHelper::merge([
            'response_type' => 'code',
            'client_id' => $this->_appId,
            'state' => $this->getStorage()->get('state'),
            'redirect_uri' => null,
        ], $options);

        // Save the redirect url for later
        $this->getStorage()->set('redirect_uri', $requestParams['redirect_uri']);

        // if 'scope' is passed as an array, convert to space separated list
        $scopeParams = isset($options['scope']) ? $options['scope'] : null;
        if ($scopeParams) {
            //if scope is an array
            if (is_array($scopeParams)) {
                $requestParams['scope'] = implode(' ', $scopeParams);
            } elseif (is_string($scopeParams)) {
                //if scope is a string with ',' => make it to an array
                $requestParams['scope'] = str_replace(',', ' ', $scopeParams);
            }
        }

        return $urlGenerator->getUrl('www', 'oauth/v2/authorization', $requestParams);
    }

    /**
     * Get the authorization code from the query parameters, if it exists,
     * and otherwise return null to signal no authorization code was
     * discovered.
     *
     * @return string|null The authorization code, or null if the authorization code not exists.
     *
     * @throws LinkedInException on invalid CSRF tokens
     */
    protected function getCode()
    {
        $code = Yii::$app->request->get('code', Yii::$app->request->getBodyParam('code'));
        $requestState = Yii::$app->request->get('state', Yii::$app->request->getBodyParam('state'));
        $storage = $this->getStorage();
        if ($code === null) {
            return null;
        }
        if ($storage->get('code') === $code) {
            //we have already validated this code
            return null;
        }
        // if stored state does not exists
        if (null === ($state = $storage->get('state'))) {
            throw new LinkedInException('Could not find a stored CSRF state token.');
        }
        // if state not exists in the request
        if (!$requestState) {
            throw new LinkedInException('Could not find a CSRF state token in the request.');
        }
        // if state exists in session and in request and if they are not equal
        if ($state !== $requestState) {
            throw new LinkedInException('The CSRF state token from the request does not match the stored token.');
        }
        // CSRF state has done its job, so clear it
        $storage->clear('state');

        return $code;
    }

    /**
     * Lays down a CSRF state token for this process.
     */
    protected function establishCSRFTokenState()
    {
        $storage = $this->getStorage();
        if ($storage->get('state') === null) {
            $storage->set('state', md5(uniqid(mt_rand(), true)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clearStorage()
    {
        $this->getStorage()->clearAll();
        return $this;
    }

    /**
     * @return DataStorageInterface
     */
    public function getStorage()
    {
        if ($this->_storage === null) {
            $this->_storage = new SessionStorage();
        }
        return $this->_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorage($storage)
    {
        if (is_array($storage)) {
            $this->_storage = Yii::createObject($storage);
        } else {
            $this->_storage = $storage;
        }
    }

    /**
     * @return RequestManagerInterface
     */
    protected function getRequestManager()
    {
        return $this->_requestManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestManager($requestManager)
    {
        if (is_array($requestManager)) {
            $this->_requestManager = Yii::createObject($requestManager);
        } else {
            $this->_requestManager = $requestManager;
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