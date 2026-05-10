<?php
// booking.php - Experience Booking Page
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Live Batik Session | BatikSL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
  .nav-signin:hover {; color: white; }

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
  .header-meta {
    display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem;
    font-size: 0.85rem; color: var(--warm-gray);
  }
  .header-meta .badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.85rem; border-radius: 2rem;
    background: #e6f4f2; color: var(--teal);
    font-size: 0.75rem; font-weight: 500;
  }

  /* ── SESSION INFO STRIP ── */
  .session-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-bottom: 1px solid var(--border);
  }
  .session-strip-item {
    padding: 1.8rem 4rem;
    display: flex; align-items: center; gap: 1rem;
    border-right: 1px solid var(--border);
  }
  .session-strip-item:last-child { border-right: none; }
  .strip-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: #e6f4f2; display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 1.1rem; flex-shrink: 0;
  }
  .strip-label {
    font-size: 0.68rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.2rem;
  }
  .strip-value {
    font-family: 'Playfair Display', serif;
    font-size: 1rem; font-weight: 700; color: var(--charcoal);
  }

  /* ── BODY LAYOUT ── */
  .booking-body {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 0;
    min-height: 70vh;
    align-items: start;
    padding: 3rem 4rem;
    gap: 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
  }

  /* ── FORM PANEL ── */
  .form-panel {
    display: flex; flex-direction: column; gap: 2rem;
  }

  .form-section {
    background: white;
    border-radius: 14px;
    border: 1px solid var(--border);
    overflow: hidden;
  }
  .form-section-header {
    padding: 1.4rem 1.8rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 0.8rem;
  }
  .form-section-header .step-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--teal); color: white;
    font-size: 0.75rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .form-section-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem; font-weight: 700;
  }
  .form-section-body { padding: 1.8rem; }

  /* Date picker */
  .flatpickr-input {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    color: var(--charcoal);
    background: white;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
  }
  .flatpickr-input:focus { border-color: var(--teal); }

  /* Time slots */
  .time-slots-label {
    font-size: 0.72rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin: 1.4rem 0 0.8rem; display: block;
  }
  .time-slots { display: flex; gap: 0.8rem; flex-wrap: wrap; }
  .time-slot {
    padding: 0.65rem 1.4rem;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    background: white;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; color: var(--warm-gray);
    cursor: pointer; transition: all 0.2s;
  }
  .time-slot:hover { border-color: var(--teal); color: var(--teal); background: #e6f4f2; }
  .time-slot.selected {
    border-color: var(--teal); background: var(--teal);
    color: white; font-weight: 500;
  }

  /* Group size stepper */
  .group-size-section { margin-top: 1.4rem; }
  .group-size-label {
    font-size: 0.72rem; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.8rem; display: block;
  }
  .stepper {
    display: inline-flex; align-items: center;
    border: 1.5px solid var(--border); border-radius: 10px;
    overflow: hidden;
  }
  .stepper-btn {
    width: 44px; height: 44px;
    background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
  }
  .stepper-btn:hover { background: var(--sand); color: var(--charcoal); }
  .stepper-val {
    min-width: 56px; text-align: center;
    font-size: 1.05rem; font-weight: 500;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    padding: 0 0.5rem; line-height: 44px;
  }
  .group-max-note {
    font-size: 0.78rem; color: var(--warm-gray);
    margin-top: 0.6rem; display: block;
  }

  /* Form fields */
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 0.8rem; }
  .field-row.single { grid-template-columns: 1fr; }

  .field-group { display: flex; flex-direction: column; gap: 0.4rem; }
  .field-group label {
    font-size: 0.72rem; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--warm-gray);
  }
  .field-group input,
  .field-group textarea {
    padding: 0.7rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem; color: var(--charcoal);
    background: white; outline: none;
    transition: border-color 0.2s;
    resize: none;
  }
  .field-group input:focus,
  .field-group textarea:focus { border-color: var(--teal); }
  .field-group input::placeholder,
  .field-group textarea::placeholder { color: #c5bdb6; }

  /* Submit button */
  .submit-btn {
    width: 100%; padding: 1rem;
    background: var(--teal); color: white;
    border: none; border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem; font-weight: 500;
    cursor: pointer; transition: background 0.25s;
    display: flex; align-items: center; justify-content: center; gap: 0.6rem;
    margin-top: 0.4rem;
  }
  .submit-btn:hover { background: var(--teal-dark); }
  .submit-note {
    text-align: center; font-size: 0.76rem; color: var(--warm-gray);
    margin-top: 0.8rem; line-height: 1.6;
  }
  .submit-note a { color: var(--teal); text-decoration: none; }

  /* ── SUMMARY PANEL ── */
  .summary-panel {
    position: sticky; top: calc(72px + 1.5rem);
  }
  .summary-card {
    background: white; border-radius: 14px;
    border: 1px solid var(--border); overflow: hidden;
  }
  .summary-card-header {
    padding: 1.4rem 1.6rem;
    background: var(--charcoal); color: white;
    display: flex; align-items: center; gap: 0.7rem;
  }
  .summary-card-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem; font-weight: 700;
  }
  .summary-card-body { padding: 1.4rem 1.6rem; }

  .summary-row {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--border);
    font-size: 0.88rem;
  }
  .summary-row:last-of-type { border-bottom: none; }
  .summary-row .s-label { color: var(--warm-gray); }
  .summary-row .s-value { font-weight: 500; color: var(--charcoal); }
  .summary-row .s-value.teal { color: var(--teal); }
  .summary-row.total {
    margin-top: 0.5rem; padding-top: 1rem;
    border-top: 2px solid var(--border); border-bottom: none;
  }
  .summary-row.total .s-label { font-weight: 600; font-size: 0.9rem; color: var(--charcoal); }
  .summary-row.total .s-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem; color: var(--teal); font-weight: 700;
  }

  .summary-breakdown {
    background: var(--sand); border-radius: 8px;
    padding: 0.9rem 1rem; margin-top: 1rem;
    font-size: 0.78rem; color: var(--warm-gray); line-height: 1.7;
  }
  .summary-breakdown strong { color: var(--charcoal); }

  /* Trust badges */
  .trust-badges {
    background: white; border-radius: 14px;
    border: 1px solid var(--border);
    padding: 1.2rem 1.4rem;
    margin-top: 1rem;
    display: flex; flex-direction: column; gap: 0.7rem;
  }
  .trust-item {
    display: flex; align-items: center; gap: 0.7rem;
    font-size: 0.81rem; color: var(--warm-gray);
  }
  .trust-item i { color: var(--teal); width: 16px; text-align: center; }

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
    .session-strip { grid-template-columns: repeat(2, 1fr); }
    .session-strip-item { padding: 1.4rem 2rem; }
  }
  @media (max-width: 860px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links, .nav-signin { display: none; }
    .catalog-header, .breadcrumb-bar { padding-left: 1.5rem; padding-right: 1.5rem; }
    .booking-body { grid-template-columns: 1fr; padding: 1.5rem; }
    .summary-panel { position: static; }
    .session-strip { grid-template-columns: repeat(2, 1fr); }
    .session-strip-item { padding: 1.2rem 1.5rem; border-right: none; border-bottom: 1px solid var(--border); }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
    footer { padding: 4rem 1.5rem 2rem; }
  }
  @media (max-width: 520px) {
    .field-row { grid-template-columns: 1fr; }
    .session-strip { grid-template-columns: 1fr; }
    .time-slots { gap: 0.5rem; }
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
    <li><a href="booking.php" class="active">Live Session</a></li>
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
  <a href="booking.php">Live Session</a>
  <i class="fas fa-chevron-right"></i>
  <span>Book a Session</span>
</div>

<!-- PAGE HEADER -->
<div class="catalog-header">
  <div>
    <span class="tag">Kandy Workshop Experience</span>
    <h1>Book a Live <em>Batik</em> Session</h1>
  </div>
  <div class="header-meta">
    <span class="badge"><i class="fas fa-circle" style="color:#4ade80;font-size:0.55rem"></i> Sessions available this week</span>
    <span>LKR 9,000 per person</span>
  </div>
</div>

<!-- SESSION STRIP -->
<div class="session-strip">
  <div class="session-strip-item">
    <div class="strip-icon"><i class="fas fa-clock"></i></div>
    <div>
      <div class="strip-label">Duration</div>
      <div class="strip-value">2 Hours</div>
    </div>
  </div>
  <div class="session-strip-item">
    <div class="strip-icon"><i class="fas fa-users"></i></div>
    <div>
      <div class="strip-label">Group Size</div>
      <div class="strip-value">Max 8 People</div>
    </div>
  </div>
  <div class="session-strip-item">
    <div class="strip-icon"><i class="fas fa-map-marker-alt"></i></div>
    <div>
      <div class="strip-label">Location</div>
      <div class="strip-value">Kandy Workshop</div>
    </div>
  </div>
  <div class="session-strip-item">
    <div class="strip-icon"><i class="fas fa-language"></i></div>
    <div>
      <div class="strip-label">Languages</div>
      <div class="strip-value">EN / SI / TA</div>
    </div>
  </div>
</div>

<!-- BOOKING BODY -->
<div class="booking-body">

  <!-- LEFT: FORM PANEL -->
  <div class="form-panel">

    <!-- Step 1: Date & Time -->
    <div class="form-section">
      <div class="form-section-header">
        <span class="step-num">1</span>
        <h2>Choose Date & Time</h2>
      </div>
      <div class="form-section-body">
        <div class="field-group">
          <label>Select Date</label>
          <input type="text" id="datepicker" placeholder="Pick a date…">
        </div>

        <span class="time-slots-label">Available Time Slots</span>
        <div class="time-slots">
          <button class="time-slot" data-time="10:00 AM">10:00 AM</button>
          <button class="time-slot" data-time="2:00 PM">2:00 PM</button>
          <button class="time-slot" data-time="4:00 PM">4:00 PM</button>
        </div>

        <div class="group-size-section">
          <span class="group-size-label">Number of People</span>
          <div class="stepper">
            <button class="stepper-btn" onclick="changeGroupSize(-1)"><i class="fas fa-minus" style="font-size:0.75rem"></i></button>
            <span class="stepper-val" id="groupSize">2</span>
            <button class="stepper-btn" onclick="changeGroupSize(1)"><i class="fas fa-plus" style="font-size:0.75rem"></i></button>
          </div>
          <span class="group-max-note">Maximum 8 people per session</span>
        </div>
      </div>
    </div>

    <!-- Step 2: Contact Info -->
    <div class="form-section">
      <div class="form-section-header">
        <span class="step-num">2</span>
        <h2>Your Details</h2>
      </div>
      <div class="form-section-body">
        <div class="field-row">
          <div class="field-group">
            <label>First Name</label>
            <input type="text" placeholder="Amara">
          </div>
          <div class="field-group">
            <label>Last Name</label>
            <input type="text" placeholder="Perera">
          </div>
        </div>
        <div class="field-row">
          <div class="field-group">
            <label>Email</label>
            <input type="email" placeholder="you@example.com">
          </div>
          <div class="field-group">
            <label>Phone</label>
            <input type="tel" placeholder="+94 77 000 0000">
          </div>
        </div>
        <div class="field-row single">
          <div class="field-group">
            <label>Special Requests</label>
            <textarea rows="3" placeholder="Dietary requirements, accessibility needs, celebrating an occasion…"></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="form-section">
      <div class="form-section-body">
        <button class="submit-btn">
          <i class="fas fa-lock" style="font-size:0.8rem"></i>
          Confirm Booking — Pay 30% Deposit
        </button>
        <p class="submit-note">
          Secure payment via PayHere · Balance of <strong id="balanceNote">LKR 12,600</strong> paid on arrival.<br>
          By booking you agree to our <a href="#">cancellation policy</a>.
        </p>
      </div>
    </div>

  </div><!-- /form-panel -->

  <!-- RIGHT: SUMMARY PANEL -->
  <div class="summary-panel">
    <div class="summary-card">
      <div class="summary-card-header">
        <i class="fas fa-receipt" style="opacity:0.6"></i>
        <h3>Booking Summary</h3>
      </div>
      <div class="summary-card-body">
        <div class="summary-row">
          <span class="s-label">Date</span>
          <span class="s-value" id="summaryDate">Not selected</span>
        </div>
        <div class="summary-row">
          <span class="s-label">Time</span>
          <span class="s-value" id="summaryTime">—</span>
        </div>
        <div class="summary-row">
          <span class="s-label">People</span>
          <span class="s-value" id="summaryPeople">2</span>
        </div>
        <div class="summary-row">
          <span class="s-label">Rate</span>
          <span class="s-value">LKR 9,000 / person</span>
        </div>
        <div class="summary-row">
          <span class="s-label">Session Total</span>
          <span class="s-value teal" id="sessionTotal">LKR 18,000</span>
        </div>
        <div class="summary-row total">
          <span class="s-label">Deposit Due (30%)</span>
          <span class="s-value" id="depositAmount">LKR 5,400</span>
        </div>

        <div class="summary-breakdown">
          <strong>What's included:</strong><br>
          All materials · Wax, dyes & fabric · Expert artisan guide · Finished piece to take home
        </div>
      </div>
    </div>

    <div class="trust-badges">
      <div class="trust-item"><i class="fas fa-shield-alt"></i> Secure payment via PayHere</div>
      <div class="trust-item"><i class="fas fa-undo"></i> Free cancellation up to 48 hrs before</div>
      <div class="trust-item"><i class="fas fa-star"></i> 4.9 / 5 from 200+ guests</div>
      <div class="trust-item"><i class="fas fa-phone-alt"></i> Need help? +94 77 123 4567</div>
    </div>
  </div><!-- /summary-panel -->

</div><!-- /booking-body -->

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
  // ── Flatpickr ──
  flatpickr("#datepicker", {
    minDate: "today",
    dateFormat: "Y-m-d",
    onChange: function(selectedDates) {
      const d = selectedDates[0];
      document.getElementById('summaryDate').innerText =
        d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }
  });

  // ── Time slots ──
  document.querySelectorAll('.time-slot').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      document.getElementById('summaryTime').innerText = btn.dataset.time;
    });
  });

  // ── Group size stepper ──
  let groupSize = 2;
  const pricePerPerson = 9000;

  function updatePrices() {
    const total   = groupSize * pricePerPerson;
    const deposit = Math.round(total * 0.3);
    const balance = total - deposit;
    document.getElementById('groupSize').innerText       = groupSize;
    document.getElementById('summaryPeople').innerText   = groupSize;
    document.getElementById('sessionTotal').innerText    = 'LKR ' + total.toLocaleString();
    document.getElementById('depositAmount').innerText   = 'LKR ' + deposit.toLocaleString();
    document.getElementById('balanceNote').innerText     = 'LKR ' + balance.toLocaleString();
  }

  function changeGroupSize(delta) {
    const newVal = groupSize + delta;
    if (newVal >= 1 && newVal <= 8) {
      groupSize = newVal;
      updatePrices();
    }
  }

  updatePrices();
</script>
</body>
</html>