<?php
// artisan/dashboard.php — Artisan / Admin Dashboard
session_start();
require_once '../config/database.php';

// ── Auth guard ──
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    header('Location: ../login.php?redirect=artisan/dashboard.php');
    exit;
}

$artisanId   = (int) $_SESSION['user_id'];
$artisanName = $_SESSION['user_name'];

// ── Stats ──
$stats = [];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE artisan_id = :id AND status = 1");
$stmt->execute(['id' => $artisanId]);
$stats['products'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.artisan_id = :id
");
$stmt->execute(['id' => $artisanId]);
$stats['orders'] = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(oi.unit_price * oi.quantity), 0)
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE p.artisan_id = :id AND o.status != 'cancelled'
");
$stmt->execute(['id' => $artisanId]);
$stats['revenue'] = (float) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
$stmt->execute();
$stats['bookings'] = (int) $stmt->fetchColumn();

// ── Recent orders ──
$recentOrders = $pdo->prepare("
    SELECT o.id, o.order_number, o.total, o.status, o.created_at,
           o.name AS customer_name
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
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
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$bookingStmt->execute();
$bookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Revenue sparkline data ──
$sparkStmt = $pdo->prepare("
    SELECT DATE(o.created_at) AS day,
           COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS daily_rev
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    WHERE p.artisan_id = :id
      AND o.status != 'cancelled'
      AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(o.created_at)
    ORDER BY day ASC
");
$sparkStmt->execute(['id' => $artisanId]);
$sparkRows = $sparkStmt->fetchAll(PDO::FETCH_ASSOC);
$sparkData = [];
for ($i = 6; $i >= 0; $i--) {
    $sparkData[date('Y-m-d', strtotime("-{$i} days"))] = 0;
}
foreach ($sparkRows as $row) {
    if (isset($sparkData[$row['day']])) {
        $sparkData[$row['day']] = (float) $row['daily_rev'];
    }
}
$sparkValues = array_values($sparkData);
$sparkMax    = max($sparkValues) ?: 1;

// ── Categories for New Product modal ──
$categories = ['saree', 'fabric', 'wall_art', 'home_decor', 'accessories', 'clothing', 'other'];
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
    transition: all 0.2s; position: relative;
    border-left: 3px solid transparent;
    cursor: pointer; background: none; border-right: none; border-top: none; border-bottom: none;
    width: 100%; font-family: 'DM Sans', sans-serif;
  }
  .nav-item:hover { color: white; background: rgba(255,255,255,0.05); }
  .nav-item.active {
    color: white; background: rgba(15,118,110,0.2);
    border-left-color: var(--teal-light);
  }
  .nav-item i { width: 18px; font-size: 0.88rem; flex-shrink: 0; }
  .nav-badge {
    margin-left: auto;
    background: var(--gold); color: #fff;
    font-size: 0.65rem; font-weight: 700;
    min-width: 18px; height: 18px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; padding: 0 4px;
  }
  .nav-badge.red { background: var(--error); }
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
  .signout-btn {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.55rem 0.9rem; border-radius: 8px;
    background: none; border: none; cursor: pointer;
    color: rgba(255,255,255,0.35); font-size: 0.82rem;
    transition: all 0.2s; width: 100%; margin-top: 0.4rem;
    font-family: 'DM Sans', sans-serif;
  }
  .signout-btn:hover { color: #f87171; background: rgba(248,113,113,0.06); }

  /* ── MAIN ── */
  .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
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
  .page-title { font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; }
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
    text-decoration: none;
  }

  /* ── CONTENT ── */
  .content { padding: 2rem 2.5rem; flex: 1; }

  .welcome-banner {
    background: linear-gradient(135deg, var(--charcoal) 0%, #2a2a2a 100%);
    border-radius: 16px; padding: 2rem 2.5rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.5rem; margin-bottom: 2rem; overflow: hidden; position: relative;
  }
  .welcome-banner::before {
    content: ''; position: absolute; right: -40px; top: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.05);
  }
  .welcome-banner::after {
    content: ''; position: absolute; right: 30px; bottom: -60px;
    width: 150px; height: 150px; border-radius: 50%;
    border: 1px solid rgba(201,168,76,0.15);
  }
  .welcome-text { position: relative; z-index: 1; }
  .welcome-text .date { font-size: 0.74rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 0.5rem; }
  .welcome-text h2 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: white; margin-bottom: 0.35rem; }
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

  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.2rem; margin-bottom: 2rem; }
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
  .stat-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.2rem; }
  .stat-label { font-size: 0.73rem; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; color: var(--warm-gray); }
  .stat-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; }
  .stat-icon.teal   { background: rgba(15,118,110,0.1);  color: var(--teal); }
  .stat-icon.gold   { background: rgba(201,168,76,0.12); color: var(--gold); }
  .stat-icon.green  { background: rgba(22,163,74,0.1);   color: var(--success); }
  .stat-icon.orange { background: rgba(217,119,6,0.1);   color: var(--warning); }
  .stat-value { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 0.35rem; }
  .stat-sub { font-size: 0.78rem; color: var(--warm-gray); }
  .stat-sub .up   { color: var(--success); font-weight: 500; }
  .stat-sub .down { color: var(--error);   font-weight: 500; }
  .sparkline-wrap { margin-top: 0.9rem; display: flex; align-items: flex-end; gap: 3px; height: 32px; }
  .spark-bar { flex: 1; border-radius: 3px 3px 0 0; background: rgba(15,118,110,0.18); transition: background 0.2s; min-height: 3px; }
  .spark-bar:hover { background: var(--teal); }

  .content-row { display: grid; grid-template-columns: 1.6fr 1fr; gap: 1.4rem; margin-bottom: 1.4rem; }

  .panel { background: white; border-radius: 14px; border: 1px solid var(--border); overflow: hidden; }
  .panel-head {
    padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
  }
  .panel-head h3 { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; }
  .panel-head a { font-size: 0.78rem; color: var(--teal); text-decoration: none; font-weight: 500; transition: opacity 0.2s; }
  .panel-head a:hover { opacity: 0.7; }

  .order-table { width: 100%; border-collapse: collapse; }
  .order-table th {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    padding: 0.7rem 1.5rem; text-align: left;
    background: var(--cream); border-bottom: 1px solid var(--border);
  }
  .order-table td { padding: 0.9rem 1.5rem; font-size: 0.87rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
  .order-table tr:last-child td { border-bottom: none; }
  .order-table tr:hover td { background: #fafaf8; }
  .order-num { font-weight: 500; color: var(--teal); }

  /* Status pills */
  .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.72rem; font-weight: 500; }
  .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
  .pill-pending     { background: #fef3c7; color: #92400e; }
  .pill-pending::before { background: #f59e0b; }
  .pill-processing  { background: #dbeafe; color: #1e40af; }
  .pill-processing::before { background: #3b82f6; }
  .pill-shipped     { background: #e0f2fe; color: #0c4a6e; }
  .pill-shipped::before { background: #0ea5e9; }
  .pill-delivered   { background: #dcfce7; color: #166534; }
  .pill-delivered::before { background: #22c55e; }
  .pill-cancelled   { background: #fee2e2; color: #991b1b; }
  .pill-cancelled::before { background: #ef4444; }
  .pill-confirmed   { background: #dcfce7; color: #166534; }
  .pill-confirmed::before { background: #22c55e; }
  .pill-completed   { background: #f3e8ff; color: #6b21a8; }
  .pill-completed::before { background: #a855f7; }

  /* Low stock */
  .stock-list { list-style: none; }
  .stock-item { display: flex; align-items: center; gap: 0.9rem; padding: 0.9rem 1.5rem; border-bottom: 1px solid var(--border); }
  .stock-item:last-child { border-bottom: none; }
  .stock-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--sand); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--warm-gray); flex-shrink: 0; }
  .stock-info { flex: 1; min-width: 0; }
  .stock-name { font-size: 0.87rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .stock-cat { font-size: 0.72rem; color: var(--warm-gray); text-transform: capitalize; }
  .stock-level { font-size: 0.82rem; font-weight: 600; flex-shrink: 0; }
  .stock-level.critical { color: var(--error); }
  .stock-level.low      { color: var(--warning); }

  /* Bookings */
  .booking-list { list-style: none; }
  .booking-item { display: flex; align-items: flex-start; gap: 0.9rem; padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); }
  .booking-item:last-child { border-bottom: none; }
  .booking-date-box {
    width: 44px; height: 44px; border-radius: 10px;
    background: var(--sand); display: flex; flex-direction: column;
    align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border);
  }
  .booking-date-box .day { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; line-height: 1; }
  .booking-date-box .mon { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--warm-gray); }
  .booking-meta { flex: 1; min-width: 0; }
  .booking-customer { font-size: 0.87rem; font-weight: 500; margin-bottom: 0.15rem; }
  .booking-details { font-size: 0.75rem; color: var(--warm-gray); }
  .booking-status { flex-shrink: 0; }

  /* Quick actions */
  .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.8rem; padding: 1.2rem; }
  .quick-btn {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.5rem; padding: 1.1rem 0.8rem;
    background: var(--cream); border-radius: 10px;
    border: 1.5px solid var(--border); text-decoration: none;
    font-size: 0.8rem; color: var(--warm-gray); font-weight: 500;
    transition: all 0.2s; cursor: pointer; font-family: 'DM Sans', sans-serif;
  }
  .quick-btn:hover { border-color: var(--teal); color: var(--teal); background: #e6f4f2; }
  .quick-btn i { font-size: 1.1rem; }

  /* Empty states */
  .empty-panel { text-align: center; padding: 3rem 1.5rem; color: var(--warm-gray); font-size: 0.88rem; }
  .empty-panel i { font-size: 2rem; color: var(--border); display: block; margin-bottom: 0.8rem; }

  /* View order button in table */
  .btn-view-order {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.28rem 0.7rem; border-radius: 6px;
    border: 1.5px solid var(--border); background: none;
    font-size: 0.74rem; font-weight: 500; color: var(--warm-gray);
    cursor: pointer; font-family: 'DM Sans', sans-serif;
    transition: all 0.2s;
  }
  .btn-view-order:hover { border-color: var(--teal); color: var(--teal); background: rgba(15,118,110,0.05); }

  /* ═══════════════════════════════════════════
     ORDER DRAWER
  ═══════════════════════════════════════════ */
  .drawer-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    z-index: 400; opacity: 0; pointer-events: none;
    transition: opacity 0.3s ease;
    backdrop-filter: blur(2px);
  }
  .drawer-overlay.active { opacity: 1; pointer-events: all; }

  .order-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 480px; max-width: 100vw;
    background: white; z-index: 500;
    display: flex; flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.35s cubic-bezier(0.4,0,0.2,1);
    box-shadow: -8px 0 40px rgba(0,0,0,0.12);
  }
  .order-drawer.active { transform: translateX(0); }

  .drawer-head {
    padding: 1.4rem 1.6rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  .drawer-head h2 { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; }
  .drawer-close {
    width: 34px; height: 34px; border-radius: 8px;
    border: 1.5px solid var(--border); background: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--warm-gray); font-size: 0.9rem; transition: all 0.2s;
  }
  .drawer-close:hover { border-color: var(--error); color: var(--error); }

  .drawer-body { flex: 1; overflow-y: auto; padding: 1.5rem 1.6rem; }

  .drawer-section { margin-bottom: 1.6rem; }
  .drawer-section-title {
    font-size: 0.68rem; font-weight: 600; letter-spacing: 0.15em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.8rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
  }

  .order-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .order-meta-item { }
  .order-meta-label { font-size: 0.72rem; color: var(--warm-gray); margin-bottom: 0.2rem; }
  .order-meta-value { font-size: 0.9rem; font-weight: 500; }

  .order-items-list { list-style: none; display: flex; flex-direction: column; gap: 0.7rem; }
  .order-item-row {
    display: flex; align-items: center; gap: 0.8rem;
    padding: 0.8rem; border-radius: 10px; border: 1px solid var(--border);
    background: var(--cream);
  }
  .order-item-img {
    width: 44px; height: 44px; border-radius: 8px;
    background: var(--sand); display: flex; align-items: center; justify-content: center;
    color: var(--warm-gray); font-size: 1rem; flex-shrink: 0;
  }
  .order-item-info { flex: 1; min-width: 0; }
  .order-item-name { font-size: 0.88rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .order-item-qty { font-size: 0.75rem; color: var(--warm-gray); }
  .order-item-price { font-size: 0.88rem; font-weight: 600; flex-shrink: 0; color: var(--teal); }

  .order-total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.9rem 1rem; background: var(--charcoal); color: white;
    border-radius: 10px; margin-top: 0.5rem;
  }
  .order-total-label { font-size: 0.82rem; color: rgba(255,255,255,0.65); }
  .order-total-value { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: var(--gold-light); }

  .customer-card {
    background: var(--cream); border-radius: 10px; padding: 1rem;
    border: 1px solid var(--border);
    display: flex; align-items: flex-start; gap: 0.8rem;
  }
  .customer-avatar-sm {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--teal-light));
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 0.85rem; font-weight: 600; flex-shrink: 0;
  }
  .customer-info-sm { }
  .customer-name-sm { font-size: 0.9rem; font-weight: 500; }
  .customer-detail-sm { font-size: 0.78rem; color: var(--warm-gray); margin-top: 0.1rem; }

  .drawer-actions {
    padding: 1.2rem 1.6rem; border-top: 1px solid var(--border);
    display: flex; gap: 0.7rem; flex-shrink: 0; flex-wrap: wrap;
  }
  .drawer-btn {
    flex: 1; padding: 0.65rem 1rem;
    border-radius: 9px; font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem; font-weight: 500; cursor: pointer;
    border: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    transition: all 0.2s; text-decoration: none; min-width: 120px;
  }
  .drawer-btn-primary { background: var(--teal); color: white; }
  .drawer-btn-primary:hover { background: var(--teal-dark); }
  .drawer-btn-outline { background: none; border: 1.5px solid var(--border); color: var(--warm-gray); }
  .drawer-btn-outline:hover { border-color: var(--teal); color: var(--teal); }

  /* Status selector in drawer */
  .status-select {
    padding: 0.5rem 0.8rem; border-radius: 8px;
    border: 1.5px solid var(--border); background: white;
    font-family: 'DM Sans', sans-serif; font-size: 0.84rem;
    color: var(--charcoal); cursor: pointer; outline: none;
    transition: border-color 0.2s;
  }
  .status-select:focus { border-color: var(--teal); }

  /* ═══════════════════════════════════════════
     NEW PRODUCT MODAL
  ═══════════════════════════════════════════ */
  .modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    z-index: 600; opacity: 0; pointer-events: none;
    transition: opacity 0.25s ease;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(3px);
  }
  .modal-overlay.active { opacity: 1; pointer-events: all; }

  .modal {
    background: white; border-radius: 18px;
    width: 100%; max-width: 620px; max-height: 92vh;
    display: flex; flex-direction: column;
    transform: scale(0.94) translateY(10px);
    transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 24px 80px rgba(0,0,0,0.18);
    overflow: hidden;
  }
  .modal-overlay.active .modal { transform: scale(1) translateY(0); }

  .modal-head {
    padding: 1.4rem 1.8rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  .modal-head-left { }
  .modal-head-label {
    font-size: 0.67rem; font-weight: 600; letter-spacing: 0.18em;
    text-transform: uppercase; color: var(--teal); margin-bottom: 0.25rem;
  }
  .modal-head h2 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; }
  .modal-close {
    width: 34px; height: 34px; border-radius: 8px;
    border: 1.5px solid var(--border); background: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--warm-gray); font-size: 0.9rem; transition: all 0.2s;
  }
  .modal-close:hover { border-color: var(--error); color: var(--error); }

  .modal-body { flex: 1; overflow-y: auto; padding: 1.6rem 1.8rem; }
  .modal-footer {
    padding: 1.2rem 1.8rem; border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 0.7rem; flex-shrink: 0;
  }

  /* Form */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
  .form-group.full { grid-column: 1 / -1; }
  .form-label {
    font-size: 0.78rem; font-weight: 500; color: var(--charcoal);
  }
  .form-label span { color: var(--error); margin-left: 2px; }
  .form-input, .form-select, .form-textarea {
    padding: 0.62rem 0.9rem;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-family: 'DM Sans', sans-serif; font-size: 0.87rem;
    color: var(--charcoal); background: white; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(15,118,110,0.1);
  }
  .form-textarea { resize: vertical; min-height: 90px; }
  .form-hint { font-size: 0.72rem; color: var(--warm-gray); }
  .form-section-title {
    font-size: 0.68rem; font-weight: 600; letter-spacing: 0.15em;
    text-transform: uppercase; color: var(--warm-gray);
    grid-column: 1 / -1;
    padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
    margin-top: 0.5rem;
  }

  /* Image upload zone */
  .image-upload-zone {
    border: 2px dashed var(--border); border-radius: 12px;
    padding: 2rem; text-align: center; cursor: pointer;
    transition: all 0.2s; position: relative;
    background: var(--cream);
  }
  .image-upload-zone:hover, .image-upload-zone.dragover {
    border-color: var(--teal); background: rgba(15,118,110,0.04);
  }
  .image-upload-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
  }
  .upload-icon { font-size: 1.6rem; color: var(--border); margin-bottom: 0.5rem; }
  .upload-text { font-size: 0.84rem; color: var(--warm-gray); }
  .upload-text strong { color: var(--teal); }
  .upload-hint { font-size: 0.73rem; color: var(--warm-gray); margin-top: 0.25rem; }
  .image-preview-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem; margin-top: 0.8rem;
  }
  .preview-thumb {
    aspect-ratio: 1; border-radius: 8px; overflow: hidden;
    position: relative; border: 1.5px solid var(--border);
  }
  .preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .preview-thumb .remove-thumb {
    position: absolute; top: 3px; right: 3px;
    width: 20px; height: 20px; border-radius: 50%;
    background: rgba(220,38,38,0.85); color: white;
    border: none; cursor: pointer; font-size: 0.65rem;
    display: flex; align-items: center; justify-content: center;
  }

  /* Toggle switch */
  .toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.8rem 0.9rem; background: var(--cream);
    border-radius: 9px; border: 1px solid var(--border);
    grid-column: 1 / -1;
  }
  .toggle-label { font-size: 0.87rem; font-weight: 500; }
  .toggle-sub { font-size: 0.73rem; color: var(--warm-gray); margin-top: 0.1rem; }
  .toggle-switch { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
  .toggle-switch input { opacity: 0; width: 0; height: 0; }
  .toggle-track {
    position: absolute; inset: 0; border-radius: 24px;
    background: var(--border); cursor: pointer; transition: background 0.2s;
  }
  .toggle-track::after {
    content: ''; position: absolute; width: 18px; height: 18px;
    border-radius: 50%; background: white;
    top: 3px; left: 3px;
    transition: transform 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.15);
  }
  .toggle-switch input:checked + .toggle-track { background: var(--teal); }
  .toggle-switch input:checked + .toggle-track::after { transform: translateX(18px); }

  /* Modal buttons */
  .modal-btn {
    padding: 0.68rem 1.5rem; border-radius: 9px;
    font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 500;
    cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem;
    transition: all 0.2s;
  }
  .modal-btn-primary { background: var(--teal); color: white; }
  .modal-btn-primary:hover { background: var(--teal-dark); }
  .modal-btn-cancel { background: none; border: 1.5px solid var(--border); color: var(--warm-gray); }
  .modal-btn-cancel:hover { border-color: var(--charcoal); color: var(--charcoal); }

  /* Success toast */
  .toast {
    position: fixed; bottom: 2rem; right: 2rem; z-index: 900;
    background: var(--charcoal); color: white;
    padding: 0.9rem 1.4rem; border-radius: 12px;
    display: flex; align-items: center; gap: 0.75rem;
    font-size: 0.87rem; font-weight: 500;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    transform: translateY(80px); opacity: 0;
    transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
    pointer-events: none;
  }
  .toast.show { transform: translateY(0); opacity: 1; }
  .toast i { color: var(--teal-light); font-size: 1rem; }

  /* Responsive */
  @media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .content-row { grid-template-columns: 1fr; }
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
    .order-drawer { width: 100vw; }
    .form-grid { grid-template-columns: 1fr; }
    .form-group.full { grid-column: 1; }
    .form-section-title { grid-column: 1; }
    .toggle-row { grid-column: 1; }
  }
  @media (max-width: 500px) {
    .stats-grid { grid-template-columns: 1fr; }
    .order-meta-grid { grid-template-columns: 1fr; }
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
    <a href="dashboard.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="products.php" class="nav-item">
      <i class="fas fa-tags"></i> Products
      <?php if ($stats['products']): ?><span class="nav-badge"><?= $stats['products'] ?></span><?php endif; ?>
    </a>
    <a href="orders.php" class="nav-item">
      <i class="fas fa-shopping-bag"></i> Orders
      <?php if ($stats['orders']): ?><span class="nav-badge red"><?= $stats['orders'] ?></span><?php endif; ?>
    </a>
    <a href="bookings.php" class="nav-item">
      <i class="fas fa-calendar-check"></i> Bookings
      <?php if ($stats['bookings']): ?><span class="nav-badge"><?= $stats['bookings'] ?></span><?php endif; ?>
    </a>
    <div class="nav-section" style="margin-top:0.8rem">Catalog</div>
    <button class="nav-item" onclick="openNewProductModal()"><i class="fas fa-plus-circle"></i> Add Product</button>
    <a href="inventory.php" class="nav-item"><i class="fas fa-warehouse"></i> Inventory</a>
    <a href="reviews.php" class="nav-item"><i class="fas fa-star"></i> Reviews</a>
    <div class="nav-section" style="margin-top:0.8rem">Account</div>
    <a href="settings.php" class="nav-item"><i class="fas fa-sliders-h"></i> Settings</a>
    <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> Profile</a>
  </nav>
  <div class="sidebar-foot">
    <a href="../index.php" class="view-store" target="_blank"><i class="fas fa-external-link-alt"></i> View Storefront</a>
    <form method="POST" action="../logout.php">
      <button type="submit" class="signout-btn"><i class="fas fa-sign-out-alt"></i> Sign Out</button>
    </form>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
      <span class="page-title">Dashboard</span>
    </div>
    <div class="topbar-right">
      <a href="bookings.php" class="topbar-icon" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <?php if ($stats['bookings'] > 0): ?><span class="topbar-dot"></span><?php endif; ?>
      </a>
      <button class="topbar-icon" onclick="openNewProductModal()" aria-label="Add product" title="Add Product" style="border:none">
        <i class="fas fa-plus"></i>
      </button>
      <a href="profile.php" class="topbar-avatar" title="<?= htmlspecialchars($artisanName) ?>">
        <?= strtoupper(substr($artisanName, 0, 1)) ?>
      </a>
    </div>
  </div>

  <div class="content">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <div class="date"><?= date('l, F j, Y') ?></div>
        <h2>Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>,
          <em><?= htmlspecialchars(explode(' ', $artisanName)[0]) ?></em></h2>
        <p>Here's an overview of your BatikSL store today.</p>
      </div>
      <div class="welcome-actions">
        <button class="btn-primary" onclick="openNewProductModal()"><i class="fas fa-plus"></i> New Product</button>
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
        <div class="stat-value">
          <?php if ($stats['revenue'] >= 1000): ?>
            LKR <?= number_format($stats['revenue'] / 1000, 1) ?>k
          <?php else: ?>
            LKR <?= number_format($stats['revenue']) ?>
          <?php endif; ?>
        </div>
        <div class="stat-sub">All non-cancelled orders</div>
        <div class="sparkline-wrap">
          <?php foreach ($sparkValues as $v): ?>
            <div class="spark-bar" style="height:<?= max(3, round(($v / $sparkMax) * 32)) ?>px" title="LKR <?= number_format($v) ?>"></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="stat-card gold">
        <div class="stat-top">
          <span class="stat-label">Total Orders</span>
          <div class="stat-icon gold"><i class="fas fa-shopping-bag"></i></div>
        </div>
        <div class="stat-value"><?= number_format($stats['orders']) ?></div>
        <div class="stat-sub">Across all statuses</div>
      </div>
      <div class="stat-card green">
        <div class="stat-top">
          <span class="stat-label">Active Products</span>
          <div class="stat-icon green"><i class="fas fa-tags"></i></div>
        </div>
        <div class="stat-value"><?= $stats['products'] ?></div>
        <div class="stat-sub">
          <?php if (count($lowStockProducts) > 0): ?>
            <span class="down"><?= count($lowStockProducts) ?> low/out of stock</span>
          <?php else: ?>
            All well stocked
          <?php endif; ?>
        </div>
      </div>
      <div class="stat-card orange">
        <div class="stat-top">
          <span class="stat-label">Pending Bookings</span>
          <div class="stat-icon orange"><i class="fas fa-calendar"></i></div>
        </div>
        <div class="stat-value"><?= $stats['bookings'] ?></div>
        <div class="stat-sub">
          <?php if ($stats['bookings'] > 0): ?>
            <span class="down">Needs attention</span>
          <?php else: ?>
            All clear
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Orders + Low Stock + Quick Actions -->
    <div class="content-row">

      <!-- Recent Orders — now with View button -->
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
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><span class="order-num">#<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?></span></td>
              <td><?= htmlspecialchars($order['customer_name']) ?></td>
              <td>LKR <?= number_format((float)$order['total']) ?></td>
              <td>
                <span class="status-pill pill-<?= htmlspecialchars($order['status']) ?>">
                  <?= ucfirst(htmlspecialchars($order['status'])) ?>
                </span>
              </td>
              <td style="color:var(--warm-gray);font-size:0.8rem"><?= date('M j', strtotime($order['created_at'])) ?></td>
              <td>
                <button class="btn-view-order" onclick='openOrderDrawer(<?= json_encode([
                  "id"            => $order["id"],
                  "order_number"  => $order["order_number"] ?? $order["id"],
                  "customer_name" => $order["customer_name"],
                  "total"         => $order["total"],
                  "status"        => $order["status"],
                  "created_at"    => $order["created_at"],
                ]) ?>)'>
                  <i class="fas fa-eye"></i> View
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- Right column -->
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
              <span class="stock-level <?= ((int)$p['stock'] === 0) ? 'critical' : 'low' ?>">
                <?= ((int)$p['stock'] === 0) ? 'Out of stock' : $p['stock'] . ' left' ?>
              </span>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="panel">
          <div class="panel-head"><h3>Quick Actions</h3></div>
          <div class="quick-actions">
            <button class="quick-btn" onclick="openNewProductModal()">
              <i class="fas fa-plus-circle"></i> Add Product
            </button>
            <a href="bookings.php" class="quick-btn"><i class="fas fa-calendar-plus"></i> Bookings</a>
            <a href="inventory.php" class="quick-btn"><i class="fas fa-warehouse"></i> Inventory</a>
            <a href="settings.php" class="quick-btn"><i class="fas fa-sliders-h"></i> Settings</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Bookings -->
    <div class="panel">
      <div class="panel-head">
        <h3>Upcoming Live Sessions</h3>
        <a href="bookings.php">View all →</a>
      </div>
      <?php if (empty($bookings)): ?>
        <div class="empty-panel"><i class="fas fa-calendar"></i>No upcoming bookings yet.</div>
      <?php else: ?>
      <ul class="booking-list">
        <?php foreach ($bookings as $b):
          $sessionDate = !empty($b['session_date']) ? $b['session_date'] : date('Y-m-d');
          $d = new DateTime($sessionDate);
        ?>
        <li class="booking-item">
          <div class="booking-date-box">
            <span class="day"><?= $d->format('d') ?></span>
            <span class="mon"><?= $d->format('M') ?></span>
          </div>
          <div class="booking-meta">
            <div class="booking-customer"><?= htmlspecialchars($b['customer_name']) ?></div>
            <div class="booking-details">
              <?= htmlspecialchars($b['session_time'] ?? 'TBD') ?>
              &nbsp;·&nbsp;
              <?= (int)($b['group_size'] ?? 1) ?> guest<?= ((int)($b['group_size'] ?? 1) > 1) ? 's' : '' ?>
              &nbsp;·&nbsp;
              <?= htmlspecialchars($b['customer_email']) ?>
            </div>
          </div>
          <div class="booking-status">
            <span class="status-pill pill-<?= htmlspecialchars($b['status']) ?>"><?= ucfirst(htmlspecialchars($b['status'])) ?></span>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>

  </div><!-- /content -->
