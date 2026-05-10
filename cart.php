<?php
// cart.php - Shopping Cart Page
require_once 'config/database.php';

// Seed demo cart if empty
if (empty($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        1 => ['id'=>1,'name'=>'Traditional Elephant Batik Sarong','price'=>4500,'quantity'=>1,'image'=>'images/products/White_Elephant_Sarong.jpg','variant'=>'M','category'=>'Clothing'],
        4 => ['id'=>4,'name'=>'Handmade Batik Scarf','price'=>2800,'quantity'=>2,'image'=>'images/products/batik-silk-scarf.jpg','variant'=>null,'category'=>'Accessories'],
    ];
}

// ── Handle AJAX / POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'update' && isset($_SESSION['cart'][$id])) {
        $qty = max(1, (int)$_POST['quantity']);
        $_SESSION['cart'][$id]['quantity'] = $qty;
    } elseif ($action === 'remove' && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    // Recalculate & return JSON
    $cart     = array_values($_SESSION['cart']);
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
    $shipping = ($subtotal > 5000 || empty($cart)) ? 0 : 500;
    $total    = $subtotal + $shipping;
    $count    = array_sum(array_column($cart, 'quantity'));

    echo json_encode([
        'success'  => true,
        'cart'     => $cart,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total'    => $total,
        'count'    => $count,
    ]);
    exit;
}

