<?php
define('DB_NAME', 'cms_education');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Clés uniques de sécurité
define('AUTH_KEY',         'groupe1');
define('SECURE_AUTH_KEY',  'groupe1');
define('LOGGED_IN_KEY',    'groupe1');
define('NONCE_KEY',        'groupe1');
define('AUTH_SALT',        'groupe1');
define('SECURE_AUTH_SALT', 'groupe1');
define('LOGGED_IN_SALT',   'groupe1');
define('NONCE_SALT',       'groupe1');

$table_prefix = 'wp_';

define('WP_DEBUG', false);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
