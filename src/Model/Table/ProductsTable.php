<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use InvalidArgumentException;

/**
 * Products Table
 *
 * @property string $name Product name (3-50 chars)
 * @property int $quantity Current stock quantity (0-1000)
 * @property float $price Product price (0-10000)
 * @property string $status Stock status (in_stock|low_stock|out_of_stock)
 * @property \Datetime $created Creation timestamp
 * @property \Datetime $modified Last modification timestamp
 * @property \Datetime $last_updated Last status update timestamp
 * @property bool $deleted Soft delete flag
 */
class ProductsTable extends Table
{
    private const STOCK_STATUS = [
        'IN_STOCK' => ['value' => 'in_stock', 'threshold' => 10],
        'LOW_STOCK' => ['value' => 'low_stock', 'threshold' => 1],
        'OUT_OF_STOCK' => ['value' => 'out_of_stock', 'threshold' => 0]
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');

        // Add optimized timestamp behavior
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                    'last_updated' => [
                        'when' => 'always',
                        'on' => ['price', 'quantity', 'status']
                    ]
                ]
            ]
        ]);

        // Add soft delete behavior
        $this->addBehavior('SoftDelete', [
            'field' => 'deleted',
            'dateTime' => 'deleted_at'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->minLength('name', 3)
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'This product name already exists'
            ])
            ->add('name', 'sanitize', [
                'rule' => function ($value) {
                    return strip_tags($value) === $value;
                },
                'message' => 'Name cannot contain HTML tags'
            ])
            ->add('name', 'noSpecialChars', [
                'rule' => function ($value) {
                    return !preg_match('/[<>{}()\/\\\\]/', $value);
                },
                'message' => 'Name contains invalid characters'
            ]);

        $validator
            ->integer('quantity')
            ->range('quantity', [0, 1000])
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity')
            ->greaterThanOrEqual('quantity', 0);

        $validator
            ->decimal('price', 2)
            ->greaterThan('price', 0)
            ->lessThanOrEqual('price', 10000)
            ->requirePresence('price', 'create')
            ->notEmptyString('price')
            ->add('price', 'format', [
                'rule' => function ($value) {
                    return is_numeric($value) && preg_match('/^\d+(\.\d{2})?$/', (string)$value);
                },
                'message' => 'Price must have exactly 2 decimal places'
            ]);

        $validator
            ->scalar('status')
            ->inList('status', array_column(self::STOCK_STATUS, 'value'))
            ->notEmptyString('status');

        $validator
            ->boolean('deleted')
            ->notEmptyString('deleted');

        $validator
            ->dateTime('last_updated')
            ->allowEmptyDateTime('last_updated');

        return $validator;
    }

    public function beforeSave(EventInterface $event, $entity, $options)
    {
        try {
            if (!is_numeric($entity->quantity)) {
                throw new InvalidArgumentException('Invalid quantity value');
            }

            // Calculate status based on quantity
            $entity->status = $this->calculateProductStatus((int)$entity->quantity);

            // Validate price-quantity relationship for expensive products
            if ($entity->price > 100 && $entity->quantity < 10) {
                $entity->setError('quantity', 'Products over $100 must have at least 10 items');
                return false;
            }

            // Validate promotional products pricing
            if (stripos($entity->name, 'promo') !== false && $entity->price >= 50) {
                $entity->setError('price', 'Promotional products must be under $50');
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Product save error: ' . $e->getMessage());
            return false;
        }
    }

    private function calculateProductStatus(int $quantity): string
    {
        return match (true) {
            $quantity > self::STOCK_STATUS['IN_STOCK']['threshold'] => self::STOCK_STATUS['IN_STOCK']['value'],
            $quantity >= self::STOCK_STATUS['LOW_STOCK']['threshold'] => self::STOCK_STATUS['LOW_STOCK']['value'],
            default => self::STOCK_STATUS['OUT_OF_STOCK']['value'],
        };
    }
}