</main>

<!-- Mobile sidebar overlay -->
<div id="sidebarOverlay" onclick="toggleSidebar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:150;backdrop-filter:blur(2px)"></div>


<!-- ═══════════════════════════════════════════
     ORDER DRAWER
═══════════════════════════════════════════ -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeOrderDrawer()"></div>
<div class="order-drawer" id="orderDrawer">
  <div class="drawer-head">
    <h2 id="drawerTitle">Order Details</h2>
    <button class="drawer-close" onclick="closeOrderDrawer()"><i class="fas fa-times"></i></button>
  </div>
  <div class="drawer-body" id="drawerBody">
    <!-- filled by JS -->
  </div>
  <div class="drawer-actions">
    <a id="drawerFullViewBtn" href="orders.php" class="drawer-btn drawer-btn-outline"><i class="fas fa-external-link-alt"></i> Full View</a>
    <button class="drawer-btn drawer-btn-primary" onclick="printOrder()"><i class="fas fa-print"></i> Print</button>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     NEW PRODUCT MODAL
═══════════════════════════════════════════ -->
<div class="modal-overlay" id="productModalOverlay" onclick="handleModalOverlayClick(event)">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-head">
      <div class="modal-head-left">
        <div class="modal-head-label">Catalog</div>
        <h2 id="modalTitle">Add New Product</h2>
      </div>
      <button class="modal-close" onclick="closeNewProductModal()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-body">
      <form id="newProductForm" action="add-product.php" method="POST" enctype="multipart/form-data">

        <div class="form-grid">

          <!-- Basic info -->
          <div class="form-section-title">Basic Information</div>

          <div class="form-group full">
            <label class="form-label" for="prod_name">Product Name <span>*</span></label>
            <input class="form-input" type="text" id="prod_name" name="name"
                   placeholder="e.g. Hand-Painted Batik Saree" required maxlength="160">
          </div>

          <div class="form-group">
            <label class="form-label" for="prod_category">Category <span>*</span></label>
            <select class="form-select" id="prod_category" name="category" required>
              <option value="" disabled selected>Select category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>"><?= ucwords(str_replace('_', ' ', $cat)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="prod_sku">SKU / Code</label>
            <input class="form-input" type="text" id="prod_sku" name="sku"
                   placeholder="e.g. BTK-001" maxlength="60">
            <span class="form-hint">Leave blank to auto-generate</span>
          </div>

          <div class="form-group full">
            <label class="form-label" for="prod_desc">Description</label>
            <textarea class="form-textarea" id="prod_desc" name="description"
                      placeholder="Describe the materials, technique, and inspiration…" maxlength="2000"></textarea>
          </div>

          <!-- Pricing & Inventory -->
          <div class="form-section-title">Pricing & Inventory</div>

          <div class="form-group">
            <label class="form-label" for="prod_price">Price (LKR) <span>*</span></label>
            <input class="form-input" type="number" id="prod_price" name="price"
                   min="0" step="0.01" placeholder="0.00" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="prod_compare">Compare-at Price (LKR)</label>
            <input class="form-input" type="number" id="prod_compare" name="compare_at_price"
                   min="0" step="0.01" placeholder="0.00">
            <span class="form-hint">Show a crossed-out original price</span>
          </div>

          <div class="form-group">
            <label class="form-label" for="prod_stock">Stock Quantity <span>*</span></label>
            <input class="form-input" type="number" id="prod_stock" name="stock"
                   min="0" step="1" placeholder="0" required value="1">
          </div>

          <div class="form-group">
            <label class="form-label" for="prod_weight">Weight (grams)</label>
            <input class="form-input" type="number" id="prod_weight" name="weight"
                   min="0" step="1" placeholder="0">
          </div>

          <!-- Images -->
          <div class="form-section-title">Product Images</div>

          <div class="form-group full">
            <div class="image-upload-zone" id="uploadZone">
              <input type="file" name="images[]" id="prod_images" multiple accept="image/*" onchange="previewImages(event)">
              <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
              <div class="upload-text"><strong>Click to upload</strong> or drag & drop images</div>
              <div class="upload-hint">PNG, JPG, WEBP · Max 5MB each · Up to 8 images</div>
            </div>
            <div class="image-preview-grid" id="imagePreviewGrid"></div>
          </div>

          <!-- Visibility -->
          <div class="form-section-title">Visibility</div>

          <div class="toggle-row">
            <div>
              <div class="toggle-label">Publish immediately</div>
              <div class="toggle-sub">Product will be visible on the storefront right away</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="status" value="1" checked id="prod_status">
              <span class="toggle-track"></span>
            </label>
          </div>

          <div class="toggle-row" style="margin-top:0">
            <div>
              <div class="toggle-label">Featured product</div>
              <div class="toggle-sub">Show on homepage featured section</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="featured" value="1" id="prod_featured">
              <span class="toggle-track"></span>
            </label>
          </div>

        </div><!-- /form-grid -->

      </form>
    </div><!-- /modal-body -->

    <div class="modal-footer">
      <button class="modal-btn modal-btn-cancel" onclick="closeNewProductModal()">Cancel</button>
      <button class="modal-btn modal-btn-primary" onclick="submitNewProduct()">
        <i class="fas fa-plus"></i> Create Product
      </button>
    </div>
  </div>
