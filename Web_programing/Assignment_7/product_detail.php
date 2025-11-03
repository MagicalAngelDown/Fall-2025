<?php
// Read index from query string (?i=0,1,2,...)
$index = isset($_GET['i']) ? $_GET['i'] : null;
if ($index === null || !ctype_digit((string)$index)) {
    http_response_code(400);
    $detailError = "Missing or invalid product index.";
}

$jsonPath = __DIR__ . '/products.json';
$raw = @file_get_contents($jsonPath);
if ($raw === false) {
    http_response_code(500);
    $detailError = "Could not read products.json.";
    $product = null;
} else {
    $products = json_decode($raw, true);
    if (!is_array($products)) {
        http_response_code(500);
        $detailError = "Invalid JSON in products.json.";
        $product = null;
    } else {
        $idx = (int)$index;
        if (!isset($products[$idx])) {
            http_response_code(404);
            $detailError = "Product not found.";
            $product = null;
        } else {
            $product = $products[$idx];
        }
    }
}

function fmt_price($n) {
    return '$' . number_format((float)$n, 2);
}
$name = $product ? htmlspecialchars($product['name'] ?? 'Unnamed') : 'Error';
$price = $product && isset($product['price']) ? fmt_price($product['price']) : 'N/A';
$desc = $product ? htmlspecialchars($product['description'] ?? '') : '';
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo $product ? $name : 'Product Error'; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="styles.css" rel="stylesheet">
</head>
<body>
  <header class="container">
    <a class="back" href="product_catalog.php">← Back to Catalog</a>
    <h1>Product Details</h1>
  </header>

  <main class="container">
    <?php if (!empty($detailError)): ?>
      <div class="alert" role="alert"><?php echo htmlspecialchars($detailError); ?></div>
    <?php else: ?>
      <article class="detail">
        <h2 class="detail-title"><?php echo $name; ?></h2>
        <p class="detail-price"><?php echo $price; ?></p>
        <p class="detail-desc"><?php echo $desc; ?></p>
      </article>
    <?php endif; ?>
  </main>

  <footer class="container footer">
    <small>Rendered with PHP from <code>products.json</code>.</small>
  </footer>
</body>
</html>
