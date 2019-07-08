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

    public function getEpisode($programId) {
        $url = str_replace('{programId}', (string)$programId, $this->config->episodes);
        return Proxy::fetch($url);
    }

    public function handle($items) {
        $output = [];
        foreach ($items as $key => $item) {
            if (!$item->IsPublished)
                continue;
            $c = new stdClass();
            $c->id = $item->Id;
            $c->image = $item->Image ? $this->config->ThumbnailUrlPrefix . str_replace('.jpg', '_xl.jpg', $item->Image) : null;
            $c->summary = $item->IntroText;
            $c->title = $item->Title;
            $c->description = $item->FullText;
            $c->director = $item->Director;
            $c->schedule = $item->Schedule;
            $c->trailer = $item->Video ? $this->config->ThumbnailUrlPrefix . str_replace('\\', '/', str_replace('.mp4', '_wlq.mp4', $item->Video)) : null;
            $output[] = $c;
        }
        return $output;
    }

}