</div>


<!-- Toast -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i> <span id="toastMsg"></span></div>


<script>
/* ── SIDEBAR ── */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const ov = document.getElementById('sidebarOverlay');
  const open = sb.classList.toggle('open');
  ov.style.display = open ? 'block' : 'none';
}

/* ── TOAST ── */
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

/* ════════════════════════════════════
   ORDER DRAWER
════════════════════════════════════ */
function openOrderDrawer(order) {
  const overlay = document.getElementById('drawerOverlay');
  const drawer  = document.getElementById('orderDrawer');
  const body    = document.getElementById('drawerBody');
  const title   = document.getElementById('drawerTitle');
  const fullBtn = document.getElementById('drawerFullViewBtn');

  title.textContent   = 'Order #' + order.order_number;
  fullBtn.href        = 'orders.php?id=' + order.id;

  // Render drawer content
  body.innerHTML = `
    <div class="drawer-section">
      <div class="drawer-section-title">Order Summary</div>
      <div class="order-meta-grid">
        <div class="order-meta-item">
          <div class="order-meta-label">Order Number</div>
          <div class="order-meta-value">#${escHtml(String(order.order_number))}</div>
        </div>
        <div class="order-meta-item">
          <div class="order-meta-label">Date Placed</div>
          <div class="order-meta-value">${formatDate(order.created_at)}</div>
        </div>
        <div class="order-meta-item">
          <div class="order-meta-label">Status</div>
          <div class="order-meta-value" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap">
            <span class="status-pill pill-${escHtml(order.status)}">${cap(order.status)}</span>
            <select class="status-select" id="statusSelect" onchange="updateOrderStatus(${order.id}, this.value)">
              ${['pending','processing','shipped','delivered','cancelled'].map(s =>
                `<option value="${s}" ${s === order.status ? 'selected' : ''}>${cap(s)}</option>`
              ).join('')}
            </select>
          </div>
        </div>
        <div class="order-meta-item">
          <div class="order-meta-label">Order Total</div>
          <div class="order-meta-value" style="color:var(--teal);font-weight:700">LKR ${Number(order.total).toLocaleString()}</div>
        </div>
      </div>
    </div>

    <div class="drawer-section">
      <div class="drawer-section-title">Customer</div>
      <div class="customer-card">
        <div class="customer-avatar-sm">${escHtml(order.customer_name.charAt(0).toUpperCase())}</div>
        <div class="customer-info-sm">
          <div class="customer-name-sm">${escHtml(order.customer_name)}</div>
          <div class="customer-detail-sm">View full profile on the orders page</div>
        </div>
      </div>
    </div>

    <div class="drawer-section">
      <div class="drawer-section-title">Items</div>
      <p style="font-size:0.82rem;color:var(--warm-gray);background:var(--cream);padding:0.9rem 1rem;border-radius:9px;border:1px solid var(--border)">
        <i class="fas fa-info-circle" style="color:var(--teal);margin-right:0.4rem"></i>
        Full item breakdown is available on the <a href="orders.php?id=${order.id}" style="color:var(--teal);font-weight:500">orders detail page</a>.
      </p>
    </div>

    <div class="order-total-row">
      <span class="order-total-label">Order Total</span>
      <span class="order-total-value">LKR ${Number(order.total).toLocaleString()}</span>
    </div>
  `;

  overlay.classList.add('active');
  drawer.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeOrderDrawer() {
  document.getElementById('drawerOverlay').classList.remove('active');
  document.getElementById('orderDrawer').classList.remove('active');
  document.body.style.overflow = '';
}

function updateOrderStatus(orderId, newStatus) {
  fetch('ajax/update-order-status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ order_id: orderId, status: newStatus })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Order status updated to ' + cap(newStatus));
      // Update pill in table row
      const pills = document.querySelectorAll('.order-table .status-pill');
      // Refresh page after short delay so table reflects update
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast('Error: ' + (data.message || 'Could not update status'));
    }
  })
  .catch(() => showToast('Network error — please try again'));
}

