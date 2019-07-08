<?php

final class WeatherHandler {

    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->weather;
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new WeatherHandler();
        }
        return self::$instance;
    }

    public function get($lon, $lat) {
        $url = $this->config->url . "?lon=$lon&lat=$lat";
        return Proxy::fetch($url);
    }

}