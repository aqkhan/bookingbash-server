<?php
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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
    include( dirname( __FILE__ ) . '/local-config.php' );
}

define('DB_NAME', 'maindb');

/** MySQL database username */
define('DB_USER', 'master');

/** MySQL database password */
define('DB_PASSWORD', 'aqkhan88');

/** MySQL hostname */
define('DB_HOST', 'main-app.cg0bpuqejsb0.us-west-2.rds.amazonaws.com:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'XMl.V`6!6R6<ZM#}ZJ=fWHFbWi7gobO|EmN,/4A`&[VM3&U7rhnOq#sP<[+D`i&P');
define('SECURE_AUTH_KEY',  'q4MML=`CocN}{1|q?o9Wph`%L.10LoGj&^]L*w{}$Vqwewl~|9J1e(5!L9pq-@dZ');
define('LOGGED_IN_KEY',    '.}-SAAu$nC/cDicx{5QV9^|,Raz3P.M3$ 1JMyBH)^)<c)B>lD%E85QJ0(Q+TK1g');
define('NONCE_KEY',        '##X?zo/<AHE!zpa*1/l<w{7pCKp#|*BO4^ ?2l9}g/iPvc__:k.NFr&JZ-GHY2Zd');
define('AUTH_SALT',        '3p;I7Q4+$=XNO=PTDRTQbXZ` Et8MUIM?M( +*wsF#/+d`*mY>ixMHuAy9I6J #~');
define('SECURE_AUTH_SALT', 'RCx7i(ZN4!9ub:}aqJqV.yfPc!.d[7YhXJ04|~DnA9Wu-%#E_T4D1,@AM[R>%/WS');
define('LOGGED_IN_SALT',   'oLya>AlV#,jX-(LRCJZ-2 PJ-{a(ZZ qw.fT(mK6<_-hQ)&PQ;1K7,wTX{Rm!3u0');
define('NONCE_SALT',       'zZesyFgGU8a!{6XY`%;gqWm0zWZGHs]Q%%~TY]4o;@rSvR(jNRD/ETml}dCSxyBY');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
