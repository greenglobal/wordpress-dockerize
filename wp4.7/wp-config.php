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
define('DB_NAME', 'wp');

/** MySQL database username */
define('DB_USER', 'wp');

/** MySQL database password */
define('DB_PASSWORD', '123456');

/** MySQL hostname */
define('DB_HOST', 'db');

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
define('AUTH_KEY',         '>RP2cB7An-Y*s;89@DH0w6@FC?Vh@~;)w89]V{yM+k~@Qi(BL3knf~A0G#R,lm}f');
define('SECURE_AUTH_KEY',  '`9BR@Z-<9W?5{1t#v@?p#@3U4WP],!P}QjpkVL}WBj3!H$8)5~]K f4j=A y!rz(');
define('LOGGED_IN_KEY',    'zolKv|vfS-WKORq4ybgk9TkE:GgXyF!n1E*r_T3<FNfMk*p@0fNj|{.F9E2[_n0X');
define('NONCE_KEY',        'MXI{5!jL0c(W.AwPVWjWe4%4%Kn/;Q:=hU(,S/nm,hVTz N5UX j]s$yCjUc6<&w');
define('AUTH_SALT',        'T=cE^n%)v}t]kut(zL65 CF/*A)-KZt`o !j>2$`rk7Lo.Tj{sgyga6n/{GqW~ C');
define('SECURE_AUTH_SALT', 'm//73&|BnabL$9U*qmJa/W)~XOx^<*]ZowfT0[(Ht2Kc1kWN/@XBBG~fdA_:H#.h');
define('LOGGED_IN_SALT',   'v%W.{DH<UhSfaH<jO[{4EQDMXSzMr$yy.?bM|4+caZr6%T9iO$CxNZ~_hN$l;UhC');
define('NONCE_SALT',       'Pdy$V0n;g;+y}YtDepp>-fVPr65M^+_]}o2lSO+-H&0P5k[WfC0*Vm2nAjG=p[@T');

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
