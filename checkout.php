<?php
// checkout.php - 3-Step Checkout Wizard (user-aware, DB-backed)
session_start();
require_once 'config/database.php';

// ── Auth guard ──
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$step   = (int)($_GET['step'] ?? 1);

// ── Fetch user profile ──
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = :uid LIMIT 1");
$userStmt->execute(['uid' => $userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// ── Fetch live cart from DB (authoritative source for checkout) ──
$cartStmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity,
           p.name, p.price, p.category, p.stock,
           pi.image_path
    FROM cart_items ci
    JOIN products p  ON p.id = ci.product_id AND p.status = 1
    LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.sort_order = 0
    WHERE ci.user_id = :uid
    ORDER BY ci.updated_at DESC
");
$cartStmt->execute(['uid' => $userId]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Redirect to cart if empty
if (empty($cartItems) && $step < 3) {
    header('Location: cart.php');
    exit;
}

// ── Recalculate totals ──
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$itemCount = array_sum(array_column($cartItems, 'quantity'));

// ── Step 1 POST: save shipping info to session and advance ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {
    $_SESSION['checkout'] = [
        'name'            => trim($_POST['name'] ?? ''),
        'email'           => trim($_POST['email'] ?? ''),
        'phone'           => trim($_POST['phone'] ?? ''),
        'address_line1'   => trim($_POST['address_line1'] ?? ''),
        'address_line2'   => trim($_POST['address_line2'] ?? ''),
        'city'            => trim($_POST['city'] ?? ''),
        'country'         => trim($_POST['country'] ?? 'Sri Lanka'),
        'postal_code'     => trim($_POST['postal_code'] ?? ''),
        'shipping_method' => $_POST['shipping_method'] ?? 'standard',
    ];
    header('Location: checkout.php?step=2');
    exit;
}

// ── Step 2 POST: place order ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $co = $_SESSION['checkout'] ?? [];

    $shippingCost = match($co['shipping_method'] ?? 'standard') {
        'express' => ($subtotal > 5000 ? 500 : 1000),
        default   => ($subtotal > 5000 ? 0 : 500),
    };
    $total = $subtotal + $shippingCost;

    // Insert order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders
          (user_id, status, subtotal, shipping_cost, total,
           name, email, phone,
           address_line1, address_line2, city, country, postal_code,
           shipping_method, created_at)
        VALUES
          (:uid, 'pending', :subtotal, :shipping, :total,
           :name, :email, :phone,
           :addr1, :addr2, :city, :country, :postal,
           :smethod, NOW())
    ");
    $orderStmt->execute([
        'uid'      => $userId,
        'subtotal' => $subtotal,
        'shipping' => $shippingCost,
        'total'    => $total,
        'name'     => $co['name'],
        'email'    => $co['email'],
        'phone'    => $co['phone'],
        'addr1'    => $co['address_line1'],
        'addr2'    => $co['address_line2'],
        'city'     => $co['city'],
        'country'  => $co['country'],
        'postal'   => $co['postal_code'],
        'smethod'  => $co['shipping_method'],
    ]);
    $orderId = $pdo->lastInsertId();

    // Insert order items & decrement stock
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (:oid, :pid, :qty, :price)
    ");
    $stockStmt = $pdo->prepare("
        UPDATE products SET stock = GREATEST(0, stock - :qty) WHERE id = :pid
    ");
    foreach ($cartItems as $item) {
        $itemStmt->execute([
            'oid'   => $orderId,
            'pid'   => $item['product_id'],
            'qty'   => $item['quantity'],
            'price' => $item['price'],
        ]);
        $stockStmt->execute(['qty' => $item['quantity'], 'pid' => $item['product_id']]);
    }

    // Clear cart from DB and session
    $pdo->prepare("DELETE FROM cart_items WHERE user_id = :uid")->execute(['uid' => $userId]);
    $_SESSION['cart'] = [];

    // Generate a friendly order number
    $orderNumber = 'BATIK-' . date('Y') . '-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);

    // Estimate delivery
    $days = ($co['shipping_method'] === 'express') ? 2 : 5;
    $deliveryDate = date('F j', strtotime("+{$days} days")) . '–' . date('j, Y', strtotime('+' . ($days + 2) . ' days'));

    $_SESSION['last_order'] = [
        'id'            => $orderId,
        'number'        => $orderNumber,
        'total'         => $total,
        'delivery_date' => $deliveryDate,
        'item_count'    => $itemCount,
    ];

    header('Location: checkout.php?step=3');
    exit;
}

// ── Step 3 data ──
$lastOrder = $_SESSION['last_order'] ?? null;

// ── Shipping cost for display ──
$co = $_SESSION['checkout'] ?? [];
$shippingCost = match($co['shipping_method'] ?? 'standard') {
    'express' => ($subtotal > 5000 ? 500 : 1000),
    default   => ($subtotal > 5000 ? 0 : 500),
};
$total = $subtotal + $shippingCost;

// ── Category label helper ──
function catLabel(string $cat): string {
    return match($cat) {
        'fabric'      => 'Fabric Yardage',
        'clothing'    => 'Clothing',
        'home_decor'  => 'Home Décor',
        'accessories' => 'Accessories',
        default       => ucfirst($cat),
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | BatikSL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root {
    --teal: #0f766e;
    --teal-light: #14b8a6;
    --teal-dark: #0d5c55;
    --cream: #faf7f2;
    --sand: #f0ebe1;
    --charcoal: #1c1c1c;
    --warm-gray: #6b6560;
    --gold: #c9a84c;
    --border: #e8e3da;
    --red: #dc2626;
    --green: #16a34a;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--charcoal); }

  /* NAVBAR */
  nav { position: sticky; top: 0; z-index: 1000; padding: 1.1rem 4rem; display: flex; align-items: center; justify-content: space-between; background: rgba(250,247,242,0.97); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); }
  .nav-logo { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--charcoal); text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-secure { font-size: 0.8rem; color: var(--warm-gray); display: flex; align-items: center; gap: 0.4rem; }
  .nav-secure i { color: var(--teal); }

  /* BREADCRUMB */
  .breadcrumb-bar { background: var(--sand); padding: 1rem 4rem; display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; color: var(--warm-gray); }
  .breadcrumb-bar a { color: inherit; text-decoration: none; }
  .breadcrumb-bar a:hover { color: var(--teal); }
  .breadcrumb-bar i { font-size: 0.6rem; opacity: 0.5; }

  /* PROGRESS STEPPER */
  .stepper-wrap { padding: 2.5rem 4rem; display: flex; justify-content: center; }
  .stepper { display: flex; align-items: center; gap: 0; }
  .step { display: flex; align-items: center; gap: 0.7rem; }
  .step-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 600; border: 2px solid var(--border); background: white; color: var(--warm-gray); flex-shrink: 0; transition: all 0.3s; }
  .step-circle.done { background: var(--teal); border-color: var(--teal); color: white; }
  .step-circle.active { background: var(--charcoal); border-color: var(--charcoal); color: white; }
  .step-label { font-size: 0.8rem; font-weight: 500; color: var(--warm-gray); white-space: nowrap; }
  .step-label.active { color: var(--charcoal); font-weight: 600; }
  .step-label.done { color: var(--teal); }
  .step-line { width: 80px; height: 2px; background: var(--border); margin: 0 0.5rem; flex-shrink: 0; transition: background 0.3s; }
  .step-line.done { background: var(--teal); }

  /* LAYOUT */
  .checkout-body { display: grid; grid-template-columns: 1fr 380px; gap: 2.5rem; padding: 0 4rem 4rem; max-width: 1280px; margin: 0 auto; align-items: start; }

  /* FORM PANEL */
  .form-card { background: white; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
  .form-card-header { padding: 1.4rem 1.8rem; background: var(--charcoal); color: white; display: flex; align-items: center; gap: 0.7rem; }
  .form-card-header h2 { font-family: 'Playfair Display', serif; font-size: 1.1rem; }
  .form-card-body { padding: 1.8rem; }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
  .form-grid .full { grid-column: 1 / -1; }
  .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
  .form-group label { font-size: 0.76rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; color: var(--warm-gray); }
  .form-group input,
  .form-group select { padding: 0.7rem 0.95rem; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: var(--charcoal); background: white; outline: none; transition: border-color 0.2s; }
  .form-group input:focus,
  .form-group select:focus { border-color: var(--teal); }
  .form-group input::placeholder { color: #c0b9b2; }

  /* Shipping method cards */
  .shipping-options { display: flex; flex-direction: column; gap: 0.7rem; margin-top: 1.5rem; }
  .ship-option { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.1rem; border: 1.5px solid var(--border); border-radius: 10px; cursor: pointer; transition: all 0.2s; }
  .ship-option:has(input:checked) { border-color: var(--teal); background: #f0faf9; }
  .ship-option input { accent-color: var(--teal); width: 16px; height: 16px; flex-shrink: 0; }
  .ship-option-info { flex: 1; }
  .ship-option-title { font-weight: 500; font-size: 0.9rem; }
  .ship-option-desc  { font-size: 0.78rem; color: var(--warm-gray); margin-top: 0.15rem; }
  .ship-option-price { font-weight: 600; color: var(--teal); font-size: 0.9rem; }

  /* Payment section */
  .payment-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.4rem; }
  .pay-tab { flex: 1; padding: 0.7rem; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 0.83rem; font-weight: 500; cursor: pointer; background: white; color: var(--warm-gray); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.4rem; }
  .pay-tab.active, .pay-tab:hover { border-color: var(--teal); color: var(--teal); background: #f0faf9; }

  .card-fields { display: flex; flex-direction: column; gap: 0.9rem; }
  .card-row { display: flex; gap: 0.8rem; }
  .card-input-wrap { position: relative; flex: 1; }
  .card-input-wrap i { position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--warm-gray); font-size: 0.9rem; pointer-events: none; }
  .card-input-wrap input { width: 100%; padding: 0.7rem 2.5rem 0.7rem 0.95rem; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: var(--charcoal); outline: none; transition: border-color 0.2s; }
  .card-input-wrap input:focus { border-color: var(--teal); }
  .card-input-wrap input::placeholder { color: #c0b9b2; }
  .payhere-notice { background: #f0faf9; border: 1px solid #c7e8e5; border-radius: 9px; padding: 1rem 1.2rem; font-size: 0.84rem; color: var(--warm-gray); display: flex; align-items: flex-start; gap: 0.7rem; }
  .payhere-notice i { color: var(--teal); margin-top: 0.1rem; flex-shrink: 0; }

  .btn-primary { width: 100%; padding: 1rem; background: var(--teal); color: white; border: none; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 0.92rem; font-weight: 500; cursor: pointer; transition: background 0.25s; display: flex; align-items: center; justify-content: center; gap: 0.6rem; margin-top: 1.5rem; }
  .btn-primary:hover { background: var(--teal-dark); }
  .btn-back { display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.83rem; color: var(--warm-gray); text-decoration: none; margin-bottom: 1.2rem; transition: color 0.2s; }
  .btn-back:hover { color: var(--teal); }

  /* ORDER SUMMARY SIDEBAR */
  .summary-panel { position: sticky; top: calc(72px + 1rem); }
  .summary-card { background: white; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
  .summary-card-header { padding: 1.3rem 1.6rem; background: var(--charcoal); color: white; display: flex; align-items: center; gap: 0.7rem; }
  .summary-card-header h3 { font-family: 'Playfair Display', serif; font-size: 1.05rem; }
  .summary-card-body { padding: 1.4rem 1.6rem; }
  .summary-items { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.2rem; }
  .summary-item { display: flex; gap: 0.9rem; align-items: flex-start; }
  .summary-item-img-wrap { position: relative; flex-shrink: 0; }
  .summary-item-img { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; background: var(--sand); }
  .summary-item-qty { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: var(--charcoal); color: white; font-size: 0.65rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
  .summary-item-info { flex: 1; min-width: 0; }
  .summary-item-name { font-size: 0.85rem; font-weight: 500; line-height: 1.35; }
  .summary-item-cat  { font-size: 0.72rem; color: var(--warm-gray); margin-top: 0.15rem; }
  .summary-item-price { font-size: 0.85rem; font-weight: 600; color: var(--teal); flex-shrink: 0; }
  .summary-divider { border: none; border-top: 1px solid var(--border); margin: 0.8rem 0; }
  .summary-row { display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.88rem; }
  .summary-row .s-label { color: var(--warm-gray); }
  .summary-row .s-value { font-weight: 500; }
  .summary-row.free .s-value { color: var(--green); }
  .summary-row.total-row { border-top: 2px solid var(--border); margin-top: 0.5rem; padding-top: 0.9rem; }
  .summary-row.total-row .s-label { font-weight: 600; font-size: 0.9rem; }
  .summary-row.total-row .s-value { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--teal); }
  .trust-list { display: flex; flex-direction: column; gap: 0.55rem; margin-top: 1rem; }
  .trust-item { display: flex; align-items: center; gap: 0.6rem; font-size: 0.79rem; color: var(--warm-gray); }
  .trust-item i { color: var(--teal); width: 14px; text-align: center; }

  /* SUCCESS STATE */
  .success-wrap { max-width: 640px; margin: 0 auto; padding: 3rem 4rem 5rem; }
  .success-card { background: white; border-radius: 20px; border: 1px solid var(--border); padding: 3rem 2.5rem; text-align: center; }
  .success-icon { width: 80px; height: 80px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
  .success-icon i { font-size: 2rem; color: var(--green); }
  .success-card h2 { font-family: 'Playfair Display', serif; font-size: 1.7rem; margin-bottom: 0.4rem; }
  .success-card .order-num { font-size: 0.85rem; color: var(--warm-gray); margin-bottom: 1.5rem; }
  .success-detail { background: var(--sand); border-radius: 12px; padding: 1.2rem 1.5rem; text-align: left; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.6rem; }
  .success-detail-row { display: flex; justify-content: space-between; font-size: 0.88rem; }
  .success-detail-row .sd-label { color: var(--warm-gray); }
  .success-detail-row .sd-value { font-weight: 500; }
  .success-actions { display: flex; flex-wrap: wrap; gap: 0.8rem; justify-content: center; margin-top: 0.5rem; }
  .btn-success-primary { padding: 0.8rem 1.8rem; background: var(--teal); color: white; border-radius: 9px; text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: background 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
  .btn-success-primary:hover { background: var(--teal-dark); }
  .btn-success-outline { padding: 0.8rem 1.8rem; border: 1.5px solid var(--teal); color: var(--teal); border-radius: 9px; text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
  .btn-success-outline:hover { background: var(--teal); color: white; }
  .social-share { display: flex; gap: 0.6rem; justify-content: center; margin-top: 1.5rem; }
  .share-btn { width: 38px; height: 38px; border-radius: 50%; background: var(--sand); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--warm-gray); text-decoration: none; font-size: 0.9rem; transition: all 0.2s; }
  .share-btn:hover { border-color: var(--teal); color: var(--teal); }

  /* FOOTER */
  footer { background: #111; color: rgba(255,255,255,0.6); padding: 3rem 4rem 2rem; margin-top: 4rem; }
  .footer-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.8rem; }
  .footer-bottom a { color: inherit; text-decoration: none; }
  .footer-bottom a:hover { color: var(--teal-light); }

  /* RESPONSIVE */
  @media (max-width: 1024px) { .checkout-body { grid-template-columns: 1fr; padding: 0 2rem 3rem; } .summary-panel { position: static; order: -1; } }
  @media (max-width: 860px)  { nav { padding: 1rem 1.5rem; } .stepper-wrap, .breadcrumb-bar { padding-left: 1.5rem; padding-right: 1.5rem; } .checkout-body { padding: 0 1.5rem 3rem; } .step-line { width: 40px; } .form-grid { grid-template-columns: 1fr; } .form-grid .full { grid-column: 1; } .success-wrap { padding: 2rem 1.5rem 4rem; } }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <div class="nav-secure"><i class="fas fa-lock"></i> Secure Checkout</div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
  <a href="index.php">Home</a>
  <i class="fas fa-chevron-right"></i>
  <a href="cart.php">Cart</a>
  <i class="fas fa-chevron-right"></i>
  <span>Checkout</span>
</div>

<!-- STEP INDICATOR -->
<div class="stepper-wrap">
  <div class="stepper">
    <div class="step">
      <div class="step-circle <?= $step > 1 ? 'done' : ($step === 1 ? 'active' : '') ?>">
        <?= $step > 1 ? '<i class="fas fa-check" style="font-size:0.75rem"></i>' : '1' ?>
      </div>
      <span class="step-label <?= $step === 1 ? 'active' : ($step > 1 ? 'done' : '') ?>">Shipping</span>
    </div>
    <div class="step-line <?= $step > 1 ? 'done' : '' ?>"></div>
    <div class="step">
      <div class="step-circle <?= $step > 2 ? 'done' : ($step === 2 ? 'active' : '') ?>">
        <?= $step > 2 ? '<i class="fas fa-check" style="font-size:0.75rem"></i>' : '2' ?>
      </div>
      <span class="step-label <?= $step === 2 ? 'active' : ($step > 2 ? 'done' : '') ?>">Payment</span>
    </div>
    <div class="step-line <?= $step > 2 ? 'done' : '' ?>"></div>
    <div class="step">
      <div class="step-circle <?= $step === 3 ? 'done' : '' ?>">
        <?= $step === 3 ? '<i class="fas fa-check" style="font-size:0.75rem"></i>' : '3' ?>
      </div>
      <span class="step-label <?= $step === 3 ? 'done' : '' ?>">Confirmed</span>
    </div>
  </div>
</div>

<!-- ── STEP 3: ORDER CONFIRMED ── -->
<?php if ($step === 3 && $lastOrder): ?>
<div class="success-wrap">
  <div class="success-card">
    <div class="success-icon"><i class="fas fa-check"></i></div>
    <h2>Order Confirmed!</h2>
    <p class="order-num">Order <?= htmlspecialchars($lastOrder['number']) ?></p>

    <div class="success-detail">
      <div class="success-detail-row">
        <span class="sd-label">Estimated Delivery</span>
        <span class="sd-value"><?= htmlspecialchars($lastOrder['delivery_date']) ?></span>
      </div>
      <div class="success-detail-row">
        <span class="sd-label">Items</span>
        <span class="sd-value"><?= $lastOrder['item_count'] ?> <?= $lastOrder['item_count'] === 1 ? 'product' : 'products' ?></span>
      </div>
      <div class="success-detail-row">
        <span class="sd-label">Total Charged</span>
        <span class="sd-value" style="color:var(--teal)">LKR <?= number_format($lastOrder['total']) ?></span>
      </div>
    </div>

    <p style="font-size:0.85rem;color:var(--warm-gray);margin-bottom:1.2rem">
      A confirmation email has been sent to <strong><?= htmlspecialchars($co['email'] ?? $user['email'] ?? '') ?></strong>
    </p>

    <div class="success-actions">
      <a href="account.php?tab=orders" class="btn-success-primary">
        <i class="fas fa-box"></i> Track Your Order
      </a>
      <a href="catalog.php" class="btn-success-outline">
        <i class="fas fa-store"></i> Continue Shopping
      </a>
    </div>

    <div class="social-share">
      <a href="#" class="share-btn"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="share-btn"><i class="fab fa-instagram"></i></a>
      <a href="#" class="share-btn"><i class="fab fa-whatsapp"></i></a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ── STEPS 1 & 2 layout ── -->
<div class="checkout-body">

  <!-- LEFT: FORM -->
  <div>

    <!-- STEP 1: SHIPPING -->
    <?php if ($step === 1): ?>
    <form method="POST" action="checkout.php?step=1">
      <div class="form-card">
        <div class="form-card-header">
          <i class="fas fa-truck" style="opacity:0.6"></i>
          <h2>Shipping Information</h2>
        </div>
        <div class="form-card-body">
          <div class="form-grid">
            <div class="form-group full">
              <label>Full Name</label>
              <input type="text" name="name" placeholder="e.g. Amara Perera" required
                     value="<?= htmlspecialchars($co['name'] ?? $user['name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="you@example.com" required
                     value="<?= htmlspecialchars($co['email'] ?? $user['email'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="tel" name="phone" placeholder="+94 77 000 0000" required
                     value="<?= htmlspecialchars($co['phone'] ?? $user['phone'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Address Line 1</label>
              <input type="text" name="address_line1" placeholder="Street / Lane" required
                     value="<?= htmlspecialchars($co['address_line1'] ?? $user['address_line1'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Address Line 2 <span style="opacity:0.5;font-weight:400">(optional)</span></label>
              <input type="text" name="address_line2" placeholder="Apartment, floor, etc."
                     value="<?= htmlspecialchars($co['address_line2'] ?? $user['address_line2'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>City</label>
              <input type="text" name="city" placeholder="e.g. Colombo" required
                     value="<?= htmlspecialchars($co['city'] ?? $user['city'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Postal Code</label>
              <input type="text" name="postal_code" placeholder="10100"
                     value="<?= htmlspecialchars($co['postal_code'] ?? $user['postal_code'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Country</label>
              <select name="country">
                <?php foreach (['Sri Lanka','India','USA','UK','Australia','Canada','UAE'] as $c): ?>
                <option <?= ($co['country'] ?? 'Sri Lanka') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="shipping-options">
            <label class="ship-option">
              <input type="radio" name="shipping_method" value="standard"
                     <?= ($co['shipping_method'] ?? 'standard') === 'standard' ? 'checked' : '' ?>>
              <div class="ship-option-info">
                <div class="ship-option-title">Standard Shipping</div>
                <div class="ship-option-desc">3–5 business days · Ships from Kandy</div>
              </div>
              <span class="ship-option-price"><?= $subtotal > 5000 ? 'Free' : 'LKR 500' ?></span>
            </label>
            <label class="ship-option">
              <input type="radio" name="shipping_method" value="express"
                     <?= ($co['shipping_method'] ?? '') === 'express' ? 'checked' : '' ?>>
              <div class="ship-option-info">
                <div class="ship-option-title">Express Shipping</div>
                <div class="ship-option-desc">1–2 business days · Priority handling</div>
              </div>
              <span class="ship-option-price"><?= $subtotal > 5000 ? 'LKR 500' : 'LKR 1,000' ?></span>
            </label>
          </div>

          <button type="submit" class="btn-primary">
            <i class="fas fa-arrow-right" style="font-size:0.8rem"></i>
            Continue to Payment
          </button>
        </div>
      </div>
    </form>
    <?php endif; ?>

    <!-- STEP 2: PAYMENT -->
    <?php if ($step === 2): ?>
    <a href="checkout.php?step=1" class="btn-back">
      <i class="fas fa-arrow-left" style="font-size:0.75rem"></i> Back to Shipping
    </a>

    <form method="POST" action="checkout.php?step=2">
      <div class="form-card">
        <div class="form-card-header">
          <i class="fas fa-credit-card" style="opacity:0.6"></i>
          <h2>Payment Details</h2>
        </div>
        <div class="form-card-body">
          <div class="payment-tabs">
            <button type="button" class="pay-tab active" onclick="showTab('card', this)">
              <i class="fas fa-credit-card"></i> Card
            </button>
            <button type="button" class="pay-tab" onclick="showTab('payhere', this)">
              <i class="fas fa-mobile-alt"></i> PayHere
            </button>
            <button type="button" class="pay-tab" onclick="showTab('cod', this)">
              <i class="fas fa-money-bill-wave"></i> Cash on Delivery
            </button>
          </div>

          <div id="tab-card" class="card-fields">
            <div class="card-input-wrap">
              <input type="text" name="card_number" placeholder="Card number" maxlength="19"
                     oninput="formatCard(this)">
              <i class="fas fa-credit-card"></i>
            </div>
            <div class="card-row">
              <div class="card-input-wrap">
                <input type="text" name="card_expiry" placeholder="MM / YY" maxlength="7"
                       oninput="formatExpiry(this)">
                <i class="far fa-calendar"></i>
              </div>
              <div class="card-input-wrap">
                <input type="text" name="card_cvv" placeholder="CVV" maxlength="4">
                <i class="fas fa-lock"></i>
              </div>
            </div>
            <div class="card-input-wrap">
              <input type="text" name="card_name" placeholder="Name on card">
              <i class="far fa-user"></i>
            </div>
          </div>

          <div id="tab-payhere" class="payhere-notice" style="display:none">
            <i class="fas fa-info-circle"></i>
            <div>
              You'll be redirected to <strong>PayHere</strong> to complete your payment securely.
              Supports Visa, Mastercard, and all major local bank cards.
            </div>
          </div>

          <div id="tab-cod" class="payhere-notice" style="display:none;border-color:#fde68a;background:#fffbeb">
            <i class="fas fa-info-circle" style="color:#d97706"></i>
            <div>
              Pay in cash when your order arrives. Available for deliveries within Sri Lanka only.
              A delivery fee applies regardless of order total.
            </div>
          </div>

          <input type="hidden" name="payment_method" id="paymentMethodInput" value="card">

          <button type="submit" class="btn-primary">
            <i class="fas fa-lock" style="font-size:0.8rem"></i>
            Place Order · LKR <?= number_format($total) ?>
          </button>

          <p style="text-align:center;font-size:0.76rem;color:var(--warm-gray);margin-top:0.8rem">
            <i class="fas fa-shield-alt"></i> 256-bit SSL encryption · Your data is safe
          </p>
        </div>
      </div>
    </form>
    <?php endif; ?>
  </div>

  <!-- RIGHT: ORDER SUMMARY -->
  <div class="summary-panel">
    <div class="summary-card">
      <div class="summary-card-header">
        <i class="fas fa-receipt" style="opacity:0.6"></i>
        <h3>Your Order</h3>
      </div>
      <div class="summary-card-body">
        <div class="summary-items">
          <?php foreach ($cartItems as $item): ?>
          <div class="summary-item">
            <div class="summary-item-img-wrap">
              <img src="<?= htmlspecialchars($item['image_path'] ?? 'images/placeholder.jpg') ?>"
                   alt="<?= htmlspecialchars($item['name']) ?>"
                   class="summary-item-img"
                   onerror="this.src='images/placeholder.jpg'">
              <span class="summary-item-qty"><?= $item['quantity'] ?></span>
            </div>
            <div class="summary-item-info">
              <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="summary-item-cat"><?= catLabel($item['category'] ?? '') ?></div>
            </div>
            <div class="summary-item-price">LKR <?= number_format($item['price'] * $item['quantity']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <hr class="summary-divider">

        <div class="summary-row">
          <span class="s-label">Subtotal (<?= $itemCount ?> items)</span>
          <span class="s-value">LKR <?= number_format($subtotal) ?></span>
        </div>
        <div class="summary-row <?= $shippingCost == 0 ? 'free' : '' ?>">
          <span class="s-label">Shipping</span>
          <span class="s-value"><?= $shippingCost == 0 ? 'Free' : 'LKR ' . number_format($shippingCost) ?></span>
        </div>
        <div class="summary-row total-row">
          <span class="s-label">Total</span>
          <span class="s-value">LKR <?= number_format($total) ?></span>
        </div>

        <div class="trust-list">
          <div class="trust-item"><i class="fas fa-shield-alt"></i> Secure checkout</div>
          <div class="trust-item"><i class="fas fa-undo"></i> Free returns within 14 days</div>
          <div class="trust-item"><i class="fas fa-shipping-fast"></i> Ships from Kandy</div>
        </div>
      </div>
    </div>
  </div>

</div>
<?php endif; ?>

<!-- FOOTER -->
<footer>
  <div class="footer-bottom">
    <div>© 2025 BatikSL. Handcrafted with ❤️ in Sri Lanka.</div>
    <div style="display:flex;gap:1.5rem">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
    </div>
  </div>
</footer>

<script>
  function showTab(tab, btn) {
    document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.pay-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = tab === 'card' ? 'flex' : 'block';
    if (tab === 'card') document.getElementById('tab-' + tab).style.flexDirection = 'column';
    btn.classList.add('active');
    document.getElementById('paymentMethodInput').value = tab;
  }

  function formatCard(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
  }

  function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 2) v = v.substring(0, 2) + ' / ' + v.substring(2);
    input.value = v;
  }
</script>
</body>
</html>