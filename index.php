<?php
require_once 'config/database.php';

?>

<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BatikSL | Handcrafted Batik from Kandy, Sri Lanka</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>

:root {
  --teal: #0f766e;
  --teal-light: #14b8a6;
  --cream: #faf7f2;
  --sand: #f0ebe1;
  --charcoal: #1c1c1c;
  --warm-gray: #6b6560;
  --gold: #c9a84c;
  --border: #e8e3da;
}

*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--cream);
  color: var(--charcoal);
  overflow-x: hidden;
}

html {
  scroll-behavior: smooth;
}

/* ── NAVBAR ── */

nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 4rem;
  transition: 0.3s;
}

nav.scrolled {
  background: rgba(250,247,242,0.97);
  backdrop-filter: blur(12px);
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border-bottom: 1px solid var(--border);
}

.nav-logo {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  color: white;
  text-decoration: none;
}

nav.scrolled .nav-logo {
  color: var(--charcoal);
}

.nav-logo span {
  color: var(--gold);
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 2.5rem;
}

.nav-links a {
  text-decoration: none;
  color: white;
  font-size: 0.82rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  transition: color 0.25s;
}

nav.scrolled .nav-links a {
  color: var(--warm-gray);
}

.nav-links a:hover,
.nav-links a.active {
  color: var(--gold);
}

nav.scrolled .nav-links a:hover,
nav.scrolled .nav-links a.active {
  color: var(--teal);
}

.nav-icons {
  display: flex;
  align-items: center;
  gap: 1.4rem;
}

.nav-icons a,
.nav-icons button {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  font-size: 1.05rem;
  text-decoration: none;
  transition: color 0.25s;
  position: relative;
}

nav.scrolled .nav-icons a,
nav.scrolled .nav-icons button {
  color: var(--warm-gray);
}

.nav-icons a:hover,
.nav-icons button:hover {
  color: var(--gold);
}

nav.scrolled .nav-icons a:hover,
nav.scrolled .nav-icons button:hover {
  color: var(--teal);
}

.cart-badge {
  position: absolute;
  top: -6px; right: -8px;
  width: 17px; height: 17px;
  border-radius: 50%;
  background: var(--gold);
  color: white;
  font-size: 0.62rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-signin {
  padding: 0.45rem 1.3rem;
  border: 1.5px solid rgba(255,255,255,0.5);
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: white !important;
  text-decoration: none;
  transition: all 0.25s;
}

nav.scrolled .nav-signin {
  border-color: var(--teal);
  color: var(--teal) !important;
}

nav.scrolled .nav-signin:hover {
  color: white !important;
}

/* ── HERO ── */

.hero {
  height: 100vh;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)),
    url('/images/hero-batik-bg.jpg') center/cover no-repeat;
}

.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  color: white;
  max-width: 800px;
  padding: 0 1rem;
}

.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(3rem, 8vw, 5.5rem);
  line-height: 1.1;
  margin-bottom: 1.5rem;
}

.hero h1 em {
  color: #b2ded8;
}

