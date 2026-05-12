<?php
// booking_confirm.php - Booking Confirmation Page
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$booking_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$booking_id) {
    header('Location: booking.php');
    exit;
}

// Fetch the booking — make sure it belongs to this user
$stmt = $pdo->prepare("
    SELECT b.*, u.name, u.email, u.phone
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: booking.php');
    exit;
}

// Derived values
$price_per_person = 9000;
$session_total    = $booking['group_size'] * $price_per_person;
$deposit          = (float) $booking['deposit_amount'];
$balance          = $session_total - $deposit;

// Format date nicely
$date_obj     = new DateTime($booking['session_date']);
$display_date = $date_obj->format('l, j F Y');  // e.g. Saturday, 17 May 2026
$short_date   = $date_obj->format('j M Y');

// Booking reference
$reference = 'BSL-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Confirmed | BatikSL</title>
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
    --green: #16a34a;
    --green-light: #dcfce7;
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
  .nav-links a:hover { color: var(--teal); }
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

  /* ── CONFIRMATION HERO ── */
  .confirm-hero {
    background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal) 60%, #1a9e94 100%);
    padding: 4rem 4rem 5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .confirm-hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.04) 0%, transparent 60%),
                      radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 50%);
  }

  /* Animated check circle */
  .check-circle {
    width: 90px; height: 90px; border-radius: 50%;
    background: white;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.8rem;
    position: relative; z-index: 1;
    box-shadow: 0 0 0 16px rgba(255,255,255,0.12);
    animation: pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
  }
  @keyframes pop {
    0%   { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
  }
  .check-circle i {
    font-size: 2.2rem;
    color: var(--teal);
  }

  .confirm-hero .tag {
    font-size: 0.72rem; font-weight: 500; letter-spacing: 0.22em;
    text-transform: uppercase; color: rgba(255,255,255,0.65);
    margin-bottom: 0.7rem; display: block;
    animation: fadeUp 0.5s 0.2s both;
  }
  .confirm-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700; color: white;
    line-height: 1.15; margin-bottom: 0.8rem;
    animation: fadeUp 0.5s 0.3s both;
  }
  .confirm-hero h1 em { font-style: italic; color: rgba(255,255,255,0.75); }
  .confirm-hero .sub {
    font-size: 1rem; color: rgba(255,255,255,0.72);
    max-width: 480px; margin: 0 auto 1.5rem; line-height: 1.7;
    animation: fadeUp 0.5s 0.4s both;
  }
  .ref-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(8px);
    border-radius: 2rem; padding: 0.5rem 1.4rem;
    font-size: 0.85rem; color: white; font-weight: 500;
    letter-spacing: 0.06em;
    animation: fadeUp 0.5s 0.5s both;
  }
  .ref-badge i { opacity: 0.7; font-size: 0.8rem; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── MAIN CONTENT ── */
  .confirm-body {
    max-width: 900px;
    margin: -2.5rem auto 0;
    padding: 0 2rem 5rem;
    position: relative; z-index: 2;
  }

  /* ── CARD ── */
  .confirm-card {
    background: white;
    border-radius: 16px;
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 1.5rem;
    animation: fadeUp 0.5s 0.55s both;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  }
  .confirm-card-header {
    padding: 1.3rem 1.8rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 0.8rem;
  }
  .confirm-card-header .icon {
    width: 36px; height: 36px; border-radius: 9px;
    background: #e6f4f2;
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 1rem; flex-shrink: 0;
  }
  .confirm-card-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; font-weight: 700;
  }
  .confirm-card-body { padding: 1.8rem; }

  /* ── BOOKING DETAILS GRID ── */
  .details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
  }
  .detail-item {
    background: var(--sand);
    border-radius: 10px;
    padding: 1.1rem 1.2rem;
  }
  .detail-item .d-label {
    font-size: 0.68rem; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.4rem;
  }
  .detail-item .d-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem; font-weight: 700; color: var(--charcoal);
    line-height: 1.3;
  }
  .detail-item .d-value.teal { color: var(--teal); }

  /* ── PRICE TABLE ── */
  .price-table { width: 100%; border-collapse: collapse; }
  .price-table tr { border-bottom: 1px solid var(--border); }
  .price-table tr:last-child { border-bottom: none; }
  .price-table td {
    padding: 0.75rem 0;
    font-size: 0.9rem;
  }
  .price-table td:last-child { text-align: right; font-weight: 500; }
  .price-table .label-cell { color: var(--warm-gray); }
  .price-table .total-row td {
    padding-top: 1rem;
    font-weight: 700; font-size: 1rem;
    color: var(--charcoal);
    border-top: 2px solid var(--border);
  }
  .price-table .total-row td:last-child {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem; color: var(--teal);
  }
  .price-table .deposit-row td {
    color: var(--green);
    font-size: 0.88rem;
  }
  .price-table .deposit-row td:last-child {
    font-weight: 600;
  }
  .price-table .balance-row td {
    color: var(--warm-gray);
    font-size: 0.85rem;
  }

  /* ── GUEST INFO ── */
  .guest-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  .guest-item {}
  .guest-item .g-label {
    font-size: 0.68rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.25rem;
  }
  .guest-item .g-value {
    font-size: 0.95rem; font-weight: 500; color: var(--charcoal);
  }

  /* ── WHAT'S NEXT STEPS ── */
  .next-steps {
    display: flex; flex-direction: column; gap: 1rem;
  }
  .next-step {
    display: flex; align-items: flex-start; gap: 1rem;
  }
  .step-dot {
    width: 36px; height: 36px; border-radius: 50%;
    background: #e6f4f2; color: var(--teal);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
    margin-top: 0.1rem;
  }
  .step-content h4 {
    font-size: 0.92rem; font-weight: 600; color: var(--charcoal);
    margin-bottom: 0.2rem;
  }
  .step-content p {
    font-size: 0.83rem; color: var(--warm-gray); line-height: 1.6;
  }

  /* ── SPECIAL REQUESTS BOX ── */
  .requests-box {
    background: var(--sand); border-radius: 10px;
    padding: 1rem 1.2rem;
    font-size: 0.88rem; color: var(--charcoal); line-height: 1.6;
  }
  .requests-box .req-label {
    font-size: 0.68rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.4rem; display: block;
  }

  /* ── INCLUDED LIST ── */
  .included-list {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
  }
  .included-item {
    display: flex; align-items: center; gap: 0.6rem;
    font-size: 0.88rem; color: var(--charcoal);
  }
  .included-item i { color: var(--teal); font-size: 0.8rem; flex-shrink: 0; }

  /* ── ACTION BUTTONS ── */
  .action-row {
    display: flex; gap: 1rem; flex-wrap: wrap;
    animation: fadeUp 0.5s 0.7s both;
  }
  .btn-primary {
    flex: 1; min-width: 180px;
    padding: 0.9rem 1.5rem;
    background: var(--teal); color: white;
    border: none; border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: background 0.25s;
  }
  .btn-primary:hover { background: var(--teal-dark); }
  .btn-outline {
    flex: 1; min-width: 180px;
    padding: 0.9rem 1.5rem;
    background: transparent; color: var(--charcoal);
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: all 0.25s;
  }
  .btn-outline:hover { border-color: var(--teal); color: var(--teal); }

  /* ── STATUS PILL ── */
  .status-pill {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.25rem 0.75rem; border-radius: 2rem;
    font-size: 0.72rem; font-weight: 600; letter-spacing: 0.06em;
    text-transform: uppercase;
  }
  .status-pill.pending  { background: #fef9c3; color: #854d0e; }
  .status-pill.confirmed { background: var(--green-light); color: var(--green); }
  .status-pill i { font-size: 0.55rem; }

  /* ── DIVIDER ── */
  .divider {
    border: none; border-top: 1px solid var(--border);
    margin: 1.4rem 0;
  }

  /* ── FOOTER ── */
  footer {
    background: #111; color: rgba(255,255,255,0.6);
    padding: 5rem 4rem 2.5rem;
    margin-top: 3rem;
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
  @media (max-width: 860px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links { display: none; }
    .confirm-hero { padding: 3rem 1.5rem 4rem; }
    .confirm-body { padding: 0 1rem 4rem; }
    .details-grid { grid-template-columns: 1fr 1fr; }
    .guest-grid { grid-template-columns: 1fr; }
    .included-list { grid-template-columns: 1fr; }
    .breadcrumb-bar { padding-left: 1.5rem; padding-right: 1.5rem; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
    footer { padding: 4rem 1.5rem 2rem; }
  }
  @media (max-width: 520px) {
    .details-grid { grid-template-columns: 1fr; }
    .action-row { flex-direction: column; }
  }

  /* ── PRINT ── */
  @media print {
    nav, .breadcrumb-bar, .action-row, footer { display: none; }
    .confirm-hero { background: var(--teal) !important; -webkit-print-color-adjust: exact; }
    body { background: white; }
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
      <span class="cart-badge">0</span>
    </a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="account.php" style="font-size:0.82rem;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;color:var(--warm-gray);text-decoration:none;transition:color 0.25s;">
        <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
      </a>
      <a href="logout.php" class="nav-signin">Sign Out</a>
    <?php else: ?>
      <a href="login.php" class="nav-signin">Sign In</a>
    <?php endif; ?>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb-bar">
  <a href="index.php">Home</a>
  <i class="fas fa-chevron-right"></i>
  <a href="booking.php">Live Session</a>
  <i class="fas fa-chevron-right"></i>
  <span>Booking Confirmed</span>
</div>

<!-- HERO -->
<div class="confirm-hero">
  <div class="check-circle">
    <i class="fas fa-check"></i>
  </div>
  <span class="tag">Booking Received</span>
  <h1>You're all set,<br><em><?= htmlspecialchars(explode(' ', $booking['name'])[0]) ?>!</em></h1>
  <p class="sub">
    Your live batik session has been booked. A confirmation email will be sent to
    <strong style="color:white"><?= htmlspecialchars($booking['email']) ?></strong> shortly.
  </p>
  <span class="ref-badge">
    <i class="fas fa-hashtag"></i>
    Reference: <?= $reference ?>
  </span>
</div>

<!-- MAIN CONTENT -->
<div class="confirm-body">

  <!-- Booking Details Card -->
  <div class="confirm-card">
    <div class="confirm-card-header">
      <div class="icon"><i class="fas fa-calendar-check"></i></div>
      <h2>Session Details</h2>
      <span class="status-pill <?= $booking['status'] ?>" style="margin-left:auto">
        <i class="fas fa-circle"></i>
        <?= ucfirst($booking['status']) ?>
      </span>
    </div>
    <div class="confirm-card-body">
      <div class="details-grid">
        <div class="detail-item">
          <div class="d-label">Date</div>
          <div class="d-value"><?= htmlspecialchars($display_date) ?></div>
        </div>
        <div class="detail-item">
          <div class="d-label">Time</div>
          <div class="d-value"><?= htmlspecialchars($booking['session_time']) ?></div>
        </div>
        <div class="detail-item">
          <div class="d-label">Group Size</div>
          <div class="d-value"><?= (int) $booking['group_size'] ?> <?= $booking['group_size'] == 1 ? 'Person' : 'People' ?></div>
        </div>
        <div class="detail-item">
          <div class="d-label">Duration</div>
          <div class="d-value">2 Hours</div>
        </div>
        <div class="detail-item">
          <div class="d-label">Location</div>
          <div class="d-value">Kandy Workshop</div>
        </div>
        <div class="detail-item">
          <div class="d-label">Booking Ref</div>
          <div class="d-value teal"><?= $reference ?></div>
        </div>
      </div>

      <?php if (!empty($booking['special_requests'])): ?>
        <div style="margin-top:1.2rem">
          <div class="requests-box">
            <span class="req-label">Special Requests</span>
            <?= htmlspecialchars($booking['special_requests']) ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Payment Summary Card -->
  <div class="confirm-card">
    <div class="confirm-card-header">
      <div class="icon"><i class="fas fa-receipt"></i></div>
      <h2>Payment Summary</h2>
    </div>
    <div class="confirm-card-body">
      <table class="price-table">
        <tr>
          <td class="label-cell">Session rate</td>
          <td>LKR 9,000 × <?= (int) $booking['group_size'] ?> people</td>
          <td>LKR <?= number_format($session_total) ?></td>
        </tr>
        <tr class="total-row">
          <td colspan="2">Session Total</td>
          <td>LKR <?= number_format($session_total) ?></td>
        </tr>
        <tr class="deposit-row">
          <td colspan="2"><i class="fas fa-check-circle" style="margin-right:0.4rem"></i>Deposit Paid (30%)</td>
          <td>− LKR <?= number_format($deposit) ?></td>
        </tr>
        <tr class="balance-row">
          <td colspan="2"><i class="fas fa-clock" style="margin-right:0.4rem"></i>Balance due on arrival</td>
          <td>LKR <?= number_format($balance) ?></td>
        </tr>
      </table>

      <hr class="divider">

      <div style="display:flex;align-items:center;gap:0.6rem;font-size:0.82rem;color:var(--warm-gray)">
        <i class="fas fa-shield-alt" style="color:var(--teal)"></i>
        Payment secured via PayHere · Booking ID #<?= $booking_id ?>
      </div>
    </div>
  </div>

  <!-- Guest Info Card -->
  <div class="confirm-card">
    <div class="confirm-card-header">
      <div class="icon"><i class="fas fa-user"></i></div>
      <h2>Guest Information</h2>
    </div>
    <div class="confirm-card-body">
      <div class="guest-grid">
        <div class="guest-item">
          <div class="g-label">Full Name</div>
          <div class="g-value"><?= htmlspecialchars($booking['name']) ?></div>
        </div>
        <div class="guest-item">
          <div class="g-label">Email</div>
          <div class="g-value"><?= htmlspecialchars($booking['email']) ?></div>
        </div>
        <div class="guest-item">
          <div class="g-label">Phone</div>
          <div class="g-value"><?= htmlspecialchars($booking['phone'] ?: '—') ?></div>
        </div>
        <div class="guest-item">
          <div class="g-label">Booked On</div>
          <div class="g-value"><?= (new DateTime($booking['created_at']))->format('j M Y, g:i A') ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- What's Included Card -->
  <div class="confirm-card">
    <div class="confirm-card-header">
      <div class="icon"><i class="fas fa-gift"></i></div>
      <h2>What's Included</h2>
    </div>
    <div class="confirm-card-body">
      <div class="included-list">
        <div class="included-item"><i class="fas fa-check-circle"></i> All wax & dyeing materials</div>
        <div class="included-item"><i class="fas fa-check-circle"></i> Pre-cut batik fabric</div>
        <div class="included-item"><i class="fas fa-check-circle"></i> Expert artisan guide</div>
        <div class="included-item"><i class="fas fa-check-circle"></i> Finished piece to take home</div>
        <div class="included-item"><i class="fas fa-check-circle"></i> Traditional tools & equipment</div>
        <div class="included-item"><i class="fas fa-check-circle"></i> Light refreshments</div>
      </div>
    </div>
  </div>

  <!-- What's Next Card -->
  <div class="confirm-card">
    <div class="confirm-card-header">
      <div class="icon"><i class="fas fa-road"></i></div>
      <h2>What Happens Next</h2>
    </div>
    <div class="confirm-card-body">
      <div class="next-steps">
        <div class="next-step">
          <div class="step-dot">1</div>
          <div class="step-content">
            <h4>Confirmation Email</h4>
            <p>A detailed confirmation with your booking reference has been sent to <?= htmlspecialchars($booking['email']) ?>. Check your spam folder if it doesn't arrive within a few minutes.</p>
          </div>
        </div>
        <div class="next-step">
          <div class="step-dot">2</div>
          <div class="step-content">
            <h4>We'll Get in Touch</h4>
            <p>Our team will reach you on <?= htmlspecialchars($booking['phone'] ?: 'your registered phone') ?> 24 hours before your session with directions and any final details.</p>
          </div>
        </div>
        <div class="next-step">
          <div class="step-dot">3</div>
          <div class="step-content">
            <h4>Day of Your Session</h4>
            <p>Arrive 10 minutes early at the Kandy Workshop. Pay your remaining balance of <strong>LKR <?= number_format($balance) ?></strong> on arrival. Wear clothes you don't mind getting dye on!</p>
          </div>
        </div>
        <div class="next-step">
          <div class="step-dot"><i class="fas fa-phone-alt" style="font-size:0.75rem"></i></div>
          <div class="step-content">
            <h4>Need to Cancel or Change?</h4>
            <p>Free cancellation up to 48 hours before your session. Call us on <strong>+94 77 123 4567</strong> or email <strong>hello@batiksl.com</strong> with your reference <strong><?= $reference ?></strong>.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="action-row">
    <a href="index.php" class="btn-outline">
      <i class="fas fa-home"></i> Back to Home
    </a>
    <a href="catalog.php" class="btn-outline">
      <i class="fas fa-shopping-bag"></i> Browse Shop
    </a>
    <a href="booking.php" class="btn-primary">
      <i class="fas fa-plus"></i> Book Another Session
    </a>
    <button onclick="window.print()" class="btn-outline">
      <i class="fas fa-print"></i> Print Confirmation
    </button>
  </div>

</div><!-- /confirm-body -->

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

</body>
</html>