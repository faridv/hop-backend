<?php

define('CSAJAX_FILTERS', false);
define('CSAJAX_FILTER_DOMAIN', false);
define('CSAJAX_DEBUG', true);

$valid_requests = array(// 'example.com'
);
$curl_options = array(
    // CURLOPT_SSL_VERIFYPEER => false,
    // CURLOPT_SSL_VERIFYHOST => 2,
);
$request_headers = array();

abstract class Proxy
{

    public static function fetch($request_url)
    {

        $p_request_url = parse_url($request_url);

        // ignore requests for proxy :)
        if (preg_match('!' . $_SERVER['SCRIPT_NAME'] . '!', $request_url) || empty($request_url) || count($p_request_url) == 1) {
            Proxy::debug('Invalid request - make sure that url is not empty');
            exit;
        }

        // check against valid requests
        if (CSAJAX_FILTERS) {
            $parsed = $p_request_url;
            if (CSAJAX_FILTER_DOMAIN) {
                if (!in_array($parsed['host'], $valid_requests)) {
                    Proxy::debug('Invalid domain - ' . $parsed['host'] . ' does not included in valid requests');
                    exit;
                }
            } else {
                $check_url = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
                $check_url .= isset($parsed['user']) ? $parsed['user'] . ($parsed['pass'] ? ':' . $parsed['pass'] : '') . '@' : '';
                $check_url .= isset($parsed['host']) ? $parsed['host'] : '';
                $check_url .= isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $check_url .= isset($parsed['path']) ? $parsed['path'] : '';
                if (!in_array($check_url, $valid_requests)) {
                    Proxy::debug('Invalid domain - ' . $request_url . ' does not included in valid requests');
                    exit;
                }
            }
        }

        // append query string for GET requests
        $request_url = str_replace(' ', '%20', $request_url);

        // let the request begin
        $ch = curl_init($request_url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);   // (re-)send headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // return response
        curl_setopt($ch, CURLOPT_HEADER, true);       // enabled response headers

        // Set multiple options for curl according to configuration
//        if (is_array($curl_options) && 0 <= count($curl_options)) {
//            curl_setopt_array($ch, $curl_options);
//        }

        // retrieve response (headers and content)
        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        // split response to header and content

        list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);

        // (re-)send the headers
//        $response_headers = preg_split('/(\r\n){1}/', $response_headers);
//        foreach ($response_headers as $key => $response_header) {
            // Rewrite the `Location` header, so clients will also use the proxy for redirects.
//            if (preg_match('/^Location:/', $response_header)) {
//                list($header, $value) = preg_split('/: /', $response_header, 2);
//                $response_header = 'Location: ' . $_SERVER['REQUEST_URI'] . '?csurl=' . $value;
//            }
//            if (!preg_match('/^(Transfer-Encoding):/', $response_header)) {
//                header($response_header, false);
//            }
//        }

        // finally, output the content
        //print($response_content);
        return $response_content;

    }
    
    static function debug($message) {
        if (true == CSAJAX_DEBUG) {
            print $message . PHP_EOL;
        }
    }
}

echo Proxy::fetch('http://localhost');