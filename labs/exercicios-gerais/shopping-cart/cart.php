<?php

require_once __DIR__ . '/ShoppingCart.php';

session_start();

$cart = $_SESSION['cart'] ?? null;

if (!$cart instanceof ShoppingCart) {
  $cart = new ShoppingCart();
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$price = (float)($_POST['price'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);
$coupon = $_POST['coupon'] ?? '';
$message = '';

switch ($action) {
  case 'add':
    $cart->addItem($id, $name, $price, $quantity);
    break;
  case 'increment':
    $cart->incrementQuantity($id, (int)($_POST['amount'] ?? 1));
    break;
  case 'decrement':
    $cart->decrementQuantity($id, (int)($_POST['amount'] ?? 1));
    break;
  case 'remove':
    $cart->removeItem($id);
    break;
  case 'apply_coupon':
    if ($cart->applyCoupon($coupon)) {
      $message = "Cupom $coupon aplicado!";
    } else {
      $message = "Cupom inválido.";
    }
    break;
  case 'remove_coupon':
    $cart->removeCoupon();
    break;
  default:
    break;
}

$_SESSION['cart'] = $cart;
?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 600px;
            margin: 40px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .totals {
            text-align: right;
        }

        .coupon {
            margin: 10px 0;
        }

        label {
            display: block;
            margin: 4px 0;
        }
    </style>
  </head>
  <body>

    <h1>Carrinho</h1>

    <?php if ($message): ?>
      <p><em><?= htmlspecialchars($message) ?></em></p>
    <?php endif; ?>

    <h2>Adicionar produto</h2>
    <form method="post">
      <input type="hidden" name="action" value="add">

      <label for="product-id">ID</label>
      <input type="text" name="id" id="product-id" placeholder="ID" required>

      <label for="product-name">Nome</label>
      <input type="text" name="name" id="product-name" placeholder="Nome" required>

      <label for="product-price">Preço</label>
      <input type="number" name="price" id="product-price" placeholder="Preço" step="0.01" required>

      <label for="product-quantity">Quantidade</label>
      <input type="number" name="quantity" id="product-quantity" value="1" min="1">

      <button type="submit">Adicionar</button>
    </form>

    <h2>Itens</h2>
    <?php if (empty($cart->getItems())): ?>
      <p>Carrinho vazio.</p>
    <?php else: ?>
      <table>
        <tr>
          <th>Produto</th>
          <th>Preço</th>
          <th>Qtd</th>
          <th>Total</th>
          <th></th>
        </tr>
        <?php foreach ($cart->getItems() as $itemId => $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>R$ <?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></td>
            <td>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="increment">
                <input type="hidden" name="id" value="<?= htmlspecialchars($itemId) ?>">
                <button type="submit">+</button>
              </form>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="decrement">
                <input type="hidden" name="id" value="<?= htmlspecialchars($itemId) ?>">
                <button type="submit">-</button>
              </form>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="id" value="<?= htmlspecialchars($itemId) ?>">
                <button type="submit">Remover</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

      <div class="totals">
        <p>Subtotal: R$ <?= number_format($cart->getSubtotal(), 2, ',', '.') ?></p>
        <?php if ($cart->getDiscount() > 0): ?>
          <p>Desconto: -R$ <?= number_format($cart->getDiscount(), 2, ',', '.') ?></p>
        <?php endif; ?>
        <?php if ($cart->getShipping() > 0): ?>
          <p>Frete: R$ <?= number_format($cart->getShipping(), 2, ',', '.') ?></p>
        <?php else: ?>
          <p>Frete: Grátis</p>
        <?php endif; ?>
        <p><strong>Total: R$ <?= number_format($cart->getTotal(), 2, ',', '.') ?></strong></p>
      </div>

      <div class="coupon">
        <?php if ($cart->getCoupon()): ?>
          <div class="coupon-active">Cupom ativo: <strong><?= htmlspecialchars($cart->getCoupon()) ?></strong>
            <form method="post" style="display:inline">
              <input type="hidden" name="action" value="remove_coupon">
              <button type="submit">Remover cupom</button>
            </form>
          </div>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="action" value="apply_coupon">
            <label for="coupon-code">Cupom</label>
            <input type="text" name="coupon" id="coupon-code" placeholder="Código do cupom">
            <button type="submit">Aplicar</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </body>
</html>