// ── Initial page render ──
$cartItems = array_values($_SESSION['cart']);
$subtotal  = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$shipping  = ($subtotal > 5000 || empty($cartItems)) ? 0 : 500;
$total     = $subtotal + $shipping;
$itemCount = array_sum(array_column($cartItems, 'quantity'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart | BatikSL</title>
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
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--charcoal);
    overflow-x: hidden;
  }

  /* ── NAVBAR ── */
  nav {
    position: sticky; top: 0; z-index: 1000;
    padding: 1.1rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(250,247,242,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }
  .nav-logo { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--charcoal); text-decoration:none; }
  .nav-logo span { color: var(--gold); }
  .nav-links { display:flex; gap:2.5rem; list-style:none; }
  .nav-links a { font-size:0.82rem; font-weight:500; letter-spacing:0.08em; text-transform:uppercase; text-decoration:none; color:var(--warm-gray); transition:color 0.25s; }
  .nav-links a:hover, .nav-links a.active { color:var(--teal); }
  .nav-icons { display:flex; align-items:center; gap:1.4rem; }
  .nav-icons a, .nav-icons button { background:none; border:none; cursor:pointer; color:var(--warm-gray); font-size:1.05rem; text-decoration:none; transition:color 0.25s; position:relative; }
  .nav-icons a:hover, .nav-icons button:hover { color:var(--teal); }
  .cart-badge { position:absolute; top:-6px; right:-8px; background:var(--gold); color:white; font-size:0.62rem; font-weight:700; width:17px; height:17px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .nav-signin { padding:0.45rem 1.3rem; border:1.5px solid var(--teal); border-radius:2rem; font-size:0.78rem; font-weight:500; letter-spacing:0.05em; text-transform:uppercase; color:var(--teal); text-decoration:none; transition:all 0.25s; }
  .nav-signin:hover { background:var(--teal); color:white; }

  /* ── BREADCRUMB ── */
  .breadcrumb-bar { background:var(--sand); padding:1rem 4rem; display:flex; align-items:center; gap:0.6rem; font-size:0.8rem; color:var(--warm-gray); }
  .breadcrumb-bar a { color:inherit; text-decoration:none; transition:color 0.2s; }
  .breadcrumb-bar a:hover { color:var(--teal); }
  .breadcrumb-bar i { font-size:0.6rem; opacity:0.5; }

  /* ── PAGE HEADER ── */
  .catalog-header {
    padding: 3rem 4rem 2rem;
    border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: flex-end;
    flex-wrap: wrap; gap: 1rem;
  }
  .catalog-header .tag { font-size:0.72rem; font-weight:500; letter-spacing:0.2em; text-transform:uppercase; color:var(--teal); margin-bottom:0.5rem; display:block; }
  .catalog-header h1 { font-family:'Playfair Display',serif; font-size:clamp(1.8rem,3.5vw,2.8rem); font-weight:700; line-height:1.15; }
  .catalog-header h1 em { font-style:italic; color:var(--teal); }
  .header-meta { font-size:0.85rem; color:var(--warm-gray); }

  /* ── LAYOUT ── */
  .cart-body {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 2.5rem;
    padding: 3rem 4rem;
    max-width: 1280px;
    margin: 0 auto;
    align-items: start;
  }

  /* ── CART ITEMS PANEL ── */
  .cart-panel { display:flex; flex-direction:column; gap:1.2rem; }

  .cart-section-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.5rem;
  }
  .cart-section-header h2 { font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:700; }
  .btn-clear-cart {
    background: none; border: none; cursor: pointer;
    font-size: 0.78rem; color: var(--warm-gray);
    display: flex; align-items: center; gap: 0.4rem;
    font-family: 'DM Sans', sans-serif;
    transition: color 0.2s;
  }
  .btn-clear-cart:hover { color: var(--red); }

  /* Cart row */
  .cart-item {
    background: white; border-radius: 14px;
    border: 1px solid var(--border);
    display: flex; gap: 1.2rem;
    padding: 1.2rem; align-items: flex-start;
    transition: opacity 0.3s, transform 0.3s;
  }
  .cart-item.removing { opacity: 0; transform: translateX(20px); }

  .cart-item-img {
    width: 90px; height: 90px; border-radius: 10px;
    object-fit: cover; flex-shrink: 0;
    background: var(--sand);
  }

  .cart-item-body { flex: 1; min-width: 0; }
  .cart-item-cat { font-size:0.68rem; letter-spacing:0.12em; text-transform:uppercase; color:var(--warm-gray); margin-bottom:0.3rem; }
  .cart-item-name { font-family:'Playfair Display',serif; font-size:1rem; font-weight:700; margin-bottom:0.4rem; line-height:1.3; }
  .cart-item-variant {
    display: inline-flex; align-items: center; gap: 0.35rem;
    background: var(--sand); border-radius: 2rem;
    padding: 0.2rem 0.65rem; font-size:0.73rem; color:var(--warm-gray);
    margin-bottom: 0.8rem;
  }

  /* Stepper */
  .stepper { display:inline-flex; align-items:center; border:1.5px solid var(--border); border-radius:8px; overflow:hidden; }
  .stepper-btn { width:32px; height:32px; background:none; border:none; cursor:pointer; color:var(--warm-gray); font-size:0.75rem; display:flex; align-items:center; justify-content:center; transition:all 0.2s; }
  .stepper-btn:hover { background:var(--sand); color:var(--charcoal); }
  .stepper-val { min-width:36px; text-align:center; font-size:0.9rem; font-weight:500; border-left:1px solid var(--border); border-right:1px solid var(--border); line-height:32px; }

  .btn-remove { background:none; border:none; cursor:pointer; font-size:0.78rem; color:var(--warm-gray); display:flex; align-items:center; gap:0.35rem; font-family:'DM Sans',sans-serif; margin-left:0.8rem; transition:color 0.2s; }
  .btn-remove:hover { color:var(--red); }

  .cart-item-actions { display:flex; align-items:center; flex-wrap:wrap; gap:0.4rem; }

  .cart-item-price { text-align:right; flex-shrink:0; }
  .cart-item-price .total { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; color:var(--teal); }
  .cart-item-price .unit { font-size:0.75rem; color:var(--warm-gray); margin-top:0.2rem; }

  /* Continue shopping link */
  .continue-link {
    display: inline-flex; align-items: center; gap: 0.5rem;
    color: var(--teal); text-decoration: none;
    font-size: 0.84rem; font-weight: 500;
    margin-top: 0.5rem; transition: gap 0.2s;
  }
  .continue-link:hover { gap: 0.8rem; }

  /* Free shipping bar */
  .shipping-bar-wrap {
    background: white; border-radius: 14px;
    border: 1px solid var(--border);
    padding: 1rem 1.4rem;
  }
  .shipping-bar-label { font-size:0.8rem; color:var(--warm-gray); margin-bottom:0.6rem; display:flex; justify-content:space-between; }
  .shipping-bar-label strong { color:var(--charcoal); }
  .shipping-bar-track { height:5px; background:var(--sand); border-radius:3px; overflow:hidden; }
  .shipping-bar-fill { height:100%; background:var(--teal); border-radius:3px; transition:width 0.6s ease; }

  /* ── ORDER SUMMARY ── */
  .summary-panel { position:sticky; top:calc(72px + 1.5rem); }

  .summary-card { background:white; border-radius:14px; border:1px solid var(--border); overflow:hidden; }
  .summary-card-header { padding:1.4rem 1.6rem; background:var(--charcoal); color:white; display:flex; align-items:center; gap:0.7rem; }
  .summary-card-header h3 { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; }
  .summary-card-body { padding:1.4rem 1.6rem; }

  .summary-row { display:flex; justify-content:space-between; align-items:baseline; padding:0.65rem 0; border-bottom:1px solid var(--border); font-size:0.88rem; }
  .summary-row:last-of-type { border-bottom:none; }
  .summary-row .s-label { color:var(--warm-gray); }
  .summary-row .s-value { font-weight:500; color:var(--charcoal); }
  .summary-row .s-value.free { color:#16a34a; font-weight:500; }
  .summary-row.total { margin-top:0.5rem; padding-top:1rem; border-top:2px solid var(--border); border-bottom:none; }
  .summary-row.total .s-label { font-weight:600; font-size:0.9rem; color:var(--charcoal); }
  .summary-row.total .s-value { font-family:'Playfair Display',serif; font-size:1.3rem; color:var(--teal); font-weight:700; }

  /* Promo code */
  .promo-wrap { margin:1.2rem 0; }
  .promo-field { display:flex; gap:0.5rem; }
  .promo-field input {
    flex:1; padding:0.6rem 0.9rem;
    border:1.5px solid var(--border); border-radius:8px;
    font-family:'DM Sans',sans-serif; font-size:0.85rem; color:var(--charcoal);
    background:white; outline:none; transition:border-color 0.2s;
  }
  .promo-field input:focus { border-color:var(--teal); }
  .promo-field input::placeholder { color:#c5bdb6; }
  .btn-promo {
    padding:0.6rem 1rem; background:var(--charcoal); color:white;
    border:none; border-radius:8px; font-family:'DM Sans',sans-serif;
    font-size:0.82rem; cursor:pointer; transition:background 0.2s; white-space:nowrap;
  }
  .btn-promo:hover { background:var(--teal); }
  .promo-msg { font-size:0.76rem; margin-top:0.5rem; }
  .promo-msg.success { color:#16a34a; }
  .promo-msg.error   { color:var(--red); }

  /* Checkout button */
  .btn-checkout {
    width:100%; padding:1rem; background:var(--teal); color:white;
    border:none; border-radius:10px; font-family:'DM Sans',sans-serif;
    font-size:0.9rem; font-weight:500; cursor:pointer;
    transition:background 0.25s; display:flex; align-items:center;
    justify-content:center; gap:0.6rem; margin-top:1rem; text-decoration:none;
  }
  .btn-checkout:hover { background:var(--teal-dark); }
  .btn-checkout:disabled { background:#ccc; cursor:not-allowed; }

  /* Trust badges */
  .trust-badges { background:white; border-radius:14px; border:1px solid var(--border); padding:1.2rem 1.4rem; margin-top:1rem; display:flex; flex-direction:column; gap:0.7rem; }
  .trust-item { display:flex; align-items:center; gap:0.7rem; font-size:0.81rem; color:var(--warm-gray); }
  .trust-item i { color:var(--teal); width:16px; text-align:center; }

  /* ── EMPTY STATE ── */
  .empty-state {
    grid-column: 1 / -1;
    text-align:center; padding:6rem 2rem;
    background:white; border-radius:14px; border:1px solid var(--border);
  }
  .empty-state i { font-size:3rem; color:var(--border); display:block; margin-bottom:1rem; }
  .empty-state h3 { font-family:'Playfair Display',serif; font-size:1.5rem; margin-bottom:0.5rem; }
  .empty-state p { color:var(--warm-gray); margin-bottom:1.5rem; }
  .btn-shop-now {
    display:inline-flex; align-items:center; gap:0.5rem;
    padding:0.8rem 1.8rem; background:var(--teal); color:white;
    border-radius:8px; text-decoration:none; font-size:0.88rem; font-weight:500;
    transition:background 0.25s;
  }
  .btn-shop-now:hover { background:var(--teal-dark); }

  /* ── FOOTER ── */
  footer { background:#111; color:rgba(255,255,255,0.6); padding:5rem 4rem 2.5rem; margin-top:6rem; }
  .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1.5fr; gap:4rem; }
  .footer-logo { font-family:'Playfair Display',serif; font-size:1.8rem; color:white; margin-bottom:1rem; display:block; }
  .footer-logo span { color:var(--gold); }
  .footer-about { font-size:0.88rem; line-height:1.7; max-width:280px; }
  .footer-col h4 { color:white; font-size:0.78rem; font-weight:500; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:1.4rem; }
  .footer-col ul { list-style:none; display:flex; flex-direction:column; gap:0.7rem; }
  .footer-col ul a { text-decoration:none; color:inherit; font-size:0.88rem; transition:color 0.2s; }
  .footer-col ul a:hover { color:var(--teal-light); }
  .social-icons { display:flex; gap:1rem; margin-bottom:1.5rem; }
  .social-icon { width:40px; height:40px; border-radius:50%; border:1px solid rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; color:inherit; text-decoration:none; font-size:0.95rem; transition:all 0.25s; }
  .social-icon:hover { border-color:var(--teal-light); color:var(--teal-light); }
  .footer-bottom { border-top:1px solid rgba(255,255,255,0.06); margin-top:4rem; padding-top:2rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; font-size:0.8rem; }
  .footer-bottom a { color:inherit; text-decoration:none; }
  .footer-bottom a:hover { color:var(--teal-light); }

  /* ── TOAST ── */
  .toast {
    position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
    background: var(--charcoal); color: white;
    padding: 0.8rem 1.4rem; border-radius: 10px;
    font-size: 0.85rem; display: flex; align-items: center; gap: 0.6rem;
    transform: translateY(80px); opacity: 0;
    transition: all 0.35s ease; pointer-events: none;
  }
  .toast.show { transform: translateY(0); opacity: 1; }
  .toast.success i { color: #4ade80; }
  .toast.error   i { color: #f87171; }

  /* ── RESPONSIVE ── */
  @media (max-width: 1024px) {
    .cart-body { grid-template-columns: 1fr; padding: 2rem 1.5rem; }
    .summary-panel { position: static; }
  }
  @media (max-width: 860px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links, .nav-signin { display: none; }
    .catalog-header, .breadcrumb-bar { padding-left: 1.5rem; padding-right: 1.5rem; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
    footer { padding: 4rem 1.5rem 2rem; }
  }
  @media (max-width: 520px) {
    .cart-item { flex-wrap: wrap; }
    .cart-item-img { width: 72px; height: 72px; }
    .cart-item-price { width: 100%; text-align: left; margin-top: 0.5rem; }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="catalog.php">Shop</a></li>
    <li><a href="story.php">Our Story</a></li>
    <li><a href="booking.php">Live Session</a></li>
  </ul>
  <div class="nav-icons">
    <button aria-label="Search"><i class="fas fa-search"></i></button>
    <a href="wishlist.php" aria-label="Wishlist"><i class="far fa-heart"></i></a>
    <a href="cart.php" aria-label="Cart" style="position:relative">
      <i class="fas fa-shopping-bag"></i>
      <span class="cart-badge" id="navCartCount"><?= $itemCount ?></span>
    </a>
    <a href="login.php" class="nav-signin">Sign In</a>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
  <a href="index.php">Home</a>
  <i class="fas fa-chevron-right"></i>
  <span>Shopping Cart</span>
</div>

<!-- PAGE HEADER -->
<div class="catalog-header">
  <div>
    <span class="tag">Your Selection</span>
    <h1>Shopping <em>Cart</em></h1>
  </div>
  <div class="header-meta" id="headerItemCount">
    <?= $itemCount ?> <?= $itemCount === 1 ? 'item' : 'items' ?> in your cart
  </div>
</div>

<!-- CART BODY -->
<div class="cart-body">

  <?php if (empty($cartItems)): ?>

    <!-- ── EMPTY STATE ── -->
    <div class="empty-state">
      <i class="fas fa-shopping-bag"></i>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet. Explore our handcrafted collection.</p>
      <a href="/catalog.php" class="btn-shop-now">
        <i class="fas fa-arrow-right"></i> Browse Collection
      </a>
    </div>

  <?php else: ?>

    <!-- ── LEFT: CART ITEMS ── -->
    <div class="form-panel">

      <!-- Free shipping progress -->
      <?php
        $freeAt    = 5000;
        $remaining = max(0, $freeAt - $subtotal);
        $pct       = min(100, round($subtotal / $freeAt * 100));
      ?>
      <div class="shipping-bar-wrap" id="shippingBar">
        <div class="shipping-bar-label">
          <?php if ($remaining > 0): ?>
            <span>Add <strong>LKR <?= number_format($remaining) ?></strong> more for free shipping</span>
          <?php else: ?>
            <span>🎉 You've unlocked <strong>free shipping!</strong></span>
          <?php endif; ?>
          <span><?= $pct ?>%</span>
        </div>
        <div class="shipping-bar-track">
          <div class="shipping-bar-fill" id="shippingFill" style="width:<?= $pct ?>%"></div>
        </div>
      </div>

      <!-- Items -->
      <div class="cart-panel">
        <div class="cart-section-header">
          <h2>Your Items</h2>
          <button class="btn-clear-cart" onclick="clearCart()">
            <i class="fas fa-trash-alt" style="font-size:0.7rem"></i> Clear all
          </button>
        </div>

        <div id="cartItemsContainer">
          <?php foreach ($cartItems as $item): ?>
          <div class="cart-item" id="item-<?= $item['id'] ?>">

            <img
              src="<?= htmlspecialchars($item['image']) ?>"
              alt="<?= htmlspecialchars($item['name']) ?>"
              class="cart-item-img"
              onerror="this.src='images/placeholder.jpg'"
            >

            <div class="cart-item-body">
              <div class="cart-item-cat"><?= htmlspecialchars($item['category'] ?? 'Batik') ?></div>
              <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>

              <?php if ($item['variant']): ?>
              <span class="cart-item-variant">
                <i class="fas fa-ruler" style="font-size:0.6rem"></i>
                Size: <?= htmlspecialchars($item['variant']) ?>
              </span>
              <?php endif; ?>

              <div class="cart-item-actions">
                <div class="stepper">
                  <button class="stepper-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">
                    <i class="fas fa-minus" style="font-size:0.65rem"></i>
                  </button>
                  <span class="stepper-val" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                  <button class="stepper-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">
                    <i class="fas fa-plus" style="font-size:0.65rem"></i>
                  </button>
                </div>
                <button class="btn-remove" onclick="removeItem(<?= $item['id'] ?>)">
                  <i class="far fa-trash-alt" style="font-size:0.75rem"></i> Remove
                </button>
              </div>
            </div>

            <div class="cart-item-price">
              <div class="total" id="itemTotal-<?= $item['id'] ?>">
                LKR <?= number_format($item['price'] * $item['quantity']) ?>
              </div>
              <div class="unit">LKR <?= number_format($item['price']) ?> each</div>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

        <a href="catalog.php" class="continue-link">
          <i class="fas fa-arrow-left" style="font-size:0.75rem"></i>
          Continue Shopping
        </a>
      </div>
    </div>

    <!-- ── RIGHT: ORDER SUMMARY ── -->
    <div class="summary-panel">
      <div class="summary-card">
        <div class="summary-card-header">
          <i class="fas fa-receipt" style="opacity:0.6"></i>
          <h3>Order Summary</h3>
        </div>
        <div class="summary-card-body">

          <div class="summary-row">
            <span class="s-label">Subtotal (<span id="summaryCount"><?= $itemCount ?></span> items)</span>
            <span class="s-value" id="summarySubtotal">LKR <?= number_format($subtotal) ?></span>
          </div>
          <div class="summary-row">
            <span class="s-label">Shipping</span>
            <span class="s-value <?= $shipping == 0 ? 'free' : '' ?>" id="summaryShipping">
              <?= $shipping == 0 ? 'Free' : 'LKR '.number_format($shipping) ?>
            </span>
          </div>
          <div class="summary-row" id="discountRow" style="display:none">
            <span class="s-label">Discount</span>
            <span class="s-value free" id="summaryDiscount">— LKR 0</span>
          </div>
          <div class="summary-row total">
            <span class="s-label">Total</span>
            <span class="s-value" id="summaryTotal">LKR <?= number_format($total) ?></span>
          </div>

          <!-- Promo Code -->
          <div class="promo-wrap">
            <div class="promo-field">
              <input type="text" id="promoInput" placeholder="Promo code">
              <button class="btn-promo" onclick="applyPromo()">Apply</button>
            </div>
            <div class="promo-msg" id="promoMsg"></div>
          </div>

          <a href="/checkout.php" class="btn-checkout" id="checkoutBtn">
            <i class="fas fa-lock" style="font-size:0.8rem"></i>
            Proceed to Checkout
          </a>

        </div>
      </div>

      <!-- Trust badges -->
      <div class="trust-badges">
        <div class="trust-item"><i class="fas fa-shield-alt"></i> Secure checkout via PayHere</div>
        <div class="trust-item"><i class="fas fa-undo"></i> Free returns within 14 days</div>
        <div class="trust-item"><i class="fas fa-shipping-fast"></i> Ships from Kandy in 1–2 days</div>
        <div class="trust-item"><i class="fas fa-headset"></i> Need help? +94 77 123 4567</div>
      </div>
    </div>

  <?php endif; ?>

</div><!-- /cart-body -->

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div>
      <span class="footer-logo">Batik<span>SL</span></span>
      <p class="footer-about">Handcrafted batik from the heart of Kandy, Sri Lanka. Every piece is a living piece of cultural heritage, made with natural dyes and generations of knowledge.</p>
      <div style="margin-top:1.5rem;font-size:0.78rem;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.3)">Est. 1989</div>
    </div>
    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="/catalog.php?category=fabric">Fabric Yardage</a></li>
        <li><a href="/catalog.php?category=clothing">Clothing</a></li>
        <li><a href="/catalog.php?category=home_decor">Home Décor</a></li>
        <li><a href="/catalog.php?category=accessories">Accessories</a></li>
        <li><a href="/catalog.php?new=1">New Arrivals</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">Shipping Info</a></li>
        <li><a href="#">Returns Policy</a></li>
        <li><a href="#">Size Guide</a></li>
        <li><a href="#">Care Instructions</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Connect</h4>
      <div class="social-icons">
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
      </div>
      <div style="font-size:0.88rem;line-height:1.9">
        <div><i class="fas fa-phone" style="color:var(--teal-light);margin-right:0.5rem;font-size:0.8rem"></i>+94 77 123 4567</div>
        <div><i class="fas fa-envelope" style="color:var(--teal-light);margin-right:0.5rem;font-size:0.8rem"></i>hello@batiksl.com</div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div>© 2025 BatikSL. All rights reserved. Handcrafted with ❤️ in Sri Lanka.</div>
    <div style="display:flex;gap:1.5rem">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
    </div>
  </div>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">Done</span>
</div>

<script>
  const FREE_SHIPPING_THRESHOLD = 5000;
  const SHIPPING_FLAT           = 500;

  // ── Valid promo codes ──
  const PROMO_CODES = {
    'BATIK10':  { type: 'percent', value: 10,   label: '10% off applied!' },
    'KANDY500': { type: 'fixed',   value: 500,  label: 'LKR 500 off applied!' },
    'WELCOME':  { type: 'percent', value: 15,   label: '15% welcome discount!' },
  };

  let activePromo   = null;
  let currentSubtotal = <?= $subtotal ?>;

  // ── Toast ──
  function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const icon = t.querySelector('i');
    document.getElementById('toastMsg').textContent = msg;
    t.className = `toast ${type}`;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  }

  // ── AJAX cart action ──
  function cartAction(data) {
    return fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data).toString(),
    }).then(r => r.json());
  }

  // ── Format LKR ──
  function fmtLKR(n) {
    return 'LKR ' + Math.round(n).toLocaleString('en-LK');
  }

  // ── Update summary UI ──
  function updateSummary(data) {
    currentSubtotal = data.subtotal;
    const discount  = activePromo ? calcDiscount(data.subtotal) : 0;
    const shipping  = data.subtotal > FREE_SHIPPING_THRESHOLD ? 0 : (data.cart.length ? SHIPPING_FLAT : 0);
    const total     = data.subtotal - discount + shipping;

    document.getElementById('summarySubtotal').textContent = fmtLKR(data.subtotal);
    document.getElementById('summaryCount').textContent    = data.count;
    document.getElementById('headerItemCount').textContent = data.count + (data.count === 1 ? ' item' : ' items') + ' in your cart';
    document.getElementById('navCartCount').textContent    = data.count;

    const shippingEl = document.getElementById('summaryShipping');
    shippingEl.textContent  = shipping === 0 ? 'Free' : fmtLKR(shipping);
    shippingEl.className    = 's-value' + (shipping === 0 ? ' free' : '');

    document.getElementById('summaryTotal').textContent = fmtLKR(total);

    // Shipping bar
    const pct = Math.min(100, Math.round(data.subtotal / FREE_SHIPPING_THRESHOLD * 100));
    document.getElementById('shippingFill').style.width = pct + '%';
    const remaining = Math.max(0, FREE_SHIPPING_THRESHOLD - data.subtotal);
    const barLabel  = document.querySelector('.shipping-bar-label span:first-child');
    if (remaining > 0) {
      barLabel.innerHTML = `Add <strong>${fmtLKR(remaining)}</strong> more for free shipping`;
    } else {
      barLabel.innerHTML = `🎉 You've unlocked <strong>free shipping!</strong>`;
    }
    document.querySelector('.shipping-bar-label span:last-child').textContent = pct + '%';

    // Discount row
    if (discount > 0) {
      document.getElementById('discountRow').style.display = 'flex';
      document.getElementById('summaryDiscount').textContent = '— ' + fmtLKR(discount);
    }
  }

  // ── Calculate discount amount ──
  function calcDiscount(subtotal) {
    if (!activePromo) return 0;
    const p = PROMO_CODES[activePromo];
    if (!p) return 0;
    return p.type === 'percent' ? Math.round(subtotal * p.value / 100) : p.value;
  }

  // ── Update quantity ──
  function updateQty(id, delta) {
    const qtyEl = document.getElementById('qty-' + id);
    const current = parseInt(qtyEl.textContent);
    const newQty  = Math.max(1, current + delta);
    if (newQty === current) return;

    qtyEl.textContent = newQty;

    cartAction({ action: 'update', id, quantity: newQty }).then(data => {
      if (!data.success) return;

      // Update item line total
      const item   = data.cart.find(i => i.id == id);
      if (item) {
        document.getElementById('itemTotal-' + id).textContent =
          fmtLKR(item.price * item.quantity);
      }
      updateSummary(data);
      showToast('Cart updated');
    });
  }

  // ── Remove item ──
  function removeItem(id) {
    const el = document.getElementById('item-' + id);
    el.classList.add('removing');

    setTimeout(() => {
      el.remove();
      cartAction({ action: 'remove', id }).then(data => {
        if (!data.success) return;
        updateSummary(data);
        showToast('Item removed', 'success');

        // If cart is now empty, reload to show empty state
        if (data.cart.length === 0) {
          setTimeout(() => location.reload(), 500);
        }
      });
    }, 300);
  }

  // ── Clear cart ──
  function clearCart() {
    if (!confirm('Remove all items from your cart?')) return;
    cartAction({ action: 'clear' }).then(data => {
      if (data.success) setTimeout(() => location.reload(), 300);
    });
  }

  // ── Promo code ──
  function applyPromo() {
    const code    = document.getElementById('promoInput').value.trim().toUpperCase();
    const msgEl   = document.getElementById('promoMsg');
    const promo   = PROMO_CODES[code];

    if (!code) {
      msgEl.textContent = 'Please enter a promo code.';
      msgEl.className   = 'promo-msg error';
      return;
    }

    if (promo) {
      activePromo = code;
      const discount = calcDiscount(currentSubtotal);
      msgEl.textContent = promo.label;
      msgEl.className   = 'promo-msg success';

      document.getElementById('discountRow').style.display = 'flex';
      document.getElementById('summaryDiscount').textContent = '— ' + fmtLKR(discount);

      // Recalculate total
      const shipping = currentSubtotal > FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FLAT;
      const total    = currentSubtotal - discount + shipping;
      document.getElementById('summaryTotal').textContent = fmtLKR(total);

      showToast(promo.label);
    } else {
      activePromo = null;
      msgEl.textContent = 'Invalid promo code.';
      msgEl.className   = 'promo-msg error';
    }
  }

  // Allow Enter key on promo input
  document.getElementById('promoInput')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') applyPromo();
  });
</script>

</body>
</html>