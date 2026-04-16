<?php
define( 'DB_NAME', 'local' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'xR7!kP2$mN9qL4wJ6vB3yT8eF1hA5dC0gU' );
define( 'SECURE_AUTH_KEY',  'nM3$jK8!pQ5wL2rT7vB4yF9eH1dA6cG0xU' );
define( 'LOGGED_IN_KEY',    'wL5!rT2$vB8yF3eH9dA4cG7xU0nM1jK6pQ' );
define( 'NONCE_KEY',        'yF9!eH3$dA7cG2xU8nM4jK1pQ5wL0rT6vB' );
define( 'AUTH_SALT',        'dA2!cG6$xU1nM5jK9pQ3wL7rT0vB4yF8eH' );
define( 'SECURE_AUTH_SALT', 'xU5!nM9$jK3pQ7wL1rT4vB8yF2eH6dA0cG' );
define( 'LOGGED_IN_SALT',   'jK8!pQ2$wL6rT0vB3yF7eH1dA5cG9xU4nM' );
define( 'NONCE_SALT',       'rT1!vB5$yF9eH3dA7cG0xU4nM8jK2pQ6wL' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';