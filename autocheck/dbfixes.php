<?php
/**
 * Created by PhpStorm.
 * User: rel
 * Date: 10/05/2016
 * Time: 11:40 AM
 */
error_reporting(-1);
ini_set('display_errors', true);

require_once "config.php";
$conn = new mysqli($DBhost, $DBuser, $DBpass, $DBname) or die ('Could not connect to the database server' . mysqli_connect_error());
if ( isset($_GET['fix']) ) {
    $fix = $_GET['fix'];
}

if ( isset($fix) && $fix == 'openload' ) {
    if ($host != 'fanstash.se') {
        $sql = "UPDATE wp_posts_links SET link_url = replace(link_url,'http','https') WHERE `link_url` LIKE '%http://openload.co%'";
        $sql2 = "UPDATE wp_posts_links SET link_url = replace(link_url,'/embed/','/f/') WHERE `link_url` LIKE '%https://openload.co%'";
        $query = $conn->query($sql);
        echo "Openload links changed to https succesfully. {$conn->affected_rows} affected rows.";
        $query = $conn->query($sql2);
        echo "Openload '/embed/' changed to '/f/': {$conn->affected_rows} affected rows.";
    } else {
        $sql = "UPDATE `fstash`.`episode_links` SET `link` = REPLACE(`link`, 'http://openload.co', 'https://openload.co') WHERE `link` LIKE '%http://openload.co%'";
        $query = $conn->query($sql);
        echo "Openload links changed to https succesfully. {$conn->affected_rows} affected rows.";
        $sql = "UPDATE `fstash`.`episode_links` SET `link` = REPLACE(link_url,'/embed/','/f/') WHERE `link` LIKE '%https://openload.co%'";
        $query = $conn->query($sql);
        echo "Openload '/embed/' changed to '/f/': {$conn->affected_rows} affected rows.";
    }
}