.hero p {
  font-size: 1.1rem;
  color: rgba(255,255,255,0.8);
  line-height: 1.7;
  margin-bottom: 2rem;
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary,
.btn-outline {
  padding: 0.9rem 2rem;
  border-radius: 50px;
  text-decoration: none;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: 0.3s;
}

.btn-primary {
  background: var(--teal);
  color: white;
}

.btn-primary:hover {
  background: var(--teal-light);
}

.btn-outline {
  border: 2px solid rgba(255,255,255,0.5);
  color: white;
}

.btn-outline:hover {
  background: rgba(255,255,255,0.1);
}

/* ── SECTION ── */

section {
  padding: 6rem 4rem;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 3rem;
  margin-bottom: 1rem;
}

.section-title em {
  color: var(--teal);
}

.section-tag {
  color: var(--teal);
  letter-spacing: 0.15em;
  text-transform: uppercase;
  font-size: 0.75rem;
}

/* ── PRODUCTS ── */

.products-scroll {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  padding-bottom: 1rem;
  scrollbar-width: none;
}

.products-scroll::-webkit-scrollbar {
  display: none;
}

.product-card {
  width: 280px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  flex-shrink: 0;
  transition: 0.3s;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.product-img-wrap {
  height: 320px;
  position: relative;
  overflow: hidden;
}

.product-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-badge {
  position: absolute;
  top: 1rem; left: 1rem;
  background: var(--gold);
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 30px;
  font-size: 0.7rem;
}

.wishlist-btn {
  position: absolute;
  top: 1rem; right: 1rem;
  width: 38px; height: 38px;
  border-radius: 50%;
  border: none;
  background: white;
  cursor: pointer;
}
.product-info {
  padding: 1.2rem;
  display: flex;
  flex-direction: column;
}

.product-category {
  font-size: 0.7rem;
  text-transform: uppercase;
  color: var(--warm-gray);
  margin-bottom: 0.4rem;
  letter-spacing: 0.08em;
}

.product-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  line-height: 1.4;
  margin-bottom: 1rem;

  /* balanced title area */
  min-height: 3.2rem;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-price {
  color: var(--teal);
  font-weight: 600;
  font-size: 1.1rem;
}

.add-cart-btn {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: var(--charcoal);
  color: white;
  cursor: pointer;
}

.add-cart-btn:hover {
  background: var(--teal);
}

/* ── REVIEWS ── */

.review-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  height: 100%;
}

.review-stars {
  color: var(--gold);
  margin-bottom: 1rem;
}

.review-text {
  font-family: 'Playfair Display', serif;
  font-style: italic;
  line-height: 1.7;
  margin-bottom: 1.5rem;
}

.reviewer {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.reviewer-avatar {
  width: 45px; height: 45px;
  border-radius: 50%;
  background: var(--sand);
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: bold;
  color: var(--teal);
}

/* ── FOOTER ── */

footer {
  background: #111;
  color: rgba(255,255,255,0.6);
  padding: 5rem 4rem 2.5rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.5fr;
  gap: 4rem;
}

.footer-logo {
  font-family: 'Playfair Display', serif;
  color: white;
  font-size: 1.8rem;
  display: block;
  margin-bottom: 1rem;
}

.footer-logo span {
  color: var(--gold);
}

.footer-about {
  font-size: 0.88rem;
  line-height: 1.7;
  max-width: 280px;
}

.footer-col h4 {
  color: white;
  font-size: 0.78rem;
  font-weight: 500;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  margin-bottom: 1.4rem;
}

.footer-col ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.footer-col ul a {
  color: inherit;
  text-decoration: none;
  font-size: 0.88rem;
  transition: color 0.2s;
}

.footer-col ul a:hover {
  color: var(--teal-light);
}

.social-icons {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.social-icon {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
  text-decoration: none;
  font-size: 0.95rem;
  transition: all 0.25s;
}

.social-icon:hover {
  border-color: var(--teal-light);
  color: var(--teal-light);
}

.footer-bottom {
  border-top: 1px solid rgba(255,255,255,0.06);
  margin-top: 4rem;
  padding-top: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.8rem;
}

.footer-bottom a {
  color: inherit;
  text-decoration: none;
}

.footer-bottom a:hover {
  color: var(--teal-light);
}

/* ── RESPONSIVE ── */

@media (max-width: 900px) {

  nav {
    padding: 1rem 1.5rem;
  }

  .nav-links {
    display: none;
  }

  section {
    padding: 4rem 1.5rem;
  }

  .footer-grid {
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
  }

  footer {
    padding: 4rem 1.5rem 2rem;
  }

}

@media (max-width: 520px) {

  .footer-grid {
    grid-template-columns: 1fr;
  }

}

</style>
</head>

<body>

<!-- NAVBAR -->
<nav id="navbar">

  <a href="index.php" class="nav-logo">Batik<span>SL</span></a>

  <ul class="nav-links">
    <li><a href="index.php" class="active">Home</a></li>
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
  <a href="account.php" style="
      font-size:0.82rem;font-weight:500;letter-spacing:0.05em;
      text-transform:uppercase;color:inherit;text-decoration:none;
      transition:color 0.25s;">
    <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
  </a>
  <a href="logout.php" class="nav-signin">Sign Out</a>
<?php else: ?>
  <a href="login.php" class="nav-signin">Sign In</a>
<?php endif; ?>
  </div>

</nav>

<!-- HERO -->
<section class="hero">

  <div class="hero-bg"></div>

  <div class="hero-content">

    <div class="section-tag" style="color:var(--gold);margin-bottom:1rem;">
      Kandy, Sri Lanka
    </div>

    <h1>
      Handcrafted Batik,<br>
      <em>Told in Wax & Colour</em>
    </h1>

    <p>
      Traditional wax-resist dyeing techniques passed down through generations — every piece carries Sri Lankan heritage.
    </p>

    <div class="hero-buttons">
      <a href="catalog.php" class="btn-primary">
        Explore Collection
        <i class="fas fa-arrow-right"></i>
      </a>
      <a href="booking.php" class="btn-outline">
        <i class="fas fa-calendar-alt"></i>
        Book Live Session
      </a>
    </div>

  </div>

</section>

<!-- FEATURED PRODUCTS -->
<section>

  <div style="display:flex;justify-content:space-between;align-items:end;margin-bottom:3rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="section-tag">Curated Picks</div>
      <h2 class="section-title">Featured <em>Creations</em></h2>
    </div>
    <a href="catalog.php" style="color:var(--teal);text-decoration:none;font-weight:600;">
      View All →
    </a>
  </div>

  <div class="products-scroll">

    <?php
    $stmt = $pdo->query("
      SELECT p.*, pi.image_path
      FROM products p
      LEFT JOIN product_images pi
        ON p.id = pi.product_id
        AND pi.sort_order = 0
      WHERE p.status = 1
      LIMIT 8
    ");

    while ($product = $stmt->fetch()):
    ?>

    <div class="product-card">

      <div class="product-img-wrap">
        <img
          src="<?= htmlspecialchars($product['image_path'] ?? '/images/placeholder.jpg') ?>"
          alt="<?= htmlspecialchars($product['name']) ?>"
          loading="lazy"
        >

        <?php if (!empty($product['featured'])): ?>
        <span class="product-badge">Featured</span>
        <?php endif; ?>

        <button class="wishlist-btn" aria-label="Add to wishlist">
          <i class="far fa-heart"></i>
        </button>
      </div>

      <div class="product-info">
        <div class="product-category">
          <?= htmlspecialchars($product['category'] ?? 'Batik') ?>
        </div>
        <div class="product-name">
          <?= htmlspecialchars($product['name']) ?>
        </div>
        <div class="product-footer">
          <div class="product-price">
            LKR <?= number_format($product['price']) ?>
          </div>
          <button class="add-cart-btn" onclick="addToCart(<?= $product['id'] ?>)" aria-label="Add to cart">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>

    </div>

    <?php endwhile; ?>

  </div>

</section>

<!-- REVIEWS -->
<section style="background:var(--sand);">

  <div style="margin-bottom:3rem;">
    <div class="section-tag">Testimonials</div>
    <h2 class="section-title">What Customers <em>Say</em></h2>
  </div>

  <div class="swiper reviews-swiper">
    <div class="swiper-wrapper">

      <?php
      $reviews = [
        [
          'name'     => 'Sarah Johnson',
          'rating'   => 5,
          'text'     => 'Absolutely stunning craftsmanship. The colors are beautiful.',
          'location' => 'London, UK'
        ],
        [
          'name'     => 'Michael Chen',
          'rating'   => 5,
          'text'     => 'The live workshop was magical and unforgettable.',
          'location' => 'Singapore'
        ],
        [
          'name'     => 'Priya Sharma',
          'rating'   => 4,
          'text'     => 'Beautiful fabrics and very fast delivery to India.',
          'location' => 'Mumbai, India'
        ],
      ];

      foreach ($reviews as $review):
      ?>

      <div class="swiper-slide">
        <div class="review-card">
          <div class="review-stars">
            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
            <i class="fas fa-star"></i>
            <?php endfor; ?>
          </div>
          <p class="review-text">"<?= htmlspecialchars($review['text']) ?>"</p>
          <div class="reviewer">
            <div class="reviewer-avatar">
              <?= strtoupper(substr($review['name'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:600;"><?= htmlspecialchars($review['name']) ?></div>
              <div style="font-size:0.85rem;color:var(--warm-gray)"><?= htmlspecialchars($review['location']) ?></div>
            </div>
          </div>
        </div>
      </div>

      <?php endforeach; ?>

    </div>

    <div class="swiper-pagination" style="margin-top:2rem;position:static;"></div>
  </div>

</section>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">

    <div>
      <span class="footer-logo">Batik<span>SL</span></span>
      <p class="footer-about">
        Handcrafted batik from the heart of Kandy, Sri Lanka.
        Every piece is a living piece of cultural heritage, made with natural dyes and generations of knowledge.
      </p>
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

  /* NAVBAR SCROLL */
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  });

  /* SWIPER */
  new Swiper('.reviews-swiper', {
    slidesPerView: 1,
    spaceBetween: 24,
    pagination: {
      el: '.swiper-pagination',
      clickable: true
    },
    breakpoints: {
      640:  { slidesPerView: 2 },
      1024: { slidesPerView: 3 }
    }
  });

  /* ADD TO CART */
  function addToCart(id) {
    alert("Product added to cart!");
  }

  /* WISHLIST */
  document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const icon = btn.querySelector('i');
      icon.classList.toggle('far');
      icon.classList.toggle('fas');
      btn.style.color = icon.classList.contains('fas') ? '#e05252' : '#ccc';
    });
  });

</script>

</body>
</html>