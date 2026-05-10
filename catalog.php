<?php
// catalog.php - Product Catalog with Sidebar Filters
require_once 'config/database.php';

$category  = $_GET['category']  ?? '';
$sort      = $_GET['sort']      ?? 'newest';
$minPrice  = $_GET['min_price'] ?? 0;
$maxPrice  = $_GET['max_price'] ?? 100000;

$query  = "SELECT p.*, pi.image_path FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.sort_order = 0 WHERE p.status=1";
$params = [];

if ($category && $category != 'all') {
    $query .= " AND p.category = :category";
    $params['category'] = $category;
}
if ($minPrice) {
    $query .= " AND p.price >= :minPrice";
    $params['minPrice'] = $minPrice;
}
if ($maxPrice && $maxPrice < 100000) {
    $query .= " AND p.price <= :maxPrice";
    $params['maxPrice'] = $maxPrice;
}

switch ($sort) {
    case 'price_low':  $query .= " ORDER BY p.price ASC";        break;
    case 'price_high': $query .= " ORDER BY p.price DESC";       break;
    case 'popular':    $query .= " ORDER BY p.id DESC";          break;
    default:           $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$allProducts = $stmt->fetchAll();

// Pagination
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;
$total      = count($allProducts);
$totalPages = ceil($total / $perPage);
$products   = array_slice($allProducts, ($page - 1) * $perPage, $perPage);

$categoryLabels = [
    ''           => 'All Products',
    'all'        => 'All Products',
    'fabric'     => 'Fabric Yardage',
    'clothing'   => 'Clothing',
    'home_decor' => 'Home Décor',
    'accessories'=> 'Accessories & Scarves',
];
$pageTitle = $categoryLabels[$category] ?? 'All Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Batik | BatikSL</title>
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
    position: sticky; top: 0; left: 0; right: 0; z-index: 1000;
    padding: 1.1rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(250,247,242,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }
  .nav-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; color: var(--charcoal);
    text-decoration: none;
  }
  .nav-logo span { color: var(--gold); }
  .nav-links { display: flex; gap: 2.5rem; list-style: none; }
  .nav-links a {
    font-size: 0.82rem; font-weight: 500; letter-spacing: 0.08em;
    text-transform: uppercase; text-decoration: none;
    color: var(--warm-gray); transition: color 0.25s;
  }
  .nav-links a:hover, .nav-links a.active { color: var(--teal); }
  .nav-icons { display: flex; align-items: center; gap: 1.4rem; }
  .nav-icons a, .nav-icons button {
    background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 1.05rem;
    text-decoration: none; transition: color 0.25s; position: relative;
  }
  .nav-icons a:hover, .nav-icons button:hover { color: var(--teal); }
  .cart-badge {
    position: absolute; top: -6px; right: -8px;
    background: var(--gold); color: white;
    font-size: 0.62rem; font-weight: 700;
    width: 17px; height: 17px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
  }
  .nav-signin {
    padding: 0.45rem 1.3rem;
    border: 1.5px solid var(--teal); border-radius: 2rem;
    font-size: 0.78rem; font-weight: 500; letter-spacing: 0.05em;
    text-transform: uppercase; color: var(--teal);
    text-decoration: none; transition: all 0.25s;
  }
  .nav-signin:hover { background: var(--teal); color: white; }

  /* ── BREADCRUMB ── */
  .breadcrumb-bar {
    background: var(--sand);
    padding: 1rem 4rem;
    display: flex; align-items: center; gap: 0.6rem;
    font-size: 0.8rem; color: var(--warm-gray);
  }
  .breadcrumb-bar a { color: inherit; text-decoration: none; transition: color 0.2s; }
  .breadcrumb-bar a:hover { color: var(--teal); }
  .breadcrumb-bar i { font-size: 0.6rem; opacity: 0.5; }

  /* ── PAGE HEADER ── */
  .catalog-header {
    padding: 3rem 4rem 2rem;
    border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: flex-end;
    flex-wrap: wrap; gap: 1rem;
  }
  .catalog-header .tag {
    font-size: 0.72rem; font-weight: 500; letter-spacing: 0.2em;
    text-transform: uppercase; color: var(--teal);
    margin-bottom: 0.5rem; display: block;
  }
  .catalog-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 700; line-height: 1.15;
  }
  .catalog-header h1 em { font-style: italic; color: var(--teal); }
  .result-count {
    font-size: 0.85rem; color: var(--warm-gray);
  }
  .result-count strong { color: var(--charcoal); font-weight: 500; }

  /* ── LAYOUT ── */
  .catalog-body {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 0;
    min-height: 70vh;
    align-items: start;
  }

  /* ── SIDEBAR ── */
  aside {
    border-right: 1px solid var(--border);
    padding: 2.5rem 2rem;
    position: sticky; top: 72px;
    max-height: calc(100vh - 72px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
  }
  aside::-webkit-scrollbar { width: 4px; }
  aside::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

  .filter-section { margin-bottom: 2.2rem; }
  .filter-section:last-of-type { margin-bottom: 0; }

  .filter-label {
    font-size: 0.7rem; font-weight: 500; letter-spacing: 0.15em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 1rem; display: block;
  }

  /* Category pills */
  .cat-pills { display: flex; flex-direction: column; gap: 0.3rem; }
  .cat-pill {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.6rem 0.9rem; border-radius: 8px;
    cursor: pointer; text-decoration: none;
    font-size: 0.88rem; color: var(--warm-gray);
    transition: all 0.2s; border: 1.5px solid transparent;
  }
  .cat-pill:hover { background: var(--sand); color: var(--charcoal); }
  .cat-pill.active {
    background: #e6f4f2; border-color: var(--teal);
    color: var(--teal); font-weight: 500;
  }
  .cat-pill .count {
    font-size: 0.72rem; color: var(--warm-gray);
    background: var(--sand); padding: 0.1rem 0.5rem; border-radius: 2rem;
  }
  .cat-pill.active .count { background: rgba(15,118,110,0.12); color: var(--teal); }

  /* Price range */
  .price-inputs { display: flex; gap: 0.6rem; align-items: center; }
  .price-input-wrap { flex: 1; position: relative; }
  .price-input-wrap span {
    position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%);
    font-size: 0.78rem; color: var(--warm-gray); pointer-events: none;
  }
  .price-input-wrap input {
    width: 100%; padding: 0.55rem 0.6rem 0.55rem 2rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 0.85rem;
    color: var(--charcoal); background: white;
    transition: border-color 0.2s; outline: none;
  }
  .price-input-wrap input:focus { border-color: var(--teal); }
  .price-sep { font-size: 0.8rem; color: var(--warm-gray); flex-shrink: 0; }

  /* Sort select */
  .styled-select {
    width: 100%; padding: 0.6rem 2.2rem 0.6rem 0.9rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
    color: var(--charcoal); background: white;
    appearance: none; cursor: pointer; outline: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b6560' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.9rem center;
    transition: border-color 0.2s;
  }
  .styled-select:focus { border-color: var(--teal); }

  /* Filter buttons */
  .filter-btn-row { display: flex; gap: 0.6rem; margin-top: 2rem; }
  .btn-apply {
    flex: 1; padding: 0.65rem;
    background: var(--teal); color: white; border: none;
    border-radius: 8px; font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem; font-weight: 500; cursor: pointer;
    transition: background 0.25s;
  }
  .btn-apply:hover { background: var(--teal-light); }
  .btn-reset {
    padding: 0.65rem 1rem;
    background: none; color: var(--warm-gray);
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 0.84rem;
    cursor: pointer; transition: all 0.25s;
  }
  .btn-reset:hover { border-color: var(--charcoal); color: var(--charcoal); }

  /* Active filters */
  .active-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.8rem; }
  .filter-tag {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.7rem; background: var(--sand);
    border-radius: 2rem; font-size: 0.76rem; color: var(--warm-gray);
    border: 1px solid var(--border);
  }
  .filter-tag button {
    background: none; border: none; cursor: pointer;
    color: inherit; font-size: 0.7rem; line-height: 1;
    padding: 0; display: flex; align-items: center;
  }

  /* ── PRODUCT AREA ── */
  .product-area { padding: 2rem 2.5rem; }

  /* Top bar */
  .product-topbar {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.8rem; flex-wrap: wrap; gap: 0.8rem;
  }
  .view-toggle { display: flex; gap: 0.4rem; }
  .view-btn {
    width: 36px; height: 36px; border-radius: 8px;
    background: none; border: 1.5px solid var(--border);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--warm-gray); font-size: 0.9rem; transition: all 0.2s;
  }
  .view-btn.active, .view-btn:hover {
    border-color: var(--teal); color: var(--teal); background: #e6f4f2;
  }
  .sort-inline { display: flex; align-items: center; gap: 0.6rem; }
  .sort-inline label { font-size: 0.8rem; color: var(--warm-gray); }
  .sort-inline select {
    padding: 0.4rem 1.8rem 0.4rem 0.7rem;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: 'DM Sans', sans-serif; font-size: 0.82rem;
    color: var(--charcoal); background: white; appearance: none;
    cursor: pointer; outline: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b6560' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.6rem center;
    transition: border-color 0.2s;
  }
  .sort-inline select:focus { border-color: var(--teal); outline: none; }

  /* Product grid */
  .product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }
  .product-grid.list-view {
    grid-template-columns: 1fr;
  }

  .product-card {
    background: white; border-radius: 14px;
    overflow: hidden; transition: transform 0.35s, box-shadow 0.35s;
    position: relative; border: 1px solid transparent;
  }
  .product-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(0,0,0,0.1); }

  .product-img-wrap {
    position: relative; overflow: hidden; height: 280px;
  }
  .product-grid.list-view .product-img-wrap {
    height: 220px; width: 260px; flex-shrink: 0; border-radius: 0;
  }
  .product-grid.list-view .product-card {
    display: flex; flex-direction: row;
  }
  .product-grid.list-view .product-info {
    flex: 1; display: flex; flex-direction: column; justify-content: space-between;
  }

  .product-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.55s ease;
  }
  .product-card:hover .product-img-wrap img { transform: scale(1.05); }

  .product-badge {
    position: absolute; top: 0.9rem; left: 0.9rem;
    font-size: 0.66rem; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; padding: 0.28rem 0.7rem; border-radius: 2rem;
  }
  .badge-gold { background: var(--gold); color: white; }
  .badge-green { background: #16a34a; color: white; }
  .badge-red { background: #dc2626; color: white; }
  .badge-orange { background: #ea580c; color: white; }

  .stock-dot {
    position: absolute; bottom: 0.9rem; left: 0.9rem;
    display: flex; align-items: center; gap: 0.4rem;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);
    padding: 0.25rem 0.65rem; border-radius: 2rem;
    font-size: 0.7rem; color: white;
  }
  .stock-dot::before {
    content: ''; width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  }
  .stock-in::before  { background: #4ade80; }
  .stock-low::before { background: #fb923c; }
  .stock-out::before { background: #f87171; }

  .wishlist-btn {
    position: absolute; top: 0.9rem; right: 0.9rem;
    width: 34px; height: 34px; border-radius: 50%;
    background: white; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    color: #ccc; font-size: 0.9rem;
    transition: color 0.2s, transform 0.2s;
  }
  .wishlist-btn:hover { color: #e05252; transform: scale(1.1); }

  .product-info { padding: 1.1rem 1.3rem 1.4rem; }
  .product-category {
    font-size: 0.68rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.35rem;
  }
  .product-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem; font-weight: 700;
    color: var(--charcoal); line-height: 1.35; margin-bottom: 0.9rem;
  }
  .product-footer {
    display: flex; align-items: center; justify-content: space-between;
  }
  .product-price {
    font-size: 1.08rem; font-weight: 500; color: var(--teal);
  }
  .product-price small {
    font-size: 0.72rem; color: var(--warm-gray); font-weight: 400; margin-left: 0.25rem;
  }
  .add-cart-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--charcoal); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.82rem;
    transition: background 0.25s, transform 0.2s;
  }
  .add-cart-btn:hover { background: var(--teal); transform: scale(1.1); }
  .add-cart-btn.added { background: var(--teal); }

  /* List view extras */
  .product-desc {
    font-size: 0.85rem; color: var(--warm-gray);
    line-height: 1.6; margin-bottom: 1rem;
    display: none;
  }
  .product-grid.list-view .product-desc { display: block; }
  .product-grid.list-view .add-cart-btn {
    width: auto; border-radius: 8px; padding: 0 1.2rem;
    font-size: 0.82rem; gap: 0.5rem;
  }
  .product-grid.list-view .add-cart-btn::after { content: ' Add to Cart'; }

  /* Empty state */
  .empty-state {
    grid-column: 1 / -1;
    text-align: center; padding: 6rem 2rem;
    color: var(--warm-gray);
  }
  .empty-state i {
    font-size: 3rem; margin-bottom: 1rem;
    color: var(--border); display: block;
  }
  .empty-state h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--charcoal);
  }

  /* ── PAGINATION ── */
  .pagination {
    display: flex; justify-content: center; align-items: center;
    gap: 0.5rem; margin-top: 3.5rem; padding-bottom: 1rem;
  }
  .page-btn {
    min-width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; font-size: 0.88rem;
    border: 1.5px solid var(--border);
    color: var(--warm-gray); background: white;
    transition: all 0.2s;
  }
  .page-btn:hover { border-color: var(--teal); color: var(--teal); }
  .page-btn.active {
    background: var(--teal); border-color: var(--teal);
    color: white; font-weight: 500;
  }
  .page-btn.disabled { opacity: 0.4; pointer-events: none; }

  /* ── FOOTER ── */
  footer {
    background: #111; color: rgba(255,255,255,0.6);
    padding: 5rem 4rem 2.5rem;
    margin-top: 6rem;
  }
  .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 4rem; }
  .footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem; color: white; margin-bottom: 1rem; display: block;
  }
  .footer-logo span { color: var(--gold); }
  .footer-about { font-size: 0.88rem; line-height: 1.7; max-width: 280px; }
  .footer-col h4 {
    color: white; font-size: 0.78rem; font-weight: 500;
    letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1.4rem;
  }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 0.7rem; }
  .footer-col ul a { text-decoration: none; color: inherit; font-size: 0.88rem; transition: color 0.2s; }
  .footer-col ul a:hover { color: var(--teal-light); }
  .social-icons { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
  .social-icon {
    width: 40px; height: 40px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    color: inherit; text-decoration: none; font-size: 0.95rem; transition: all 0.25s;
  }
  .social-icon:hover { border-color: var(--teal-light); color: var(--teal-light); }
  .footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.06);
    margin-top: 4rem; padding-top: 2rem;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 1rem; font-size: 0.8rem;
  }
  .footer-bottom a { color: inherit; text-decoration: none; }
  .footer-bottom a:hover { color: var(--teal-light); }

  /* ── RESPONSIVE ── */
  @media (max-width: 1100px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 860px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links, .nav-signin { display: none; }
    .catalog-header, .breadcrumb-bar { padding-left: 1.5rem; padding-right: 1.5rem; }
    .catalog-body { grid-template-columns: 1fr; }
    aside { position: static; max-height: none; border-right: none; border-bottom: 1px solid var(--border); padding: 1.5rem; }
    .product-area { padding: 1.5rem; }
    .product-grid { grid-template-columns: repeat(2, 1fr); }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
    footer { padding: 4rem 1.5rem 2rem; }
  }
  @media (max-width: 520px) {
    .product-grid { grid-template-columns: 1fr; }
    .product-grid.list-view .product-card { flex-direction: column; }
    .product-grid.list-view .product-img-wrap { width: 100%; height: 240px; }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="catalog.php" class="active">Shop</a></li>
    <li><a href="story.php">Our Story</a></li>
    <li><a href="booking.php">Live Session</a></li>
    <li><a href="account.php">Account</a></li>
  </ul>
  <div class="nav-icons">
    <button aria-label="Search"><i class="fas fa-search"></i></button>
    <a href="wishlist.php" aria-label="Wishlist"><i class="far fa-heart"></i></a>
    <a href="cart.php" aria-label="Cart" style="position:relative">
      <i class="fas fa-shopping-bag"></i>
      <span class="cart-badge">0</span>
    </a>
    <a href="login.php" class="nav-signin">Sign In</a>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
  <a href="index.php">Home</a>
  <i class="fas fa-chevron-right"></i>
  <a href="catalog.php">Shop</a>
  <?php if ($category && $category !== 'all'): ?>
  <i class="fas fa-chevron-right"></i>
  <span><?= htmlspecialchars($categoryLabels[$category] ?? ucfirst($category)) ?></span>
  <?php endif; ?>
</div>

<!-- PAGE HEADER -->
<div class="catalog-header">
  <div>
    <span class="tag">Handcrafted Collection</span>
    <h1><?= $pageTitle !== 'All Products' ? htmlspecialchars($pageTitle) . ' — <em>Batik</em>' : 'All <em>Creations</em>' ?></h1>
  </div>
  <div class="result-count">
    Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?></strong> pieces
  </div>
</div>

<!-- BODY -->
<div class="catalog-body">

  <!-- SIDEBAR -->
  <aside>

    <!-- Category -->
    <div class="filter-section">
      <span class="filter-label">Category</span>
      <div class="cat-pills">
        <a href="catalog.php" class="cat-pill <?= (!$category || $category === 'all') ? 'active' : '' ?>">
          All Products <span class="count">—</span>
        </a>
        <a href="?category=fabric&sort=<?= urlencode($sort) ?>" class="cat-pill <?= $category === 'fabric' ? 'active' : '' ?>">
          Fabric Yardage <span class="count">36</span>
        </a>
        <a href="?category=clothing&sort=<?= urlencode($sort) ?>" class="cat-pill <?= $category === 'clothing' ? 'active' : '' ?>">
          Clothing <span class="count">44</span>
        </a>
        <a href="?category=home_decor&sort=<?= urlencode($sort) ?>" class="cat-pill <?= $category === 'home_decor' ? 'active' : '' ?>">
          Home Décor <span class="count">28</span>
        </a>
        <a href="?category=accessories&sort=<?= urlencode($sort) ?>" class="cat-pill <?= $category === 'accessories' ? 'active' : '' ?>">
          Accessories <span class="count">19</span>
        </a>
      </div>
    </div>

    <!-- Price Range -->
    <div class="filter-section">
      <span class="filter-label">Price Range (LKR)</span>
      <div class="price-inputs">
        <div class="price-input-wrap">
          <span>LKR</span>
          <input type="number" id="minPrice" placeholder="0" value="<?= $minPrice ?: '' ?>">
        </div>
        <span class="price-sep">—</span>
        <div class="price-input-wrap">
          <span>LKR</span>
          <input type="number" id="maxPrice" placeholder="100,000" value="<?= ($maxPrice < 100000) ? $maxPrice : '' ?>">
        </div>
      </div>
    </div>

    <!-- Sort -->
    <div class="filter-section">
      <span class="filter-label">Sort By</span>
      <select id="sortBy" class="styled-select">
        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest First</option>
        <option value="price_low"  <?= $sort === 'price_low'  ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="popular"    <?= $sort === 'popular'    ? 'selected' : '' ?>>Most Popular</option>
      </select>
    </div>

    <!-- Active filter tags -->
    <?php if ($category || $minPrice || ($maxPrice && $maxPrice < 100000)): ?>
    <div class="filter-section">
      <span class="filter-label">Active Filters</span>
      <div class="active-filters">
        <?php if ($category): ?>
        <span class="filter-tag">
          <?= htmlspecialchars($categoryLabels[$category] ?? $category) ?>
          <button onclick="clearFilter('category')" aria-label="Remove"><i class="fas fa-times"></i></button>
        </span>
        <?php endif; ?>
        <?php if ($minPrice): ?>
        <span class="filter-tag">
          Min LKR <?= number_format($minPrice) ?>
          <button onclick="clearFilter('min_price')" aria-label="Remove"><i class="fas fa-times"></i></button>
        </span>
        <?php endif; ?>
        <?php if ($maxPrice && $maxPrice < 100000): ?>
        <span class="filter-tag">
          Max LKR <?= number_format($maxPrice) ?>
          <button onclick="clearFilter('max_price')" aria-label="Remove"><i class="fas fa-times"></i></button>
        </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Buttons -->
    <div class="filter-btn-row">
      <button class="btn-apply" onclick="applyFilters()">Apply Filters</button>
      <button class="btn-reset" onclick="resetFilters()"><i class="fas fa-undo" style="font-size:0.75rem"></i></button>
    </div>

  </aside>

  <!-- PRODUCT AREA -->
  <div class="product-area">

    <!-- Top bar -->
    <div class="product-topbar">
      <div class="view-toggle">
        <button class="view-btn active" id="gridBtn" onclick="setView('grid')" title="Grid view"><i class="fas fa-th"></i></button>
        <button class="view-btn" id="listBtn" onclick="setView('list')" title="List view"><i class="fas fa-list"></i></button>
      </div>
      <div class="sort-inline">
        <label>Sort:</label>
        <select onchange="quickSort(this.value)">
          <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest</option>
          <option value="price_low"  <?= $sort === 'price_low'  ? 'selected' : '' ?>>Price ↑</option>
          <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price ↓</option>
          <option value="popular"    <?= $sort === 'popular'    ? 'selected' : '' ?>>Popular</option>
        </select>
      </div>
    </div>

    <!-- Grid -->
    <div class="product-grid" id="productGrid">
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <i class="fas fa-search"></i>
          <h3>No pieces found</h3>
          <p>Try adjusting your filters or <a href="catalog.php" style="color:var(--teal)">browse everything</a>.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($products as $product):
        $stock = (int)($product['stock'] ?? 15);
        if ($stock > 10) {
            $stockLabel = 'In Stock';
            $stockClass = 'stock-in';
        } elseif ($stock > 0) {
            $stockLabel = 'Low Stock';
            $stockClass = 'stock-low';
        } else {
            $stockLabel = 'Out of Stock';
            $stockClass = 'stock-out';
        }
        $catLabel = $categoryLabels[$product['category'] ?? ''] ?? ucfirst($product['category'] ?? '');
      ?>
      <div class="product-card">
        <div class="product-img-wrap">
          <img
            src="<?= htmlspecialchars($product['image_path'] ?? '/images/placeholder.jpg') ?>"
            alt="<?= htmlspecialchars($product['name']) ?>"
            loading="lazy">

          <?php if ($stock === 0): ?>
            <span class="product-badge badge-red">Sold Out</span>
          <?php elseif (isset($product['is_new']) && $product['is_new']): ?>
            <span class="product-badge badge-green">New</span>
          <?php elseif (isset($product['is_featured']) && $product['is_featured']): ?>
            <span class="product-badge badge-gold">Featured</span>
          <?php endif; ?>

          <span class="stock-dot <?= $stockClass ?>"><?= $stockLabel ?></span>

          <button class="wishlist-btn" data-id="<?= $product['id'] ?>" onclick="toggleWishlist(this)" aria-label="Add to wishlist">
            <i class="far fa-heart"></i>
          </button>
        </div>

        <div class="product-info">
          <div class="product-category"><?= htmlspecialchars($catLabel) ?></div>
          <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
          <p class="product-desc"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 100)) ?>…</p>
          <div class="product-footer">
            <span class="product-price">
              LKR <?= number_format($product['price']) ?>
              <small>/ piece</small>
            </span>
            <button
              class="add-cart-btn <?= $stock === 0 ? 'disabled' : '' ?>"
              onclick="addToCart(<?= $product['id'] ?>, this)"
              <?= $stock === 0 ? 'disabled title="Out of stock"' : '' ?>>
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <!-- Prev -->
      <a href="?page=<?= max(1, $page - 1) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>"
         class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
        <i class="fas fa-chevron-left" style="font-size:0.7rem"></i>
      </a>

      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        if ($start > 1): ?>
          <a href="?page=1&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>" class="page-btn">1</a>
          <?php if ($start > 2): ?><span style="color:var(--warm-gray);padding:0 0.2rem">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?= $i ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($end < $totalPages): ?>
          <?php if ($end < $totalPages - 1): ?><span style="color:var(--warm-gray);padding:0 0.2rem">…</span><?php endif; ?>
          <a href="?page=<?= $totalPages ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>" class="page-btn"><?= $totalPages ?></a>
      <?php endif; ?>

      <!-- Next -->
      <a href="?page=<?= min($totalPages, $page + 1) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>"
         class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <i class="fas fa-chevron-right" style="font-size:0.7rem"></i>
      </a>
    </div>
    <?php endif; ?>

  </div><!-- /product-area -->
