<?php
$config_dir =  __DIR__ . '/configs/';

if ( $_SERVER['HTTP_HOST'] == 'wblog.info' ) {

  define('DB_NAME', 'wblog');
  /** MySQL database username */
  define('DB_USER', 'wblog');
  /** MySQL database password */
  define('DB_PASSWORD', 'hnPgRSuu#YMC1X$');
  /** MySQL hostname */
  define('DB_HOST', '138.201.194.169');
  /** Database Charset to use in creating database tables. */
  define('DB_CHARSET', 'utf8');
  /** The Database Collate type. Don't change this if in doubt. */
  define('DB_COLLATE', '');

} else {
    define('DB_NAME', 'wblog');
    /** MySQL database username */
    define('DB_USER', 'root');
    /** MySQL database password */
    define('DB_PASSWORD', '');
    /** MySQL hostname */
    define('DB_HOST', 'localhost');
    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', 'utf8');
    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', '');
  }

require_once ($config_dir . 'base.php');
