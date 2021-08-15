<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use ResponseHelper as Response;

final class UhdHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;
    private $today = '';

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->{'uhd'};
        $this->cacheClient = Cache::getInstance();
        $this->today = date('Y-m-d', time());
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new UhdHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'uhd') {
        return $this->cacheClient->get($key);
    }

    public function getAll(Request $request, $args) {
        $uri = isset($args['uri']) && $args['uri'] ? $args['uri'] : '';
        $uri .= '?' . $request->getUri()->getQuery();
        $cacheKey = 'uhd' . str_replace('=', '-', str_replace('?', '_', $uri));
        $cachedData = $this->checkCache($cacheKey);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->url . $uri));
        $cachedData = $this->cacheClient->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getEpgByDate($date = null) {
        if (!$date) {
            $date = $this->today;
        }
        $cacheKey = 'uhd-epg_' . $date;
        $cachedData = $this->checkCache($cacheKey);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $jdate = JDate::gregorianToJalaali($date)->format('yyyy/MM/dd');
        $url = str_replace('{jdate}', $jdate, $this->config->epg);
        $data = $this->handleEpg(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handle($items) {
        foreach ($items as $item) {
            $item->introtext = strip_tags($item->introtext);
        }
        return $items;
    }

    private function handleEpg($items) {
        $output = [];
        foreach ($items as $item) {
            $c = new stdClass();
//            $item->introtext = strip_tags($item->introtext);
            $c->mediaId = 0;
            $c->description = strip_tags($item->description);
            $c->start = str_ireplace('/', '-', $item->start);
            $c->duration = $item->duration;
            $c->episodeTitle = $item->title;
            $c->programTitle = '';
            $c->thumbnail = '';
//            $c->hasVideo = false;
//            $c->isCurrent = false;

            $output[] = $c;
        }
        return $output;
    }

}