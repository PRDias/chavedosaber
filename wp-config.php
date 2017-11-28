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

define('WP_HOME','http://escolachavedosaber.com.br/');
define('WP_SITEURL','http://escolachavedosaber.com.br/');


define('FS_METHOD', 'direct');
define('DB_NAME', 'u851984465_chave');

/** MySQL database username */
define('DB_USER', 'u851984465_chave');

/** MySQL database password */
define('DB_PASSWORD', 'm20b30a0');

/** MySQL hostname */
define('DB_HOST', 'mysql.hostinger.com.br');

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
define('AUTH_KEY',         'B4(<b]P(EN-jqAa;~xHmXM&2Aatz7~3$&<p#E;5b/mM9)8kbuS3PzP {j.>ct~bK');
define('SECURE_AUTH_KEY',  'g;:,`YCN[|AHIE,] :Mt{4=.}`)1)iN,rq$;kV]U]uRDdJ./YEWHeW?]7`k|5Ie9');
define('LOGGED_IN_KEY',    '[K]=?F%XD&!nmT?U+:_{cW7U:#GM;Hk}<bgX@0|]JTq^{+h22d9(MO|vq<o*`H0?');
define('NONCE_KEY',        '&:3>lCG}vl9<Bc/j^bs[JIfz,Y[p=rd_{f|3:Iez/m:m[L=jkHvPC60-Y^p0(+zX');
define('AUTH_SALT',        'X>AEXg!mEyqL,4?(3K_6`CbL5_2tz}%qH-rCpSjJ-:B!7u~F}DXG<hFd{Lp1Egu_');
define('SECURE_AUTH_SALT', 'RPhNz`fl+dl8#RnCZU.R!#FmXe)[y+3ba,G{_W0ueR-%!{AVt&JCYwt3kln|&]Um');
define('LOGGED_IN_SALT',   'afHRu[1{9ORpuF!t?omNmAMF~#b:4Nkjw8U3NVF!6K.^AD@7rz ;rj|L=!oI/mW6');
define('NONCE_SALT',       'bbK{v%+UO?&@7xE3u~Kjvm,84<Pp3<X01_d,|~TUs?~-V,q:xT}y3a.ssN`*a2!Q');

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
