<?php

final class ScheduleHandler {

    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->schedule;
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ScheduleHandler();
        }
        return self::$instance;
    }

    public function get($date) {
        $url = $this->config->url . '?date=' . $date;
//        echo $url; die;
        return ScheduleHandler::handle(Proxy::fetch($url));
    }

    private function handle($items) {
        $output = [];
        foreach($items as $key => $item) {
            $c = new stdClass();
            $c->mediaId = $item->MediaId;
            $c->description = $item->description;
            $c->start = $item->start;
            $c->duration = $item->duration;
            $c->episodeTitle = $item->episodeTitle;
            $c->programTitle = $item->programTitle;
            $c->thumbnail = $item->thumbnail;
            $c->hasVideo = $item->hasVideo;
            $output[] = $c;
        }
        return $output;
    }

}