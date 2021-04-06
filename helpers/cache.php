<?php

final class Cache {

    private static $instance = null;
    private $config;
    private $serverConfig = [];
    public $client;

    private function __construct() {
        $this->config = Config::getInstance()->data->cache;
        $this->serverConfig = [
            'scheme' => 'tcp',
            'host' => $this->config->host,
            'port' => $this->config->port,
            'database' => $this->config->database,
        ];
        try {
            $this->client = new Predis\Client($this->serverConfig);
        } catch (Exception $exception) {
            // ignore
            $this->client = null;
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    public function set($key, $data, $expire) {
        if ($this->client) {
            try {
                if ($expire !== -1)
                    $this->client->set($key, $data, 'EX', $expire);
                else
                    $this->client->set($key, $data);
            } catch (Exception $exception) {
                // ignore
            }
        }
        return is_string($data) ? json_decode($data) : $data;
    }

    public function get($key) {
        $data = '';
        try {
            $data = $this->client->get($key);
        } catch (Exception $exception) {
            // ignore
        }
//        $data = $this->client !== null ?  : '';
        if (strlen($data) === 0)
            return $data;
        return is_string($data) ? json_decode($data) : $data;
    }

}