function printOrder() {
  window.open('orders.php?id=' + (document.getElementById('drawerFullViewBtn').href.split('id=')[1]) + '&print=1', '_blank');
}

/* ════════════════════════════════════
   NEW PRODUCT MODAL
════════════════════════════════════ */
function openNewProductModal() {
  document.getElementById('productModalOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
  // Close sidebar on mobile
  if (window.innerWidth <= 860) {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').style.display = 'none';
  }
}

function closeNewProductModal() {
  document.getElementById('productModalOverlay').classList.remove('active');
  document.body.style.overflow = '';
}

function handleModalOverlayClick(e) {
  if (e.target === document.getElementById('productModalOverlay')) closeNewProductModal();
}

function previewImages(event) {
  const grid  = document.getElementById('imagePreviewGrid');
  const files = Array.from(event.target.files).slice(0, 8);
  grid.innerHTML = '';
  files.forEach((file, i) => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'preview-thumb';
      div.innerHTML = `<img src="${e.target.result}" alt="Preview ${i+1}">
        <button type="button" class="remove-thumb" onclick="this.closest('.preview-thumb').remove()">
          <i class="fas fa-times"></i>
        </button>`;
      grid.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

// Drag-over styling
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
zone.addEventListener('drop',      e => {
  e.preventDefault(); zone.classList.remove('dragover');
  const input = zone.querySelector('input[type=file]');
  input.files = e.dataTransfer.files;
  previewImages({ target: input });
});

function submitNewProduct() {
  const form = document.getElementById('newProductForm');
  if (!form.checkValidity()) { form.reportValidity(); return; }

  const btn = document.querySelector('.modal-btn-primary');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

  const data = new FormData(form);
  fetch('ajax/add-product.php', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-plus"></i> Create Product';
    if (res.success) {
      closeNewProductModal();
      form.reset();
      document.getElementById('imagePreviewGrid').innerHTML = '';
      showToast('Product created successfully!');
      setTimeout(() => location.reload(), 2000);
    } else {
      showToast('Error: ' + (res.message || 'Could not save product'));
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-plus"></i> Create Product';
    // Fallback: submit form normally
    form.submit();
  });
}

/* ── Helpers ── */
function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function cap(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
function formatDate(str) {
  try { return new Date(str).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }); }
  catch(e) { return str; }
}

/* ── ESC key closes overlays ── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeOrderDrawer();
    closeNewProductModal();
  }
});
</script>
</body>
</html>