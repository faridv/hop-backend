<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use ResponseHelper as Response;

final class ItemsHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->{'uhd'};
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ItemsHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'uhd') {
        return $this->cacheClient->get($key);
    }

    public function getAll(Request $request, $args) {
        $uri = isset($args['uri']) && $args['uri'] ? $args['uri'] : '';
		$uri .= '?' . $request->getUri()->getQuery();
        $cacheKey = 'uhd' . str_replace('=', '-', str_replace('?', '_', $uri));
        $cachedData = $this->checkCache($cacheKey);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->url . $uri));
        $cachedData = $this->cacheClient->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handle($items) {
		foreach ($items as $item) {
			$item->introtext = strip_tags($item->introtext);
		}
        return $items;
    }

}