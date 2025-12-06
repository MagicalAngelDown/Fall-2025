<?php
    session_start();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    $csrf = $_SESSION['csrf'];


    function read_cart($file) {
        if (!file_exists($file)) return [];
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    function write_cart($file, $cart) {
        file_put_contents($file, json_encode(array_values($cart), JSON_PRETTY_PRINT));
    }

    $cartFile = __DIR__ . "/cart.json";


    $products = [
        ["name" => "product1", "price" => 3.00, "description" => "Tasty item!"],
        ["name" => "product2", "price" => 1.00, "description" => "Small gadget"],
        ["name" => "product3", "price" => 5.50, "description" => "Premium goodie"]
    ];


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cart = read_cart($cartFile);
        $name = $_POST['name'];
        $qty = max(1, (int)$_POST['quantity']);

        foreach ($products as $p) {
            if ($p['name'] === $name) {
                $price = $p['price'];
                break;
            }
        }

        $found = false;
        foreach ($cart as &$item) {
            if ($item['name'] === $name) {
                $item['quantity'] += $qty;
                $found = true;
            }
        }
        if (!$found) {
            $cart[] = ["name" => $name, "price" => $price, "quantity" => $qty];
        }

        write_cart($cartFile, $cart);
        header("Location: cart.php?added=1");
        exit;
    }

?>


<!DOCTYPE html>
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
        <div class="container">
            <h1>Product Catalog</h1>
            <a href="cart.php">View Cart →</a><br><br>
            <?php if (!$products): ?>
                <div class="empty">
                    No products found. Try importing <code class="inline">products.sql</code> into your MySQL database.
                </div>
            <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="card">
                    <h3><?= $p['name'] ?></h3>
                    <p><?= $p['description'] ?></p>
                    <p><b>$<?= number_format($p['price'],2) ?></b></p>

                    <form method="post">
                        <input type="number" name="quantity" value="1" min="1">
                        <input type="hidden" name="name" value="<?= $p['name'] ?>">
                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                        <button>Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </body>
</html>