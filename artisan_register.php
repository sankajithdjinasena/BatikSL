<?php
// register-artisan.php — BatikSL Artisan Registration
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';
$fields  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name']          ?? '');
    $email         = trim($_POST['email']         ?? '');
    $phone         = trim($_POST['phone']         ?? '');
    $password      = $_POST['password']           ?? '';
    $confirm       = $_POST['confirm']            ?? '';
    $bio           = trim($_POST['bio']           ?? '');
    $experience    = trim($_POST['experience']    ?? '');
    $location      = trim($_POST['location']      ?? '');
    $specialties   = $_POST['specialties']        ?? [];
    $instagram     = trim($_POST['instagram']     ?? '');
    $website       = trim($_POST['website']       ?? '');
    $terms         = isset($_POST['terms']);

    $fields = compact('name','email','phone','bio','experience','location','instagram','website');

    // Validation
    if (!$name || !$email || !$password || !$confirm || !$bio || !$experience || !$location) {
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
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $chk->execute(['email' => $email]);
        if ($chk->fetch()) {
            $error = 'An account with this email already exists. <a href="login.php">Sign in instead?</a>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Insert user with role = artisan
            $ins = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, role, created_at)
                VALUES (:name, :email, :password, :phone, 'artisan', NOW())
            ");
            $ins->execute([
                'name'     => $name,
                'email'    => $email,
                'password' => $hashed,
                'phone'    => $phone,
            ]);
            $newId = $pdo->lastInsertId();

            // TODO: Insert artisan profile details into a separate artisan_profiles table
            // $pdo->prepare("INSERT INTO artisan_profiles (user_id, bio, experience_years, location, specialties, instagram, website)
            //                VALUES (?,?,?,?,?,?,?)")
            //     ->execute([$newId, $bio, $experience, $location, implode(',', $specialties), $instagram, $website]);

            // Don't auto-login artisans — flag as pending review
            // $_SESSION['pending_artisan'] = true;

            // For now, auto-login and redirect to dashboard
            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'artisan';

            header('Location: artisan/dashboard.php?welcome=1');
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
<title>Join as Artisan | BatikSL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root {
    --teal:       #0f766e;
    --teal-light: #14b8a6;
    --teal-dark:  #0d5c55;
    --cream:      #faf7f2;
    --sand:       #f0ebe1;
    --charcoal:   #1c1c1c;
    --warm-gray:  #6b6560;
    --gold:       #c9a84c;
    --gold-light: #e2c97e;
    --gold-dim:   rgba(201,168,76,0.18);
    --border:     #e8e3da;
    --error:      #dc2626;
    --success:    #16a34a;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    color: var(--charcoal);
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* ── NAVBAR ── */
  nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    padding: 1.1rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(250,247,242,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }
  .nav-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.8rem; font-weight: 600;
    color: var(--charcoal); text-decoration: none;
  }
  .nav-logo span { color: var(--gold); }
  .nav-right { display: flex; align-items: center; gap: 1.5rem; }
  .nav-link {
    font-size: 0.8rem; font-weight: 500; letter-spacing: 0.06em;
    text-transform: uppercase; text-decoration: none;
    color: var(--warm-gray); transition: color 0.2s;
    display: flex; align-items: center; gap: 0.4rem;
  }
  .nav-link:hover { color: var(--teal); }
  .nav-link i { font-size: 0.68rem; }
  .nav-cta {
    padding: 0.45rem 1.2rem;
    border: 1.5px solid var(--teal); border-radius: 2rem;
    font-size: 0.78rem; font-weight: 500; letter-spacing: 0.05em;
    text-transform: uppercase; color: var(--teal);
    text-decoration: none; transition: all 0.25s;
  }
  .nav-cta:hover { background: var(--teal); color: white; }

  /* ── HERO BANNER ── */
  .hero {
    margin-top: 72px;
    background: var(--charcoal);
    padding: 5rem 4rem 4rem;
    position: relative; overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem; align-items: center;
  }

  /* Ambient glows */
  .hero::before {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
      radial-gradient(ellipse 60% 80% at 0% 50%, rgba(15,118,110,0.2) 0%, transparent 65%),
      radial-gradient(ellipse 50% 60% at 100% 80%, rgba(201,168,76,0.14) 0%, transparent 60%);
  }

  /* Grid lines */
  .hero::after {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background-image:
      linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 48px 48px;
  }

  .hero-text { position: relative; z-index: 2; }
  .hero-eyebrow {
    display: inline-flex; align-items: center; gap: 0.6rem;
    font-size: 0.7rem; font-weight: 500; letter-spacing: 0.22em;
    text-transform: uppercase; color: var(--gold-light);
    margin-bottom: 1.4rem;
    padding: 0.35rem 1rem;
    background: var(--gold-dim); border-radius: 2rem;
    border: 1px solid rgba(201,168,76,0.3);
  }
  .hero-eyebrow i { font-size: 0.7rem; }

  .hero-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2.6rem, 4.5vw, 3.8rem);
    font-weight: 700; color: white;
    line-height: 1.1; margin-bottom: 1.3rem;
  }
  .hero-title em { font-style: italic; color: var(--gold-light); }

  .hero-desc {
    font-size: 0.95rem; color: rgba(255,255,255,0.5);
    line-height: 1.8; max-width: 420px; margin-bottom: 2.2rem;
  }

  .hero-stats {
    display: flex; gap: 2.5rem;
  }
  .stat-item { }
  .stat-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem; font-weight: 700; color: var(--gold-light);
    line-height: 1;
  }
  .stat-label {
    font-size: 0.74rem; color: rgba(255,255,255,0.35);
    letter-spacing: 0.08em; margin-top: 0.2rem;
  }

  /* Hero right — decorative batik mandala */
  .hero-deco { position: relative; z-index: 2; display: flex; align-items: center; justify-content: center; }
  .mandala-wrap {
    position: relative; width: 340px; height: 340px;
  }
  .mandala-ring {
    position: absolute; border-radius: 50%; border: 1px solid;
    top: 50%; left: 50%; transform: translate(-50%, -50%);
    animation: spin-slow linear infinite;
  }
  .mandala-ring:nth-child(1) { width: 320px; height: 320px; border-color: rgba(255,255,255,0.05); animation-duration: 60s; }
  .mandala-ring:nth-child(2) { width: 260px; height: 260px; border-color: rgba(20,184,166,0.12); animation-duration: 40s; animation-direction: reverse; }
  .mandala-ring:nth-child(3) { width: 200px; height: 200px; border-color: rgba(201,168,76,0.15); animation-duration: 28s; }
  .mandala-ring:nth-child(4) { width: 140px; height: 140px; border-color: rgba(255,255,255,0.08); animation-duration: 18s; animation-direction: reverse; }
  @keyframes spin-slow { to { transform: translate(-50%, -50%) rotate(360deg); } }

  .mandala-svg {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 280px; height: 280px; opacity: 0.18;
  }

  .mandala-center {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, rgba(15,118,110,0.4), rgba(20,184,166,0.2));
    border: 1px solid rgba(20,184,166,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
  }

  /* ── STEP PROGRESS BAR ── */
  .progress-section {
    background: white; border-bottom: 1px solid var(--border);
    padding: 0 4rem;
    position: sticky; top: 72px; z-index: 100;
  }
  .step-nav {
    display: flex; align-items: stretch;
    max-width: 780px; margin: 0 auto;
  }
  .step-tab {
    flex: 1; display: flex; align-items: center; gap: 0.8rem;
    padding: 1.2rem 0.5rem;
    border-bottom: 2.5px solid transparent;
    cursor: default; transition: all 0.3s;
    position: relative;
  }
  .step-tab + .step-tab::before {
    content: '';
    position: absolute; left: 0; top: 50%; transform: translateY(-50%);
    width: 1px; height: 40%; background: var(--border);
  }
  .step-tab.active { border-bottom-color: var(--gold); }
  .step-tab.done   { border-bottom-color: var(--teal); }

  .step-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--sand); border: 1.5px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.74rem; font-weight: 600; color: var(--warm-gray);
    flex-shrink: 0; transition: all 0.3s;
  }
  .step-tab.active .step-num {
    background: var(--gold); border-color: var(--gold); color: white;
  }
  .step-tab.done .step-num {
    background: var(--teal); border-color: var(--teal); color: white;
  }
  .step-tab.done .step-num::after { content: '✓'; font-size: 0.7rem; }
  .step-tab.done .step-num .num-text { display: none; }

  .step-info { overflow: hidden; }
  .step-info .s-name {
    font-size: 0.82rem; font-weight: 500; color: var(--warm-gray);
    white-space: nowrap; transition: color 0.3s;
  }
  .step-tab.active .s-name,
  .step-tab.done   .s-name { color: var(--charcoal); }
  .step-info .s-desc {
    font-size: 0.7rem; color: var(--border);
    white-space: nowrap; transition: color 0.3s;
    display: none;
  }
  .step-tab.active .s-desc { color: var(--warm-gray); display: block; }

  /* ── FORM AREA ── */
  .form-area {
    max-width: 780px; margin: 0 auto;
    padding: 3rem 4rem 6rem;
  }

  /* Section headings */
  .section-head { margin-bottom: 2rem; }
  .section-head .tag {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.2em;
    text-transform: uppercase; color: var(--gold);
    margin-bottom: 0.5rem; display: block;
  }
  .section-head h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.9rem; font-weight: 700; line-height: 1.2;
  }
  .section-head h2 em { font-style: italic; color: var(--teal); }
  .section-head p {
    font-size: 0.9rem; color: var(--warm-gray);
    line-height: 1.65; margin-top: 0.5rem;
  }

  /* Form steps */
  .form-step { display: none; }
  .form-step.active {
    display: block;
    animation: stepIn 0.4s cubic-bezier(0.22,1,0.36,1);
  }
  @keyframes stepIn {
    from { opacity: 0; transform: translateX(18px); }
    to   { opacity: 1; transform: translateX(0); }
  }
  .form-step.going-back.active {
    animation: stepBack 0.4s cubic-bezier(0.22,1,0.36,1);
  }
  @keyframes stepBack {
    from { opacity: 0; transform: translateX(-18px); }
    to   { opacity: 1; transform: translateX(0); }
  }

  /* Field grid */
  .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem 1.4rem; }
  .field-grid .full { grid-column: 1 / -1; }

  .form-group { display: flex; flex-direction: column; }
  .form-group label {
    font-size: 0.74rem; font-weight: 500; letter-spacing: 0.08em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.5rem;
  }
  .form-group label .req { color: var(--error); margin-left: 2px; }
  .form-group label .opt {
    color: rgba(107,101,96,0.45); font-weight: 400;
    text-transform: none; letter-spacing: 0; margin-left: 4px;
  }

  /* Input wrap */
  .input-wrap { position: relative; }
  .input-icon {
    position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
    color: var(--warm-gray); font-size: 0.83rem; pointer-events: none;
    transition: color 0.2s; z-index: 1;
  }
  .input-wrap textarea ~ .input-icon { top: 1.1rem; transform: none; }

  .input-wrap input,
  .input-wrap select,
  .input-wrap textarea {
    width: 100%;
    padding: 0.82rem 1rem 0.82rem 2.7rem;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    color: var(--charcoal); background: white;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none; appearance: none;
  }
  .input-wrap textarea { padding-top: 0.9rem; resize: vertical; min-height: 110px; line-height: 1.6; }
  .input-wrap input::placeholder,
  .input-wrap textarea::placeholder { color: #c5bfb8; }
  .input-wrap input:focus,
  .input-wrap select:focus,
  .input-wrap textarea:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(15,118,110,0.08);
  }
  .input-wrap:focus-within .input-icon { color: var(--teal); }

  /* Validation states */
  .input-wrap.valid input,
  .input-wrap.valid select,
  .input-wrap.valid textarea { border-color: var(--success); }
  .input-wrap.invalid input,
  .input-wrap.invalid select,
  .input-wrap.invalid textarea { border-color: var(--error); }
  .input-status {
    position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
    font-size: 0.78rem; pointer-events: none;
  }

  /* Select arrow */
  .select-wrap::after {
    content: '';
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid var(--warm-gray);
    pointer-events: none;
  }
  .select-wrap select { padding-right: 2.5rem; }

  .field-hint {
    font-size: 0.73rem; color: var(--warm-gray);
    margin-top: 0.38rem; display: flex; align-items: center; gap: 0.3rem;
    line-height: 1.4;
  }
  .field-hint i { font-size: 0.68rem; flex-shrink: 0; }
  .field-hint.err { color: var(--error); }

  /* Char counter */
  .char-counter {
    font-size: 0.71rem; color: var(--warm-gray);
    text-align: right; margin-top: 0.35rem;
  }
  .char-counter.warn { color: var(--gold); }
  .char-counter.over { color: var(--error); }

  /* Specialty checkboxes */
  .specialty-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.7rem;
  }
  .specialty-chip {
    display: flex; align-items: center; gap: 0.55rem;
    padding: 0.65rem 0.85rem; border-radius: 9px;
    border: 1.5px solid var(--border); background: white;
    cursor: pointer; transition: all 0.2s;
    font-size: 0.83rem; color: var(--warm-gray);
    user-select: none;
  }
  .specialty-chip input { display: none; }
  .specialty-chip:hover { border-color: var(--teal); color: var(--teal); background: #f0faf9; }
  .specialty-chip.checked {
    border-color: var(--teal); background: #e6f4f2; color: var(--teal); font-weight: 500;
  }
  .specialty-chip i { font-size: 0.82rem; }

  /* Password */
  .pw-toggle {
    position: absolute; right: 0.9rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--warm-gray); font-size: 0.85rem; padding: 0;
    display: flex; transition: color 0.2s;
  }
  .pw-toggle:hover { color: var(--teal); }
  .strength-bar { display: flex; gap: 4px; margin-top: 0.5rem; }
  .strength-seg { height: 3px; flex: 1; border-radius: 3px; background: var(--border); transition: background 0.3s; }
  .strength-label { font-size: 0.71rem; color: var(--warm-gray); margin-top: 0.28rem; }

  /* Divider */
  .form-divider {
    display: flex; align-items: center; gap: 1rem;
    margin: 2rem 0;
    font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--warm-gray);
  }
  .form-divider::before, .form-divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
  }

  /* Alert */
  .alert {
    padding: 1rem 1.2rem; border-radius: 10px;
    font-size: 0.87rem; margin-bottom: 1.8rem;
    display: flex; align-items: flex-start; gap: 0.7rem;
    animation: alertIn 0.3s ease;
  }
  @keyframes alertIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); }
  .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--success); }
  .alert i { flex-shrink: 0; margin-top: 0.05rem; }
  .alert a { color: inherit; font-weight: 600; }

  /* Terms checkbox */
  .checkbox-wrap {
    display: flex; align-items: flex-start; gap: 0.7rem;
    cursor: pointer; margin-bottom: 2rem;
  }
  .checkbox-wrap input { display: none; }
  .custom-check {
    width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
    border: 1.5px solid var(--border); background: white;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s; margin-top: 2px;
  }
  .checkbox-wrap input:checked + .custom-check { background: var(--teal); border-color: var(--teal); }
  .custom-check::after {
    content: ''; width: 5px; height: 9px;
    border-right: 2px solid white; border-bottom: 2px solid white;
    transform: rotate(45deg) translate(-1px,-1px);
    opacity: 0; transition: opacity 0.15s;
  }
  .checkbox-wrap input:checked + .custom-check::after { opacity: 1; }
  .check-label { font-size: 0.87rem; color: var(--warm-gray); line-height: 1.6; user-select: none; }
  .check-label a { color: var(--teal); text-decoration: none; font-weight: 500; }

  /* Review summary */
  .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
  .review-card {
    background: white; border: 1px solid var(--border);
    border-radius: 12px; padding: 1.2rem 1.4rem;
  }
  .review-card h4 {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--warm-gray);
    margin-bottom: 0.9rem; display: flex; align-items: center; gap: 0.5rem;
  }
  .review-card h4 i { color: var(--teal); }
  .review-row {
    display: flex; justify-content: space-between; gap: 0.5rem;
    font-size: 0.85rem; padding: 0.35rem 0;
    border-bottom: 1px solid var(--border);
  }
  .review-row:last-child { border-bottom: none; padding-bottom: 0; }
  .review-row .rk { color: var(--warm-gray); flex-shrink: 0; }
  .review-row .rv { font-weight: 500; text-align: right; word-break: break-word; }
  .review-card.full { grid-column: 1 / -1; }

  /* CTA buttons */
  .btn-row { display: flex; gap: 1rem; margin-top: 2rem; }
  .btn-back {
    padding: 0.9rem 1.5rem;
    background: white; color: var(--warm-gray);
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem; font-weight: 500;
    cursor: pointer; display: flex; align-items: center; gap: 0.5rem;
    transition: all 0.2s; flex-shrink: 0;
  }
  .btn-back:hover { border-color: var(--charcoal); color: var(--charcoal); }
  .btn-next, .btn-submit {
    flex: 1; padding: 0.9rem 2rem;
    background: var(--gold); color: white; border: none;
    border-radius: 10px; font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 0.6rem;
    transition: all 0.25s;
    box-shadow: 0 4px 16px rgba(201,168,76,0.3);
  }
  .btn-next:hover, .btn-submit:hover {
    background: #b8922e;
    box-shadow: 0 6px 22px rgba(201,168,76,0.4);
    transform: translateY(-1px);
  }
  .btn-submit.loading { opacity: 0.8; pointer-events: none; }
  .spinner {
    width: 17px; height: 17px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
    animation: spin 0.7s linear infinite; display: none;
  }
  .btn-submit.loading .spinner { display: block; }
  .btn-submit.loading .btn-text { display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Footer */
  .page-footer {
    background: var(--charcoal); color: rgba(255,255,255,0.35);
    padding: 2rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem; font-size: 0.8rem;
  }
  .page-footer a { color: inherit; text-decoration: none; transition: color 0.2s; }
  .page-footer a:hover { color: var(--teal-light); }
  .page-footer .foot-links { display: flex; gap: 1.5rem; }

  /* ── RESPONSIVE ── */
  @media (max-width: 960px) {
    nav { padding: 1rem 1.5rem; }
    .nav-link.hide-mob { display: none; }
    .hero { grid-template-columns: 1fr; padding: 3rem 1.5rem; gap: 2rem; }
    .hero-deco { display: none; }
    .progress-section { padding: 0 1.5rem; }
    .step-info .s-name { font-size: 0.74rem; }
    .form-area { padding: 2rem 1.5rem 5rem; }
    .field-grid { grid-template-columns: 1fr; }
    .specialty-grid { grid-template-columns: 1fr 1fr; }
    .review-grid { grid-template-columns: 1fr; }
    .review-card.full { grid-column: 1; }
    .page-footer { padding: 2rem 1.5rem; }
  }
  @media (max-width: 560px) {
    .step-info { display: none; }
    .specialty-grid { grid-template-columns: 1fr; }
    .hero-stats { gap: 1.5rem; }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>
  <div class="nav-right">
    <a href="catalog.php" class="nav-link hide-mob"><i class="fas fa-arrow-left"></i> Back to Shop</a>
    <a href="register.php" class="nav-link hide-mob">Customer signup</a>
    <a href="login.php" class="nav-cta">Sign In</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-text">
    <div class="hero-eyebrow"><i class="fas fa-paint-brush"></i> Artisan Programme</div>
    <h1 class="hero-title">Share Your <em>Craft</em><br>With the World</h1>
    <p class="hero-desc">
      Join BatikSL's community of master artisans. Sell your handcrafted batik directly to customers across Sri Lanka and around the globe — with zero hidden fees.
    </p>
    <div class="hero-stats">
      <div class="stat-item">
        <div class="stat-num">40+</div>
        <div class="stat-label">Artisans</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">0%</div>
        <div class="stat-label">Commission</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">120+</div>
        <div class="stat-label">Countries reached</div>
      </div>
    </div>
  </div>
  <div class="hero-deco">
    <div class="mandala-wrap">
      <div class="mandala-ring"></div>
      <div class="mandala-ring"></div>
      <div class="mandala-ring"></div>
      <div class="mandala-ring"></div>
      <svg class="mandala-svg" viewBox="0 0 280 280" xmlns="http://www.w3.org/2000/svg" fill="none">
        <circle cx="140" cy="140" r="130" stroke="white" stroke-width="0.6"/>
        <circle cx="140" cy="140" r="100" stroke="white" stroke-width="0.5"/>
        <circle cx="140" cy="140" r="70"  stroke="white" stroke-width="0.6"/>
        <circle cx="140" cy="140" r="40"  stroke="white" stroke-width="0.5"/>
        <ellipse cx="140" cy="60" rx="14" ry="44" fill="white" fill-opacity="0.1" transform="rotate(0 140 140)"/>
        <ellipse cx="140" cy="60" rx="14" ry="44" fill="white" fill-opacity="0.1" transform="rotate(45 140 140)"/>
        <ellipse cx="140" cy="60" rx="14" ry="44" fill="white" fill-opacity="0.1" transform="rotate(90 140 140)"/>
        <ellipse cx="140" cy="60" rx="14" ry="44" fill="white" fill-opacity="0.1" transform="rotate(135 140 140)"/>
        <line x1="10" y1="140" x2="270" y2="140" stroke="white" stroke-opacity="0.15" stroke-width="0.4"/>
        <line x1="140" y1="10" x2="140" y2="270" stroke="white" stroke-opacity="0.15" stroke-width="0.4"/>
        <line x1="42" y1="42" x2="238" y2="238" stroke="white" stroke-opacity="0.1" stroke-width="0.4"/>
        <line x1="238" y1="42" x2="42" y2="238" stroke="white" stroke-opacity="0.1" stroke-width="0.4"/>
        <circle cx="140" cy="10" r="3" fill="white" fill-opacity="0.4"/>
        <circle cx="140" cy="270" r="3" fill="white" fill-opacity="0.4"/>
        <circle cx="10" cy="140" r="3" fill="white" fill-opacity="0.4"/>
        <circle cx="270" cy="140" r="3" fill="white" fill-opacity="0.4"/>
      </svg>
      <div class="mandala-center">🎨</div>
    </div>
  </div>
</div>

<!-- STEP PROGRESS -->
<div class="progress-section">
  <div class="step-nav" id="stepNav">
    <div class="step-tab active" data-step="1">
      <div class="step-num"><span class="num-text">1</span></div>
      <div class="step-info">
        <div class="s-name">Personal Info</div>
        <div class="s-desc">Your name, email & contact</div>
      </div>
    </div>
    <div class="step-tab" data-step="2">
      <div class="step-num"><span class="num-text">2</span></div>
      <div class="step-info">
        <div class="s-name">Artisan Profile</div>
        <div class="s-desc">Craft background & specialties</div>
      </div>
    </div>
    <div class="step-tab" data-step="3">
      <div class="step-num"><span class="num-text">3</span></div>
      <div class="step-info">
        <div class="s-name">Set Password</div>
        <div class="s-desc">Secure your account</div>
      </div>
    </div>
    <div class="step-tab" data-step="4">
      <div class="step-num"><span class="num-text">4</span></div>
      <div class="step-info">
        <div class="s-name">Review & Submit</div>
        <div class="s-desc">Confirm and go live</div>
      </div>
    </div>
  </div>
</div>

<!-- FORM -->
<div class="form-area">

  <?php if ($error): ?>
  <div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?= $error ?></span>
  </div>
  <?php endif; ?>

  <form method="POST" action="artisan_register.php" id="artisanForm" novalidate>

    <!-- ══ STEP 1: Personal Info ══ -->
    <div class="form-step active" id="step1">
      <div class="section-head">
        <span class="tag">Step 1 of 4</span>
        <h2>Your Personal <em>Details</em></h2>
        <p>Tell us who you are. This information will be used for your account and seller profile.</p>
      </div>

      <div class="field-grid">
        <div class="form-group full">
          <label for="name">Full Name <span class="req">*</span></label>
          <div class="input-wrap" id="wrapName">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="name" name="name" placeholder="e.g. Nalini Jayawardena"
              value="<?= htmlspecialchars($fields['name'] ?? '') ?>" autocomplete="name">
            <span class="input-status" id="statusName"></span>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address <span class="req">*</span></label>
          <div class="input-wrap" id="wrapEmail">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" placeholder="you@example.com"
              value="<?= htmlspecialchars($fields['email'] ?? '') ?>" autocomplete="email">
            <span class="input-status" id="statusEmail"></span>
          </div>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number <span class="req">*</span></label>
          <div class="input-wrap" id="wrapPhone">
            <i class="fas fa-phone input-icon"></i>
            <input type="tel" id="phone" name="phone" placeholder="+94 77 000 0000"
              value="<?= htmlspecialchars($fields['phone'] ?? '') ?>" autocomplete="tel">
          </div>
        </div>

        <div class="form-group">
          <label for="location">Workshop Location <span class="req">*</span></label>
          <div class="input-wrap select-wrap" id="wrapLocation">
            <i class="fas fa-map-marker-alt input-icon"></i>
            <select id="location" name="location">
              <option value="">Select your city / district</option>
              <option value="Kandy"       <?= ($fields['location'] ?? '') === 'Kandy'       ? 'selected':'' ?>>Kandy</option>
              <option value="Colombo"     <?= ($fields['location'] ?? '') === 'Colombo'     ? 'selected':'' ?>>Colombo</option>
              <option value="Galle"       <?= ($fields['location'] ?? '') === 'Galle'       ? 'selected':'' ?>>Galle</option>
              <option value="Matara"      <?= ($fields['location'] ?? '') === 'Matara'      ? 'selected':'' ?>>Matara</option>
              <option value="Negombo"     <?= ($fields['location'] ?? '') === 'Negombo'     ? 'selected':'' ?>>Negombo</option>
              <option value="Kurunegala"  <?= ($fields['location'] ?? '') === 'Kurunegala'  ? 'selected':'' ?>>Kurunegala</option>
              <option value="Anuradhapura"<?= ($fields['location'] ?? '') === 'Anuradhapura'? 'selected':'' ?>>Anuradhapura</option>
              <option value="Jaffna"      <?= ($fields['location'] ?? '') === 'Jaffna'      ? 'selected':'' ?>>Jaffna</option>
              <option value="Trincomalee" <?= ($fields['location'] ?? '') === 'Trincomalee' ? 'selected':'' ?>>Trincomalee</option>
              <option value="Batticaloa"  <?= ($fields['location'] ?? '') === 'Batticaloa'  ? 'selected':'' ?>>Batticaloa</option>
              <option value="Other"       <?= ($fields['location'] ?? '') === 'Other'       ? 'selected':'' ?>>Other</option>
            </select>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <button type="button" class="btn-next" onclick="goStep(2)">
          Continue <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <!-- ══ STEP 2: Artisan Profile ══ -->
    <div class="form-step" id="step2">
      <div class="section-head">
        <span class="tag">Step 2 of 4</span>
        <h2>Your Artisan <em>Profile</em></h2>
        <p>Help customers discover your unique story. A complete profile gets 3× more sales.</p>
      </div>

      <div class="field-grid">
        <div class="form-group full">
          <label for="bio">Artisan Bio <span class="req">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-align-left input-icon"></i>
            <textarea id="bio" name="bio" placeholder="Tell your story — how you learned batik, your inspiration, your process…" maxlength="500"><?= htmlspecialchars($fields['bio'] ?? '') ?></textarea>
          </div>
          <div class="char-counter" id="bioCounter">0 / 500</div>
          <div class="field-hint"><i class="fas fa-info-circle"></i> Minimum 80 characters. This appears on your public profile.</div>
        </div>

        <div class="form-group">
          <label for="experience">Years of Experience <span class="req">*</span></label>
          <div class="input-wrap select-wrap" id="wrapExperience">
            <i class="fas fa-award input-icon"></i>
            <select id="experience" name="experience">
              <option value="">Select experience level</option>
              <option value="1-2"   <?= ($fields['experience'] ?? '') === '1-2'   ? 'selected':'' ?>>1 – 2 years (Emerging)</option>
              <option value="3-5"   <?= ($fields['experience'] ?? '') === '3-5'   ? 'selected':'' ?>>3 – 5 years (Skilled)</option>
              <option value="6-10"  <?= ($fields['experience'] ?? '') === '6-10'  ? 'selected':'' ?>>6 – 10 years (Advanced)</option>
              <option value="11-20" <?= ($fields['experience'] ?? '') === '11-20' ? 'selected':'' ?>>11 – 20 years (Expert)</option>
              <option value="20+"   <?= ($fields['experience'] ?? '') === '20+'   ? 'selected':'' ?>>20+ years (Master)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="instagram">Instagram Handle <span class="opt">(optional)</span></label>
          <div class="input-wrap">
            <i class="fab fa-instagram input-icon"></i>
            <input type="text" id="instagram" name="instagram" placeholder="@yourusername"
              value="<?= htmlspecialchars($fields['instagram'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group full">
          <label for="website">Website / Portfolio <span class="opt">(optional)</span></label>
          <div class="input-wrap">
            <i class="fas fa-globe input-icon"></i>
            <input type="url" id="website" name="website" placeholder="https://yourwebsite.com"
              value="<?= htmlspecialchars($fields['website'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group full">
          <label>Craft Specialties <span class="opt">(select all that apply)</span></label>
          <div class="specialty-grid" id="specialtyGrid">
            <?php
            $specs = [
              ['val'=>'wax_resist',   'icon'=>'fa-fire',          'label'=>'Wax Resist'],
              ['val'=>'block_print',  'icon'=>'fa-stamp',         'label'=>'Block Printing'],
              ['val'=>'natural_dye',  'icon'=>'fa-leaf',          'label'=>'Natural Dyes'],
              ['val'=>'hand_drawn',   'icon'=>'fa-pen-nib',       'label'=>'Hand Drawn'],
              ['val'=>'geometric',    'icon'=>'fa-shapes',        'label'=>'Geometric'],
              ['val'=>'floral',       'icon'=>'fa-seedling',      'label'=>'Floral Motifs'],
              ['val'=>'fabric',       'icon'=>'fa-tshirt',        'label'=>'Fabric Yardage'],
              ['val'=>'clothing',     'icon'=>'fa-vest',          'label'=>'Clothing'],
              ['val'=>'home_decor',   'icon'=>'fa-couch',         'label'=>'Home Décor'],
            ];
            $selSpecs = $_POST['specialties'] ?? [];
            foreach ($specs as $sp): ?>
              <label class="specialty-chip <?= in_array($sp['val'], $selSpecs) ? 'checked' : '' ?>">
                <input type="checkbox" name="specialties[]" value="<?= $sp['val'] ?>"
                  <?= in_array($sp['val'], $selSpecs) ? 'checked' : '' ?>>
                <i class="fas <?= $sp['icon'] ?>"></i>
                <?= $sp['label'] ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <button type="button" class="btn-back" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-next" onclick="goStep(3)">Continue <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- ══ STEP 3: Password ══ -->
    <div class="form-step" id="step3">
      <div class="section-head">
        <span class="tag">Step 3 of 4</span>
        <h2>Secure Your <em>Account</em></h2>
        <p>Choose a strong password. As an artisan, your account has dashboard access — keep it safe.</p>
      </div>

      <div class="field-grid">
        <div class="form-group">
          <label for="password">Password <span class="req">*</span></label>
          <div class="input-wrap" id="wrapPassword">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password">
            <button type="button" class="pw-toggle" id="pwT1" aria-label="Toggle visibility"><i class="far fa-eye" id="pwI1"></i></button>
          </div>
          <div class="strength-bar">
            <div class="strength-seg" id="s1"></div>
            <div class="strength-seg" id="s2"></div>
            <div class="strength-seg" id="s3"></div>
            <div class="strength-seg" id="s4"></div>
          </div>
          <div class="strength-label" id="strengthLbl">Enter a password</div>
        </div>

        <div class="form-group">
          <label for="confirm">Confirm Password <span class="req">*</span></label>
          <div class="input-wrap" id="wrapConfirm">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="confirm" name="confirm" placeholder="Repeat your password" autocomplete="new-password">
            <button type="button" class="pw-toggle" id="pwT2" aria-label="Toggle visibility"><i class="far fa-eye" id="pwI2"></i></button>
          </div>
          <div class="field-hint" id="confirmHint" style="display:none">
            <i class="fas fa-times-circle"></i> Passwords do not match
          </div>
        </div>

        <div class="form-group full" style="background:var(--sand);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.4rem">
          <div style="font-size:0.72rem;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--warm-gray);margin-bottom:0.9rem">
            Password requirements
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
            <?php
            $reqs = [
              ['id'=>'req-len',  'txt'=>'At least 8 characters'],
              ['id'=>'req-upper','txt'=>'One uppercase letter'],
              ['id'=>'req-num',  'txt'=>'One number'],
              ['id'=>'req-sym',  'txt'=>'One special character'],
            ];
            foreach ($reqs as $r): ?>
            <div style="display:flex;align-items:center;gap:0.45rem;font-size:0.82rem;color:var(--warm-gray)" id="<?= $r['id'] ?>">
              <i class="fas fa-circle" style="font-size:0.45rem;color:var(--border)"></i>
              <?= $r['txt'] ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="btn-row">
        <button type="button" class="btn-back" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="button" class="btn-next" onclick="goStep(4)">Continue <i class="fas fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- ══ STEP 4: Review & Submit ══ -->
    <div class="form-step" id="step4">
      <div class="section-head">
        <span class="tag">Step 4 of 4</span>
        <h2>Review & <em>Join</em></h2>
        <p>Check your details below. You can go back to edit anything before submitting.</p>
      </div>

      <div class="review-grid" id="reviewGrid">
        <div class="review-card">
          <h4><i class="fas fa-user"></i> Personal Info</h4>
          <div class="review-row"><span class="rk">Name</span><span class="rv" id="r-name">—</span></div>
          <div class="review-row"><span class="rk">Email</span><span class="rv" id="r-email">—</span></div>
          <div class="review-row"><span class="rk">Phone</span><span class="rv" id="r-phone">—</span></div>
          <div class="review-row"><span class="rk">Location</span><span class="rv" id="r-location">—</span></div>
        </div>
        <div class="review-card">
          <h4><i class="fas fa-paint-brush"></i> Artisan Profile</h4>
          <div class="review-row"><span class="rk">Experience</span><span class="rv" id="r-experience">—</span></div>
          <div class="review-row"><span class="rk">Instagram</span><span class="rv" id="r-instagram">—</span></div>
          <div class="review-row"><span class="rk">Website</span><span class="rv" id="r-website">—</span></div>
          <div class="review-row"><span class="rk">Specialties</span><span class="rv" id="r-specialties">—</span></div>
        </div>
        <div class="review-card full">
          <h4><i class="fas fa-align-left"></i> Bio</h4>
          <p id="r-bio" style="font-size:0.88rem;color:var(--warm-gray);line-height:1.65">—</p>
        </div>
      </div>

      <!-- Terms -->
      <label class="checkbox-wrap">
        <input type="checkbox" name="terms" id="termsCheck">
        <div class="custom-check"></div>
        <span class="check-label">
          I agree to BatikSL's <a href="terms.php" target="_blank">Terms & Conditions</a>,
          <a href="privacy.php" target="_blank">Privacy Policy</a>, and
          <a href="artisan-agreement.php" target="_blank">Artisan Seller Agreement</a>.
          I confirm that all information provided is accurate.
        </span>
      </label>

      <div class="btn-row">
        <button type="button" class="btn-back" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="submit" class="btn-submit" id="submitBtn">
          <span class="btn-text"><i class="fas fa-paint-brush"></i> Create Artisan Account</span>
          <div class="spinner"></div>
        </button>
      </div>

      <p style="font-size:0.78rem;color:var(--warm-gray);margin-top:1.2rem;line-height:1.6">
        <i class="fas fa-shield-alt" style="color:var(--teal);margin-right:0.3rem"></i>
        Your account will be reviewed by our team within 24 hours. You'll receive a confirmation email once approved.
      </p>
    </div>

  </form>
</div>

<!-- FOOTER -->
<footer class="page-footer">
  <div>© <?= date('Y') ?> BatikSL. All rights reserved.</div>
  <div class="foot-links">
    <a href="index.php">Home</a>
    <a href="catalog.php">Shop</a>
    <a href="register.php">Customer Register</a>
    <a href="login.php">Sign In</a>
  </div>
</footer>

<script>
  let currentStep = 1;
  const totalSteps = 4;
  let goingBack = false;

  // ── Step navigation ──
  function goStep(n) {
    if (n > currentStep && !validateStep(currentStep)) return;

    goingBack = n < currentStep;

    document.getElementById('step' + currentStep).classList.remove('active');

    // Update step tabs
    document.querySelectorAll('.step-tab').forEach(tab => {
      const s = parseInt(tab.dataset.step);
      tab.classList.remove('active', 'done');
      if (s < n)      tab.classList.add('done');
      else if (s === n) tab.classList.add('active');
    });

    currentStep = n;
    const el = document.getElementById('step' + currentStep);
    el.classList.remove('going-back');
    void el.offsetWidth; // reflow
    if (goingBack) el.classList.add('going-back');
    el.classList.add('active');

    if (n === 4) populateReview();

    window.scrollTo({ top: document.querySelector('.progress-section').offsetTop - 80, behavior: 'smooth' });
  }

  // ── Validation ──
  function validateStep(step) {
    if (step === 1) {
      const name  = v('name');
      const email = v('email');
      const phone = v('phone');
      const loc   = v('location');
      let ok = true;
      if (name.length < 2)       { shake('wrapName');     ok = false; }
      if (!isEmail(email))       { shake('wrapEmail');    ok = false; }
      if (phone.length < 7)      { shake('wrapPhone');    ok = false; }
      if (!loc)                  { shake('wrapLocation'); ok = false; }
      return ok;
    }
    if (step === 2) {
      const bio  = v('bio');
      const exp  = v('experience');
      let ok = true;
      if (bio.length < 80)  { shake('wrapName'); alert('Bio must be at least 80 characters.'); ok = false; }
      if (!exp)             { shake('wrapExperience'); ok = false; }
      return ok;
    }
    if (step === 3) {
      const pw  = v('password');
      const con = v('confirm');
      let ok = true;
      if (pw.length < 8)  { shake('wrapPassword'); ok = false; }
      if (pw !== con)     { shake('wrapConfirm');
        document.getElementById('confirmHint').style.display = 'flex'; ok = false; }
      return ok;
    }
    return true;
  }

  const v   = id => document.getElementById(id).value.trim();
  const isEmail = s => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);

  function shake(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.animation = 'none'; void el.offsetHeight;
    el.style.animation = 'shake .4s ease';
  }

  // Shake keyframes
  const sStyle = document.createElement('style');
  sStyle.textContent = `@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-6px)}40%{transform:translateX(6px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}`;
  document.head.appendChild(sStyle);

  // ── Live field validation ──
  function liveValidate(inputId, wrapId, statusId, fn) {
    const inp  = document.getElementById(inputId);
    const wrap = document.getElementById(wrapId);
    const stat = statusId ? document.getElementById(statusId) : null;
    if (!inp) return;
    inp.addEventListener('blur', () => {
      const ok = fn(inp.value.trim());
      wrap.className = 'input-wrap ' + (ok ? 'valid' : 'invalid');
      if (stat) stat.innerHTML = ok
        ? '<i class="fas fa-check-circle" style="color:var(--success)"></i>'
        : '<i class="fas fa-times-circle" style="color:var(--error)"></i>';
    });
  }
  liveValidate('name',  'wrapName',  'statusName',  s => s.length >= 2);
  liveValidate('email', 'wrapEmail', 'statusEmail', isEmail);
  liveValidate('phone', 'wrapPhone', null,          s => s.length >= 7);

  // ── Specialty chips ──
  document.querySelectorAll('.specialty-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      const cb = chip.querySelector('input');
      cb.checked = !cb.checked;
      chip.classList.toggle('checked', cb.checked);
    });
  });

  // ── Bio char counter ──
  const bioEl = document.getElementById('bio');
  const bioCounter = document.getElementById('bioCounter');
  bioEl.addEventListener('input', () => {
    const len = bioEl.value.length;
    bioCounter.textContent = len + ' / 500';
    bioCounter.className = 'char-counter' + (len > 480 ? ' over' : len > 400 ? ' warn' : '');
  });

  // ── Password strength ──
  const pwEl = document.getElementById('password');
  const conEl = document.getElementById('confirm');
  const segs  = [1,2,3,4].map(i => document.getElementById('s'+i));
  const colors = {1:'#ef4444',2:'#f97316',3:'#eab308',4:'#22c55e'};
  const labels = {0:'Enter a password',1:'Weak — add more variety',2:'Fair',3:'Good',4:'Strong 🎉'};

  const reqMap = {
    'req-len':   pw => pw.length >= 8,
    'req-upper': pw => /[A-Z]/.test(pw),
    'req-num':   pw => /[0-9]/.test(pw),
    'req-sym':   pw => /[^A-Za-z0-9]/.test(pw),
  };

  pwEl.addEventListener('input', () => {
    const pw = pwEl.value;
    let score = 0;
    Object.entries(reqMap).forEach(([id, fn]) => {
      const ok = fn(pw);
      if (ok) score++;
      const el = document.getElementById(id);
      const icon = el.querySelector('i');
      icon.style.color = ok ? 'var(--success)' : 'var(--border)';
      icon.className   = ok ? 'fas fa-check-circle' : 'fas fa-circle';
      el.style.color   = ok ? 'var(--charcoal)' : 'var(--warm-gray)';
    });
    segs.forEach((s, i) => s.style.background = i < score ? (colors[score] || '#22c55e') : 'var(--border)');
    document.getElementById('strengthLbl').textContent = labels[score];
  });

  conEl.addEventListener('input', () => {
    const hint = document.getElementById('confirmHint');
    if (!conEl.value) { hint.style.display = 'none'; return; }
    const match = conEl.value === pwEl.value;
    hint.style.display = 'flex';
    hint.className = match ? 'field-hint' : 'field-hint err';
    hint.innerHTML = match
      ? '<i class="fas fa-check-circle" style="color:var(--success)"></i> Passwords match'
      : '<i class="fas fa-times-circle"></i> Passwords do not match';
  });

  // ── PW toggles ──
  [['pwT1','pwI1','password'],['pwT2','pwI2','confirm']].forEach(([btn,icon,inp]) => {
    document.getElementById(btn).addEventListener('click', () => {
      const el = document.getElementById(inp);
      const ic = document.getElementById(icon);
      const show = el.type === 'password';
      el.type = show ? 'text' : 'password';
      ic.className = show ? 'far fa-eye-slash' : 'far fa-eye';
    });
  });

  // ── Populate review ──
  function populateReview() {
    const specialties = [...document.querySelectorAll('.specialty-chip.checked')]
      .map(c => c.textContent.trim()).join(', ') || 'None selected';

    document.getElementById('r-name').textContent       = v('name')       || '—';
    document.getElementById('r-email').textContent      = v('email')      || '—';
    document.getElementById('r-phone').textContent      = v('phone')      || '—';
    document.getElementById('r-location').textContent   = v('location')   || '—';
    document.getElementById('r-experience').textContent = v('experience') || '—';
    document.getElementById('r-instagram').textContent  = v('instagram')  || 'Not provided';
    document.getElementById('r-website').textContent    = v('website')    || 'Not provided';
    document.getElementById('r-specialties').textContent = specialties;
    document.getElementById('r-bio').textContent        = v('bio')        || '—';
  }

  // ── Submit ──
  document.getElementById('artisanForm').addEventListener('submit', function () {
    if (!document.getElementById('termsCheck').checked) return;
    document.getElementById('submitBtn').classList.add('loading');
  });

  window.addEventListener('pageshow', () => {
    document.getElementById('submitBtn').classList.remove('loading');
  });
</script>
</body>
</html>