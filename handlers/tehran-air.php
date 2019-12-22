<?php

use ResponseHelper as Response;

final class TehranAirHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $moduleName = 'tehran-air';
        $this->config = Config::getInstance()->data->modules->{'tehran-air'};
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new TehranAirHandler();
        }
        return self::$instance;
    }

    private function checkCache($type) {
        return $this->cacheClient->get("air-{$type}");
    }

    public function get($type) {
        $cachedData = $this->checkCache($type);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }

        $url = str_ireplace('{type}', (string) $type, $this->config->url);
        $data = file_get_contents($url);
        $data = str_ireplace('</head>', '<base href="http://31.24.238.89"></head>', $data);
        $cachedData = $this->cacheClient->set("air-{$type}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

}