<?php

final class ProgramHandler {

    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->programs;
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ProgramHandler();
        }
        return self::$instance;
    }

    public function get() {
        return ProgramHandler::getLatest();
    }

    public function getLatest() {
        return $this->handle(Proxy::fetch($this->config->url));
    }

    public function getList($type) {
        $url = str_replace('{programType}', (string)$type, $this->config->list);
        return Proxy::fetch($url);
    }

    public function getEpisodes($programId) {
        $url = str_replace('{programId}', (string)$programId, $this->config->episodes);
//        return Proxy::fetch($url);
        return $this->handleEpisodes(Proxy::fetch($url));
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