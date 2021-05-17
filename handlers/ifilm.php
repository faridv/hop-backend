<?php

use ResponseHelper as Response;

final class IfilmHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->ifilm;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new IfilmHandler();
        }
        return self::$instance;
    }

    private function checkCache($key = 'ifilm') {
        return $this->cacheClient->get($key);
    }

    public function get() {
        return IfilmHandler::getHomepage();
    }


    public function getHomepage() {
        $cachedData = $this->checkCache('ifilm');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $data = $this->handle(Proxy::fetch($this->config->homepage));
        $cachedData = $this->cacheClient->set('ifilm', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getNews() {
        $cachedData = $this->checkCache('ifilm/news');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        try {
            $data = $this->handleNews(Proxy::fetch($this->config->news));
            $cachedData = $this->cacheClient->set('ifilm/news', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (Exception $e) {
        }
    }

    public function getNewsDetail($id) {
        $cachedData = $this->checkCache("ifilm/news/{$id}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{id}', (string)$id, $this->config->newsDetail);
        $data = $this->handleNewsDetail(Proxy::fetch($url)->HeadLine);
        $cachedData = $this->cacheClient->set("ifilm/news/{$id}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getSchedule($date) {
        $cachedData = $this->checkCache("ifilm/schedule/{$date}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{date}', (string)$date, $this->config->schedule);
        $data = $this->handleSchedule(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set("ifilm/schedule/{$date}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    public function getSeries() {
        $cachedData = $this->checkCache('ifilm/series');
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        try {
            $data = $this->handleSeries(Proxy::fetch($this->config->series));
            $cachedData = $this->cacheClient->set('ifilm/series', json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (Exception $e) {
        }
    }

    public function getSerieEpisodes($id) {
        $cachedData = $this->checkCache("ifilm/series/{$id}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{id}', (string)$id, $this->config->newsDetail);
        try {
            $data = $this->handleEpisodes(Proxy::fetch($url)->HeadLine->Episods);
            $cachedData = $this->cacheClient->set("ifilm/series/{$id}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
            return Response::prepare($cachedData, $this->fromCache);
        } catch (Exception $e) {
        }
    }


    public function closeTags($html) {
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i = 0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</' . $openedtags[$i] . '>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }

    private function handleSeries($items) {
        $output = [];
        foreach ($items as $key => $item) {
            $c = new stdClass();
            $c->id = $item->Id;
            $c->image = $this->config->url . $item->ImageAddress_L;
            $c->summary = $item->Summary;
            $c->title = $item->Title;
            $c->description = html_entity_decode($this->closeTags($item->TextBody), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $c->director = $item->StrDirectorsName;
            $c->schedule = null;
            $c->trailer = count($item->Trailers) ? $this->config->url . $item->Trailers[0]->VideoAddress : null;

            $output[] = $c;
        }
        return $output;
    }

    private function handleEpisodes($items) {
        $output = [];
        foreach ($items as $key => $item) {
            if (strtotime($item->Date) < time()) {
                $c = new stdClass();
                $c->id = $item->Id;
                $c->image = str_ireplace('{serieId}', $item->ItemId, str_ireplace('{episodeNumber}', $key + 1, $this->config->episodeThumbnailUrl));
                $c->videoDownload = str_ireplace('{serieId}', $item->ItemId, str_ireplace('{episodeNumber}', $key + 1, $this->config->episodeVideoUrl));
                $c->video = str_ireplace('{serieId}', $item->ItemId, str_ireplace('{episodeNumber}', $key + 1, $this->config->episodeVideoStreamUrl));
                $c->title = '';
                $c->summary = '';
                $c->part = ($key + 1);

                $output[] = $c;
            }
        }
        return $output;
    }

    private function handleNews($items) {
        $sections = ['Iran', 'World', 'Article', 'InterView', 'MostViewed'];

        $output = new stdClass();
        $output->iran = [];
        $output->world = [];
        $output->articles = [];
        $output->interview = [];
        $output->mostviewed = [];

        foreach ($sections as $section) {
            foreach ($items->{$section} as $item) {
                $c = new stdClass();
                $c->id = $item->Id;
                $c->shortTitle = $item->Title;
                $c->title = $item->Title;
                $c->summary = isset($item->Summary) ? $item->Summary : '';
                $c->text = '';

                $c->thumbnail = new stdClass();
                $c->thumbnail->url = $this->config->url . (isset($item->ImageAddress_L) ? $item->ImageAddress_L : $item->ImageAddress_S);
                $c->thumbnail->desc = $item->Title;
                $c->media = isset($item->VideoAddress) ? $this->config->url . $item->VideoAddress : null;

                $outputType = strtolower($section) === 'article' ? 'articles' : strtolower($section);
                $output->{$outputType}[] = $c;
            }
        }

        return $output;
    }

    private function handleNewsDetail($item) {
        $output = new stdClass();
        $output->id = $item->Id;
        $output->title = $item->Title;
        $output->summary = $item->Summary;
        $output->text = str_ireplace(' src="/', ' src="' . $this->config->url . '/', $item->TextBody);
        $output->date = $item->Start_Date;
        $output->image = $this->config->url . $item->ImageAddress_L;
        $output->categories = [];
        if (count($item->Tags)) {
            foreach ($item->Tags as $category) {
                $output->categories[] = $category->Name;
            }
        }

        return $output;
    }

    private function handleSchedule($items) {
        $output = [];
        foreach ($items as $key => $item) {
            $c = new stdClass();
            $c->mediaId = $item->ItemId;
            $c->description = $item->Summary;
            $c->start = str_replace('T', ' ', $item->Date);
            if (isset($items[$key + 1])) {
                $durationTime = (strtotime($items[$key + 1]->Time . ':00') - strtotime($item->Time . ':00')) / 3600;
            } else {
                $durationTime = (strtotime('24:00:00') - strtotime($item->Time . ':00')) / 3600;
            }
            $duration = floor($durationTime) . ':' . (($durationTime - floor($durationTime)) * 60);
            $durationFormatted = sprintf('%02d:%02d:00', explode(':', $duration)[0], explode(':', $duration)[1]);
			$c->duration = strtotime($durationFormatted) - strtotime('today');
            $c->isCurrent = false;
            if (isset($items[$key + 1])) {
                if (time() > strtotime($item->Time) && time() < strtotime($items[$key + 1]->Time)) {
                    $c->isCurrent = true;
                }
            }
            $c->episodeTitle = $item->Title;
            $c->programTitle = '';
            $c->thumbnail = $this->config->url . $item->ImageLink;
            $c->hasVideo = false;
            $output[] = $c;
        }
        return $output;
    }

}