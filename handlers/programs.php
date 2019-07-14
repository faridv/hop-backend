<?php

use ResponseHelper as Response;

final class ProgramHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->programs;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ProgramHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'programs') {
        return $this->cacheClient->get($key);
    }

    public function get() {
        return ProgramHandler::getLatest();
    }

    public function getLatest() {
        $cachedData = $this->checkCache('programs/latest');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->url));
        $cachedData = $this->cacheClient->set('programs/latest', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getList($type) {
        $cachedData = $this->checkCache("programs-bytype/{$type}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{programType}', (string)$type, $this->config->list);
        $data = $this->handle(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set("programs-bytype/{$type}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getEpisodes($programId) {
        $cachedData = $this->checkCache("programs-episodes/{$programId}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{programId}', (string)$programId, $this->config->episodes);
        $data = $this->handleEpisodes(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set("programs-episodes/{$programId}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handle($items) {
        $output = [];
        foreach ($items as $key => $item) {
            if (!$item->IsPublished)
                continue;
            $c = new stdClass();
            $c->id = $item->Id;
            $c->image = $item->Image ? $this->config->thumbnailUrlPrefix . str_replace('.jpg', '_xl.jpg', $item->Image) : null;
            $c->summary = $item->IntroText;
            $c->title = $item->Title;
            $c->description = $item->FullText;
            $c->director = $item->Director;
            $c->schedule = $item->Schedule;
            $c->trailer = $item->Video ? $this->config->trailerUrlPrefix . str_replace('\\', '/', str_replace('.mp4', '_wlq.mp4', $item->Video)) : null;

            $output[] = $c;
        }
        return $output;
    }

    private function handleEpisodes($items) {
        $output = [];
        foreach ($items as $key => $item) {
            if (!$item->IsPublished)
                continue;
            $c = new stdClass();
            $c->id = $item->Id;
            $c->image = $item->Image ? $this->config->episodeThumbnailUrlPrefix . $item->Image : null;
            $c->video = $item->Image ? $this->config->episodeThumbnailUrlPrefix . str_replace('.jpg', '_wlq.mp4', $item->Image) : null;
            $c->title = $item->Title;
            $c->summary= $item->Introtext;
            $c->part = $item->Number;

            $output[] = $c;
        }
        return $output;
    }

}