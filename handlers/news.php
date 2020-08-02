<?php

use ResponseHelper as Response;

final class NewsHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->news;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new NewsHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'news') {
        return $this->cacheClient->get($key);
    }

    public function getAll() {
        $cachedData = $this->checkCache();
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->url));
        $cachedData = $this->cacheClient->set('news', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }
	
	public function getCategory($newsId) {
		$cachedData = $this->checkCache('news-' . $newsId);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_ireplace('Categories=-1', 'Categories=' . $newsId, $this->config->url);
        $data = $this->handle(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set('news-' . $newsId, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
	}

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
                    $c->categories[] = $category->Title;
                }
            }
            if (count($item->Repositories)) {
                $c->thumbnail = new stdClass();
                $c->thumbnail->url = $item->Repositories[0]->Thumbnail;
                $c->thumbnail->desc = $item->Repositories[0]->Description;
				$c->media = null;
				
				if (count($item->Repositories) > 1) {
					foreach($item->Repositories as $repo) {
						if (stristr($repo->FilePath, '.mp4')) {
							$c->media = str_replace('\\', '/', str_replace('.jpg', '_wlq.mp4', $repo->Thumbnail));
						}
					}
				}
            }
            $output[] = $c;
        }
        return $output;
    }

}