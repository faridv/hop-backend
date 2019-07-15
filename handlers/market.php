<?php

use ResponseHelper as Response;

final class MarketHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->market;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MarketHandler();
        }
        return self::$instance;
    }

    private function checkCache($key) {
        return $this->cacheClient->get("market-{$key}");
    }

    public function getLabels($pid) {
        $cachedData = $this->checkCache("labels-{$pid}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handleLabels(Proxy::fetch($this->config->labels . "?pid={$pid}"));
        $cachedData = $this->cacheClient->set("market-labels-{$pid}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getData($pid) {
        $cachedData = $this->checkCache("data-{$pid}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handleData(Proxy::fetch($this->config->data . "?pid={$pid}"));
        $cachedData = $this->cacheClient->set("market-data-{$pid}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handleLabels($labels) {
        $output = [];
        foreach ($labels as $label) {
            $c = new stdClass();
            if ($label->State < 1)
                continue;
            $c->id = $label->Id;
            $c->title = $label->Title;
            $c->unit = $label->Unit;
            $c->ref = $label->Ref;
            $output[] = $c;
        }
        return $output;
    }

    private function handleData($data) {
        $output = [];
        foreach ($data as $item) {
            $c = new stdClass();
            if ($item->State < 1)
                continue;
            $c->id = $item->Id;
            $c->title = $item->Title;
            $c->unit = $item->Unit;
            $c->ref = $item->Ref;
            $c->value = $item->Value;
            $c->lastValue = $item->Last_Value;
            $output[] = $c;
        }
        return $output;
    }

}