<?php

final class Config {

    public $data;
    private static $instance = null;

    private function __construct() {
        // Check redis for cached config
        // if no config available in cache, include the file
        // cache config as use here
        $this->data = $this->getConfig();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    private function getConfig() {
        $config = $this->retrieveCache();
        if ($config) {
            return $config;
        }
        return $this->loadFile();
    }

    private function retrieveCache() {
        // TODO
        return false;
    }

    private function loadFile() {
        $str = file_get_contents(dirname(__FILE__) . DS . 'config.json');
        return json_decode($str);
    }
}