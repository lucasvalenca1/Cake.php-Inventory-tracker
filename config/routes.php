<?php

declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Http\Middleware\CsrfProtectionMiddleware;

return static function (RouteBuilder $routes) {
    // Set default route class
    $routes->setRouteClass(DashedRoute::class);

    // Apply CSRF protection to all routes
    $routes->registerMiddleware('csrf', new CsrfProtectionMiddleware([
        'httponly' => true,
        'secure' => !empty(env('HTTPS')),
        'samesite' => 'Lax'
    ]));

    $routes->scope('/', function (RouteBuilder $builder) {
        // Apply CSRF protection
        $builder->applyMiddleware('csrf');

        // Home page route
        $builder->connect(
            '/',
            ['controller' => 'Products', 'action' => 'index'],
            ['_name' => 'home']
        );

        // RESTful API routes for Products
        $builder->resources('Products', [
            'map' => [
                'index' => [
                    'action' => 'index',
                    'method' => 'GET',
                    'path' => '/',
                ],
                'view' => [
                    'action' => 'view',
                    'method' => 'GET',
                    'path' => '/{id}',
                ],
                'add' => [
                    'action' => 'add',
                    'method' => ['GET', 'POST'],
                    'path' => '/add',
                ],
                'edit' => [
                    'action' => 'edit',
                    'method' => ['GET', 'PUT', 'POST'],
                    'path' => '/{id}/edit',
                ],
                'delete' => [
                    'action' => 'delete',
                    'method' => ['DELETE', 'POST'],
                    'path' => '/{id}/delete',
                ],
                'search' => [
                    'action' => 'search',
                    'method' => 'GET',
                    'path' => '/search',
                ]
            ],
            'id' => '[0-9]+',
            'connectOptions' => [
                'persist' => ['id'],
                '_ext' => ['json']
            ]
        ]);

        // Error handling routes
        $builder->connect(
            '/error/{code}',
            ['controller' => 'Error', 'action' => 'index'],
            ['pass' => ['code'], '_name' => 'error']
        );

        $builder->fallbacks(DashedRoute::class);
    });
};
