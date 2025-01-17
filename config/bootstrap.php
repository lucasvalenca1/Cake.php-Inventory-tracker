<?php

declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Error\ConsoleErrorHandler;
use Cake\Error\ErrorHandler;
use Cake\Log\Log;
use Cake\Utility\Security;

try {
    // Security Configuration
    if (!Configure::read('debug')) {
        if (empty(env('SECURITY_SALT'))) {
            throw new \RuntimeException('SECURITY_SALT environment variable is not set');
        }
        Configure::write('Security.salt', env('SECURITY_SALT'));
    }

    // Timezone and Encoding Configuration
    if (!date_default_timezone_set(Configure::read('App.defaultTimezone'))) {
        Log::warning('Failed to set default timezone to ' . Configure::read('App.defaultTimezone'));
    }

    if (!mb_internal_encoding(Configure::read('App.encoding'))) {
        Log::warning('Failed to set internal encoding to ' . Configure::read('App.encoding'));
    }

    if (!ini_set('intl.default_locale', Configure::read('App.defaultLocale'))) {
        Log::warning('Failed to set default locale to ' . Configure::read('App.defaultLocale'));
    }

    // Error Handler Configuration
    $isCli = PHP_SAPI === 'cli';
    if ($isCli) {
        (new ConsoleErrorHandler(Configure::read('Error')))->register();
    } else {
        (new ErrorHandler(Configure::read('Error')))->register();
    }

    // Debug Mode Cache Configuration
    if (Configure::read('debug')) {
        Configure::write('Cache._cake_model_.duration', '+2 minutes');
        Configure::write('Cache._cake_core_.duration', '+2 minutes');
        Configure::write('Cache._cake_routes_.duration', '+2 seconds');
    } else {
        // Production Cache Settings
        Configure::write('Cache._cake_model_.duration', '+1 hour');
        Configure::write('Cache._cake_core_.duration', '+1 hour');
        Configure::write('Cache._cake_routes_.duration', '+1 hour');
    }

    // Additional Security Headers
    if (!$isCli) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        if (!Configure::read('debug')) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
} catch (\Exception $e) {
    Log::emergency('Bootstrap configuration failed: ' . $e->getMessage());
    throw $e;
}
