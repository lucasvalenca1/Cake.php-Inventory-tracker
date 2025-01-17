<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsTable;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Products Table Test Case
 */
class ProductsTableTest extends TestCase
{
    protected $Products;
    protected $fixtures = ['app.Products'];

    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Products') ? 
            [] : ['className' => ProductsTable::class];
        $this->Products = $this->getTableLocator()->get('Products', $config);
    }

    public function testValidationRules(): void
    {
        // Test valid product
        $product = $this->Products->newEntity([
            'name' => 'Test Product',
            'quantity' => 15,
            'price' => 99.99,
            'status' => 'in_stock'
        ]);
        $this->assertEmpty($product->getErrors());

        // Test name validation
        $product = $this->Products->newEntity([
            'name' => 'ab', // Too short
            'quantity' => 15,
            'price' => 99.99
        ]);
        $this->assertNotEmpty($product->getErrors()['name']);

        // Test quantity validation
        $product = $this->Products->newEntity([
            'name' => 'Test Product',
            'quantity' => 1001, // Exceeds maximum
            'price' => 99.99
        ]);
        $this->assertNotEmpty($product->getErrors()['quantity']);
    }

    public function testStatusCalculation(): void
    {
        $testCases = [
            ['quantity' => 11, 'expected' => 'in_stock'],
            ['quantity' => 5, 'expected' => 'low_stock'],
            ['quantity' => 0, 'expected' => 'out_of_stock']
        ];

        foreach ($testCases as $case) {
            $product = $this->Products->newEntity([
                'name' => 'Status Test Product',
                'quantity' => $case['quantity'],
                'price' => 99.99
            ]);
            $this->Products->save($product);
            $this->assertEquals($case['expected'], $product->status);
        }
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

    public function tearDown(): void
    {
        unset($this->Products);
        parent::tearDown();
        TableRegistry::clear();
    }
}
