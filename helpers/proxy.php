<?php

define('CSAJAX_DEBUG', true);

abstract class Proxy {

    public static function fetch($request_url) {
        $p_request_url = parse_url($request_url);

        // ignore requests for proxy :)
        if (preg_match('!' . $_SERVER['SCRIPT_NAME'] . '!', $request_url) || empty($request_url) || count($p_request_url) == 1) {
            Proxy::debug('Invalid request - make sure that url is not empty');
            exit;
        }

        // append query string for GET requests
        $request_url = str_replace(' ', '%20', $request_url);

        // let the request begin
        $ch = curl_init($request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // return response
        curl_setopt($ch, CURLOPT_HEADER, true);       // enabled response headers
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        // retrieve response (headers and content)
        try {
            $response = curl_exec($ch);
        } catch (Exception $exception) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);
        list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);

        // finally, output the content
//        $output;
        try {
            $output = json_decode($response_content);
        } catch (Exception $exception) {
            $output = $response_content;
        }
        return $output;
    }

    static function debug($message) {
        if (true == CSAJAX_DEBUG) {
            print $message . PHP_EOL;
        }
    }
}