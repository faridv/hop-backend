<?php

final class Config {

    public $data;
    private static $instance = null;

    private function __construct() {
        // Check redis for cached config
        // if no config available in cache, include the file
        // cache config as use here
        $this->data = $this->getConfig();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    private function getConfig() {
        $config = $this->retrieveCache();
        if ($config) {
            return $config;
        }
        return $this->loadFile();
    }

    private function retrieveCache() {
        // TODO
        return false;
    }

    private function loadFile() {
        $str = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.json');
        return json_decode($str);
    }
}

//"api": {
//    "url": "http://77.36.163.194/proxy.php?csurl=",
//        "api_guide": "https://services.iktv.ir/config/app.json",
//        "services": {
//        "ipg": "http://www.pooyatv.ir/ipg",
//            "weather": "http://77.36.163.194/weather.php",
//            "log": "/misc/log.php",
//            "schedule": "https://services.iktv.ir/pl/frontend.svc/schedule/",
//            "program.latest": "https://services.iktv.ir/pl/app.svc/programs/list/1,2,3,4/50/published%20desc/1",
//            "program.list": "https://iktv.ir/services/pl/app.svc/programs/list/{programType}/20/Title/1",
//            "program.episodes": "https://services.iktv.ir/pl/app.svc/programs/episodes/{programId}/20/published%20desc",
//            "news": "https://services.iktv.ir/pl/app.svc/contents/list/",
//            "episode": "https://services.iktv.ir/pl/programs.svc/episode",
//            "media": "https://au.iktv.ir/services/api/media",
//            "market.labels": "http://au.iktv.ir/services/api/economy/tree",
//            "market.data": "http://au.iktv.ir/services/api/economy/data"
//        }
//    },