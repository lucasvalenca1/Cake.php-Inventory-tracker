<?php

declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsTable;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;

class ProductsTableTest extends TestCase
{
    protected $Products;
    protected $fixtures = ['app.Products'];

    public function setUp(): void
    {
        parent::setUp();
        $this->Products = TableRegistry::getTableLocator()->get('Products');
    }

    public function testValidationRules(): void
    {
        // Test valid product
        $product = $this->Products->newEntity([
            'name' => 'Test Product',
            'quantity' => 15,
            'price' => 99.99
        ]);
        $this->assertEmpty($product->getErrors());

        // Test name validation
        $product = $this->Products->newEntity([
            'name' => 'ab',
            'quantity' => 15,
            'price' => 99.99
        ]);
        $this->assertNotEmpty($product->getErrors()['name']);

        // Test quantity validation
        $product = $this->Products->newEntity([
            'name' => 'Test Product',
            'quantity' => 1001,
            'price' => 99.99
        ]);
        $this->assertNotEmpty($product->getErrors()['quantity']);

        // Test price validation
        $product = $this->Products->newEntity([
            'name' => 'Test Product',
            'quantity' => 15,
            'price' => 10001
        ]);
        $this->assertNotEmpty($product->getErrors()['price']);
    }

    public function testStatusCalculation(): void
    {
        // Test in_stock status
        $product = $this->Products->newEntity([
            'name' => 'In Stock Product',
            'quantity' => 11,
            'price' => 99.99
        ]);
        $this->Products->save($product);
        $this->assertEquals('in_stock', $product->status);

        // Test low_stock status
        $product = $this->Products->newEntity([
            'name' => 'Low Stock Product',
            'quantity' => 5,
            'price' => 99.99
        ]);
        $this->Products->save($product);
        $this->assertEquals('low_stock', $product->status);

        // Test out_of_stock status
        $product = $this->Products->newEntity([
            'name' => 'Out of Stock Product',
            'quantity' => 0,
            'price' => 99.99
        ]);
        $this->Products->save($product);
        $this->assertEquals('out_of_stock', $product->status);
    }

    public function testCustomValidationRules(): void
    {
        // Test expensive product quantity rule
        $product = $this->Products->newEntity([
            'name' => 'Expensive Product',
            'quantity' => 5,
            'price' => 150.00
        ]);
        $this->Products->save($product);
        $this->assertNotEmpty($product->getErrors());

        // Test promo price rule
        $product = $this->Products->newEntity([
            'name' => 'Promo Product',
            'quantity' => 15,
            'price' => 75.00
        ]);
        $this->Products->save($product);
        $this->assertNotEmpty($product->getErrors());
    }

    public function testVirtualFields(): void
    {
        $product = $this->Products->newEntity([
            'name' => 'Virtual Test',
            'quantity' => 15,
            'price' => 99.99
        ]);
        $this->Products->save($product);

        $this->assertEquals('In Stock', $product->status_label);
        $this->assertEquals('$99.99', $product->formatted_price);
    }

    public function tearDown(): void
    {
        unset($this->Products);
        parent::tearDown();
    }
}
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
    }

    public function index()
    {
        try {
            $query = $this->Products->find()
                ->where(['deleted IS NOT' => true]);

            if ($search = $this->getValidatedSearch()) {
                $query->where(function (QueryExpression $exp) use ($search) {
                    return $exp->like('name', '%' . $this->escapeLikeString($search) . '%');
                });
            }

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
                    $this->Flash->success(__('Product updated successfully.'));
                    return $this->redirect(['action' => 'index']);
                }

                $this->handleValidationErrors($product);
            }

        } catch (BadRequestException $e) {
            $this->Flash->error(__('Invalid product ID.'));
            return $this->redirect(['action' => 'index']);
        } catch (\Exception $e) {
            Log::error('Edit error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('product'));
    }

    private function configurePagination(): void
    {
        $this->paginate = [
            'limit' => 10,
            'maxLimit' => 100,
            'order' => ['name' => 'asc'],
            'sortWhitelist' => ['name', 'price', 'created', 'modified']
        ];
    }

    private function getValidatedSearch(): ?string
    {
        $search = trim($this->request->getQuery('search', ''));
        return !empty($search) ? htmlspecialchars($search) : null;
    }


    private function getValidatedStatus(): ?string
    {
        $status = $this->request->getQuery('status');
        return in_array($status, self::VALID_STATUSES, true) ? $status : null;
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
