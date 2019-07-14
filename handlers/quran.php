<?php

use ResponseHelper as Response;

final class QuranHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->quran;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new QuranHandler();
        }
        return self::$instance;
    }

    private function generateCacheKey($type, $id = 0, $offset = 0, $limit = 300) {
        return "quran-{$type}/{$id}/editions/{$this->config->edition}?offset={$offset}&limit={$limit}";
    }

    private function checkCache($type, $id = 0, $offset = 0, $limit = 300) {
        $key = $this->generateCacheKey($type, $id, $offset, $limit);
        return $this->cacheClient->get($key);
    }

    public function getSurah($surahId, $offset = 0, $limit = 300) {
        $cachedData = $this->checkCache('surah', $surahId);
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = $this->config->url . "surah/{$surahId}/editions/{$this->config->edition}?offset={$offset}&limit={$limit}";
        $data = $this->handleSurah(Proxy::fetch($url));
        $key = $this->generateCacheKey('surah', $surahId, $offset, $limit);
        $cachedData = $this->cacheClient->set($key, json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handleSurah($items) {
        if (is_array($items->data)) {
            foreach ($items->data as $data) {
                $data->ayahs = $this->handleAyah($data->ayahs);
            }
        } else {
            $items->ayahs = $this->handleAyah($items->data->ayahs);
        }
        return $items->data;
    }

    private function handleAyah($ayahs) {
        foreach ($ayahs as $ayah) {
            $ayah->number = $ayah->numberInSurah;
            unset($ayah->numberInSurah);
            unset($ayah->juz);
            unset($ayah->manzil);
            unset($ayah->page);
            unset($ayah->ruku);
            unset($ayah->hizbQuarter);
            if (!$ayah->sajda)
                unset($ayah->sajda);
        }
        return $ayahs;
    }

}