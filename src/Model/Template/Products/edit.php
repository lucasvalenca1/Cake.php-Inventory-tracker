<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Log\Log;

/**
 * Products Controller - Edit Action
 * 
 * Handles product updates with validation and security measures
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

    public function edit($id = null)
    {
        $this->request->allowMethod(['get', 'post', 'put']);

        try {
            // Validate ID
            if (!$id || !is_numeric($id)) {
                throw new BadRequestException(__('Invalid product ID'));
            }

            // Fetch product with validation
            $product = $this->Products->get($id, [
                'conditions' => [
                    'deleted IS NOT' => true
                ]
            ]);

            if ($this->request->is(['post', 'put'])) {
                // Log original state for audit
                Log::debug('Editing product: ' . json_encode([
                    'id' => $id,
                    'original' => $product->toArray()
                ]));

                // Sanitize and validate input data
                $data = $this->sanitizeProductData($this->request->getData());

                // Patch entity with new data
                $product = $this->Products->patchEntity($product, $data);

                if ($this->Products->save($product)) {
                    Log::info('Product updated successfully: ' . $id);
                    $this->Flash->success(__('Product has been updated.'));
                    return $this->redirect(['action' => 'index']);
                }

                if ($product->getErrors()) {
                    Log::warning('Validation errors: ' . json_encode($product->getErrors()));
                    $this->Flash->error(__('Please correct the errors below.'));
                }
            }
        } catch (NotFoundException $e) {
            Log::error('Product not found: ' . $id);
            $this->Flash->error(__('Product not found.'));
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            $this->Flash->error(__('Unable to update product. Please try again.'));
        }

        // Set view variables
        $this->set(compact('product'));

        // Handle JSON requests
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
                htmlspecialchars(trim($data['name']), ENT_QUOTES) : null,
            'quantity' => isset($data['quantity']) ?
                filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT) : null,
            'price' => isset($data['price']) ?
                filter_var(
                    $data['price'],
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                ) : null,
            'status' => isset($data['status']) ?
                htmlspecialchars(trim($data['status']), ENT_QUOTES) : null
        ];
    }

    /**
     * Validates product status
     *
     * @param string $status Status to validate
     * @return bool
     */
    private function isValidStatus(string $status): bool
    {
        return in_array($status, ['in_stock', 'low_stock', 'out_of_stock'], true);
    }
}
