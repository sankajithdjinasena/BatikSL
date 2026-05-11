<?php
// logout.php — BatikSL Secure Sign Out
session_start();

// Capture name before destroying
$userName = $_SESSION['user_name'] ?? 'there';
$userRole = $_SESSION['user_role'] ?? 'customer';

// Full session cleanup
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Clear remember-me cookie if set
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    // TODO: delete hashed token from DB
}

// Extract first name
$firstName = explode(' ', trim($userName))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signed Out | BatikSL</title>
<meta http-equiv="refresh" content="6;url=index.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root {
    --teal: #0f766e;
    --teal-light: #14b8a6;
    --cream: #faf7f2;
    --sand: #f0ebe1;
    --charcoal: #1c1c1c;
    --warm-gray: #6b6560;
    --gold: #c9a84c;
    --gold-light: #e2c97e;
    --border: #e8e3da;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--charcoal);
    color: white;
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    overflow: hidden; position: relative;
  }

  /* ── Background atmosphere ── */
  .bg-glow {
    position: fixed; inset: 0; pointer-events: none;
    background:
      radial-gradient(ellipse 70% 60% at 15% 20%, rgba(15,118,110,0.18) 0%, transparent 65%),
      radial-gradient(ellipse 60% 50% at 85% 80%, rgba(201,168,76,0.12) 0%, transparent 60%),
      radial-gradient(ellipse 50% 40% at 50% 50%, rgba(20,184,166,0.06) 0%, transparent 70%);
  }

  /* Batik pattern overlay */
  .bg-pattern {
    position: fixed; inset: 0; pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Ccircle cx='40' cy='40' r='30' stroke='white' stroke-opacity='0.015' stroke-width='1'/%3E%3Ccircle cx='40' cy='40' r='18' stroke='white' stroke-opacity='0.02' stroke-width='1'/%3E%3Ccircle cx='40' cy='40' r='6' stroke='white' stroke-opacity='0.025' stroke-width='1'/%3E%3C/g%3E%3C/svg%3E");
    background-size: 80px 80px;
  }

  /* Floating particles */
  .particle {
    position: fixed; border-radius: 50%;
    background: rgba(255,255,255,0.04);
    animation: float linear infinite;
    pointer-events: none;
  }

  @keyframes float {
    0%   { transform: translateY(110vh) rotate(0deg); opacity: 0; }
    5%   { opacity: 1; }
    95%  { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
  }

  /* ── Card ── */
  .card {
    position: relative; z-index: 10;
    text-align: center;
    padding: 3.5rem 3rem;
    max-width: 480px; width: 90%;
    animation: cardIn 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }

  @keyframes cardIn {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* Logo */
  .logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.7rem; color: white;
    text-decoration: none; display: inline-block;
    margin-bottom: 2.5rem;
    animation: fadeIn 0.5s ease 0.2s both;
  }
  .logo span { color: var(--gold); }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Icon ring */
  .icon-ring {
    width: 90px; height: 90px; border-radius: 50%;
    margin: 0 auto 2rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    display: flex; align-items: center; justify-content: center;
    position: relative;
    animation: ringIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.3s both;
  }

  @keyframes ringIn {
    from { opacity: 0; transform: scale(0.6); }
    to   { opacity: 1; transform: scale(1); }
  }

  .icon-ring::before {
    content: '';
    position: absolute; inset: -8px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.06);
  }
  .icon-ring::after {
    content: '';
    position: absolute; inset: -16px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.03);
  }

  .icon-ring i {
    font-size: 2rem; color: rgba(255,255,255,0.7);
    animation: waveHand 2s ease 0.8s infinite;
  }

  @keyframes waveHand {
    0%,100%  { transform: rotate(0deg); }
    15%  { transform: rotate(14deg); }
    30%  { transform: rotate(-8deg); }
    45%  { transform: rotate(14deg); }
    60%  { transform: rotate(-4deg); }
    75%  { transform: rotate(8deg); }
  }

  /* Text */
  .farewell {
    font-family: 'Playfair Display', serif;
    font-size: 2rem; font-weight: 700;
    line-height: 1.2; margin-bottom: 0.8rem;
    animation: fadeIn 0.5s ease 0.5s both;
  }
  .farewell em { font-style: italic; color: var(--gold-light); }

  .subtitle {
    font-size: 0.92rem; color: rgba(255,255,255,0.5);
    line-height: 1.7; margin-bottom: 2.5rem;
    animation: fadeIn 0.5s ease 0.65s both;
  }

  /* Countdown + redirect */
  .redirect-notice {
    display: flex; align-items: center; gap: 0.8rem; justify-content: center;
    margin-bottom: 2.2rem;
    animation: fadeIn 0.5s ease 0.8s both;
  }
  .countdown-ring {
    position: relative; width: 44px; height: 44px; flex-shrink: 0;
  }
  .countdown-ring svg { transform: rotate(-90deg); }
  .countdown-ring circle {
    fill: none; stroke-width: 3;
  }
  .ring-bg  { stroke: rgba(255,255,255,0.1); }
  .ring-fill {
    stroke: var(--teal-light);
    stroke-dasharray: 113;
    stroke-dashoffset: 0;
    stroke-linecap: round;
    transition: stroke-dashoffset 1s linear;
  }
  .countdown-num {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 600; color: var(--teal-light);
  }
  .redirect-text {
    font-size: 0.85rem; color: rgba(255,255,255,0.45); text-align: left; line-height: 1.5;
  }
  .redirect-text strong { color: rgba(255,255,255,0.7); }

  /* Divider */
  .divider {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.8rem;
    animation: fadeIn 0.5s ease 0.9s both;
  }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08);
  }
  .divider span { font-size: 0.72rem; color: rgba(255,255,255,0.25); }

  /* Action buttons */
  .actions {
    display: flex; gap: 0.9rem; justify-content: center; flex-wrap: wrap;
    animation: fadeIn 0.5s ease 1s both;
  }
  .btn-home {
    padding: 0.75rem 1.8rem;
    background: var(--teal); color: white; border: none;
    border-radius: 9px; font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; font-weight: 500; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;
    transition: background 0.2s, transform 0.15s;
    box-shadow: 0 4px 14px rgba(15,118,110,0.3);
  }
  .btn-home:hover { background: var(--teal-light); transform: translateY(-1px); }

  .btn-login {
    padding: 0.75rem 1.8rem;
    background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.65);
    border: 1px solid rgba(255,255,255,0.12); border-radius: 9px;
    font-family: 'DM Sans', sans-serif; font-size: 0.88rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 0.5rem;
    transition: all 0.2s;
  }
  .btn-login:hover { background: rgba(255,255,255,0.12); color: white; }

  /* Footer note */
  .footer-note {
    margin-top: 3rem; font-size: 0.74rem;
    color: rgba(255,255,255,0.2); line-height: 1.6;
    animation: fadeIn 0.5s ease 1.1s both;
  }

  @media (max-width: 480px) {
    .card { padding: 2.5rem 1.5rem; }
    .farewell { font-size: 1.6rem; }
    .actions { flex-direction: column; align-items: stretch; }
    .btn-home, .btn-login { justify-content: center; }
  }
