<?php

declare(strict_types=1);
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<div class="products view content">
    <h3><?= __('Product Details') ?></h3>

    <?= $this->Flash->render() ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-hover" aria-label="Product Details">
                        <tr>
                            <th scope="row"><?= __('Name') ?></th>
                            <td><?= h($product->name) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?= __('Quantity') ?></th>
                            <td><?= $this->Number->format($product->quantity ?? 0) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?= __('Price') ?></th>
                            <td><?= $this->Number->currency($product->price ?? 0, 'USD') ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?= __('Status') ?></th>
                            <td>
                                <span class="badge bg-<?= $this->Product->getStatusBadgeClass($product->status) ?>">
                                    <?= h($product->status_label ?? 'Unknown') ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?= __('Last Updated') ?></th>
                            <td>
                                <?php if ($product->last_updated): ?>
                                    <time datetime="<?= $product->last_updated->format('Y-m-d\TH:i:s\Z') ?>">
                                        <?= $product->last_updated->format('Y-m-d H:i:s') ?>
                                    </time>
                                <?php else: ?>
                                    <?= __('Never') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="btn-group" role="group" aria-label="<?= __('Product Actions') ?>">
                        <?= $this->Html->link(
                            __('Edit Product'),
                            ['action' => 'edit', $product->id],
                            ['class' => 'btn btn-warning me-2']
                        ) ?>
                        <?= $this->Form->postLink(
                            __('Delete Product'),
                            ['action' => 'delete', $product->id],
                            [
                                'confirm' => __('Are you sure you want to delete {0}?', h($product->name)),
                                'class' => 'btn btn-danger',
                                'escapeTitle' => false
                            ]
                        ) ?>
                        <?= $this->Html->link(
                            __('Back to List'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-secondary ms-2']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>