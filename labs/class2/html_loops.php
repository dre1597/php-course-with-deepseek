<?php
$products = [
    [
        'name' => 'Product 1',
        'price' => 10.99,
    ],
    [
        'name' => 'Product 2',
        'price' => 19.99,
    ],
];
?>

<ul>
    <?php foreach ($products as $product): ?>
        <li>
            <strong><?= htmlspecialchars($product['name']) ?></strong>
            — R$ <?= number_format($product['price'], 2, ',', '.') ?>
        </li>
    <?php endforeach; ?>
</ul>
