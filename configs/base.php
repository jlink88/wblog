<?php
/**
 * Created by PhpStorm.
 * User: Josué
 * Date: 30/12/2015
 * Time: 18:00
 */

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

/**
 * Shared configuration parameters
 */

/**
 * Defines
 */

define('WP_CACHE', true); // Added by W3 Total Cache
define('DISALLOW_FILE_EDIT', true);
define('WP_AUTO_UPDATE_CORE', false);
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define('DISABLE_WP_CRON', false);
define( 'WP_POST_REVISIONS', false );
define( 'SAVEQUERIES', true );
define('WP_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/app');
define('WP_HOME', 'http://' . $_SERVER['SERVER_NAME']);
define('WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/wp-content');
define('WP_CONTENT_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/wp-content');

if ($_SERVER['HTTP_HOST'] != 'wblog.local') {
    $_SERVER['HTTPS'] = 'on';
}


$myDevStatus = 0;

// Enable WP_DEBUG mode
define( 'WP_DEBUG', $myDevStatus );


// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', 0 );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', $myDevStatus );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', 0 );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '|2|J@RkXIe@1E[oJM6lpEw@g{4G~&Z+eVLJZ ?XWXjg15X!!=lp8JBk}He5}L&7z');
define('SECURE_AUTH_KEY',  'lg ta4121rIbO<b-MV%AW#7@E|.-oYVQW3}lbl,Fvpy{?*xNL jmZIMgmoXMJv=t');
define('LOGGED_IN_KEY',    '<es^Ba|=Eq.,+`ZS[_!Vh3A%M34P(-t&G!+n*_01;Wb=E|.(+qS3]HU&,PeY?-2g');
define('NONCE_KEY',        'V0$0kz+K8kY(XA@jqfceoam[I9Tf3+l2;G,60|++-2rylN5F!8IGL__+evS$d*?n');
define('AUTH_SALT',        'H[m8|!Lc/Py5r&)i3NvMrmu|Vzg{8R!x CWe`av6N9Xor|C~Vh]=R9YJa#2HMaVW');
define('SECURE_AUTH_SALT', '$Vl%IZ4-_J#i4cSHH?*9xSwqW9OV&^gHd-%^^3Vo}5ad}jAV/f5Y-[U,UB$Fg0c-');
define('LOGGED_IN_SALT',   'z4[h$)psU/hC+6uwZ>CAp:Dxczx]t+V?HwU25C{|Y#ncfbbI^+PAEu5^ZsMDdv{+');
define('NONCE_SALT',       'MAPI@LM3C,y!~Y0,ok(x%,54|(O;-&Qo97)G<^-}4h/P,H%TQn4.aO,G[{NZ_tK)');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');