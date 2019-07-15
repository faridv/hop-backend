<?php

use ResponseHelper as Response;

final class Mediahandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->media;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Mediahandler();
        }
        return self::$instance;
    }

    private function checkCache($id) {
        return $this->cacheClient->get("media-{$id}");
    }

    public function getItem($id) {
        $cachedData = $this->checkCache($id);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->url . $id));
        $cachedData = $this->cacheClient->set("media-{$id}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handle($items) {
        $item = $items[0];
        $c = new stdClass();
        $c->id = $item->Id;
        $c->title = $item->Title;
        $c->thumbnail = $item->Thumbnail;
        $c->video = ($item->WebSite && $item->WebSite->Video) ? str_replace('.mp4', '_wlq.mp4', $item->WebSite->Video) : str_replace('.jpg', '_lq.mp4', $item->Thumbnail);
        return $c;
    }

}