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
        if ($id !== 0)
            $key = "quran-{$type}/{$id}/editions/{$this->config->edition}?offset={$offset}&limit={$limit}";
        else
            $key = "quran-{$type}";
        return $key;
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

    public function getSurahList() {
        $cachedData = $this->checkCache('surah_list');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $str = file_get_contents(BASE . DS . 'config/surahs.json');
        $data = $this->handleSurahList(json_decode($str));
        $cachedData = $this->cacheClient->set('quran-surah_list', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handleSurahList($items) {
        $output = [];
        foreach ($items as $surah) {
            $c = new stdClass();
            $c->id = $surah->number;
            $c->title = $surah->name;
            $c->verses = $surah->total_verses;
            $c->type = $surah->revelation_type;
            $output[] = $c;
        }
        return $output;
    }

    private function handleSurah($items) {
        if (is_array($items->data)) {
            foreach ($items->data as $data) {
                if ($data->englishName)
                    unset($data->englishName);
                if ($data->englishNameTranslation)
                    unset($data->englishNameTranslation);

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