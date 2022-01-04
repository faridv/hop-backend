<?php

use ResponseHelper as Response;
use Morilog\Jalali\Jalalian;

date_default_timezone_set('Asia/Tehran');

final class SamtHandler {

    private static $instance = null;
    private $config;
    private $cacheClient;
    private $fromCache = false;

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->samt;
        $this->cacheClient = Cache::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new SamtHandler();
        }
        return self::$instance;
    }

    private function checkCache($key) {
        return $this->cacheClient->get("samt-{$key}");
    }

    public function getData($query, $channelId = null) {
        if (!$channelId) {
            $channelId = $this->config->defaultChannelId;
        }
        $channelId = (int)$channelId;
        $cachedData = $this->checkCache("{$channelId}_{$query}");
        if ($cachedData) {
            $this->fromCache = true;
            return Response::prepare($cachedData, $this->fromCache);
        }
        $url = str_replace('{q}', urlencode((string)$query), $this->config->url);
        $url = str_replace('{channelId}', urlencode((string)$channelId), $url);
        $data = $this->handleData(Proxy::fetch($url));
        $cachedData = $this->cacheClient->set("samt-{$channelId}_{$query}", json_encode($data, JSON_UNESCAPED_UNICODE), $this->config->expire);
        return Response::prepare($cachedData, $this->fromCache);
    }

    private function handleData($data) {
        if (count($data->Data)) {
            $output = [];
            foreach ($data->Data as $item) {
                $c = new stdClass();
                $c->samtId = (int)$item->ID;
                $c->title = $item->Title;
                $c->summary = $item->summary;
                $c->productionStartDate = substr(Jalalian::fromFormat('Y/m/d', $item->ProductionStartDate)->toCarbon(), 0, 10);
                $c->productionEndtDate = substr(Jalalian::fromFormat('Y/m/d', $item->ProductionEndtDate)->toCarbon(), 0, 10);
                $c->estimateId = $item->EstimateNo;
                $c->licenseId = $item->LicenseNo;
                $c->isLive = $item->IsLive;
                $c->producer = [];
                foreach ($item->ListProducer as $producer) {
                    $p = new stdClass();
//                    $p->samtId = $producer->AgentID;
                    $p->firstName = $producer->FirstName;
                    $p->lastName = $producer->LastName;
//                    $p->type = $producer->ResourceTypeTitle;
//                    $p->samtTypeId = $producer->ResourceTypeID;
                    $c->producer[] = $p;
                }
                $c->approach = [];
                foreach ($item->ListApproach as $approach) {
                    $a = new stdClass();
//                    $a->samtId = $approach->ApproachID;
//                    $a->percent = $approach->ApproachPercent;
                    $a->title = $approach->ApproachTitle;
                    $c->approach[] = $a;
                }
                $c->structure = $item->StructureTitle;
                $c->grade = $item->GradeTitle;
                $output[] = $c;
            }
            return $output;
        }
        return [];
    }

}