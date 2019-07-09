<?php

final class Cache {

    private static $instance = null;
    private $config;
    private $serverConfig = [];
    public $client;

    private function __construct() {
        $this->config = Config::getInstance()->data->cache;
        $this->serverConfig = [
            'host' => $this->config->host,
            'port' => $this->config->port,
            'database' => $this->config->database,
        ];
        $this->client = new Predis\Client($this->serverConfig);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    public function set($data) {
        echo 111; die;
        return $this->client->set($data);
    }

    public function get($key) {
        echo 222,$key; die;
    }

}