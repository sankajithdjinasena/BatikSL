<?php
// login.php — BatikSL Sign In
session_start();
require_once 'config/database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', true, true);
                // TODO: store hashed token in DB
            }

            // Redirect based on role
            if ($user['role'] === 'artisan') {
                header('Location: artisan/dashboard.php');
            } else {
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In | BatikSL</title>
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
    display: flex;
    flex-direction: column;
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
    font-size: 1.6rem; color: var(--charcoal);
    text-decoration: none; letter-spacing: -0.01em;
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

  /* ── MAIN LAYOUT ── */
  .login-wrapper {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 100vh;
    padding-top: 72px;
  }

  /* ── LEFT PANEL — Decorative ── */
  .login-left {
    position: relative;
    background: var(--charcoal);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 3.5rem;
  }

  /* Batik-inspired SVG background pattern */
  .batik-bg {
    position: absolute; inset: 0;
    opacity: 0.18;
    background-image:
      radial-gradient(circle at 20% 30%, var(--teal) 0%, transparent 50%),
      radial-gradient(circle at 80% 70%, var(--gold) 0%, transparent 45%),
      radial-gradient(circle at 50% 10%, var(--teal-light) 0%, transparent 40%);
  }

  /* SVG decorative pattern overlay */
  .batik-pattern {
    position: absolute; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3Ccircle cx='30' cy='30' r='10'/%3E%3Ccircle cx='30' cy='30' r='3'/%3E%3Cpath d='M30 10 L50 30 L30 50 L10 30 Z' fill='none' stroke='%23ffffff' stroke-opacity='0.04' stroke-width='1'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    background-size: 60px 60px;
  }

  /* Floating decorative rings */
  .deco-ring {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.07);
    animation: drift 20s ease-in-out infinite;
  }
  .deco-ring:nth-child(1) { width: 420px; height: 420px; top: -80px; right: -100px; animation-delay: 0s; }
  .deco-ring:nth-child(2) { width: 280px; height: 280px; top: 20%; left: -60px; animation-delay: -6s; }
  .deco-ring:nth-child(3) { width: 200px; height: 200px; bottom: 15%; right: 10%; animation-delay: -12s; border-color: rgba(201,168,76,0.12); }

  @keyframes drift {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    33% { transform: translateY(-18px) rotate(1deg); }
    66% { transform: translateY(10px) rotate(-1deg); }
  }

  /* Batik SVG illustration placeholder */
  .batik-illustration {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -55%);
    width: 340px;
    height: 340px;
    opacity: 0.22;
  }

  .left-content { position: relative; z-index: 2; }

  .left-tag {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.25em;
    text-transform: uppercase; color: var(--gold-light);
    margin-bottom: 1.2rem; display: block;
  }
  .left-headline {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3.5vw, 2.8rem);
    font-weight: 700; color: white;
    line-height: 1.2; margin-bottom: 1.2rem;
  }
  .left-headline em { font-style: italic; color: var(--gold-light); }
  .left-body {
    font-size: 0.9rem; color: rgba(255,255,255,0.55);
    line-height: 1.75; max-width: 320px; margin-bottom: 2.5rem;
  }

  .left-pills { display: flex; flex-wrap: wrap; gap: 0.6rem; }
  .left-pill {
    padding: 0.4rem 1rem; border-radius: 2rem;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    font-size: 0.78rem; color: rgba(255,255,255,0.6);
    display: flex; align-items: center; gap: 0.45rem;
  }
  .left-pill i { color: var(--teal-light); font-size: 0.72rem; }

  /* ── RIGHT PANEL — Form ── */
  .login-right {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 3rem 4rem;
    background: var(--cream);
  }

  .login-card {
    width: 100%;
    max-width: 420px;
  }

  .login-header { margin-bottom: 2.5rem; }
  .login-header .mobile-logo {
    display: none;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem; color: var(--charcoal);
    text-decoration: none; margin-bottom: 2rem;
  }
  .login-header .mobile-logo span { color: var(--gold); }
  .login-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700;
    color: var(--charcoal); line-height: 1.2;
    margin-bottom: 0.5rem;
  }
  .login-header h1 em { font-style: italic; color: var(--teal); }
  .login-header p {
    font-size: 0.9rem; color: var(--warm-gray); line-height: 1.6;
  }
  .login-header p a {
    color: var(--teal); text-decoration: none; font-weight: 500;
    transition: opacity 0.2s;
  }
  .login-header p a:hover { opacity: 0.75; }

  /* Role toggle */
  .role-toggle {
    display: flex; gap: 0; margin-bottom: 2rem;
    background: var(--sand); border-radius: 10px; padding: 4px;
    border: 1px solid var(--border);
  }
  .role-btn {
    flex: 1; padding: 0.6rem 1rem;
    background: none; border: none; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.84rem; font-weight: 500;
    color: var(--warm-gray); border-radius: 7px;
    transition: all 0.25s; display: flex;
    align-items: center; justify-content: center; gap: 0.5rem;
  }
  .role-btn.active {
    background: white;
    color: var(--charcoal);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  .role-btn i { font-size: 0.82rem; }
  .role-btn.active.artisan i { color: var(--gold); }
  .role-btn.active.customer i { color: var(--teal); }

  /* Form fields */
  .form-group { margin-bottom: 1.3rem; }
  .form-group label {
    display: block; font-size: 0.78rem; font-weight: 500;
    letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--warm-gray); margin-bottom: 0.55rem;
  }
  .input-wrap { position: relative; }
  .input-wrap .input-icon {
    position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
    color: var(--warm-gray); font-size: 0.88rem; pointer-events: none;
    transition: color 0.2s;
  }
  .input-wrap input {
    width: 100%; padding: 0.82rem 1rem 0.82rem 2.8rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.92rem;
    color: var(--charcoal); background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }
  .input-wrap input::placeholder { color: #bbb; }
  .input-wrap input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,0.08); }
  .input-wrap input:focus + .input-icon,
  .input-wrap:focus-within .input-icon { color: var(--teal); }

  /* Password toggle */
  .pw-toggle {
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 0.88rem;
    transition: color 0.2s; padding: 0; display: flex;
  }
  .pw-toggle:hover { color: var(--teal); }

  /* Remember & forgot */
  .form-options {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.8rem;
  }
  .checkbox-wrap {
    display: flex; align-items: center; gap: 0.55rem; cursor: pointer;
  }
  .checkbox-wrap input[type="checkbox"] { display: none; }
  .custom-check {
    width: 18px; height: 18px; border-radius: 5px;
    border: 1.5px solid var(--border); background: white;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s; flex-shrink: 0;
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
    font-size: 0.84rem; color: var(--warm-gray); user-select: none;
  }
  .forgot-link {
    font-size: 0.84rem; color: var(--teal); text-decoration: none;
    font-weight: 500; transition: opacity 0.2s;
  }
  .forgot-link:hover { opacity: 0.7; }

  /* Error / success alerts */
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
  .alert-error {
    background: #fef2f2; border: 1px solid #fecaca; color: var(--error);
  }
  .alert-success {
    background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--success);
  }
  .alert i { margin-top: 0.05rem; flex-shrink: 0; }

  /* Submit button */
  .btn-signin {
    width: 100%; padding: 0.9rem;
    background: var(--teal); color: white; border: none;
    border-radius: 10px; font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem; font-weight: 500; cursor: pointer;
    transition: background 0.25s, transform 0.15s, box-shadow 0.25s;
    display: flex; align-items: center; justify-content: center; gap: 0.6rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 14px rgba(15,118,110,0.25);
    position: relative; overflow: hidden;
  }
  .btn-signin::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 60%);
  }
  .btn-signin:hover {
    background: var(--teal-light);
    box-shadow: 0 6px 20px rgba(15,118,110,0.35);
    transform: translateY(-1px);
  }
  .btn-signin:active { transform: translateY(0); }
  .btn-signin.loading { pointer-events: none; opacity: 0.8; }
  .spinner {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    animation: spin 0.7s linear infinite;
    display: none;
  }
  .btn-signin.loading .spinner { display: block; }
  .btn-signin.loading .btn-text { display: none; }

  @keyframes spin { to { transform: rotate(360deg); } }

  /* Divider */
  .divider {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.5rem;
    font-size: 0.78rem; color: var(--warm-gray);
  }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
  }

  /* Register link */
  .register-link {
    text-align: center; font-size: 0.88rem; color: var(--warm-gray);
  }
  .register-link a {
    color: var(--teal); text-decoration: none; font-weight: 500;
    transition: opacity 0.2s;
  }
  .register-link a:hover { opacity: 0.75; }

  /* Artisan callout */
  .artisan-callout {
    display: none;
    margin-top: 2rem;
    padding: 1.1rem 1.2rem;
    background: linear-gradient(135deg, #fefce8 0%, #fffbeb 100%);
    border: 1px solid #fde68a; border-radius: 12px;
    font-size: 0.84rem; color: #92400e;
    line-height: 1.6;
  }
  .artisan-callout.visible { display: flex; gap: 0.7rem; align-items: flex-start; }
  .artisan-callout i { color: var(--gold); margin-top: 0.1rem; flex-shrink: 0; }

  /* Footer note */
  .login-footer-note {
    margin-top: 2.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    gap: 1.5rem; flex-wrap: wrap;
  }
  .login-footer-note a {
    font-size: 0.78rem; color: var(--warm-gray); text-decoration: none;
    transition: color 0.2s;
  }
  .login-footer-note a:hover { color: var(--teal); }
  .login-footer-note span { color: var(--border); }

  /* ── RESPONSIVE ── */
  @media (max-width: 860px) {
    nav { padding: 1rem 1.5rem; }
    .login-wrapper { grid-template-columns: 1fr; }
    .login-left { display: none; }
    .login-right { padding: 2.5rem 1.5rem; justify-content: flex-start; padding-top: 3rem; }
    .login-header .mobile-logo { display: block; }
  }

  @media (max-width: 480px) {
    .login-right { padding: 2rem 1.2rem; }
    .login-card { max-width: 100%; }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <a href="catalog.php" class="nav-back">
    <i class="fas fa-arrow-left"></i> Back to Shop
  </a>
</nav>

<!-- MAIN -->
<div class="login-wrapper">

  <!-- LEFT: Decorative Panel -->
  <div class="login-left">
    <div class="batik-bg"></div>
    <div class="batik-pattern"></div>

    <!-- Decorative rings -->
    <div class="deco-ring"></div>
    <div class="deco-ring"></div>
    <div class="deco-ring"></div>

    <!-- Central batik SVG illustration -->
    <svg class="batik-illustration" viewBox="0 0 340 340" xmlns="http://www.w3.org/2000/svg" fill="none">
      <!-- Concentric batik circles -->
      <circle cx="170" cy="170" r="160" stroke="white" stroke-width="0.8"/>
      <circle cx="170" cy="170" r="130" stroke="white" stroke-width="0.5"/>
      <circle cx="170" cy="170" r="100" stroke="white" stroke-width="0.8"/>
      <circle cx="170" cy="170" r="70"  stroke="white" stroke-width="0.5"/>
      <circle cx="170" cy="170" r="40"  stroke="white" stroke-width="0.8"/>
      <circle cx="170" cy="170" r="12"  fill="white" fill-opacity="0.4"/>
      <!-- Petal shapes -->
      <ellipse cx="170" cy="80" rx="16" ry="50" fill="white" fill-opacity="0.12" transform="rotate(0 170 170)"/>
      <ellipse cx="170" cy="80" rx="16" ry="50" fill="white" fill-opacity="0.12" transform="rotate(45 170 170)"/>
      <ellipse cx="170" cy="80" rx="16" ry="50" fill="white" fill-opacity="0.12" transform="rotate(90 170 170)"/>
      <ellipse cx="170" cy="80" rx="16" ry="50" fill="white" fill-opacity="0.12" transform="rotate(135 170 170)"/>
      <!-- Diamond grid -->
      <line x1="10" y1="170" x2="330" y2="170" stroke="white" stroke-width="0.4" stroke-opacity="0.3"/>
      <line x1="170" y1="10" x2="170" y2="330" stroke="white" stroke-width="0.4" stroke-opacity="0.3"/>
      <line x1="50" y1="50" x2="290" y2="290" stroke="white" stroke-width="0.4" stroke-opacity="0.3"/>
      <line x1="290" y1="50" x2="50" y2="290" stroke="white" stroke-width="0.4" stroke-opacity="0.3"/>
      <!-- Outer dots -->
      <circle cx="170" cy="10" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="170" cy="330" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="10" cy="170" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="330" cy="170" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="50" cy="50" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="290" cy="290" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="290" cy="50" r="3" fill="white" fill-opacity="0.5"/>
      <circle cx="50" cy="290" r="3" fill="white" fill-opacity="0.5"/>
    </svg>

    <!-- Text content -->
    <div class="left-content">
      <span class="left-tag">Handcrafted Since 1989</span>
      <h2 class="left-headline">
        Where Art Meets<br><em>Heritage</em>
      </h2>
      <p class="left-body">
        Join the BatikSL community and discover authentic handcrafted batik from the heart of Kandy. Every piece carries generations of artisanship.
      </p>
      <div class="left-pills">
        <span class="left-pill"><i class="fas fa-check-circle"></i> Authentic Handmade</span>
        <span class="left-pill"><i class="fas fa-check-circle"></i> Natural Dyes</span>
        <span class="left-pill"><i class="fas fa-check-circle"></i> Direct from Artisan</span>
        <span class="left-pill"><i class="fas fa-check-circle"></i> Worldwide Shipping</span>
      </div>
    </div>
  </div>

  <!-- RIGHT: Form Panel -->
  <div class="login-right">
    <div class="login-card">

      <!-- Mobile logo (hidden on desktop) -->
      <div class="login-header">
        <a href="index.php" class="mobile-logo">Batik<span>SL</span></a>
        <h1>Welcome <em>Back</em></h1>
        <p>Don't have an account? <a href="register.php">Create one — it's free</a></p>
      </div>

      <!-- Role toggle -->
      <div class="role-toggle" id="roleToggle">
        <button class="role-btn active customer" id="btnCustomer" onclick="setRole('customer')">
          <i class="fas fa-user"></i> Customer
        </button>
        <button class="role-btn artisan" id="btnArtisan" onclick="setRole('artisan')">
          <i class="fas fa-paint-brush"></i> Artisan / Admin
        </button>
      </div>

      <!-- Alert messages -->
      <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success) ?>
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" id="loginForm" novalidate>
        <input type="hidden" name="role" id="roleInput" value="customer">

        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <i class="fas fa-envelope input-icon"></i>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="you@example.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              autocomplete="email"
              required>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              autocomplete="current-password"
              required>
            <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password">
              <i class="far fa-eye" id="pwIcon"></i>
            </button>
          </div>
        </div>

        <div class="form-options">
          <label class="checkbox-wrap">
            <input type="checkbox" name="remember" id="rememberCheck">
            <div class="custom-check"></div>
            <span class="checkbox-label">Remember me</span>
          </label>
          <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-signin" id="submitBtn">
          <span class="btn-text"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</span>
          <div class="spinner"></div>
        </button>
      </form>

      <div class="divider">or</div>

      <div class="register-link">
        New to BatikSL? <a href="register.php">Create your account</a>
      </div>

      <!-- Artisan callout (shown when artisan tab active) -->
      <div class="artisan-callout" id="artisanCallout">
        <i class="fas fa-paint-brush"></i>
        <div>
          <strong>Artisan Portal</strong><br>
          Sign in to manage your products, track orders, and handle bookings from your dashboard.
          <br><br>
          Not registered as an artisan? <a href="mailto:hello@batiksl.com" style="color:var(--teal);text-decoration:none;font-weight:500">Contact us</a> to join.
        </div>
      </div>

      <!-- Footer links -->
      <div class="login-footer-note">
        <a href="index.php">Home</a>
        <span>·</span>
        <a href="catalog.php">Shop</a>
        <span>·</span>
        <a href="#">Privacy Policy</a>
        <span>·</span>
        <a href="#">Terms</a>
      </div>

    </div>
  </div>

</div><!-- /login-wrapper -->

<script>
  // ── Role toggle ──
  let currentRole = 'customer';

  function setRole(role) {
    currentRole = role;
    document.getElementById('roleInput').value = role;

    const btnC = document.getElementById('btnCustomer');
    const btnA = document.getElementById('btnArtisan');
    const callout = document.getElementById('artisanCallout');
    const heading = document.querySelector('.login-header h1');

    if (role === 'artisan') {
      btnA.classList.add('active');
      btnC.classList.remove('active');
      callout.classList.add('visible');
      heading.innerHTML = 'Artisan <em>Portal</em>';
    } else {
      btnC.classList.add('active');
      btnA.classList.remove('active');
      callout.classList.remove('visible');
      heading.innerHTML = 'Welcome <em>Back</em>';
    }
  }

  // ── Password visibility toggle ──
  const pwToggle = document.getElementById('pwToggle');
  const pwInput  = document.getElementById('password');
  const pwIcon   = document.getElementById('pwIcon');

  pwToggle.addEventListener('click', () => {
    const isText = pwInput.type === 'text';
    pwInput.type = isText ? 'password' : 'text';
    pwIcon.className = isText ? 'far fa-eye' : 'far fa-eye-slash';
  });

  // ── Custom checkbox visual ──
  document.getElementById('rememberCheck').addEventListener('change', function () {
    // Handled purely by CSS :checked selector
  });

  // ── Form submit loading state ──
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const btn      = document.getElementById('submitBtn');

    if (!email || !password) return; // Let native validation handle

    btn.classList.add('loading');
  });

  // ── Restore loading state on back navigation ──
  window.addEventListener('pageshow', () => {
    document.getElementById('submitBtn').classList.remove('loading');
  });
</script>
</body>
</html>
