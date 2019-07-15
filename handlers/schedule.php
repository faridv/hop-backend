<?php

use ResponseHelper as Response;

final class ScheduleHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->schedule;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ScheduleHandler();
        }
        return self::$instance;
    }

    private function checkCache($date) {
        return $this->cacheClient->get("schedule-{$date}");
    }

    public function get($date) {
        $cachedData = $this->checkCache($date);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = $this->config->url . '?date=' . $date;
        $data = $this->handle(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set("schedule-{$date}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handle($items) {
        $output = [];
        foreach($items as $key => $item) {
            $c = new stdClass();
            $c->mediaId = $item->MediaId;
            $c->description = $item->description;
            $c->start = $item->start;
            $c->duration = $item->duration;
            if ($item->isCurrent) {
                $c->isCurrent = $item->isCurrent;
            }
            $c->episodeTitle = $item->episodeTitle;
            $c->programTitle = $item->programTitle;
            $c->thumbnail = $item->thumbnail;
            $c->hasVideo = $item->hasVideo;
            $output[] = $c;
        }
        return $output;
    }

}