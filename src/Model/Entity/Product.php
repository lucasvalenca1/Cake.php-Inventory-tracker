<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;
use Cake\Database\Expression\QueryExpression;

class ProductsController extends AppController
{
    private const VALID_STATUSES = [
        'in_stock',
        'low_stock',
        'out_of_stock'
    ];

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Security');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Paginator');

        // Add CSRF protection
        $this->Security->setConfig('validatePost', true);
        Log::debug('ProductsController initialized');
    }

    public function index()
    {
        try {
            $query = $this->Products->find()
                ->where(['deleted IS NOT' => true]);

            // Handle search
            if ($search = $this->getValidatedSearch()) {
                $query->where(function (QueryExpression $exp) use ($search) {
                    return $exp->like('name', '%' . $this->escapeLikeString($search) . '%');
                });
            }

            // Handle status filtering
            if ($status = $this->getValidatedStatus()) {
                $query->where(['status' => $status]);
            }

            $this->configurePagination();
            $products = $this->paginate($query);

            $this->set(compact('products'));
            $this->viewBuilder()->setOption('serialize', ['products']);
        } catch (\Exception $e) {
            Log::error('Index error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred while loading products.'));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function add()
    {
        $this->request->allowMethod(['get', 'post']);
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            try {
                $data = $this->sanitizeProductData($this->request->getData());
                $product = $this->Products->patchEntity($product, $data);

                if ($this->Products->save($product)) {
                    Log::info('Product created: ' . $product->id);
                    $this->Flash->success(__('Product saved successfully.'));
                    return $this->redirect(['action' => 'index']);
                }

                $this->handleValidationErrors($product);
            } catch (\Exception $e) {
                Log::error('Add product error: ' . $e->getMessage());
                $this->Flash->error(__('Unable to save product.'));
            }
        }

        $this->set(compact('product'));
    }

    public function edit($id = null)
    {
        $this->request->allowMethod(['get', 'post', 'put']);

        try {
            $product = $this->validateAndGetProduct($id);

            if ($this->request->is(['post', 'put'])) {
                $data = $this->sanitizeProductData($this->request->getData());
                $product = $this->Products->patchEntity($product, $data);

                if ($this->Products->save($product)) {
                    Log::info('Product updated: ' . $id);
                    $this->Flash->success(__('Product updated successfully.'));
                    return $this->redirect(['action' => 'index']);
                }

                $this->handleValidationErrors($product);
            }
        } catch (NotFoundException $e) {
            $this->Flash->error(__('Product not found.'));
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            Log::error('Edit error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('product'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        try {
            $product = $this->validateAndGetProduct($id);

            $product = $this->Products->patchEntity($product, [
                'deleted' => true,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            if ($this->Products->save($product)) {
                Log::info('Product soft deleted: ' . $id);
                $this->Flash->success(__('Product deleted successfully.'));
            } else {
                Log::error('Soft delete failed: ' . json_encode($product->getErrors()));
                $this->Flash->error(__('Unable to delete product.'));
            }
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    private function configurePagination(): void
    {
        $this->paginate = [
            'limit' => 10,
            'maxLimit' => 100,
            'order' => ['name' => 'asc'],
            'sortWhitelist' => ['name', 'price', 'quantity', 'status', 'created', 'modified']
        ];
    }

    private function sanitizeProductData(array $data): array
    {
        return [
            'name' => isset($data['name']) ? htmlspecialchars(trim($data['name']), ENT_QUOTES) : null,
            'quantity' => isset($data['quantity']) ? filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT) : null,
            'price' => isset($data['price']) ? filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null,
            'status' => isset($data['status']) ? htmlspecialchars(trim($data['status']), ENT_QUOTES) : null
        ];
    }

    private function validateAndGetProduct($id): object
    {
        if (!$id || !is_numeric($id)) {
            throw new BadRequestException(__('Invalid product ID'));
        }

        return $this->Products->get($id, [
            'conditions' => ['deleted IS NOT' => true]
        ]);
    }

    private function handleValidationErrors($product): void
    {
        Log::error('Validation errors: ' . json_encode($product->getErrors()));
        $this->Flash->error(__('Please correct the errors below.'));
    }
}
