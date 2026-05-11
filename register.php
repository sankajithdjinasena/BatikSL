<?php
// register.php — BatikSL Customer Registration
session_start();
require_once 'config/database.php';

// Already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';
$fields  = []; // repopulate on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';
    $terms    = isset($_POST['terms']);

    $fields = compact('name', 'email', 'phone');

    // Validation
    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'Please accept the Terms & Conditions to continue.';
    } else {
        // Check duplicate email
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $chk->execute(['email' => $email]);
        if ($chk->fetch()) {
            $error = 'An account with this email already exists. <a href="login.php">Sign in instead?</a>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, role, created_at)
                VALUES (:name, :email, :password, :phone, 'customer', NOW())
            ");
            $ins->execute([
                'name'     => $name,
                'email'    => $email,
                'password' => $hashed,
                'phone'    => $phone,
            ]);
            $newId = $pdo->lastInsertId();

            // Auto-login
            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'customer';

            header('Location: index.php?welcome=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account | BatikSL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
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
    --error: #dc2626;
    --success: #16a34a;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--charcoal);
    min-height: 100vh;
    display: flex; flex-direction: column;
    overflow-x: hidden;
  }

  /* ── NAVBAR ── */
  nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    padding: 1.1rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(250,247,242,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }
  .nav-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem; color: var(--charcoal); text-decoration: none;
  }
  .nav-logo span { color: var(--gold); }
  .nav-back {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.82rem; font-weight: 500; letter-spacing: 0.06em;
    text-transform: uppercase; text-decoration: none;
    color: var(--warm-gray); transition: color 0.2s;
  }
  .nav-back:hover { color: var(--teal); }
  .nav-back i { font-size: 0.7rem; }

  /* ── LAYOUT ── */
  .register-wrapper {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    min-height: 100vh;
    padding-top: 72px;
  }

  /* ── LEFT PANEL ── */
  .register-left {
    position: relative;
    background: var(--teal-dark);
    overflow: hidden;
    display: flex; flex-direction: column;
    justify-content: space-between;
    padding: 3.5rem;
  }

  /* Layered backgrounds */
  .left-glow {
    position: absolute; inset: 0; pointer-events: none;
    background:
      radial-gradient(ellipse 80% 60% at 20% 0%, rgba(20,184,166,0.25) 0%, transparent 70%),
      radial-gradient(ellipse 60% 50% at 90% 100%, rgba(201,168,76,0.2) 0%, transparent 60%);
  }
  .left-grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
      linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
  }

  /* Step progress on left */
  .left-top { position: relative; z-index: 2; }
  .step-track {
    display: flex; flex-direction: column; gap: 0;
    margin-top: 2.5rem;
  }
  .step-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding-bottom: 2rem; position: relative;
  }
  .step-item:last-child { padding-bottom: 0; }
  .step-item:not(:last-child)::after {
    content: '';
    position: absolute; left: 15px; top: 32px; bottom: 0;
    width: 1px; background: rgba(255,255,255,0.12);
  }
  .step-item.done:not(:last-child)::after { background: rgba(20,184,166,0.5); }
  .step-circle {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1.5px solid rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; color: rgba(255,255,255,0.45);
    flex-shrink: 0; transition: all 0.3s; z-index: 1;
  }
  .step-item.active .step-circle {
    background: var(--teal-light); border-color: var(--teal-light);
    color: white; box-shadow: 0 0 0 4px rgba(20,184,166,0.2);
  }
  .step-item.done .step-circle {
    background: rgba(20,184,166,0.2); border-color: var(--teal-light);
    color: var(--teal-light);
  }
  .step-text { padding-top: 0.35rem; }
  .step-name {
    font-size: 0.88rem; font-weight: 500;
    color: rgba(255,255,255,0.4);
    transition: color 0.3s;
  }
  .step-item.active .step-name { color: white; }
  .step-item.done  .step-name { color: rgba(255,255,255,0.6); }
  .step-desc {
    font-size: 0.76rem; color: rgba(255,255,255,0.28);
    margin-top: 0.15rem; line-height: 1.4;
    transition: color 0.3s;
  }
  .step-item.active .step-desc { color: rgba(255,255,255,0.5); }

  /* Benefits at bottom of left */
  .left-bottom { position: relative; z-index: 2; }
  .benefits-title {
    font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase;
    color: rgba(255,255,255,0.3); margin-bottom: 1rem;
  }
  .benefit-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 0;
    border-top: 1px solid rgba(255,255,255,0.06);
    font-size: 0.85rem; color: rgba(255,255,255,0.55);
  }
  .benefit-row i { color: var(--teal-light); font-size: 0.82rem; width: 14px; }

  /* ── RIGHT PANEL ── */
  .register-right {
    display: flex; flex-direction: column;
    justify-content: center; align-items: center;
    padding: 3.5rem 4rem;
    background: var(--cream);
    overflow-y: auto;
  }

  .register-card {
    width: 100%; max-width: 460px;
  }

  /* Header */
  .reg-header { margin-bottom: 2.2rem; }
  .mobile-logo {
    display: none;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem; color: var(--charcoal);
    text-decoration: none; margin-bottom: 2rem;
  }
  .mobile-logo span { color: var(--gold); }
  .reg-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700; line-height: 1.2;
    color: var(--charcoal); margin-bottom: 0.45rem;
  }
  .reg-header h1 em { font-style: italic; color: var(--teal); }
  .reg-header p { font-size: 0.9rem; color: var(--warm-gray); line-height: 1.6; }
  .reg-header p a { color: var(--teal); text-decoration: none; font-weight: 500; }
  .reg-header p a:hover { opacity: 0.75; }

  /* Progress bar (mobile) */
  .progress-bar-wrap {
    display: none;
    height: 4px; background: var(--border); border-radius: 4px;
    margin-bottom: 2rem; overflow: hidden;
  }
  .progress-bar-fill {
    height: 100%; background: var(--teal);
    border-radius: 4px; transition: width 0.4s ease;
  }

  /* Step labels (mobile) */
  .step-label-mobile {
    display: none;
    font-size: 0.74rem; font-weight: 500; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--teal); margin-bottom: 0.3rem;
  }

  /* Form steps */
  .form-step { display: none; }
  .form-step.active { display: block; animation: fadeUp 0.35s ease; }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Form groups */
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .form-group { margin-bottom: 1.25rem; }
  .form-group.full { grid-column: 1 / -1; }
  .form-group label {
    display: block; font-size: 0.76rem; font-weight: 500;
    letter-spacing: 0.07em; text-transform: uppercase;
    color: var(--warm-gray); margin-bottom: 0.5rem;
  }
  .input-wrap { position: relative; }
  .input-icon {
    position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
    color: var(--warm-gray); font-size: 0.85rem; pointer-events: none;
    transition: color 0.2s;
  }
  .input-wrap input,
  .input-wrap select {
    width: 100%; padding: 0.82rem 1rem 0.82rem 2.75rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    color: var(--charcoal); background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none; appearance: none;
  }
  .input-wrap input::placeholder { color: #bbb; }
  .input-wrap input:focus,
  .input-wrap select:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(15,118,110,0.08);
  }
  .input-wrap:focus-within .input-icon { color: var(--teal); }

  /* Validation states */
  .input-wrap.valid input   { border-color: var(--success); }
  .input-wrap.invalid input { border-color: var(--error); }
  .input-status {
    position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
    font-size: 0.8rem; pointer-events: none;
  }
  .input-status.ok  { color: var(--success); }
  .input-status.err { color: var(--error); }

  .field-hint {
    font-size: 0.74rem; color: var(--warm-gray);
    margin-top: 0.4rem; display: flex; align-items: center; gap: 0.35rem;
  }
  .field-hint.error { color: var(--error); }
  .field-hint i { font-size: 0.7rem; }

  /* Password strength */
  .pw-toggle {
    position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 0.88rem;
    transition: color 0.2s; display: flex; padding: 0;
  }
  .pw-toggle:hover { color: var(--teal); }

  .strength-bar {
    display: flex; gap: 4px; margin-top: 0.5rem;
  }
  .strength-seg {
    height: 3px; flex: 1; border-radius: 3px;
    background: var(--border); transition: background 0.3s;
  }
  .strength-label {
    font-size: 0.72rem; color: var(--warm-gray); margin-top: 0.3rem;
  }

  /* Alert */
  .alert {
    padding: 0.9rem 1.1rem; border-radius: 10px;
    font-size: 0.86rem; margin-bottom: 1.4rem;
    display: flex; align-items: flex-start; gap: 0.6rem;
    animation: slideIn 0.3s ease;
  }
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
  .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--success); }
  .alert a { color: inherit; font-weight: 600; }

  /* Checkbox */
  .checkbox-wrap {
    display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;
    margin-bottom: 1.6rem;
  }
  .checkbox-wrap input { display: none; }
  .custom-check {
    width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
    border: 1.5px solid var(--border); background: white;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s; margin-top: 1px;
  }
  .checkbox-wrap input:checked + .custom-check {
    background: var(--teal); border-color: var(--teal);
  }
  .custom-check::after {
    content: ''; width: 5px; height: 9px;
    border-right: 2px solid white; border-bottom: 2px solid white;
    transform: rotate(45deg) translate(-1px, -1px);
    opacity: 0; transition: opacity 0.15s;
  }
  .checkbox-wrap input:checked + .custom-check::after { opacity: 1; }
  .checkbox-label {
    font-size: 0.85rem; color: var(--warm-gray); line-height: 1.5; user-select: none;
  }
  .checkbox-label a { color: var(--teal); text-decoration: none; font-weight: 500; }

  /* Buttons */
  .btn-row { display: flex; gap: 0.8rem; }
  .btn-next, .btn-back, .btn-submit {
    flex: 1; padding: 0.9rem;
    border-radius: 10px; font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem; font-weight: 500; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    transition: all 0.25s; border: none;
  }
  .btn-next, .btn-submit {
    background: var(--teal); color: white;
    box-shadow: 0 4px 14px rgba(15,118,110,0.25);
  }
  .btn-next:hover, .btn-submit:hover {
    background: var(--teal-light);
    box-shadow: 0 6px 20px rgba(15,118,110,0.35);
    transform: translateY(-1px);
  }
  .btn-back {
    background: white; color: var(--warm-gray);
    border: 1.5px solid var(--border); flex: 0 0 auto; padding: 0.9rem 1.4rem;
  }
  .btn-back:hover { border-color: var(--charcoal); color: var(--charcoal); }
  .btn-submit.loading { opacity: 0.8; pointer-events: none; }
  .spinner {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
    animation: spin 0.7s linear infinite; display: none;
  }
  .btn-submit.loading .spinner { display: block; }
  .btn-submit.loading .btn-text { display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Sign in link */
  .signin-link {
    text-align: center; font-size: 0.87rem;
    color: var(--warm-gray); margin-top: 1.4rem;
  }
  .signin-link a { color: var(--teal); text-decoration: none; font-weight: 500; }

  /* Footer links */
  .reg-footer {
    margin-top: 2.2rem; padding-top: 1.5rem;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    gap: 1.5rem; flex-wrap: wrap;
  }
  .reg-footer a {
    font-size: 0.76rem; color: var(--warm-gray);
    text-decoration: none; transition: color 0.2s;
  }
  .reg-footer a:hover { color: var(--teal); }
  .reg-footer span { color: var(--border); }

  /* ── RESPONSIVE ── */
  @media (max-width: 900px) {
    nav { padding: 1rem 1.5rem; }
    .register-wrapper { grid-template-columns: 1fr; }
    .register-left { display: none; }
    .register-right { padding: 2.5rem 1.5rem; justify-content: flex-start; padding-top: 3rem; }
    .mobile-logo { display: block; }
    .progress-bar-wrap { display: block; }
    .step-label-mobile { display: block; }
    .form-row { grid-template-columns: 1fr; }
  }
  @media (max-width: 480px) {
    .register-right { padding: 2rem 1.2rem; }
  }
</style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <a href="login.php" class="nav-back"><i class="fas fa-arrow-left"></i> Sign In</a>
</nav>

<div class="register-wrapper">

  <!-- LEFT: Steps panel -->
  <div class="register-left">
    <div class="left-top">
      <div style="font-family:'Playfair Display',serif;font-size:1.5rem;color:white;margin-bottom:0.3rem">
        Batik<span style="color:var(--gold)">SL</span>
      </div>
      <div style="font-size:0.7rem;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.35);margin-bottom:0.5rem">
        Create your account
      </div>

      <div class="step-track" id="stepTrack">
        <div class="step-item active" data-step="1">
          <div class="step-circle"><i class="fas fa-user"></i></div>
          <div class="step-text">
            <div class="step-name">Your Details</div>
            <div class="step-desc">Name, email & phone number</div>
          </div>
        </div>
        <div class="step-item" data-step="2">
          <div class="step-circle"><i class="fas fa-lock"></i></div>
          <div class="step-text">
            <div class="step-name">Secure Password</div>
            <div class="step-desc">Choose a strong password</div>
          </div>
        </div>
        <div class="step-item" data-step="3">
          <div class="step-circle"><i class="fas fa-check"></i></div>
          <div class="step-text">
            <div class="step-name">Confirm & Join</div>
            <div class="step-desc">Review and create account</div>
          </div>
        </div>
      </div>
    </div>

    <div class="left-bottom">
      <div class="benefits-title">Why join BatikSL?</div>
      <div class="benefit-row"><i class="fas fa-heart"></i> Save items to your wishlist</div>
      <div class="benefit-row"><i class="fas fa-truck"></i> Track your orders in real time</div>
      <div class="benefit-row"><i class="fas fa-calendar"></i> Book exclusive batik sessions</div>
      <div class="benefit-row"><i class="fas fa-tags"></i> Early access to new collections</div>
    </div>
  </div>

  <!-- RIGHT: Form panel -->
  <div class="register-right">
    <div class="register-card">

      <a href="index.php" class="mobile-logo">Batik<span>SL</span></a>

      <!-- Mobile progress -->
      <div class="progress-bar-wrap" id="progressBarWrap">
        <div class="progress-bar-fill" id="progressFill" style="width:33%"></div>
      </div>

      <div class="step-label-mobile" id="stepLabelMobile">Step 1 of 3 — Your Details</div>

      <div class="reg-header">
        <h1 id="stepHeading">Join the <em>Craft</em></h1>
        <p id="stepSubhead">Create your BatikSL account. Already have one? <a href="login.php">Sign in</a></p>
      </div>

      <!-- Server-side error (shown after POST) -->
      <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle" style="margin-top:0.05rem;flex-shrink:0"></i>
        <span><?= $error ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" id="regForm" novalidate>

        <!-- ── STEP 1: Personal Info ── -->
        <div class="form-step active" id="step1">
          <div class="form-group">
            <label for="name">Full Name <span style="color:var(--error)">*</span></label>
            <div class="input-wrap" id="wrapName">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="name" name="name" placeholder="e.g. Amara Perera"
                value="<?= htmlspecialchars($fields['name'] ?? '') ?>" autocomplete="name">
              <span class="input-status" id="statusName"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address <span style="color:var(--error)">*</span></label>
            <div class="input-wrap" id="wrapEmail">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" id="email" name="email" placeholder="you@example.com"
                value="<?= htmlspecialchars($fields['email'] ?? '') ?>" autocomplete="email">
              <span class="input-status" id="statusEmail"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number <span style="color:rgba(107,101,96,0.5);font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
            <div class="input-wrap">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="phone" name="phone" placeholder="+94 77 000 0000"
                value="<?= htmlspecialchars($fields['phone'] ?? '') ?>" autocomplete="tel">
            </div>
          </div>

          <button type="button" class="btn-next" onclick="goStep(2)">
            Continue <i class="fas fa-arrow-right"></i>
          </button>
        </div>

        <!-- ── STEP 2: Password ── -->
        <div class="form-step" id="step2">
          <div class="form-group">
            <label for="password">Password <span style="color:var(--error)">*</span></label>
            <div class="input-wrap" id="wrapPassword">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password">
              <button type="button" class="pw-toggle" id="pwToggle1" aria-label="Show password">
                <i class="far fa-eye" id="pwIcon1"></i>
              </button>
            </div>
            <div class="strength-bar" id="strengthBar">
              <div class="strength-seg" id="seg1"></div>
              <div class="strength-seg" id="seg2"></div>
              <div class="strength-seg" id="seg3"></div>
              <div class="strength-seg" id="seg4"></div>
            </div>
            <div class="strength-label" id="strengthLabel">Enter a password</div>
          </div>

          <div class="form-group">
            <label for="confirm">Confirm Password <span style="color:var(--error)">*</span></label>
            <div class="input-wrap" id="wrapConfirm">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="confirm" name="confirm" placeholder="Repeat your password" autocomplete="new-password">
              <button type="button" class="pw-toggle" id="pwToggle2" aria-label="Show password">
                <i class="far fa-eye" id="pwIcon2"></i>
              </button>
            </div>
            <div class="field-hint" id="confirmHint" style="display:none">
              <i class="fas fa-times-circle"></i> Passwords do not match
            </div>
          </div>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goStep(1)">
              <i class="fas fa-arrow-left"></i>
            </button>
            <button type="button" class="btn-next" onclick="goStep(3)">
              Continue <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- ── STEP 3: Review & Submit ── -->
        <div class="form-step" id="step3">

          <!-- Summary card -->
          <div style="background:var(--sand);border:1px solid var(--border);border-radius:12px;padding:1.3rem 1.4rem;margin-bottom:1.5rem">
            <div style="font-size:0.7rem;font-weight:500;letter-spacing:0.15em;text-transform:uppercase;color:var(--warm-gray);margin-bottom:1rem">
              Account Summary
            </div>
            <div style="display:grid;gap:0.65rem">
              <div style="display:flex;justify-content:space-between;font-size:0.88rem">
                <span style="color:var(--warm-gray)">Name</span>
                <strong id="summaryName">—</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.88rem">
                <span style="color:var(--warm-gray)">Email</span>
                <strong id="summaryEmail">—</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.88rem">
                <span style="color:var(--warm-gray)">Phone</span>
                <strong id="summaryPhone">—</strong>
              </div>
              <div style="display:flex;justify-content:space-between;font-size:0.88rem">
                <span style="color:var(--warm-gray)">Password</span>
                <strong>••••••••</strong>
              </div>
            </div>
          </div>

          <label class="checkbox-wrap">
            <input type="checkbox" name="terms" id="termsCheck">
            <div class="custom-check"></div>
            <span class="checkbox-label">
              I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a>
              and <a href="privacy.php" target="_blank">Privacy Policy</a>.
              I understand my data will be used to manage my account and orders.
            </span>
          </label>

          <div class="btn-row">
            <button type="button" class="btn-back" onclick="goStep(2)">
              <i class="fas fa-arrow-left"></i>
            </button>
            <button type="submit" class="btn-submit" id="submitBtn">
              <span class="btn-text"><i class="fas fa-user-plus"></i> Create Account</span>
              <div class="spinner"></div>
            </button>
          </div>
        </div>

      </form>

      <div class="signin-link">
        Already have an account? <a href="login.php">Sign in here</a>
      </div>

      <div class="reg-footer">
        <a href="index.php">Home</a><span>·</span>
        <a href="catalog.php">Shop</a><span>·</span>
        <a href="#">Privacy</a><span>·</span>
        <a href="#">Terms</a>
      </div>
    </div>
  </div>
</div>

<script>
  // ── Step state ──
  let currentStep = <?= ($error) ? 1 : 1 ?>;
  const totalSteps = 3;

  const headings = [
    '', // 1-indexed
    'Join the <em>Craft</em>',
    'Secure Your <em>Account</em>',
    'Almost <em>There</em>',
  ];
  const subheads = [
    '',
    'Create your BatikSL account. Already have one? <a href="login.php">Sign in</a>',
    'Choose a strong password to protect your account.',
    'Review your details and create your account.',
  ];
  const stepLabels = ['', 'Step 1 of 3 — Your Details', 'Step 2 of 3 — Password', 'Step 3 of 3 — Confirm & Join'];
  const progressPct = ['', '33%', '66%', '100%'];

  function goStep(n) {
    // Validate before advancing
    if (n > currentStep) {
      if (!validateStep(currentStep)) return;
    }

    // Hide current
    document.getElementById('step' + currentStep).classList.remove('active');
    // Update sidebar
    updateSidebar(currentStep, n);
    currentStep = n;
    // Show new
    document.getElementById('step' + currentStep).classList.add('active');
    // Update header
    document.getElementById('stepHeading').innerHTML  = headings[n];
    document.getElementById('stepSubhead').innerHTML  = subheads[n];
    document.getElementById('stepLabelMobile').textContent = stepLabels[n];
    document.getElementById('progressFill').style.width = progressPct[n];

    // Populate summary on step 3
    if (n === 3) populateSummary();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateSidebar(prev, next) {
    const items = document.querySelectorAll('.step-item');
    items.forEach((el, i) => {
      const s = i + 1;
      el.classList.remove('active', 'done');
      if (s < next)      el.classList.add('done');
      else if (s === next) el.classList.add('active');
    });
  }

  // ── Validation ──
  function validateStep(step) {
    if (step === 1) {
      const name  = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      if (!name) { shakeField('wrapName'); return false; }
      if (!email || !isEmail(email)) { shakeField('wrapEmail'); return false; }
      return true;
    }
    if (step === 2) {
      const pw  = document.getElementById('password').value;
      const con = document.getElementById('confirm').value;
      if (pw.length < 8) { shakeField('wrapPassword'); return false; }
      if (pw !== con) {
        document.getElementById('confirmHint').style.display = 'flex';
        shakeField('wrapConfirm'); return false;
      }
      return true;
    }
    return true;
  }

  function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  function shakeField(id) {
    const el = document.getElementById(id);
    el.style.animation = 'none';
    el.offsetHeight; // reflow
    el.style.animation = 'shake 0.4s ease';
  }

  // Shake keyframes via JS
  const style = document.createElement('style');
  style.textContent = `@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-6px)}
    40%{transform:translateX(6px)}
    60%{transform:translateX(-4px)}
    80%{transform:translateX(4px)}
  }`;
  document.head.appendChild(style);

  // ── Live validation ──
  const nameEl  = document.getElementById('name');
  const emailEl = document.getElementById('email');

  nameEl.addEventListener('blur', () => {
    const w = document.getElementById('wrapName');
    const s = document.getElementById('statusName');
    if (nameEl.value.trim().length >= 2) {
      w.className = 'input-wrap valid';
      s.innerHTML = '<i class="fas fa-check-circle" style="color:var(--success)"></i>';
    } else {
      w.className = 'input-wrap invalid';
      s.innerHTML = '<i class="fas fa-times-circle" style="color:var(--error)"></i>';
    }
  });

  emailEl.addEventListener('blur', () => {
    const w = document.getElementById('wrapEmail');
    const s = document.getElementById('statusEmail');
    if (isEmail(emailEl.value.trim())) {
      w.className = 'input-wrap valid';
      s.innerHTML = '<i class="fas fa-check-circle" style="color:var(--success)"></i>';
    } else {
      w.className = 'input-wrap invalid';
      s.innerHTML = '<i class="fas fa-times-circle" style="color:var(--error)"></i>';
    }
  });

  // ── Password strength ──
  const pwEl = document.getElementById('password');
  const segs = ['seg1','seg2','seg3','seg4'].map(id => document.getElementById(id));
  const colors = { 1:'#ef4444', 2:'#f97316', 3:'#eab308', 4:'#22c55e' };
  const labels = { 0:'Enter a password', 1:'Weak', 2:'Fair', 3:'Good', 4:'Strong 🎉' };

  pwEl.addEventListener('input', () => {
    const v = pwEl.value;
    let score = 0;
    if (v.length >= 8)           score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[0-9]/.test(v))         score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    segs.forEach((s, i) => {
      s.style.background = i < score ? (colors[score] || '#22c55e') : 'var(--border)';
    });
    document.getElementById('strengthLabel').textContent = labels[score];
  });

  // Confirm match live
  const conEl = document.getElementById('confirm');
  conEl.addEventListener('input', () => {
    const hint = document.getElementById('confirmHint');
    if (conEl.value && conEl.value !== pwEl.value) {
      hint.style.display = 'flex';
      hint.className = 'field-hint error';
    } else if (conEl.value && conEl.value === pwEl.value) {
      hint.style.display = 'flex';
      hint.className = 'field-hint';
      hint.innerHTML = '<i class="fas fa-check-circle" style="color:var(--success)"></i> Passwords match';
    } else {
      hint.style.display = 'none';
    }
  });

  // ── PW toggles ──
  function setupPwToggle(btnId, iconId, inputId) {
    document.getElementById(btnId).addEventListener('click', () => {
      const inp  = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      const show = inp.type === 'password';
      inp.type = show ? 'text' : 'password';
      icon.className = show ? 'far fa-eye-slash' : 'far fa-eye';
    });
  }
  setupPwToggle('pwToggle1','pwIcon1','password');
  setupPwToggle('pwToggle2','pwIcon2','confirm');

  // ── Populate summary ──
  function populateSummary() {
    document.getElementById('summaryName').textContent  = document.getElementById('name').value.trim()  || '—';
    document.getElementById('summaryEmail').textContent = document.getElementById('email').value.trim() || '—';
    document.getElementById('summaryPhone').textContent = document.getElementById('phone').value.trim() || 'Not provided';
  }

  // ── Submit loading state ──
  document.getElementById('regForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    if (!document.getElementById('termsCheck').checked) return;
    btn.classList.add('loading');
  });

  window.addEventListener('pageshow', () => {
    document.getElementById('submitBtn').classList.remove('loading');
  });
</script>
</body>
</html>