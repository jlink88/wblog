<?php
/**
 * Created by PhpStorm.
 * User: rel
 * Date: 12/7/2016
 * Time: 7:20 PM
 */

$hostPattern="<title>File not found";
foreach($urls as $url){
    $page=curlreq($url); // changed to follow location = true ,"","","" ,false,false,false

    preg_match('/^HTTP\/1\.0|1 ([0-9]+) .*/',$page,$status);

    if ($status[1] == 200 || $status[1] == 404) {
        if (stripos($page ,$hostPattern)!== false){
            $result[]=$url;
            $badResult[]=$hostPattern;
        }
    }
}