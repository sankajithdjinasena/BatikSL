<?php
// artisan/dashboard.php — Artisan / Admin Dashboard
session_start();
require_once '../config/database.php';

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    header('Location: ../login.php?redirect=artisan/dashboard.php');
    exit;
}

$artisanId   = $_SESSION['user_id'];
$artisanName = $_SESSION['user_name'];

// ── Stats ──
$stats = [];

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = :id AND status = 1");
$stmt->execute(['id' => $artisanId]);
$stats['products'] = $stmt->fetchColumn();

// Total orders involving this artisan's products
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.artisan_id = :id
");
$stmt->execute(['id' => $artisanId]);
$stats['orders'] = $stmt->fetchColumn();

// Revenue
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE p.artisan_id = :id AND o.status != 'cancelled'
");
$stmt->execute(['id' => $artisanId]);
$stats['revenue'] = $stmt->fetchColumn();

// Pending bookings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
$stmt->execute();
$stats['bookings'] = $stmt->fetchColumn();

// ── Recent orders ──
$recentOrders = $pdo->prepare("
    SELECT o.id, o.order_number, o.total, o.status, o.created_at,
           u.name AS customer_name
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    JOIN users u ON u.id = o.user_id
    WHERE p.artisan_id = :id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 6
");
$recentOrders->execute(['id' => $artisanId]);
$orders = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

// ── Low stock products ──
$lowStock = $pdo->prepare("
    SELECT id, name, stock, category FROM products
    WHERE artisan_id = :id AND stock <= 5 AND status = 1
    ORDER BY stock ASC LIMIT 5
");
$lowStock->execute(['id' => $artisanId]);
$lowStockProducts = $lowStock->fetchAll(PDO::FETCH_ASSOC);

// ── Recent bookings ──
$bookingStmt = $pdo->prepare("
    SELECT b.*, u.name AS customer_name, u.email AS customer_email
    FROM bookings b JOIN users u ON u.id = b.user_id
    ORDER BY b.created_at DESC LIMIT 5
");
$bookingStmt->execute();
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | BatikSL Artisan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
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
    --gold-light: #e2c97e;
    --border: #e8e3da;
    --sidebar-w: 260px;
    --sidebar-bg: #111;
    --success: #16a34a;
    --warning: #d97706;
    --error: #dc2626;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--charcoal);
    display: flex;
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    min-height: 100vh;
    position: fixed; left: 0; top: 0; bottom: 0;
    display: flex; flex-direction: column;
    z-index: 200;
    transition: transform 0.3s ease;
  }

  .sidebar-head {
    padding: 1.8rem 1.6rem 1.4rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .sidebar-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem; color: white; text-decoration: none;
  }
  .sidebar-logo span { color: var(--gold); }
  .sidebar-badge {
    display: inline-block; margin-top: 0.4rem;
    font-size: 0.65rem; font-weight: 500; letter-spacing: 0.15em;
    text-transform: uppercase; padding: 0.2rem 0.65rem;
    background: rgba(201,168,76,0.18); color: var(--gold-light);
    border-radius: 2rem; border: 1px solid rgba(201,168,76,0.25);
  }

  /* Artisan info */
  .sidebar-user {
    padding: 1.2rem 1.6rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex; align-items: center; gap: 0.8rem;
  }
  .user-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--teal-light));
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; color: white; font-weight: 600; flex-shrink: 0;
  }
  .user-meta { overflow: hidden; }
  .user-name {
    font-size: 0.88rem; font-weight: 500; color: white;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .user-role {
    font-size: 0.72rem; color: rgba(255,255,255,0.4);
    text-transform: uppercase; letter-spacing: 0.08em;
  }

  /* Nav items */
  .sidebar-nav { flex: 1; padding: 1.2rem 0; overflow-y: auto; }
  .nav-section {
    font-size: 0.62rem; font-weight: 500; letter-spacing: 0.2em;
    text-transform: uppercase; color: rgba(255,255,255,0.25);
    padding: 0.8rem 1.6rem 0.4rem;
  }
  .nav-item {
    display: flex; align-items: center; gap: 0.8rem;
    padding: 0.72rem 1.6rem; text-decoration: none;
    font-size: 0.875rem; color: rgba(255,255,255,0.55);
    transition: all 0.2s; position: relative; border-radius: 0;
    cursor: pointer; border: none; background: none; width: 100%;
  }
  .nav-item:hover { color: white; background: rgba(255,255,255,0.05); }
  .nav-item.active {
    color: white; background: rgba(15,118,110,0.2);
    border-left: 3px solid var(--teal-light);
  }
  .nav-item i { width: 18px; font-size: 0.88rem; flex-shrink: 0; }
  .nav-badge {
    margin-left: auto;
    background: var(--gold); color: white;
    font-size: 0.65rem; font-weight: 700;
    min-width: 18px; height: 18px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px;
  }
  .nav-badge.red { background: var(--error); }

  /* Sidebar footer */
  .sidebar-foot {
    padding: 1rem 1.6rem;
    border-top: 1px solid rgba(255,255,255,0.06);
  }
  .view-store {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.65rem 0.9rem; border-radius: 8px;
    background: rgba(255,255,255,0.05);
    text-decoration: none; color: rgba(255,255,255,0.55);
    font-size: 0.82rem; transition: all 0.2s;
  }
  .view-store:hover { color: white; background: rgba(255,255,255,0.09); }
  .view-store i { font-size: 0.78rem; }
  .signout-btn {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.55rem 0.9rem; border-radius: 8px;
    background: none; border: none; cursor: pointer;
    color: rgba(255,255,255,0.35); font-size: 0.82rem;
    transition: all 0.2s; width: 100%; margin-top: 0.4rem;
    font-family: 'DM Sans', sans-serif;
  }
  .signout-btn:hover { color: #f87171; background: rgba(248,113,113,0.06); }

  /* ── MAIN CONTENT ── */
  .main {
    margin-left: var(--sidebar-w);
    flex: 1; display: flex; flex-direction: column;
    min-height: 100vh;
  }

  /* Top bar */
  .topbar {
    background: white; border-bottom: 1px solid var(--border);
    padding: 0 2.5rem;
    display: flex; align-items: center; justify-content: space-between;
    height: 66px; position: sticky; top: 0; z-index: 100;
  }
  .topbar-left { display: flex; align-items: center; gap: 0.8rem; }
  .menu-toggle {
    display: none; background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 1.1rem; padding: 0.3rem;
  }
  .page-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem; font-weight: 700;
  }
  .topbar-right { display: flex; align-items: center; gap: 1rem; }
  .topbar-icon {
    width: 38px; height: 38px; border-radius: 9px;
    border: 1.5px solid var(--border); background: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--warm-gray); font-size: 0.9rem;
    transition: all 0.2s; text-decoration: none; position: relative;
  }
  .topbar-icon:hover { border-color: var(--teal); color: var(--teal); }
  .topbar-dot {
    position: absolute; top: -3px; right: -3px;
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--error); border: 2px solid white;
  }
  .topbar-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--teal-light));
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.85rem; font-weight: 600; cursor: pointer;
  }

  /* ── DASHBOARD CONTENT ── */
  .content { padding: 2rem 2.5rem; flex: 1; }

  /* Welcome banner */
  .welcome-banner {
    background: linear-gradient(135deg, var(--charcoal) 0%, #2a2a2a 100%);
    border-radius: 16px; padding: 2rem 2.5rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.5rem; margin-bottom: 2rem; overflow: hidden;
    position: relative;
  }
  .welcome-banner::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.05);
  }
  .welcome-banner::after {
    content: '';
    position: absolute; right: 30px; bottom: -60px;
    width: 150px; height: 150px; border-radius: 50%;
    border: 1px solid rgba(201,168,76,0.15);
  }
  .welcome-text { position: relative; z-index: 1; }
  .welcome-text .date {
    font-size: 0.74rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: rgba(255,255,255,0.4); margin-bottom: 0.5rem;
  }
  .welcome-text h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; color: white; margin-bottom: 0.35rem;
  }
  .welcome-text h2 em { font-style: italic; color: var(--gold-light); }
  .welcome-text p { font-size: 0.88rem; color: rgba(255,255,255,0.5); }
  .welcome-actions { position: relative; z-index: 1; display: flex; gap: 0.8rem; flex-shrink: 0; }
  .btn-primary {
    padding: 0.65rem 1.4rem;
    background: var(--teal); color: white; border: none;
    border-radius: 9px; font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem; font-weight: 500; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: background 0.2s;
  }
  .btn-primary:hover { background: var(--teal-light); }
  .btn-secondary {
    padding: 0.65rem 1.4rem;
    background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.12); border-radius: 9px;
    font-family: 'DM Sans', sans-serif; font-size: 0.84rem; font-weight: 500;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: all 0.2s;
  }
  .btn-secondary:hover { background: rgba(255,255,255,0.14); color: white; }

  /* Stat cards */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.2rem; margin-bottom: 2rem;
  }
  .stat-card {
    background: white; border-radius: 14px;
    padding: 1.5rem; border: 1px solid var(--border);
    transition: box-shadow 0.2s, transform 0.2s;
    position: relative; overflow: hidden;
  }
  .stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.07); transform: translateY(-2px); }
  .stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; border-radius: 14px 14px 0 0;
  }
  .stat-card.teal::before   { background: var(--teal); }
  .stat-card.gold::before   { background: var(--gold); }
  .stat-card.green::before  { background: var(--success); }
  .stat-card.orange::before { background: var(--warning); }

  .stat-top {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 1.2rem;
  }
  .stat-label {
    font-size: 0.73rem; font-weight: 500; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--warm-gray);
  }
  .stat-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.88rem;
  }
  .stat-icon.teal   { background: rgba(15,118,110,0.1);  color: var(--teal); }
  .stat-icon.gold   { background: rgba(201,168,76,0.12); color: var(--gold); }
  .stat-icon.green  { background: rgba(22,163,74,0.1);   color: var(--success); }
  .stat-icon.orange { background: rgba(217,119,6,0.1);   color: var(--warning); }

  .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700; line-height: 1;
    margin-bottom: 0.35rem;
  }
  .stat-sub {
    font-size: 0.78rem; color: var(--warm-gray);
  }
  .stat-sub .up   { color: var(--success); font-weight: 500; }
  .stat-sub .down { color: var(--error); font-weight: 500; }

  /* Content row */
  .content-row {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 1.4rem; margin-bottom: 1.4rem;
  }
  .content-row.three {
    grid-template-columns: 1fr 1fr 1fr;
  }

  /* Panels */
  .panel {
    background: white; border-radius: 14px;
    border: 1px solid var(--border); overflow: hidden;
  }
  .panel-head {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
  }
  .panel-head h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem; font-weight: 700;
  }
  .panel-head a {
    font-size: 0.78rem; color: var(--teal); text-decoration: none;
    font-weight: 500; transition: opacity 0.2s;
  }
  .panel-head a:hover { opacity: 0.7; }

  /* Order table */
  .order-table { width: 100%; border-collapse: collapse; }
  .order-table th {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    padding: 0.7rem 1.5rem; text-align: left;
    background: var(--cream); border-bottom: 1px solid var(--border);
  }
  .order-table td {
    padding: 0.9rem 1.5rem;
    font-size: 0.87rem; border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }
  .order-table tr:last-child td { border-bottom: none; }
  .order-table tr:hover td { background: #fafaf8; }
  .order-num { font-weight: 500; color: var(--teal); }
  .status-pill {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.25rem 0.75rem; border-radius: 2rem;
    font-size: 0.72rem; font-weight: 500;
  }
  .status-pill::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%;
  }
  .pill-pending   { background: #fef3c7; color: #92400e; }
  .pill-pending::before { background: #f59e0b; }
  .pill-processing { background: #dbeafe; color: #1e40af; }
  .pill-processing::before { background: #3b82f6; }
  .pill-shipped   { background: #e0f2fe; color: #0c4a6e; }
  .pill-shipped::before { background: #0ea5e9; }
  .pill-delivered { background: #dcfce7; color: #166534; }
  .pill-delivered::before { background: #22c55e; }
  .pill-cancelled { background: #fee2e2; color: #991b1b; }
  .pill-cancelled::before { background: #ef4444; }

  /* Low stock list */
  .stock-list { list-style: none; }
  .stock-item {
    display: flex; align-items: center; gap: 0.9rem;
    padding: 0.9rem 1.5rem; border-bottom: 1px solid var(--border);
  }
  .stock-item:last-child { border-bottom: none; }
  .stock-icon {
    width: 34px; height: 34px; border-radius: 8px;
    background: var(--sand); display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; color: var(--warm-gray); flex-shrink: 0;
  }
  .stock-info { flex: 1; min-width: 0; }
  .stock-name {
    font-size: 0.87rem; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .stock-cat { font-size: 0.72rem; color: var(--warm-gray); text-transform: capitalize; }
  .stock-level {
    font-size: 0.82rem; font-weight: 600; flex-shrink: 0;
  }
  .stock-level.critical { color: var(--error); }
  .stock-level.low      { color: var(--warning); }

  /* Booking list */
  .booking-list { list-style: none; }
  .booking-item {
    display: flex; align-items: flex-start; gap: 0.9rem;
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
  }
  .booking-item:last-child { border-bottom: none; }
  .booking-date-box {
    width: 44px; height: 44px; border-radius: 10px;
    background: var(--sand); display: flex; flex-direction: column;
    align-items: center; justify-content: center; flex-shrink: 0;
    border: 1px solid var(--border);
  }
  .booking-date-box .day {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; font-weight: 700; line-height: 1;
    color: var(--charcoal);
  }
  .booking-date-box .mon {
    font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--warm-gray);
  }
  .booking-meta { flex: 1; min-width: 0; }
  .booking-customer { font-size: 0.87rem; font-weight: 500; margin-bottom: 0.15rem; }
  .booking-details { font-size: 0.75rem; color: var(--warm-gray); }
  .booking-status { flex-shrink: 0; }

  /* Quick actions */
  .quick-actions {
    display: grid; grid-template-columns: repeat(2, 1fr);
    gap: 0.8rem; padding: 1.2rem;
  }
  .quick-btn {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.5rem; padding: 1.1rem 0.8rem;
    background: var(--cream); border-radius: 10px;
    border: 1.5px solid var(--border); text-decoration: none;
    font-size: 0.8rem; color: var(--warm-gray); font-weight: 500;
    transition: all 0.2s; cursor: pointer;
  }
  .quick-btn:hover { border-color: var(--teal); color: var(--teal); background: #e6f4f2; }
  .quick-btn i { font-size: 1.1rem; }

  /* Empty state */
  .empty-panel {
    text-align: center; padding: 3rem 1.5rem;
    color: var(--warm-gray); font-size: 0.88rem;
  }
  .empty-panel i { font-size: 2rem; color: var(--border); display: block; margin-bottom: 0.8rem; }

  /* ── RESPONSIVE ── */
  @media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .content-row { grid-template-columns: 1fr; }
    .content-row.three { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 860px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .main { margin-left: 0; }
    .menu-toggle { display: block; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .content { padding: 1.5rem; }
    .topbar { padding: 0 1.5rem; }
    .welcome-banner { padding: 1.5rem; }
    .welcome-actions { display: none; }
  }
  @media (max-width: 500px) {
    .stats-grid { grid-template-columns: 1fr; }
    .content-row.three { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-head">
    <a href="../index.php" class="sidebar-logo">Batik<span>SL</span></a><br>
    <span class="sidebar-badge">Artisan Portal</span>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr($artisanName, 0, 1)) ?></div>
    <div class="user-meta">
      <div class="user-name"><?= htmlspecialchars($artisanName) ?></div>
      <div class="user-role">Artisan</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="dashboard.php" class="nav-item active">
      <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a href="products.php" class="nav-item">
      <i class="fas fa-tags"></i> Products
      <?php if ($stats['products']): ?>
        <span class="nav-badge"><?= $stats['products'] ?></span>
      <?php endif; ?>
    </a>
    <a href="orders.php" class="nav-item">
      <i class="fas fa-shopping-bag"></i> Orders
      <?php if ($stats['orders']): ?>
        <span class="nav-badge red"><?= $stats['orders'] ?></span>
      <?php endif; ?>
    </a>
    <a href="bookings.php" class="nav-item">
      <i class="fas fa-calendar-check"></i> Bookings
      <?php if ($stats['bookings']): ?>
        <span class="nav-badge"><?= $stats['bookings'] ?></span>
      <?php endif; ?>
    </a>

    <div class="nav-section" style="margin-top:0.8rem">Catalog</div>
    <a href="add-product.php" class="nav-item">
      <i class="fas fa-plus-circle"></i> Add Product
    </a>
    <a href="inventory.php" class="nav-item">
      <i class="fas fa-warehouse"></i> Inventory
    </a>
    <a href="reviews.php" class="nav-item">
      <i class="fas fa-star"></i> Reviews
    </a>

    <div class="nav-section" style="margin-top:0.8rem">Account</div>
    <a href="settings.php" class="nav-item">
      <i class="fas fa-sliders-h"></i> Settings
    </a>
    <a href="profile.php" class="nav-item">
      <i class="fas fa-user-circle"></i> Profile
    </a>
  </nav>

  <div class="sidebar-foot">
    <a href="../catalog.php" class="view-store" target="_blank">
      <i class="fas fa-external-link-alt"></i> View Storefront
    </a>
    <form method="POST" action="../logout.php">
      <button type="submit" class="signout-btn">
        <i class="fas fa-sign-out-alt"></i> Sign Out
      </button>
    </form>
  </div>
</aside>

<!-- MAIN -->
<main class="main">

  <!-- Top bar -->
  <div class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
      </button>
      <span class="page-title">Dashboard</span>
    </div>
    <div class="topbar-right">
      <a href="notifications.php" class="topbar-icon" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <?php if ($stats['bookings'] > 0): ?>
          <span class="topbar-dot"></span>
        <?php endif; ?>
      </a>
      <a href="add-product.php" class="topbar-icon" aria-label="Add product" title="Add Product">
        <i class="fas fa-plus"></i>
      </a>
      <a href="profile.php" class="topbar-avatar" title="<?= htmlspecialchars($artisanName) ?>">
        <?= strtoupper(substr($artisanName, 0, 1)) ?>
      </a>
    </div>
  </div>

  <!-- Content -->
  <div class="content">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <div class="date"><?= date('l, F j, Y') ?></div>
        <h2>Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>, <em><?= htmlspecialchars(explode(' ', $artisanName)[0]) ?></em></h2>
        <p>Here's an overview of your BatikSL store today.</p>
      </div>
      <div class="welcome-actions">
        <a href="add-product.php" class="btn-primary"><i class="fas fa-plus"></i> New Product</a>
        <a href="orders.php" class="btn-secondary"><i class="fas fa-shopping-bag"></i> View Orders</a>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card teal">
        <div class="stat-top">
          <span class="stat-label">Total Revenue</span>
          <div class="stat-icon teal"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="stat-value">LKR <?= number_format($stats['revenue'] / 1000, 1) ?>k</div>
        <div class="stat-sub"><span class="up">↑ 12%</span> vs last month</div>
      </div>
      <div class="stat-card gold">
        <div class="stat-top">
          <span class="stat-label">Total Orders</span>
          <div class="stat-icon gold"><i class="fas fa-shopping-bag"></i></div>
        </div>
        <div class="stat-value"><?= number_format($stats['orders']) ?></div>
        <div class="stat-sub"><span class="up">↑ 8%</span> vs last month</div>
      </div>
      <div class="stat-card green">
        <div class="stat-top">
          <span class="stat-label">Active Products</span>
          <div class="stat-icon green"><i class="fas fa-tags"></i></div>
        </div>
        <div class="stat-value"><?= $stats['products'] ?></div>
        <div class="stat-sub">Across all categories</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-top">
          <span class="stat-label">Pending Bookings</span>
          <div class="stat-icon orange"><i class="fas fa-calendar"></i></div>
        </div>
        <div class="stat-value"><?= $stats['bookings'] ?></div>
        <div class="stat-sub"><?= $stats['bookings'] > 0 ? '<span class="down">Needs attention</span>' : 'All clear' ?></div>
      </div>
    </div>

    <!-- Orders + Low Stock -->
    <div class="content-row">

      <!-- Recent Orders -->
      <div class="panel">
        <div class="panel-head">
          <h3>Recent Orders</h3>
          <a href="orders.php">View all →</a>
        </div>
        <?php if (empty($orders)): ?>
          <div class="empty-panel">
            <i class="fas fa-shopping-bag"></i>
            No orders yet. Share your store link to get started!
          </div>
        <?php else: ?>
        <table class="order-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><span class="order-num">#<?= htmlspecialchars($order['order_number']) ?></span></td>
              <td><?= htmlspecialchars($order['customer_name']) ?></td>
              <td>LKR <?= number_format($order['total']) ?></td>
              <td>
                <span class="status-pill pill-<?= $order['status'] ?>">
                  <?= ucfirst($order['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- Low Stock + Quick Actions -->
      <div style="display:flex;flex-direction:column;gap:1.4rem">

        <!-- Low Stock -->
        <div class="panel">
          <div class="panel-head">
            <h3>Low Stock Alert</h3>
            <a href="inventory.php">Manage →</a>
          </div>
          <?php if (empty($lowStockProducts)): ?>
            <div class="empty-panel" style="padding:2rem 1.5rem">
              <i class="fas fa-check-circle" style="color:var(--success)"></i>
              All products are well stocked!
            </div>
          <?php else: ?>
          <ul class="stock-list">
            <?php foreach ($lowStockProducts as $p): ?>
            <li class="stock-item">
              <div class="stock-icon"><i class="fas fa-tshirt"></i></div>
              <div class="stock-info">
                <div class="stock-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="stock-cat"><?= str_replace('_', ' ', $p['category']) ?></div>
              </div>
              <span class="stock-level <?= $p['stock'] === 0 ? 'critical' : 'low' ?>">
                <?= $p['stock'] === 0 ? 'Out' : $p['stock'] . ' left' ?>
              </span>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="panel">
          <div class="panel-head">
            <h3>Quick Actions</h3>
          </div>
          <div class="quick-actions">
            <a href="add-product.php" class="quick-btn">
              <i class="fas fa-plus-circle"></i> Add Product
            </a>
            <a href="bookings.php" class="quick-btn">
              <i class="fas fa-calendar-plus"></i> Bookings
            </a>
            <a href="inventory.php" class="quick-btn">
              <i class="fas fa-warehouse"></i> Inventory
            </a>
            <a href="settings.php" class="quick-btn">
              <i class="fas fa-sliders-h"></i> Settings
            </a>
          </div>
        </div>

      </div>
    </div>

    <!-- Recent Bookings -->
    <div class="panel">
      <div class="panel-head">
        <h3>Upcoming Live Sessions</h3>
        <a href="bookings.php">View all →</a>
      </div>
      <?php if (empty($bookings)): ?>
        <div class="empty-panel">
          <i class="fas fa-calendar"></i>
          No upcoming bookings yet.
        </div>
      <?php else: ?>
      <ul class="booking-list">
        <?php foreach ($bookings as $b):
          $d = new DateTime($b['session_date']);
        ?>
        <li class="booking-item">
          <div class="booking-date-box">
            <span class="day"><?= $d->format('d') ?></span>
            <span class="mon"><?= $d->format('M') ?></span>
          </div>
          <div class="booking-meta">
            <div class="booking-customer"><?= htmlspecialchars($b['customer_name']) ?></div>
            <div class="booking-details">
              <?= htmlspecialchars($b['session_time']) ?>
              &nbsp;·&nbsp; <?= $b['group_size'] ?> guest<?= $b['group_size'] > 1 ? 's' : '' ?>
              &nbsp;·&nbsp; <?= htmlspecialchars($b['customer_email']) ?>
            </div>
          </div>
          <div class="booking-status">
            <span class="status-pill pill-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</main>

<!-- Sidebar overlay (mobile) -->
<div id="sidebarOverlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:150;backdrop-filter:blur(2px)"></div>

<script>
  function toggleSidebar() {
    const sb  = document.getElementById('sidebar');
    const ov  = document.getElementById('sidebarOverlay');
    const open = sb.classList.toggle('open');
    ov.style.display = open ? 'block' : 'none';
  }
</script>
</body>
</html>
