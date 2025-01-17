<?php

declare(strict_types=1);
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product[] $products
 */
?>
<div class="products index content">
    <h3><?= __('Product Inventory') ?></h3>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline']) ?>
        <div class="col-md-4">
            <?= $this->Form->control('search', [
                'label' => false,
                'placeholder' => __('Search products...'),
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $this->Form->select('status', [
                '' => __('All Status'),
                'in_stock' => __('In Stock'),
                'low_stock' => __('Low Stock'),
                'out_of_stock' => __('Out of Stock')
            ], ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-2">
            <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>

    <!-- Flash Messages -->
    <?= $this->Flash->render() ?>

    <!-- Products Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('name', __('Product Name')) ?></th>
                    <th><?= $this->Paginator->sort('quantity', __('Quantity')) ?></th>
                    <th><?= $this->Paginator->sort('price', __('Price')) ?></th>
                    <th><?= $this->Paginator->sort('status', __('Status')) ?></th>
                    <th><?= $this->Paginator->sort('last_updated', __('Last Updated')) ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= h($product->name) ?></td>
                            <td><?= $this->Number->format($product->quantity) ?></td>
                            <td><?= $this->Number->currency($product->price, 'USD') ?></td>
                            <td>
                                <span class="badge bg-<?= $this->Product->getStatusBadgeClass($product->status) ?>">
                                    <?= h($product->status_label) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($product->last_updated): ?>
                                    <?= $product->last_updated->format('Y-m-d H:i:s') ?>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __('View'),
                                    ['action' => 'view', $product->id],
                                    ['class' => 'btn btn-sm btn-info']
                                ) ?>
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['action' => 'edit', $product->id],
                                    ['class' => 'btn btn-sm btn-warning']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['action' => 'delete', $product->id],
                                    [
                                        'confirm' => __('Are you sure you want to delete {0}?', $product->name),
                                        'class' => 'btn btn-sm btn-danger'
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center"><?= __('No products found') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('First')) ?>
            <?= $this->Paginator->prev('< ' . __('Previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('Next') . ' >') ?>
            <?= $this->Paginator->last(__('Last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>