</div><!-- /catalog-body -->

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

<script>
  // ── View toggle ──
  function setView(mode) {
    const grid = document.getElementById('productGrid');
    const gBtn = document.getElementById('gridBtn');
    const lBtn = document.getElementById('listBtn');
    if (mode === 'list') {
      grid.classList.add('list-view');
      lBtn.classList.add('active');
      gBtn.classList.remove('active');
    } else {
      grid.classList.remove('list-view');
      gBtn.classList.add('active');
      lBtn.classList.remove('active');
    }
    localStorage.setItem('batiksl_view', mode);
  }
  // Restore saved view
  const savedView = localStorage.getItem('batiksl_view');
  if (savedView === 'list') setView('list');

  // ── Filters ──
  function applyFilters() {
    const category = '<?= urlencode($category) ?>';
    const minPrice = document.getElementById('minPrice').value || 0;
    const maxPrice = document.getElementById('maxPrice').value || 100000;
    const sort     = document.getElementById('sortBy').value;
    window.location.href = `catalog.php?category=${category}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sort}`;
  }

  function quickSort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
  }

  function resetFilters() {
    window.location.href = 'catalog.php';
  }

  function clearFilter(key) {
    const url = new URL(window.location.href);
    url.searchParams.delete(key);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
  }

  // ── Cart ──
  function addToCart(id, btn) {
    if (btn.disabled) return;
    btn.classList.add('added');
    btn.innerHTML = '<i class="fas fa-check"></i>';
    // TODO: Replace alert with actual AJAX cart call
    setTimeout(() => {
      btn.classList.remove('added');
      btn.innerHTML = '<i class="fas fa-plus"></i>';
    }, 1600);
  }

  // ── Wishlist ──
  function toggleWishlist(btn) {
    const icon = btn.querySelector('i');
    const isAdded = icon.classList.contains('fas');
    icon.classList.toggle('far', isAdded);
    icon.classList.toggle('fas', !isAdded);
    btn.style.color = !isAdded ? '#e05252' : '#ccc';
    // TODO: AJAX wishlist toggle
  }
</script>
</body>
</html>