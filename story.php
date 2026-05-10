<?php
// story.php - Artisan Storytelling Page
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Story | BatikSL</title>
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
    position: sticky; top: 0; z-index: 1000;
    padding: 1.1rem 4rem;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(250,247,242,0.97);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
  }
  .nav-logo { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--charcoal); text-decoration: none; }
  .nav-logo span { color: var(--gold); }
  .nav-links { display: flex; gap: 2.5rem; list-style: none; }
  .nav-links a { font-size: 0.82rem; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; color: var(--warm-gray); transition: color 0.25s; }
  .nav-links a:hover, .nav-links a.active { color: var(--teal); }
  .nav-icons { display: flex; align-items: center; gap: 1.4rem; }
  .nav-icons a, .nav-icons button { background: none; border: none; cursor: pointer; color: var(--warm-gray); font-size: 1.05rem; text-decoration: none; transition: color 0.25s; position: relative; }
  .nav-icons a:hover, .nav-icons button:hover { color: var(--teal); }
  .cart-badge { position: absolute; top: -6px; right: -8px; background: var(--gold); color: white; font-size: 0.62rem; font-weight: 700; width: 17px; height: 17px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
  .nav-signin { padding: 0.45rem 1.3rem; border: 1.5px solid var(--teal); border-radius: 2rem; font-size: 0.78rem; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; color: var(--teal); text-decoration: none; transition: all 0.25s; }
  .nav-signin:hover { color: white; }

  /* ── HERO ── */
  .story-hero {
    height: 88vh; min-height: 560px;
    display: flex; flex-direction: column;
    align-items: center; justify-content: flex-end;
    padding-bottom: 5rem;
    position: relative; overflow: hidden;
    text-align: center;
  }
  .story-hero-bg {
    position: absolute; inset: 0;
    background: url('https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=1600&q=80') center/cover no-repeat;
  }
  .story-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(10,35,30,0.92) 0%, rgba(0,0,0,0.35) 60%, transparent 100%);
  }
  .story-hero-content { position: relative; z-index: 2; padding: 0 1.5rem; }
  .hero-eyebrow {
    display: inline-flex; align-items: center; gap: 0.8rem;
    font-size: 0.75rem; font-weight: 500; letter-spacing: 0.22em;
    text-transform: uppercase; color: var(--gold); margin-bottom: 1.4rem;
  }
  .hero-eyebrow::before, .hero-eyebrow::after { content: ''; width: 36px; height: 1px; background: var(--gold); opacity: 0.55; }
  .story-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 700; line-height: 1.05; color: white;
    margin-bottom: 1.2rem;
  }
  .story-hero h1 em { font-style: italic; color: #a8d8d2; }
  .story-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.65); font-weight: 300; max-width: 480px; margin: 0 auto; line-height: 1.7; }

  /* scroll hint */
  .scroll-hint {
    position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    color: rgba(255,255,255,0.4); font-size: 0.7rem; letter-spacing: 0.15em; text-transform: uppercase;
    animation: scrollBounce 2.2s ease-in-out infinite;
  }
  @keyframes scrollBounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(7px)} }

  /* ── INTRO ── */
  .intro-strip {
    background: var(--charcoal);
    display: grid; grid-template-columns: repeat(3,1fr);
    text-align: center;
  }
  .intro-stat {
    padding: 2.8rem 2rem;
    border-right: 1px solid rgba(255,255,255,0.06);
  }
  .intro-stat:last-child { border-right: none; }
  .intro-stat .num { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; color: var(--gold); }
  .intro-stat .lbl { font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-top: 0.3rem; }

  /* ── SECTION COMMON ── */
  .section-tag { font-size: 0.72rem; font-weight: 500; letter-spacing: 0.2em; text-transform: uppercase; color: var(--teal); margin-bottom: 0.7rem; display: block; }
  .section-title { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; line-height: 1.15; }
  .section-title em { font-style: italic; color: var(--teal); }

  /* ── MALINI FEATURE ── */
  .malini-section {
    display: grid; grid-template-columns: 1fr 1fr;
    min-height: 600px;
  }
  .malini-img {
    position: relative; overflow: hidden;
  }
  .malini-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .malini-img-caption {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(10,35,30,0.85));
    padding: 3rem 2.5rem 2rem;
    color: white;
  }
  .malini-img-caption .name { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; }
  .malini-img-caption .role { font-size: 0.78rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-top: 0.3rem; }
  .malini-text {
    background: var(--sand);
    padding: 5rem 4.5rem;
    display: flex; flex-direction: column; justify-content: center;
  }
  .malini-quote {
    font-family: 'Playfair Display', serif; font-style: italic;
    font-size: 1.35rem; line-height: 1.65; color: var(--charcoal);
    border-left: 3px solid var(--gold); padding-left: 1.5rem;
    margin: 2rem 0;
  }
  .malini-body { font-size: 0.95rem; color: var(--warm-gray); line-height: 1.85; font-weight: 300; }
  .malini-body + .malini-body { margin-top: 1rem; }

  /* ── TIMELINE ── */
  .timeline-section { padding: 8rem 4rem; background: var(--cream); }
  .timeline-section .header { text-align: center; max-width: 560px; margin: 0 auto 5rem; }
  .timeline-section .header .section-desc { color: var(--warm-gray); font-size: 0.95rem; line-height: 1.7; margin-top: 0.8rem; font-weight: 300; }

  .timeline { position: relative; max-width: 860px; margin: 0 auto; }
  .timeline::before {
    content: ''; position: absolute; left: 50%; transform: translateX(-50%);
    top: 0; bottom: 0; width: 1px; background: var(--border);
  }

  .timeline-item {
    display: grid; grid-template-columns: 1fr 60px 1fr;
    gap: 0; align-items: start; margin-bottom: 4.5rem;
    position: relative;
  }
  .timeline-item:last-child { margin-bottom: 0; }

 /* left cards */
