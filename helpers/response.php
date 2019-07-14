<?php

abstract class ResponseHelper {

    public static function prepare($data, $fromCache = false, $source = 'api') {
        $output = new stdClass();
        $output->success = true;
        $output->cache = $fromCache;
        $output->source = $fromCache ? 'cache' : $source;
        $output->data = $data;
        return $output;
    }

}