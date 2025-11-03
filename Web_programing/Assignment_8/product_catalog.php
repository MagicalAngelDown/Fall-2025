<?php
function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

$PRODUCTS_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'products.json';
$products = [];
$error = '';

if (file_exists($PRODUCTS_FILE)) {
    $json = file_get_contents($PRODUCTS_FILE);
    if ($json === false) {
        $error = "Unable to read products.json.";
    } else {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $error = "products.json is not valid JSON.";
        } else {
            $products = $data;
        }
    }
} else {
    $products = [];
}
?>

<!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Product Catalog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .banner { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .error { background: #ffefef; border: 1px solid #e1b3b3; color: #8a0000; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; }
        .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:1rem; }
        .card { border:1px solid #e5e7eb; border-radius: 12px; padding: 1rem; }
        .name { font-weight:700; font-size:1.1rem; margin:0 0 .25rem 0; }
        .price { font-weight:600; }
        .muted { color:#6b7280; font-size:.9rem; }
        .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
        .btn { background: #111827; color:#fff; padding:.6rem .9rem; border-radius:8px; text-decoration:none; display:inline-block; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="top-actions">
        <h1>Product Catalog</h1>
        <a class="btn" href="add_product.php">Add a Product</a>
      </div>

      <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="banner">Product added successfully.</div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="error"><?= h($error) ?></div>
      <?php endif; ?>

      <?php if (!$error && count($products) === 0): ?>
        <p class="muted">No products yet. <a href="add_product.php">Add your first product</a>.</p>
      <?php endif; ?>

      <?php if (!$error && count($products) > 0): ?>
        <div class="grid">
          <?php foreach ($products as $p): ?>
            <div class="card">
              <div class="name"><?= h($p['name'] ?? 'Untitled') ?></div>
              <div class="price">$<?= number_format((float)($p['price'] ?? 0), 2) ?></div>
              <?php if (!empty($p['description'])): ?>
                <p><?= nl2br(h($p['description'])) ?></p>
              <?php endif; ?>
              <?php if (!empty($p['created_at'])): ?>
                <div class="muted">Added: <?= h(date('M j, Y g:ia', strtotime($p['created_at']))) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </body>
</html>