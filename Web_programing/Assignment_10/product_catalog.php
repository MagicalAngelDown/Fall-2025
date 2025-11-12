<?php

  declare(strict_types=1);
  require_once __DIR__ . '/db.php';

  $pdo = get_pdo();

  // Fetch products
  $stmt = $pdo->query('SELECT id, name, price, description FROM products ORDER BY id ASC');
  $products = $stmt->fetchAll();

  function e(string $value): string {
      return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Product Catalog</title>
  <style>
    :root { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji", sans-serif; }
    body { margin: 2rem; background: #f7f7fb; color: #1e1e24; }
    h1 { margin-bottom: 1rem; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
    .card { background: white; border: 1px solid #e8e8ef; border-radius: 14px; padding: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .name { font-weight: 600; margin: 0 0 .5rem; }
    .price { font-variant-numeric: tabular-nums; margin: .5rem 0; }
    .muted { color: #5f6270; }
    .empty { padding: 1rem; background: #fff8db; border: 1px solid #ffe58f; border-radius: 12px; }
    code.inline { background: #f0f1f5; padding: .15rem .35rem; border-radius: 8px; }
  </style>
</head>

<body>
  <h1>Product Catalog</h1>

  <?php if (!$products): ?>
    <div class="empty">
      No products found. Try importing <code class="inline">products.sql</code> into your MySQL database.
    </div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($products as $p): ?>
        <article class="card">
          <h2 class="name"><?= e($p['name']) ?></h2>
          <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
          <?php if (!empty($p['description'])): ?>
            <p class="muted"><?= e($p['description']) ?></p>
          <?php endif; ?>
          <small class="muted">#<?= (int)$p['id'] ?></small>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</body>
</html>
