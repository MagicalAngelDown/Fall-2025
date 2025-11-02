<?php
// Load and decode products
$jsonPath = __DIR__ . '/products.json';
$raw = @file_get_contents($jsonPath);
if ($raw === false) {
    http_response_code(500);
    $error = "Could not read products.json. Make sure the file exists and is readable.";
    $products = [];
} else {
    $products = json_decode($raw, true);
    if (!is_array($products)) {
        http_response_code(500);
        $error = "Invalid JSON in products.json.";
        $products = [];
    }
}
function fmt_price($n) {
    return '$' . number_format((float)$n, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product Catalog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="styles.css" rel="stylesheet">
</head>
<body>
  <header class="container">
    <h1>Product Catalog</h1>
    <p class="subhead">Browse our products generated dynamically with PHP.</p>
  </header>

  <main class="container">
    <?php if (!empty($error)): ?>
      <div class="alert" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (count($products) === 0 && empty($error)): ?>
      <p>No products available.</p>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($products as $i => $p): 
          $name = htmlspecialchars($p['name'] ?? 'Unnamed');
          $price = isset($p['price']) ? fmt_price($p['price']) : 'N/A';
          $desc = htmlspecialchars($p['description'] ?? '');
          // Link uses array index as the id parameter "i"
          $href = 'product_detail.php?i=' . urlencode((string)$i);
        ?>
          <article class="card">
            <h2 class="card-title">
              <a href="<?php echo $href; ?>"><?php echo $name; ?></a>
            </h2>
            <p class="price"><?php echo $price; ?></p>
            <p class="desc"><?php echo $desc; ?></p>
            <p><a class="btn" href="<?php echo $href; ?>">View details</a></p>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>

  <footer class="container footer">
    <small>Rendered with PHP from <code>products.json</code>.</small>
  </footer>
</body>
</html>
