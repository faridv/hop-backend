<?php

use ResponseHelper as Response;

final class WeatherHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->weather;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new WeatherHandler();
        }
        return self::$instance;
    }

    private function checkCache($lat, $lon) {
        return $this->cacheClient->get("weather-{$lon}_{$lat}");
    }

    public function get($lat, $lon) {
        $cachedData = $this->checkCache($lon, $lat);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }

        $url = $this->config->url . "?lon=$lon&lat=$lat";
        $data = Proxy::fetch($url);
        $cachedData = $this->cacheClient->set("weather-{$lon}_{$lat}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

}