<?php
session_start();

// CSRF token
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token");
    }

    $cart = read_cart($cartFile);
    $name = trim($_POST['name']);
    $action = $_POST['action'];

    if ($action === "update") {
        $qty = (int)$_POST['quantity'];

        if ($qty <= 0) {
            $cart = array_filter($cart, fn($i) => $i['name'] !== $name);
        } else {
            foreach ($cart as &$item) {
                if ($item['name'] === $name) {
                    $item['quantity'] = $qty;
                    break;
                }
            }
        }

        write_cart($cartFile, $cart);
        header("Location: cart.php?updated=1");
        exit;
    }

    if ($action === "delete") {
        $cart = array_filter($cart, fn($i) => $i['name'] !== $name);
        write_cart($cartFile, $cart);
        header("Location: cart.php?deleted=1");
        exit;
    }
}

// --- Display Cart ---
$cart = read_cart($cartFile);
$totalItems = 0;
$totalCost = 0;
foreach ($cart as $item) {
    $totalItems += $item['quantity'];
    $totalCost += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
    <html>
    <head>
        <title>Shopping Cart</title>
        <link rel="stylesheet" href="assets/styles.css">
    </head>
    <body>
        <div class="container">
            <h1>Shopping Cart</h1>
            <a href="product_catalog.php">← Back to Catalog</a><br><br>

            <?php if (isset($_GET['updated'])) echo "<div class='notice'>Cart Updated</div>"; ?>
            <?php if (isset($_GET['deleted'])) echo "<div class='notice warning'>Item Deleted</div>"; ?>

            <?php if (!$cart): ?>
            <p>Your cart is empty.</p>
            <?php else: ?>
            <table>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th></tr>

                <?php foreach ($cart as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <form method="post">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0">
                            <input type="hidden" name="name" value="<?= $item['name'] ?>">
                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="update">
                            <button>Update</button>
                        </form>
                    </td>
                    <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Remove item?');">
                                <input type="hidden" name="name" value="<?= $item['name'] ?>">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="delete">Delete</button>
                            </form>
                        </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <p><b>Total Items:</b> <?= $totalItems ?> | 
            <b>Total Cost:</b> $<?= number_format($totalCost, 2) ?></p>
            <?php endif; ?>
        </div>
    </body>
</html>