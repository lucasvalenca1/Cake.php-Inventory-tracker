<?php

declare(strict_types=1);

return [
  'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

  'App' => [
    'namespace' => 'App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => ROOT . DS . 'src',
    'webroot' => ROOT . DS . 'webroot',
    'wwwRoot' => ROOT . DS . 'webroot',
    'fullBaseUrl' => env('APP_FULL_BASE_URL', false),
    'name' => 'Inventory Tracker',
    'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
    'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
    'path' => 'webroot',
  ],

  'Datasources' => [
    'default' => [
      'className' => 'Cake\Database\Connection',
      'driver' => 'Cake\Database\Driver\Mysql',
      'persistent' => false,
      'host' => env('DB_HOST', 'localhost'),
      'port' => env('DB_PORT', '3306'),
      'username' => env('DB_USERNAME', null),
      'password' => env('DB_PASSWORD', null),
      'database' => env('DB_DATABASE', 'inventory_tracker'),
      'encoding' => 'utf8mb4',
      'timezone' => 'UTC',
      'cacheMetadata' => true,
      'quoteIdentifiers' => true,
      'log' => env('DEBUG', false),
      'ssl_ca' => env('DB_SSL_CA', null),
      'ssl_verify' => env('DB_SSL_VERIFY', true),
    ],
  ],

  'Security' => [
    'salt' => env('SECURITY_SALT', null),
    'cookieKey' => env('SECURITY_COOKIE_KEY', null),
  ],

  'Error' => [
    'errorLevel' => E_ALL,
    'exceptionRenderer' => 'Cake\Error\ExceptionRenderer',
    'skipLog' => [
      'Cake\Http\Exception\NotFoundException',
      'Cake\Http\Exception\UnauthorizedException',
      'Cake\Http\Exception\ForbiddenException',
    ],
    'log' => true,
    'trace' => env('DEBUG', false),
  ],

  'Log' => [
    'debug' => [
      'className' => 'Cake\Log\Engine\FileLog',
      'path' => LOGS,
      'file' => 'debug',
      'url' => env('LOG_DEBUG_URL', null),
      'levels' => ['notice', 'info', 'debug'],
      'scopes' => false,
      'rotate' => 7,
      'size' => '10MB',
    ],
    'error' => [
      'className' => 'Cake\Log\Engine\FileLog',
      'path' => LOGS,
      'file' => 'error',
      'url' => env('LOG_ERROR_URL', null),
      'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
      'scopes' => false,
      'rotate' => 30,
      'size' => '50MB',
    ],
  ],

  'Session' => [
    'defaults' => 'php',
    'timeout' => 240, // 4 hours
    'cookieTimeout' => 240,
    'cookie' => env('SESSION_COOKIE_NAME', 'CAKEPHP'),
    'ini' => [
      'session.cookie_secure' => env('HTTPS', false),
      'session.cookie_httponly' => true,
      'session.cookie_samesite' => 'Lax',
      'session.use_strict_mode' => true,
      'session.gc_maxlifetime' => 14400,
      'session.gc_probability' => 1,
      'session.gc_divisor' => 100,
    ],
  ],

  'Asset' => [
    'timestamp' => true,
    'cacheTime' => '+1 year',
  ],
];
