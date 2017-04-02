<?php
/**
 * Created by PhpStorm.
 * User: rel
 * Date: 22/06/2016
 * Time: 12:29 PM
 */

function cleanString($string,$match) {
    $string = str_replace($match, "", $string);
    return $string;
}
function common_check($hostPattern) {
    global $urls, $result, $badResult;
    foreach($urls as $url){
        $page=curlreq($url);

        preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

        if ( $status[1] == 200 || $status[1] == 404 ) {
            if (stripos($page ,$hostPattern)!== false){
                $result[]=$url;
                $badResult[]=$hostPattern;
            }
        }
    }
}

function otherHosts($url) {
    global $services,$statsDir;
    $othersHostsPath = $statsDir . "otherhosts.txt";
    $host = trim(strtolower(urlToHost($url)));
    $otherHosts = file_get_contents($othersHostsPath);
    $otherHosts = explode("\n",$otherHosts);

    if ( ! in_array($host,$otherHosts) && ! in_array($host,$services)) {
        file_put_contents($othersHostsPath,$host . "\n",FILE_APPEND);
    }
}

function get_http_code($url)
{
// Create a cURL handle
    $http_code = null;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // Execute
    curl_exec($ch);

    // Check HTTP status code
    if (!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    // Close handle
    curl_close($ch);
    return $http_code;
}

function otherTests($url,$linkId) {
    // keep track of invalid urls
    $myUrl = new \reLink\Parse_Url($url);
    if ($myUrl->isValid === false || $myUrl->isHome === true) {
        saveInvalidLink($url,$linkId);
    }
}

function saveInvalidLink($url,$linkId) {
    global $statsDir;
    $invalidLinksPath = $statsDir . "invalid.txt";
    if ( file_exists($invalidLinksPath) ) {
        $invalidLinks = file_get_contents($invalidLinksPath);
        $invalidLinks = explode("\n",$invalidLinks);
    } else {
        $invalidLinks = array();
    }
    if ( ! in_array($url,$invalidLinks) ) {
        file_put_contents($invalidLinksPath,"lid:" . $linkId . " " . $url . "\n",FILE_APPEND);
    }
}

function urlToHost($url)
{
    $parsed = parse_url($url);
    $host   = $parsed['host'];
    $host   = preg_replace('~[^a-zA-Z0-9\.]+~', '', $host);
    $host   = str_replace("www.", "", $host);
    return trim($host);
}