<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Log\Log;
use Cake\Http\Exception\BadRequestException;

/**
 * Products Controller - Add Action
 * 
 * Handles product creation with validation and security measures
 */
class ProductsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Security');
        $this->loadComponent('RequestHandler');
    }

    public function add()
    {
        $this->request->allowMethod(['get', 'post']);

        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            try {
                // Sanitize and validate input data
                $data = $this->sanitizeProductData($this->request->getData());
                Log::debug('Processing product data: ' . json_encode($data));

                $product = $this->Products->patchEntity($product, $data);

                if ($this->Products->save($product)) {
                    Log::info('Product created successfully: ' . $product->id);
                    $this->Flash->success(__('Product has been saved.'));
                    return $this->redirect(['action' => 'index']);
                }

                if ($product->getErrors()) {
                    Log::warning('Validation errors: ' . json_encode($product->getErrors()));
                    $this->Flash->error(__('Please correct the errors below.'));
                }
            } catch (\Exception $e) {
                Log::error('Error saving product: ' . $e->getMessage());
                $this->Flash->error(__('Unable to save product. Please try again.'));
            }
        }

        // Set up any required data for the view
        $this->set(compact('product'));

        // Set JSON response if requested
        if ($this->request->is('json')) {
            $this->viewBuilder()
                ->setOption('serialize', ['product', 'errors'])
                ->setOption('jsonOptions', JSON_PRETTY_PRINT);
        }
    }

    /**
     * Sanitizes product input data
     *
     * @param array $data Raw input data
     * @return array Sanitized data
     */
    private function sanitizeProductData(array $data): array
    {
        return [
            'name' => isset($data['name']) ?
                strip_tags(trim($data['name'])) : null,
            'quantity' => isset($data['quantity']) ?
                filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT) : null,
            'price' => isset($data['price']) ?
                filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null,
            'status' => isset($data['status']) ?
                strip_tags(trim($data['status'])) : null
        ];
    }
}