.tl-left {
  text-align: right;
  padding-right: 2.5rem;
}

/* right cards */
.tl-right {
  text-align: left;
  padding-left: 2.5rem;
}

/* keep card width consistent */
.tl-card {
  background: white;
  border-radius: 14px;
  padding: 1.6rem 1.8rem;
  border: 1px solid var(--border);
  transition: box-shadow 0.3s, transform 0.3s;
  max-width: 360px;
}

/* align cards properly */
.tl-left .tl-card {
  margin-left: auto;
}

.tl-right .tl-card {
  margin-right: auto;
}

  .tl-left  { text-align: right; padding-right: 2.5rem; }
  .tl-right { text-align: left;  padding-left: 2.5rem; }

  .tl-center {
    display: flex; flex-direction: column; align-items: center;
    position: relative; z-index: 1;
  }
  .tl-dot {
    width: 52px; height: 52px; border-radius: 50%;
    background: white; border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: border-color 0.3s, background 0.3s;
  }
  .tl-dot .year {
    font-family: 'Playfair Display', serif;
    font-size: 0.72rem; font-weight: 700; color: var(--teal);
    line-height: 1; text-align: center;
  }
  .timeline-item:hover .tl-dot { background: var(--teal); border-color: var(--teal); }
  .timeline-item:hover .tl-dot .year { color: white; }

  .tl-card {
    background: white; border-radius: 14px;
    padding: 1.6rem 1.8rem;
    border: 1px solid var(--border);
    transition: box-shadow 0.3s, transform 0.3s;
  }
  .timeline-item:hover .tl-card { box-shadow: 0 12px 36px rgba(0,0,0,0.08); transform: translateY(-4px); }

  .tl-card .year-tag {
    font-size: 0.68rem; font-weight: 500; letter-spacing: 0.15em;
    text-transform: uppercase; color: var(--teal); margin-bottom: 0.5rem;
  }
  .tl-card h3 { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.6rem; }
  .tl-card p { font-size: 0.87rem; color: var(--warm-gray); line-height: 1.65; }
  .tl-card img { width: 100%; border-radius: 8px; margin-top: 1rem; object-fit: cover; height: 140px; }

  .tl-placeholder { /* empty cell on alternate side */ }

  /* ── PROCESS ── */
  .process-section { background: var(--charcoal); padding: 8rem 4rem; }
  .process-section .header { text-align: center; max-width: 520px; margin: 0 auto 5rem; }
  .process-section .section-title { color: white; }
  .process-section .section-title em { color: var(--teal-light); }
  .process-section .section-desc { color: rgba(255,255,255,0.45); font-size: 0.95rem; line-height: 1.7; margin-top: 0.8rem; font-weight: 300; }

  .process-steps {
    display: grid; grid-template-columns: repeat(4,1fr);
    position: relative; max-width: 1000px; margin: 0 auto;
  }
  .process-steps::before {
    content: ''; position: absolute;
    top: 2.8rem; left: 15%; right: 15%;
    height: 1px; background: rgba(255,255,255,0.08);
  }

  .process-step { text-align: center; padding: 0 1.5rem; }
  .step-icon-wrap {
    width: 56px; height: 56px; border-radius: 50%;
    margin: 0 auto 1.8rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; position: relative; z-index: 1;
  }
  .step-num-label {
    position: absolute; top: -6px; right: -6px;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--gold); color: white;
    font-size: 0.6rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
  }
  .process-step h3 { font-size: 0.95rem; font-weight: 500; color: white; margin-bottom: 0.7rem; }
  .process-step p { font-size: 0.82rem; color: rgba(255,255,255,0.45); line-height: 1.6; }
  .icon-amber { background: rgba(201,168,76,0.15); color: var(--gold); }
  .icon-teal  { background: rgba(15,118,110,0.2);  color: var(--teal-light); }
  .icon-blue  { background: rgba(59,130,246,0.15); color: #60a5fa; }
  .icon-sand  { background: rgba(240,235,225,0.1); color: rgba(255,255,255,0.6); }

  /* ── BTS GALLERY ── */
  .gallery-section { padding: 8rem 4rem; background: var(--sand); }
  .gallery-section .header { margin-bottom: 3.5rem; }
  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-template-rows: 260px 260px;
    gap: 1rem;
  }
  .g1 { grid-column: span 5; grid-row: span 2; }
  .g2 { grid-column: span 7; }
  .g3 { grid-column: span 4; }
  .g4 { grid-column: span 3; }

  .gallery-cell {
    border-radius: 14px; overflow: hidden; position: relative;
  }
  .gallery-cell img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s; display: block; }
  .gallery-cell:hover img { transform: scale(1.05); }
  .gallery-overlay {
    position: absolute; inset: 0;
    background: rgba(15,118,110,0.65);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.35s;
    color: white; font-size: 1.6rem;
  }
  .gallery-cell:hover .gallery-overlay { opacity: 1; }

  /* ── VIDEO ── */
  .video-section { padding: 8rem 4rem; background: var(--cream); }
  .video-section .header { text-align: center; max-width: 560px; margin: 0 auto 3.5rem; }
  .video-wrap {
    max-width: 820px; margin: 0 auto;
    border-radius: 20px; overflow: hidden;
    position: relative; aspect-ratio: 16/9;
    background: #0a2826;
    cursor: pointer;
  }
  .video-thumb {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: opacity 0.4s;
  }
  .video-play-overlay {
    position: absolute; inset: 0;
    background: rgba(10,40,38,0.55);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 1rem;
  }
  .video-play-btn {
    width: 82px; height: 82px; border-radius: 50%;
    background: white; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--teal); font-size: 1.6rem; padding-left: 6px;
    transition: transform 0.25s, box-shadow 0.25s;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  }
  .video-play-btn:hover { transform: scale(1.08); box-shadow: 0 16px 48px rgba(0,0,0,0.3); }
  .video-label { color: rgba(255,255,255,0.75); font-size: 0.85rem; letter-spacing: 0.05em; }
  .video-duration { color: rgba(255,255,255,0.45); font-size: 0.75rem; }

  /* ── VALUES ── */
  .values-section { padding: 8rem 4rem; background: var(--sand); }
  .values-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 2rem; margin-top: 4rem; }
  .value-card {
    background: white; border-radius: 16px; padding: 2.5rem 2rem;
    border: 1px solid var(--border);
    transition: transform 0.3s, box-shadow 0.3s;
  }
  .value-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
  .value-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 1.4rem;
  }
  .value-card h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; margin-bottom: 0.6rem; }
  .value-card p { font-size: 0.87rem; color: var(--warm-gray); line-height: 1.7; }

  /* ── CTA ── */
  .cta-section {
    background: url('https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=1400&q=80') center/cover no-repeat;
    position: relative; padding: 9rem 4rem; text-align: center;
  }
  .cta-section::before { content: ''; position: absolute; inset: 0; background: rgba(10,40,38,0.82); }
  .cta-inner { position: relative; z-index: 1; max-width: 620px; margin: 0 auto; }
  .cta-inner .section-tag { color: var(--gold); }
  .cta-inner h2 { font-family: 'Playfair Display', serif; font-size: clamp(2.2rem,5vw,3.8rem); font-weight: 700; color: white; line-height: 1.15; margin-bottom: 1rem; }
  .cta-inner h2 em { font-style: italic; color: #a8d8d2; }
  .cta-inner p { color: rgba(255,255,255,0.6); font-size: 1rem; line-height: 1.7; margin-bottom: 2.5rem; font-weight: 300; }
  .cta-btns { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
  .btn-primary { padding: 0.9rem 2.4rem; background: var(--teal); color: white; text-decoration: none; border-radius: 3rem; font-size: 0.88rem; font-weight: 500; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s; border: 2px solid var(--teal); }
  .btn-primary:hover { background: var(--teal-light); border-color: var(--teal-light); transform: translateY(-2px); }
  .btn-ghost { padding: 0.9rem 2.4rem; background: transparent; color: white; text-decoration: none; border-radius: 3rem; font-size: 0.88rem; font-weight: 500; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.5rem; border: 2px solid rgba(255,255,255,0.4); transition: all 0.3s; }
  .btn-ghost:hover { background: rgba(255,255,255,0.1); border-color: white; transform: translateY(-2px); }

  /* ── FOOTER ── */
  footer { background: #111; color: rgba(255,255,255,0.6); padding: 5rem 4rem 2.5rem; }
  .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 4rem; }
  .footer-logo { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: white; margin-bottom: 1rem; display: block; }
  .footer-logo span { color: var(--gold); }
  .footer-about { font-size: 0.88rem; line-height: 1.7; max-width: 280px; }
  .footer-col h4 { color: white; font-size: 0.78rem; font-weight: 500; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1.4rem; }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 0.7rem; }
  .footer-col ul a { text-decoration: none; color: inherit; font-size: 0.88rem; transition: color 0.2s; }
  .footer-col ul a:hover { color: var(--teal-light); }
  .social-icons { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
  .social-icon { width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center; color: inherit; text-decoration: none; font-size: 0.95rem; transition: all 0.25s; }
  .social-icon:hover { border-color: var(--teal-light); color: var(--teal-light); }
  .footer-bottom { border-top: 1px solid rgba(255,255,255,0.06); margin-top: 4rem; padding-top: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.8rem; }
  .footer-bottom a { color: inherit; text-decoration: none; }
  .footer-bottom a:hover { color: var(--teal-light); }

  /* ── VIDEO MODAL ── */
  .modal-overlay { display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.92); align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal-video { width: 90%; max-width: 900px; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; }
  .modal-close { position: absolute; top: 1.5rem; right: 2rem; background: none; border: none; cursor: pointer; color: white; font-size: 2.2rem; line-height: 1; }

  /* ── RESPONSIVE ── */
  @media (max-width: 1000px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links, .nav-signin { display: none; }
    .malini-section { grid-template-columns: 1fr; }
    .malini-text { padding: 3.5rem 2rem; }
    .timeline::before { left: 22px; transform: none; }
    .timeline-item { grid-template-columns: 44px 1fr; }
    .tl-left, .tl-placeholder { display: none; }
    .tl-right { padding-left: 1.5rem; text-align: left; }
    .timeline-item:nth-child(even) .tl-left  { display: none; }
    .timeline-item:nth-child(even) .tl-center { order: 1; }
    .timeline-item:nth-child(even) .tl-right { order: 2; padding-left: 1.5rem; padding-right: 0; text-align: left; }
    .tl-dot { width: 44px; height: 44px; }
    .process-steps { grid-template-columns: 1fr 1fr; gap: 3rem; }
    .process-steps::before { display: none; }
    .gallery-grid { grid-template-columns: 1fr 1fr; grid-template-rows: auto; }
    .g1, .g2, .g3, .g4 { grid-column: span 1; grid-row: span 1; height: 220px; }
    .values-grid { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2.5rem; }
    section, .timeline-section, .process-section, .gallery-section, .video-section, .values-section { padding-left: 1.5rem; padding-right: 1.5rem; }
    footer { padding: 4rem 1.5rem 2rem; }
    .cta-section { padding: 6rem 1.5rem; }
  }
  @media (max-width: 600px) {
    .intro-strip { grid-template-columns: 1fr; }
    .intro-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .gallery-grid { grid-template-columns: 1fr; }
    .process-steps { grid-template-columns: 1fr; }
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
    <li><a href="story.php" class="active">Our Story</a></li>
    <li><a href="booking.php">Live Session</a></li>
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

<!-- HERO -->
<section class="story-hero">
  <div class="story-hero-bg"></div>
  <div class="story-hero-overlay"></div>
  <div class="story-hero-content">
    <div class="hero-eyebrow">Kandy, Sri Lanka · Est. 1989</div>
    <h1>25 Years of<br><em>Living Craft</em></h1>
    <p>A family lineage, a workshop, and a mission to keep the ancient art of batik alive for generations to come.</p>
  </div>
  <div class="scroll-hint"><span>Scroll</span><i class="fas fa-chevron-down"></i></div>
</section>

<!-- STATS STRIP -->
<div class="intro-strip">
  <div class="intro-stat"><div class="num">1989</div><div class="lbl">Year Founded</div></div>
  <div class="intro-stat"><div class="num">15</div><div class="lbl">Artisans Employed</div></div>
  <div class="intro-stat"><div class="num">100%</div><div class="lbl">Natural Dyes</div></div>
</div>

<!-- MALINI FEATURE -->
<div class="malini-section">
  <div class="malini-img">
    <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=800&q=80" alt="Malini Weerasinghe, master artisan">
    <div class="malini-img-caption">
      <div class="name">Malini Weerasinghe</div>
      <div class="role">Master Artisan &amp; Founder</div>
    </div>
  </div>
  <div class="malini-text">
    <span class="section-tag">The Maker Behind It All</span>
    <h2 class="section-title">Where <em>Every Thread</em> Begins</h2>
    <blockquote class="malini-quote">
      "Batik is not just art — it is our story woven into every line and colour. When I press the canting to cloth, I hear my grandmother's voice."
    </blockquote>
    <p class="malini-body">Malini began learning batik at age 12 from her grandmother in Kandy. What started as quiet afternoons watching wax drip onto cotton became a lifelong devotion to preserving one of Sri Lanka's most treasured art forms.</p>
    <p class="malini-body" style="margin-top:1rem">Today she leads a collective of 15 artisans — many of whom she has trained herself — each carrying forward a tradition that stretches back centuries. Every piece leaving the BatikSL studio bears her signature commitment to quality, authenticity, and soul.</p>
  </div>
</div>

<!-- TIMELINE -->
<section class="timeline-section">
  <div class="header">
    <span class="section-tag">Our Journey</span>
    <h2 class="section-title">A Legacy <em>Built Year by Year</em></h2>
    <p class="section-desc">From a small Kandy workshop to international recognition — this is the story of batik that refused to be forgotten.</p>
  </div>

  <div class="timeline">

    <div class="timeline-item">
      <div class="tl-left">
        <div class="tl-card">
          <div class="year-tag">1989</div>
          <h3>The Beginning</h3>
          <p>Malini opens her first small workshop in Kandy with 3 looms, a copper canting set, and her grandmother's recipe book for natural dyes.</p>
          <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=400&q=80" alt="1989 workshop">
        </div>
      </div>
      <div class="tl-center">
        <div class="tl-dot"><span class="year">1989</span></div>
      </div>
      <div class="tl-placeholder"></div>
    </div>

    <div class="timeline-item">
      <div class="tl-placeholder"></div>
      <div class="tl-center">
        <div class="tl-dot"><span class="year">2005</span></div>
      </div>
      <div class="tl-right">
        <div class="tl-card">
          <div class="year-tag">2005</div>
          <h3>Growing the Collective</h3>
          <p>The workshop expands to 8 artisans. Malini begins training young women from Kandy's surrounding villages in traditional batik techniques.</p>
        </div>
      </div>
    </div>

    <div class="timeline-item">
      <div class="tl-left">
        <div class="tl-card">
          <div class="year-tag">2010</div>
          <h3>Natural Dyes Revolution</h3>
          <p>Full transition to 100% natural, eco-friendly dyes sourced from local plants — turmeric, indigo, jackfruit bark, and red onion skins.</p>
        </div>
      </div>
      <div class="tl-center">
        <div class="tl-dot"><span class="year">2010</span></div>
      </div>
      <div class="tl-placeholder"></div>
    </div>

    <div class="timeline-item">
      <div class="tl-placeholder"></div>
      <div class="tl-center">
        <div class="tl-dot"><span class="year">2018</span></div>
      </div>
      <div class="tl-right">
        <div class="tl-card">
          <div class="year-tag">2018</div>
          <h3>Global Recognition</h3>
          <p>BatikSL is featured in UNESCO's global spotlight on Intangible Cultural Heritage. Orders arrive from 42 countries within the year.</p>
        </div>
      </div>
    </div>

    <div class="timeline-item">
      <div class="tl-left">
        <div class="tl-card">
          <div class="year-tag">2024</div>
          <h3>Training Centre Opens</h3>
          <p>A dedicated training centre for young artisans opens, offering 6-month residencies to ensure the craft lives on through the next generation.</p>
        </div>
      </div>
      <div class="tl-center">
        <div class="tl-dot"><span class="year">2024</span></div>
      </div>
      <div class="tl-placeholder"></div>
    </div>

  </div>
</section>

<!-- PROCESS -->
<section class="process-section">
  <div class="header">
    <span class="section-tag" style="color:var(--gold)">The Craft</span>
    <h2 class="section-title">How Batik <em>Comes to Life</em></h2>
    <p class="section-desc">Each piece passes through four meticulous stages over 4–7 days. There are no shortcuts when you're making something meant to last a lifetime.</p>
  </div>
  <div class="process-steps">
    <div class="process-step">
      <div class="step-icon-wrap icon-amber">
        <i class="fas fa-fill-drip"></i>
        <span class="step-num-label">1</span>
      </div>
      <h3>Waxing</h3>
      <p>Hot wax is hand-applied with a traditional copper canting tool, drawing intricate patterns freehand directly onto raw cotton.</p>
    </div>
    <div class="process-step">
      <div class="step-icon-wrap icon-teal">
        <i class="fas fa-tint"></i>
        <span class="step-num-label">2</span>
      </div>
      <h3>Dyeing</h3>
      <p>The cloth is submerged in baths of natural plant-based colour, sometimes in multiple layers to build depth and complexity.</p>
    </div>
    <div class="process-step">
      <div class="step-icon-wrap icon-blue">
        <i class="fas fa-water"></i>
        <span class="step-num-label">3</span>
      </div>
      <h3>Wax Removal</h3>
      <p>The wax is dissolved in boiling water, revealing the resist-dyed pattern — a moment of transformation that never gets old.</p>
    </div>
    <div class="process-step">
      <div class="step-icon-wrap icon-sand">
        <i class="fas fa-check-double"></i>
        <span class="step-num-label">4</span>
      </div>
      <h3>Finishing</h3>
      <p>Each piece is hand-ironed, inspected, and signed before being wrapped in recycled cotton cloth and sent to its new home.</p>
    </div>
  </div>
</section>

<!-- BTS GALLERY -->
<section class="gallery-section">
  <div class="header">
    <span class="section-tag">Behind the Scenes</span>
    <h2 class="section-title">Inside the <em>Studio</em></h2>
  </div>
  <<div class="gallery-grid">

  <div class="gallery-cell g1">
    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=1200&auto=format&fit=crop" alt="Studio">
    <div class="gallery-overlay"><i class="fas fa-expand"></i></div>
  </div>

  <div class="gallery-cell g2">
    <img src="https://images.unsplash.com/photo-1521572267360-ee0c2909d518?q=80&w=1200&auto=format&fit=crop" alt="Dyeing process">
    <div class="gallery-overlay"><i class="fas fa-expand"></i></div>
  </div>

  <div class="gallery-cell g3">
    <img src="https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?q=80&w=1200&auto=format&fit=crop" alt="Finished fabric">
    <div class="gallery-overlay"><i class="fas fa-expand"></i></div>
  </div>

  <div class="gallery-cell g4">
    <img src="https://images.unsplash.com/photo-1459908676235-d5f02a50184b?q=80&w=1200&auto=format&fit=crop" alt="Artisan tools">
    <div class="gallery-overlay"><i class="fas fa-expand"></i></div>
  </div>

</div>
</section>

<!-- VIDEO -->
<section class="video-section">
  <div class="header">
    <span class="section-tag">Watch & Learn</span>
    <h2 class="section-title">The Art of Batik — <em>In Motion</em></h2>
  </div>

  <div class="video-wrap" onclick="openVideoModal()">
    <img class="video-thumb"
         src="https://img.youtube.com/vi/hDTE3DDYvuE/maxresdefault.jpg"
         onerror="this.src='https://img.youtube.com/vi/hDTE3DDYvuE/hqdefault.jpg'">

    <div class="video-play-overlay">
      <button class="video-play-btn"><i class="fas fa-play"></i></button>
      <div class="video-label">Watch: The Art of Batik</div>
      <div class="video-duration">4 min · English</div>
    </div>
  </div>
</section>

<!-- SINGLE CLEAN MODAL -->
<div class="modal-overlay" id="videoModal" onclick="closeVideoModal(event)">
  <button class="modal-close" onclick="closeVideoModal(event)">&times;</button>

  <div class="modal-video">
    <iframe id="videoFrame"
      src="about:blank"
      frameborder="0"
      allow="autoplay; fullscreen"
      allowfullscreen></iframe>
  </div>
</div>

<script>
function openVideoModal() {
  const modal = document.getElementById('videoModal');
  const frame = document.getElementById('videoFrame');

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  frame.src = "https://www.youtube.com/embed/hDTE3DDYvuE?autoplay=1";
}

function closeVideoModal(e) {
  const modal = document.getElementById('videoModal');
  const frame = document.getElementById('videoFrame');

  if (!e || e.target === modal || e.target.classList.contains('modal-close')) {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    frame.src = 'about:blank';
  }
}
</script>


<!-- VALUES -->
<section class="values-section">
  <div>
    <span class="section-tag">What We Stand For</span>
    <h2 class="section-title">Our <em>Core Values</em></h2>
  </div>
  <div class="values-grid">
    <div class="value-card">
      <div class="value-icon" style="background:#e6f4f2;color:var(--teal)"><i class="fas fa-leaf"></i></div>
      <h3>Sustainable by Nature</h3>
      <p>Every dye we use comes from plants grown within 50km of our studio. We believe beautiful things shouldn't cost the earth.</p>
    </div>
    <div class="value-card">
      <div class="value-icon" style="background:#fdf6e3;color:var(--gold)"><i class="fas fa-hands"></i></div>
      <h3>Entirely Handmade</h3>
      <p>No machines, no shortcuts. Every wax line is drawn by hand, every pattern is a decision made by a skilled human being.</p>
    </div>
    <div class="value-card">
      <div class="value-icon" style="background:#f0ebe1;color:#8b6c42"><i class="fas fa-heart"></i></div>
      <h3>Fair &amp; Community-First</h3>
      <p>Our artisans earn fair wages and co-own the studio's profits. When you buy from us, you invest directly in their lives.</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="cta-inner">
    <span class="section-tag">Own a Piece of Heritage</span>
    <h2>Every Thread<br><em>Tells a Story</em></h2>
    <p>Take a piece of Malini's craft home, or come meet the artisans in person at one of our live workshops.</p>
    <div class="cta-btns">
      <a href="catalog.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Shop the Collection</a>
      <a href="booking.php" class="btn-ghost"><i class="fas fa-calendar-alt"></i> Book a Live Session</a>
    </div>
  </div>
</section>

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

<!-- VIDEO MODAL -->
<div class="modal-overlay" id="videoModal" onclick="closeVideoModal(event)">
  <button class="modal-close" onclick="closeVideoModal()">&times;</button>
  <div class="modal-video">
    <iframe width="100%" height="100%" src="about:blank" id="videoFrame"
      frameborder="0" allow="autoplay; fullscreen" allowfullscreen style="border-radius:12px"></iframe>
  </div>
</div>

<script>
  function openVideoModal() {
    document.getElementById('videoModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    // Replace the src below with your actual video embed URL
    // document.getElementById('videoFrame').src = 'https://www.youtube.com/embed/YOUR_ID?autoplay=1';
  }
  function closeVideoModal(e) {
    if (!e || e.target === document.getElementById('videoModal') || e.currentTarget.tagName === 'BUTTON') {
      document.getElementById('videoModal').classList.remove('open');
      document.body.style.overflow = '';
      document.getElementById('videoFrame').src = 'about:blank';
    }
  }

  // Subtle scroll-reveal for timeline cards
  const cards = document.querySelectorAll('.tl-card, .value-card, .gallery-cell');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.12 });
  cards.forEach(c => {
    c.style.opacity = '0';
    c.style.transform = 'translateY(20px)';
    c.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(c);
  });
</script>
</body>
</html>