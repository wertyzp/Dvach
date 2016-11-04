<?php

namespace Dvach;

class WebLoader {

    private function executeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch ($code) {
            case 404:
                throw new Exception("Not Found");
            case 200;
                break;
            default:
                throw new Exception("Unexpected response code");
        }
        
        if (!$response) {
            throw new Exception("Empty response");
        }

        return $response;
    }

    protected function loadHTML($url) {
        $response = $this->executeRequest($url);
        $doc = new \DOMDocument();
        @$doc->loadHTML($response);
        return $doc;
    }

    protected function loadJSON($url) {
        $response = $this->executeRequest($url);
        return json_decode($response, true);
    }

}
