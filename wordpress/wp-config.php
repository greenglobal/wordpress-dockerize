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
define('AUTH_KEY',         ' D<OW@Wv`;O[Sa{_by-:B}%7~P%-T(Vkw3y{5ym<#S>kwjQ(tnbc9r=Co{)v~{kd');
define('SECURE_AUTH_KEY',  'UF<K-78}9gc]/M63gq3voj#V|OffC_+Kw];&D L10XHWB&4R-9J_H<$i$XoN5h{G');
define('LOGGED_IN_KEY',    'yVw$S.3V&uApF&gcrbeMB@-MWQ2y1GfdJ2:`o~V?x}D4!oK&AXv5HAR1E$P#5&;T');
define('NONCE_KEY',        'h,P.R`+Dacx5K(:%tx)ES:X$kYj2{AzOJ4kRbUByJ}1>g$aJ{[[q**C|.. `HrW=');
define('AUTH_SALT',        '^*WQX=?&&{-*vju3V_G4lS&iXjJ^dcf4sJ<x>gB$v. |,]pAuq{!yAfY@vfW{rY9');
define('SECURE_AUTH_SALT', '=!p#fydg^Pk!q,[E3~rn.#*x1Mln4`QbV5lWGg$09LN1P}pSFpM:,#pfP3]Dvnny');
define('LOGGED_IN_SALT',   'T#[VwO_h727PGtYel^3R&{Klp~N{$l|~5F_A~$7{PC:AUI*-nM@E)<f%T&(ra5;m');
define('NONCE_SALT',       ',2/fK]]]@M/e0x2qKN0FAm3gRhfPGz:L[Mu>>O:x5P1YGhD]Lj7d!iE}&-r:)1F[');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ggwp_';

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
