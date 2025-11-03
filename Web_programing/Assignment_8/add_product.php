<?php
function h($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

function read_products($path) {
    if (!file_exists($path)) {
        return []; // Start fresh if it doesn't exist yet
    }
    $json = file_get_contents($path);
    if ($json === false) {
        throw new RuntimeException("Unable to read products.json");
    }
    $data = json_decode($json, true);
    if (!is_array($data)) {
        // If file is corrupted, fail loudly so the student can see the error
        throw new RuntimeException("products.json is not valid JSON");
    }
    return $data;
}

function write_products($path, array $products) {
    // JSON_PRETTY_PRINT for readability
    $encoded = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        throw new RuntimeException("Failed to encode products to JSON");
    }

    // Use a temporary file + rename for atomic write (safer), plus an exclusive lock
    $tmpPath = $path . '.tmp';
    $fp = @fopen($tmpPath, 'wb');
    if (!$fp) {
        throw new RuntimeException("Unable to open temp file for writing: $tmpPath");
    }
    try {
        if (!flock($fp, LOCK_EX)) {
            throw new RuntimeException("Unable to obtain file lock for writing");
        }
        if (fwrite($fp, $encoded) === false) {
            throw new RuntimeException("Failed writing JSON to temp file");
        }
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }

    // Now atomically replace the original
    if (!@rename($tmpPath, $path)) {
        @unlink($tmpPath);
        throw new RuntimeException("Failed to replace products.json with new data");
    }
}

// --------- state ----------
$errors = ['name' => '', 'price' => ''];
$values = ['name' => '', 'price' => '', 'description' => ''];
$generalError = '';
$PRODUCTS_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'products.json';

// --------- handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather + trim
    $values['name'] = trim($_POST['name'] ?? '');
    $values['price'] = trim($_POST['price'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');

    // Validate name
    if ($values['name'] === '') {
        $errors['name'] = 'Product name is required.';
    }

    // Validate price (must be numeric and > 0)
    if ($values['price'] === '') {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($values['price'])) {
        $errors['price'] = 'Price must be a number.';
    } elseif ((float)$values['price'] <= 0) {
        $errors['price'] = 'Price must be greater than 0.';
    }

    // If no errors, append and redirect
    if ($errors['name'] === '' && $errors['price'] === '') {
        try {
            $products = read_products($PRODUCTS_FILE);

            // Make a simple ID (timestamp + random)
            $newProduct = [
                'id' => uniqid('prod_', true),
                'name' => $values['name'],
                'price' => round((float)$values['price'], 2),
                'description' => $values['description'],
                'created_at' => date('c'),
            ];

            $products[] = $newProduct;
            write_products($PRODUCTS_FILE, $products);

            // Redirect with a success flag (Post/Redirect/Get)
            header('Location: product_catalog.php?added=1');
            exit;
        } catch (Throwable $e) {
            $generalError = $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; }
      .container { max-width: 640px; margin: 0 auto; }
      .card { border: 1px solid #ddd; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
      label { display:block; font-weight:600; margin-top: 1rem; }
      input[type="text"], input[type="number"], textarea {
          width: 100%; padding: .65rem .75rem; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem;
      }
      textarea { min-height: 120px; }
      .error { color: #b00020; font-size: .95rem; margin-top: .25rem; }
      .general-error { background: #ffefef; border: 1px solid #e1b3b3; color: #8a0000; padding: .75rem; border-radius: 8px; margin-bottom: 1rem; }
      .actions { margin-top: 1.25rem; display:flex; gap:.75rem; align-items:center; }
      .btn { background: #111827; color: #fff; padding: .7rem 1rem; border-radius: 8px; border: none; cursor: pointer; font-weight:600; }
      .btn.secondary { background: #f3f4f6; color: #111827; }
      .hint { color:#6b7280; font-size:.9rem; }
  </style>
</head>
<body>
<div class="container">
  <h1>Add a Product</h1>
  <div class="card">
    <?php if ($generalError): ?>
      <div class="general-error">Error: <?= h($generalError) ?></div>
    <?php endif; ?>
    <form method="post" action="add_product.php" novalidate>
      <label for="name">Product Name *</label>
      <input type="text" id="name" name="name" value="<?= h($values['name']) ?>" required>
      <?php if ($errors['name']): ?><div class="error"><?= h($errors['name']) ?></div><?php endif; ?>

      <label for="price">Price (e.g., 19.99) *</label>
      <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?= h($values['price']) ?>" required>
      <?php if ($errors['price']): ?><div class="error"><?= h($errors['price']) ?></div><?php endif; ?>

      <label for="description">Description <span class="hint">(optional)</span></label>
      <textarea id="description" name="description"><?= h($values['description']) ?></textarea>

      <div class="actions">
        <button type="submit" class="btn">Add Product</button>
        <a class="btn secondary" href="product_catalog.php">View Catalog</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>