</style>
</head>
<body>

<div class="bg-glow"></div>
<div class="bg-pattern"></div>

<!-- Floating particles -->
<script>
  for (let i = 0; i < 14; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = 30 + Math.random() * 80;
    p.style.cssText = `
      width:${size}px; height:${size}px;
      left:${Math.random()*100}%;
      animation-duration:${12 + Math.random()*18}s;
      animation-delay:${-Math.random()*20}s;
    `;
    document.body.appendChild(p);
  }
</script>

<div class="card">

  <!-- Logo -->
  <a href="index.php" class="logo">Batik<span>SL</span></a>

  <!-- Wave icon -->
  <div class="icon-ring">
    <i class="far fa-hand-wave" id="handIcon"></i>
  </div>

  <!-- Farewell text -->
  <h1 class="farewell">See you soon, <em><?= htmlspecialchars($firstName) ?></em></h1>
  <p class="subtitle">
    You've been signed out securely.<br>
    Your wishlist and order history will be waiting when you return.
  </p>

  <!-- Countdown -->
  <div class="redirect-notice">
    <div class="countdown-ring">
      <svg width="44" height="44" viewBox="0 0 44 44">
        <circle class="ring-bg"   cx="22" cy="22" r="18"/>
        <circle class="ring-fill" cx="22" cy="22" r="18" id="ringFill"/>
      </svg>
      <div class="countdown-num" id="countNum">6</div>
    </div>
    <div class="redirect-text">
      Redirecting to <strong>homepage</strong><br>in a moment…
    </div>
  </div>

  <div class="divider"><span>or choose</span></div>

  <!-- Actions -->
  <div class="actions">
    <a href="index.php" class="btn-home">
      <i class="fas fa-home"></i> Go to Homepage
    </a>
    <a href="login.php" class="btn-login">
      <i class="fas fa-arrow-right-to-bracket"></i> Sign In Again
    </a>
  </div>

  <p class="footer-note">
    © <?= date('Y') ?> BatikSL · Handcrafted with ❤️ in Kandy, Sri Lanka
  </p>

</div>

<script>
  // Hand wave icon fallback (fa-hand-wave not in all versions)
  const hi = document.getElementById('handIcon');
  hi.className = 'fas fa-hand-sparkles';

  // ── Countdown ──
  let count = 6;
  const circumference = 2 * Math.PI * 18; // ~113.1
  const ringEl = document.getElementById('ringFill');
  const numEl  = document.getElementById('countNum');

  ringEl.style.strokeDasharray  = circumference;
  ringEl.style.strokeDashoffset = 0;

  const timer = setInterval(() => {
    count--;
    numEl.textContent = count;
    const offset = circumference * (1 - count / 6);
    ringEl.style.strokeDashoffset = offset;
    if (count <= 0) {
      clearInterval(timer);
      window.location.href = 'index.php';
    }
  }, 1000);
</script>
</body>
</html>