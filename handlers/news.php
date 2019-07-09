<?php

final class NewsHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->news;
        $this->cacheClient = Cache::getInstance()->client;
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new NewsHandler();
        }
        return self::$instance;
    }

    private function checkCache() {
        return $this->cacheClient->get('news');
    }

    public function getAll() {
//        echo $this->checkCache(); die();
//        $cachedData = $this->checkCache();
//        if ($cachedData) {
//            return $cachedData;
//        }
        $data = $this->handle(Proxy::fetch($this->config->url));
        return $this->cacheClient->set('news', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

//    public function getById($id) {
//        return Proxy::fetch($this->config->url . '/' . $id);
//    }

    private function handle($items) {
        $output = [];
        foreach ($items as $key => $item) {
            if (!$item->IsPublished)
                continue;
            $c = new stdClass();
            $c->id = $item->Id;
            $c->shortTitle = $item->ShortTitle;
            $c->title = $item->Title;
            $c->summary = $item->Introtext;
            $c->text = $item->Fulltext;
            $c->categories = [];
            if (count($item->Categories)) {
                foreach ($item->Categories as $category) {
                    $c->category[] = $category->Title;
                }
            }
            if (count($item->Repositories)) {
                $c->thumbnail = new stdClass();
                $c->thumbnail->url = $item->Repositories[0]->Thumbnail;
                $c->thumbnail->desc = $item->Repositories[0]->Description;
            }

            $output[] = $c;
        }
        return $output;
    }

}