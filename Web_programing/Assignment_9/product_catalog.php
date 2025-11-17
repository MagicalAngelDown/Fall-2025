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
<html>
    <head>
        <title>Catalog</title>
        <link rel="stylesheet" href="assets/styles.css">
    </head>

    <body>
        <div class="container">
        <h1>Products</h1>
        <a href="cart.php">View Cart →</a><br><br>

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
        </div>
    </body>
</html>