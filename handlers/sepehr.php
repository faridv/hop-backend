<?php

require BASE . DS . 'lib/sepehr-auth-client.php';

use ResponseHelper as Response;
use GuzzleHttp\Client as Client;
use GuzzleHttp\HandlerStack as HandlerStack;
use GuzzleHttp\Handler\CurlHandler as CurlHandler;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as Oauth1;
use GuzzleHttp\Exception\GuzzleException as GuzzleException;

final class SepehrHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;
    private $today = '';
    private $token = null;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->sepehr;
        $this->cacheClient = Cache::getInstance();
        $this->today = date('YYYY-MM-DD', time());
        $this->token = $this->getAuth();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new SepehrHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'sepehr') {
        return $this->cacheClient->get($key);
    }

    private function getAuth() {
//        if (!$this->checkCache('sepehr-auth')) {
        $auth = new OAuthClient();
        return $auth->getTokens();
//        }
    }

    public function getCategories() {
        $cachedData = $this->checkCache('sepehr-categories');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        if (!$this->token->oauth_token) {
            $this->returnError(500, 'Authentication failed');
        }
        $client = $this->getRequestClient($this->config->categories, [
            'token' => $this->token->oauth_token,
            'token_secret' => $this->token->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ]);
        try {
            $response = $client->get($this->config->categories, ['auth' => 'oauth']);
            $data = $this->handleCategories($response->getBody()->getContents());
            $cachedData = $this->cacheClient->set('sepehr-categories', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (GuzzleException $e) {
            $this->returnError($e->getCode(), (string)$e->getResponse()->getBody());
        }
    }

    private function getRequestClient(string $url, $parameters): Client {
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);
        $middleware = ['consumer_key' => $this->config->consumerKey, 'consumer_secret' => $this->config->consumerSecret];
        $stack->push(new Oauth1(array_merge($middleware, $parameters)));
        return new Client(['base_uri' => $url, 'handler' => $stack]);
    }

    public function getChannels() {
        $cachedData = $this->checkCache('sepehr-channels');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        if (!$this->token->oauth_token) {
            $this->returnError(500, 'Authentication failed');
        }
        $client = $this->getRequestClient($this->config->channels, [
            'token' => $this->token->oauth_token,
            'token_secret' => $this->token->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ]);
        try {
            $response = $client->get($this->config->channels, ['auth' => 'oauth']);
            $data = $this->handleChannels($response->getBody()->getContents());
            $cachedData = $this->cacheClient->set('sepehr-channels', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (GuzzleException $e) {
            $this->returnError($e->getCode(), (string)$e->getResponse()->getBody());
        }
    }

    public function getChannelsByCategory($categoryId) {
        $cacheKey = 'sepehr-channels-' . $categoryId;
        $cachedData = $this->checkCache($cacheKey);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        if (!$this->token->oauth_token) {
            $this->returnError(500, 'Authentication failed');
        }
        $url = str_replace('{catId}', $categoryId, $this->config->channelsByCatId);
        $client = $this->getRequestClient($url, [
            'token' => $this->token->oauth_token,
            'token_secret' => $this->token->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ]);
        try {
            $response = $client->get($url, ['auth' => 'oauth']);
            $data = $this->handleChannels($response->getBody()->getContents());
            $cachedData = $this->cacheClient->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (GuzzleException $e) {
            $this->returnError($e->getCode(), (string)$e->getResponse()->getBody());
        }
    }

    public function getEpg($channelId, $date = null) {
        if (!$date) {
            $date = $this->today;
        }
        $cacheKey = 'sepehr-epg-' . $channelId . '_' . $date;
        $cachedData = $this->checkCache($cacheKey);
        // if ($cachedData) {
        // $this->fromCache = true;
        // return Response::prepare($cachedData, $this->fromCache);
        // }
        if (!$this->token->oauth_token) {
            $this->returnError(500, 'Authentication failed');
        }
        $url = str_replace('{channelId}', $channelId, $this->config->channelEpg);
        $url = str_replace('{date}', $date, $url);
        $client = $this->getRequestClient($url, [
            'token' => $this->token->oauth_token,
            'token_secret' => $this->token->oauth_token_secret,
            'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC,
        ]);
        try {
            $response = $client->get($url, ['auth' => 'oauth']);
            $data = $this->handleEpg($response->getBody()->getContents());
            $cachedData = $this->cacheClient->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (GuzzleException $e) {
            $this->returnError($e->getCode(), (string)$e->getResponse()->getBody());
        }
    }

    private function returnError($errorCode, $message) {
        $response = new \Slim\Http\Response($errorCode);
        return $response->write('{"success":false,"message":"' . $message . '"}');
    }

    private function handleEpg($data) {
        $output = [];
        $items = json_decode($data)->list;
        date_default_timezone_set('UTC');
        foreach ($items as $key => $item) {
            $c = new stdClass();
//            $c->id = $item->id;
            $c->mediaId = $item->id;
            $c->description = $item->descSummary;

            $timezone = new DateTimeZone('Asia/Tehran');
            $time = new \DateTime('now', $timezone);
            $timeOffsetInSeconds = $timezone->getOffset($time);
            $start = date('Y-m-d h:i:s', ($item->start / 1000));
            $newDateTime = new DateTime($start, $timezone);
            $newDateTime->add(new DateInterval('PT' . $timeOffsetInSeconds . 'S'));
            $c->start = $newDateTime->format('Y-m-d H:i:s');

//            $c->start = date('Y-m-d h:i:s', ($item->start / 1000));
            $c->d = $item->start;
            $c->duration = $item->duration;
            if ($item->current) {
                $c->isCurrent = $item->current;
            }
            $c->episodeTitle = $item->title;
            $c->thumbnail = $item->imageUrl;

            @$c->poster = $item->src_poster;

            $output[] = $c;
        }
        return $output;
    }

    private function handleCategories($data) {
        $output = [];
        $items = json_decode($data)->list;
        foreach ($items as $key => $item) {
//            if (!$item->src_poster)
//                continue;
            $c = new stdClass();
            $c->id = $item->id;
            $c->name = $item->name;
            @$c->poster = $item->src_poster;

            $output[] = $c;
        }
        return $output;
    }

    private function handleChannels($data, $fromCategory = false) {
        $output = [];
        $items = json_decode($data)->list;
        foreach ($items as $key => $item) {
            $c = new stdClass();
            $c->id = $item->id;
            $c->name = $item->name;
            $c->number = $item->number;
            $c->icon = $item->icon;
            @$c->poster = $item->poster;
            $c->preview = $item->preview;
            $c->stream = [];
            if (count($item->streams)) {
                foreach ($item->streams as $stream) {
                    $c->stream[] = $stream->src;
                }
            }
//            $c->stream = $item->streams[0]->src;
            $c->current = new stdClass();
            $c->current->start = $item->currentProgram->start / 1000;
            $c->current->duration = $item->currentProgram->duration;
            $c->current->title = $item->currentProgram->title;
            $c->current->summary = $item->currentProgram->descSummary;
            $c->current->desc = $item->currentProgram->descFull;

            $output[] = $c;
        }
        return $output;
    }

}