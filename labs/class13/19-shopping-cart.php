<?php
// carrinho.php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Products de exemplo
$products = [
    1 => ['name' => 'Camiseta PHP',         'price' => 59.90],
    2 => ['name' => 'Caneca Programador',   'price' => 39.90],
    3 => ['name' => 'Adesivo Elefante PHP', 'price' =>  9.90],
    4 => ['name' => 'Livro PHP Moderno',    'price' => 129.90],
];

// Ação: adicionar
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if (isset($products[$id])) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name'  => $products[$id]['name'],
                'price' => $products[$id]['price'],
                'qty'   => 1,
            ];
        }
        $_SESSION['flash'] = "{$products[$id]['name']} adicionado ao carrinho!";
    }
}

// Ação: remover
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

// Ação: limpar carrinho
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

// Calcular total
$total = 0;
$counter = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
    $counter += $item['qty'];
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Carrinho de Compras</title>
<style>
    body { font-family: sans-serif; max-width: 700px; margin: 30px auto; padding: 0 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    .flash { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; }
    .btn { display: inline-block; padding: 6px 12px; text-decoration: none; border-radius: 4px;
           color: white; font-size: 0.85rem; }
    .btn-add { background: #2563eb; }
    .btn-remove { background: #dc2626; }
    .btn-clear { background: #6b7280; }
    .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .produto { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
    .total { font-size: 1.2rem; font-weight: bold; text-align: right; }
</style></head>
<body>
    <h1>Carrinho (<?= $counter ?> itens)</h1>

    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <h2>Products</h2>
    <div class="products">
        <?php foreach ($products as $id => $p): ?>
            <div class="produto">
                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                R$ <?= number_format($p['price'], 2, ',', '.') ?><br>
                <a href="?add=<?= $id ?>" class="btn btn-add">Adicionar</a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
        <h2>Seu Carrinho</h2>
        <table>
            <tr><th>Product</th><th>Preço</th><th>Qtd</th><th>Subtotal</th><th></th></tr>
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                <td><?= $item['qty'] ?></td>
                <td>R$ <?= number_format($item['price'] * $item['qty'], 2, ',', '.') ?></td>
                <td><a href="?remove=<?= $id ?>" class="btn btn-remove">X</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: R$ <?= number_format($total, 2, ',', '.') ?></p>
        <a href="?clear=1" class="btn btn-clear">Limpar Carrinho</a>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>
</body>
</html>
