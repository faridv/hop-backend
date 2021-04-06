<?php

//session_start();

use GuzzleHttp\Client as Client;
use GuzzleHttp\HandlerStack as HandlerStack;
use GuzzleHttp\Handler\CurlHandler as CurlHandler;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as Oauth1;
use GuzzleHttp\Exception\GuzzleException as GuzzleException;

class OAuthClient {

    public $requestTokenUrl = 'https://sepehrapi.irib.ir/v1/oauth/request_token?device_uid=eed90b36-b2f4-57e3-8e7a-6b5ee8cadeed';
    public $authorizeUrl = 'https://sepehrapi.irib.ir/v1/oauth/authorize';
    public $accessTokenUrl = 'https://sepehrapi.irib.ir/v1/oauth/access_token';
    public $tokenRequestMethod = 'GET';
    public $consumerKey = 'QKORpgyu9mpw3MZUUwu8Mm4qxYMsXq3L';
    public $consumerSecret = 'jtroj3hkyjlU06j7MtJimJ1I3PTTpx39';
    public $oauthUsername = 'roshan';
    public $oauthPassword = 'e10adc3949ba59abbe56e057f20f883e';
    public $tokens = null;
    private $cacheClient;

    public function __construct() {
        $this->cacheClient = Cache::getInstance();
        $cachedToken = $this->checkCache('sepehr-auth');
        if ($cachedToken) {
            $this->tokens = $cachedToken;
        } else {
            $requestTokens = $this->getRequestToken();
            if ($this->authorize($requestTokens)) {
                $this->tokens = $this->getAccessToken($requestTokens);
            }
        }
        return null;
    }

    public function getTokens() {
        return $this->tokens;
    }

    private function checkCache($key = 'sepehr-auth') {
        return $this->cacheClient->get($key);
    }

    // Step 1
    private function getRequestToken() {
        $cachedToken = $this->checkCache('sepehr-token');
        if ($cachedToken) {
            return $cachedToken;
        }
        $client = $this->getClient($this->requestTokenUrl, []);
        try {
            $response = $client->request($this->tokenRequestMethod, '');
            $output = [
                'statusCode' => $response->getStatusCode(),
                'contentType' => $response->getHeaderLine('content-type'),
                'body' => $response->getBody()->getContents(),
            ];
            if (isset($output['statusCode']) && $output['statusCode'] === 200) {
                parse_str(urldecode($output['body']), $array);
                // cache it
                $this->cacheClient->set('sepehr-token', json_encode($array), 59);
                return json_decode(json_encode($array));
            }
        } catch (GuzzleException $e) {
            // probably ttl
            $this->throwError($e->getCode(), (string)$e->getResponse()->getBody());
        }
        return null;
    }

    // Step 2
    private function authorize($tokens): bool {
        if (!$tokens->oauth_token) {
            $this->throwError(500, 'OAuth tokens not available');
        }
        $arguments = [
            'token' => $tokens->oauth_token,
            'token_secret' => $tokens->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ];
        $client = $this->getClient($this->authorizeUrl, $arguments);
        try {
            $response = $client->post($this->authorizeUrl, [
                'auth' => 'oauth',
                'form_params' => [
                    'username' => $this->oauthUsername,
                    'password' => $this->oauthPassword,
                ]
            ]);
            $output = [
                'statusCode' => $response->getStatusCode(),
                'contentType' => $response->getHeaderLine('content-type'),
                'body' => $response->getBody()->getContents(),
            ];
            if (isset($output['statusCode']) && $output['statusCode'] === 200) {
                return true;
            } else {
                $this->throwError(500, $output['body']);
            }
        } catch (GuzzleException $e) {
//            var_dump((string)$e->getResponse()->getBody());
            // probably ttl
            $this->throwError($e->getCode(), (string)$e->getResponse()->getBody());
        }
        return false;
    }

    // Step 3
    private function getAccessToken($tokens): object {
        $cachedToken = $this->checkCache('sepehr-auth');
        if ($cachedToken) {
            return $cachedToken;
        }
        if (!$tokens->oauth_token) {
            $this->throwError(500, 'OAuth tokens not available');
        }
        $arguments = [
            'token' => $tokens->oauth_token,
            'token_secret' => $tokens->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ];
        $client = $this->getClient($this->accessTokenUrl, $arguments);
        try {
            $response = $client->get($this->accessTokenUrl, ['auth' => 'oauth']);
            $output = [
                'statusCode' => $response->getStatusCode(),
                'contentType' => $response->getHeaderLine('content-type'),
                'body' => $response->getBody()->getContents(),
            ];
            parse_str(urldecode($output['body']), $array);
            $this->cacheClient->set('sepehr-auth', json_encode($array), -1);
            return json_decode(json_encode($array));
        } catch (GuzzleException $e) {
            $this->throwError($e->getCode(), (string)$e->getResponse()->getBody());
        }
        return null;
    }

    private function getClient(string $url, $parameters): Client {
//        $stack = HandlerStack::create();
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);
        $middleware = [
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
        ];
        $middleware = new Oauth1(array_merge($middleware, $parameters));
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => $url,
            'handler' => $stack
        ]);
        return $client;
    }

    private function throwError($code, $body) {
        http_response_code($code);
        echo $body;
    }

}
