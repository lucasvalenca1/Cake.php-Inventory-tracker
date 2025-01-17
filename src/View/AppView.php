<?php

declare(strict_types=1);

namespace App\View;

use Cake\View\View;
use Cake\Log\Log;
use RuntimeException;

/**
 * Application View
 *
 * Your application's default view class
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * @return void
     * @throws RuntimeException When component loading fails
     */
    public function initialize(): void
    {
        try {
            // Load required components with configurations
            $this->loadComponent('Flash', [
                'clear' => true,
                'element' => 'default'
            ]);

            $this->loadComponent('RequestHandler', [
                'enableBeforeRender' => true,
                'viewClassMap' => [
                    'json' => 'Json',
                    'xml' => 'Xml'
                ]
            ]);

            $this->loadComponent('Paginator', [
                'limit' => 20,
                'maxLimit' => 100
            ]);

            // Load security components
            $this->loadComponent('Security');
            $this->loadComponent('FormProtection');

            // Set default layout with error handling
            if (!$this->getLayout()) {
                $this->setLayout('default');
            }

            // Initialize common view blocks
            $this->initializeBlocks();
        } catch (\Exception $e) {
            Log::error('View initialization error: ' . $e->getMessage());
            throw new RuntimeException('Failed to initialize view components');
        }
    }

    /**
     * Initialize common view blocks
     *
     * @return void
     */
    private function initializeBlocks(): void
    {
        $this->assign('title', '');
        $this->assign('meta', '');
        $this->assign('css', '');
        $this->assign('script', '');
    }

    /**
     * Override beforeRender to add common data
     *
     * @param mixed $viewFile The view file being rendered
     * @return void
     */
    public function beforeRender($viewFile): void
    {
        parent::beforeRender($viewFile);

        // Add common view variables
        $this->set('isAjax', $this->getRequest()->is('ajax'));
        $this->set('isJson', $this->getRequest()->is('json'));
    }
}
