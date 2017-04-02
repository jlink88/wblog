<?php
/*ini_set("log_errors", 1);
ini_set("error_log" , "stats/Errors.log.txt");*/
/** Loads the WordPress Environment and Template */
//TODO: Be sure to research which is the most appropriate way to include wp functions for this script
require ('../app/wp-blog-header.php');
//require_once('../app/wp-load.php');
//require_once(ABSPATH . WPINC . '/pluggable.php');
define('WP_USE_THEMES', false);

$env = getenv('env');
// our defaults for wordpress based databases
$badLinksTable = 'wp_bad_links';
$linksTable = 'wp_posts_links';
$tableFieldNames = array( 'link_id' => 'link_id','link_url' => 'link_url','post_id' => 'post_id', 'link_submitter' => 'link_submitter', 'date_added' => 'date_added' );
$rel_link_checker_options = get_option( 'rel_link_checker_option_name' ); // Array of All Options


$DBhost = $rel_link_checker_options['db_host'];
$DBname = $rel_link_checker_options['db_name'];
$DBuser = $rel_link_checker_options['db_user'];
$DBpass = $rel_link_checker_options['db_pass'];

if ($DBname == 'fstash') {
    $linksTable = 'episode_links';
    $tableFieldNames = array( 'link_id' => 'id','link_url' => 'link','post_id' => 'episode', 'link_submitter' => 'uid', 'date_added' => 'date_added' );
}

$services=array(
    "vidzi.tv",
    "thevideo.me",
    "openload.co",
    "vshare.eu",
);

$ds = DIRECTORY_SEPARATOR;
$root = dirname(__FILE__) . $ds;
$statsDir = $root."stats".$ds;
$includesPath = $root."includes".$ds;
$classPath = $root.$ds."class".$ds;
$statsPath = $root.$ds."stats".$ds."data.stats";
