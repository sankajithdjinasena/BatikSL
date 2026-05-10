<?php
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

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>

:root{
  --teal:#0f766e;
  --teal-light:#14b8a6;
  --cream:#faf7f2;
  --sand:#f0ebe1;
  --charcoal:#1c1c1c;
  --warm-gray:#6b6560;
  --gold:#c9a84c;
}

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

body{
  font-family:'DM Sans',sans-serif;
  background:var(--cream);
  color:var(--charcoal);
  overflow-x:hidden;
}

html{
  scroll-behavior:smooth;
}

/* NAVBAR */

nav{
  position:fixed;
  top:0;
  left:0;
  right:0;
  z-index:1000;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:1.2rem 4rem;
  transition:0.3s;
}

nav.scrolled{
  background:rgba(250,247,242,0.95);
  backdrop-filter:blur(10px);
  box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.nav-logo{
  font-family:'Playfair Display',serif;
  font-size:1.7rem;
  color:white;
  text-decoration:none;
}

nav.scrolled .nav-logo{
  color:var(--charcoal);
}

.nav-logo span{
  color:var(--gold);
}

.nav-links{
  list-style:none;
  display:flex;
  gap:2rem;
}

.nav-links a{
  text-decoration:none;
  color:white;
  font-size:0.85rem;
  text-transform:uppercase;
  letter-spacing:0.08em;
}

nav.scrolled .nav-links a{
  color:var(--warm-gray);
}

.nav-icons{
  display:flex;
  align-items:center;
  gap:1rem;
}

.nav-icons a{
  color:white;
  text-decoration:none;
  position:relative;
}

nav.scrolled .nav-icons a{
  color:var(--warm-gray);
}

.cart-badge{
  position:absolute;
  top:-8px;
  right:-10px;
  width:18px;
  height:18px;
  border-radius:50%;
  background:var(--gold);
  color:white;
  font-size:0.65rem;
  display:flex;
  align-items:center;
  justify-content:center;
}

.nav-signin{
  padding:0.5rem 1.3rem;
  border-radius:30px;
  border:1px solid rgba(255,255,255,0.5);
  color:white !important;
}

nav.scrolled .nav-signin{
  border-color:var(--teal);
  color:var(--teal) !important;
}

/* HERO */

.hero{
  height:100vh;
  position:relative;
  display:flex;
  justify-content:center;
  align-items:center;
}

.hero-bg{
  position:absolute;
  inset:0;
  background:
  linear-gradient(rgba(0,0,0,0.45),rgba(0,0,0,0.55)),
  url('/images/hero-batik-bg.jpg') center/cover no-repeat;
}

.hero-content{
  position:relative;
  z-index:2;
  text-align:center;
  color:white;
  max-width:800px;
  padding:0 1rem;
}

.hero h1{
  font-family:'Playfair Display',serif;
  font-size:clamp(3rem,8vw,5.5rem);
  line-height:1.1;
  margin-bottom:1.5rem;
}

.hero h1 em{
  color:#b2ded8;
}

.hero p{
  font-size:1.1rem;
  color:rgba(255,255,255,0.8);
  line-height:1.7;
  margin-bottom:2rem;
}

.hero-buttons{
  display:flex;
  gap:1rem;
  justify-content:center;
  flex-wrap:wrap;
}

.btn-primary,
.btn-outline{
  padding:0.9rem 2rem;
  border-radius:50px;
  text-decoration:none;
  font-size:0.9rem;
  display:inline-flex;
  align-items:center;
  gap:0.5rem;
  transition:0.3s;
}

.btn-primary{
  background:var(--teal);
  color:white;
}

.btn-primary:hover{
  background:var(--teal-light);
}

.btn-outline{
  border:2px solid rgba(255,255,255,0.5);
  color:white;
}

.btn-outline:hover{
  background:rgba(255,255,255,0.1);
}

/* SECTION */

section{
  padding:6rem 4rem;
}

.section-title{
  font-family:'Playfair Display',serif;
  font-size:3rem;
  margin-bottom:1rem;
}

.section-title em{
  color:var(--teal);
}

.section-tag{
  color:var(--teal);
  letter-spacing:0.15em;
  text-transform:uppercase;
  font-size:0.75rem;
}

/* PRODUCTS */

.products-scroll{
  display:flex;
  gap:1.5rem;
  overflow-x:auto;
  padding-bottom:1rem;
  scrollbar-width:none;
}

.products-scroll::-webkit-scrollbar{
  display:none;
}

.product-card{
  width:280px;
  background:white;
  border-radius:16px;
  overflow:hidden;
  flex-shrink:0;
  transition:0.3s;
}

.product-card:hover{
  transform:translateY(-8px);
  box-shadow:0 20px 40px rgba(0,0,0,0.1);
}

.product-img-wrap{
  height:320px;
  position:relative;
  overflow:hidden;
}

.product-img-wrap img{
  width:100%;
  height:100%;
  object-fit:cover;
}

.product-badge{
  position:absolute;
  top:1rem;
  left:1rem;
  background:var(--gold);
  color:white;
  padding:0.3rem 0.8rem;
  border-radius:30px;
  font-size:0.7rem;
}

.wishlist-btn{
  position:absolute;
  top:1rem;
  right:1rem;
  width:38px;
  height:38px;
  border-radius:50%;
  border:none;
  background:white;
  cursor:pointer;
}

.product-info{
  padding:1.2rem;
}

.product-category{
  font-size:0.7rem;
  text-transform:uppercase;
  color:var(--warm-gray);
  margin-bottom:0.4rem;
}

.product-name{
  font-family:'Playfair Display',serif;
  font-size:1.15rem;
  margin-bottom:1rem;
}

.product-footer{
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.product-price{
  color:var(--teal);
  font-weight:600;
  font-size:1.1rem;
}

.add-cart-btn{
  width:40px;
  height:40px;
  border-radius:50%;
  border:none;
  background:var(--charcoal);
  color:white;
  cursor:pointer;
}

.add-cart-btn:hover{
  background:var(--teal);
}

/* REVIEWS */

.review-card{
  background:white;
  padding:2rem;
  border-radius:16px;
  height:100%;
}

.review-stars{
  color:var(--gold);
  margin-bottom:1rem;
}

.review-text{
  font-family:'Playfair Display',serif;
  font-style:italic;
  line-height:1.7;
  margin-bottom:1.5rem;
}

.reviewer{
  display:flex;
  gap:1rem;
  align-items:center;
}

.reviewer-avatar{
  width:45px;
  height:45px;
  border-radius:50%;
  background:var(--sand);
  display:flex;
  justify-content:center;
  align-items:center;
  font-weight:bold;
  color:var(--teal);
}

/* FOOTER */

footer{
  background:#111;
  color:rgba(255,255,255,0.6);
  padding:5rem 4rem 2rem;
}

.footer-grid{
  display:grid;
  grid-template-columns:2fr 1fr 1fr 1fr;
  gap:3rem;
}

.footer-logo{
  font-family:'Playfair Display',serif;
  color:white;
  font-size:1.8rem;
}

.footer-logo span{
  color:var(--gold);
}

.footer-col h4{
  color:white;
  margin-bottom:1rem;
}

.footer-col ul{
  list-style:none;
}

.footer-col li{
  margin-bottom:0.7rem;
}

.footer-col a{
  color:inherit;
  text-decoration:none;
}

.footer-bottom{
  border-top:1px solid rgba(255,255,255,0.08);
  margin-top:3rem;
  padding-top:1.5rem;
  text-align:center;
  font-size:0.85rem;
}

/* RESPONSIVE */

@media(max-width:900px){

  nav{
    padding:1rem 1.5rem;
  }

  .nav-links{
    display:none;
  }

  section{
    padding:4rem 1.5rem;
  }

  .footer-grid{
    grid-template-columns:1fr;
  }

}

</style>
</head>

<body>

<!-- NAVBAR -->

<nav id="navbar">

<a href="index.php" class="nav-logo">
Batik<span>SL</span>
</a>

<ul class="nav-links">
<li><a href="index.php">Home</a></li>
<li><a href="catalog.php">Shop</a></li>
<li><a href="story.php">Our Story</a></li>
<li><a href="booking.php">Live Session</a></li>
<li><a href="account.php">Account</a></li>
</ul>

<div class="nav-icons">

<a href="wishlist.php">
<i class="far fa-heart"></i>
</a>

<a href="cart.php">
<i class="fas fa-shopping-bag"></i>
<span class="cart-badge">0</span>
</a>

<a href="login.php" class="nav-signin">
Sign In
</a>

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
<h2 class="section-title">
Featured <em>Creations</em>
</h2>
</div>

<a href="catalog.php"
style="color:var(--teal);text-decoration:none;font-weight:600;">
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

while($product = $stmt->fetch()):

?>

<div class="product-card">

<div class="product-img-wrap">

<img
src="<?= $product['image_path'] ?? '/images/placeholder.jpg' ?>"
alt="<?= htmlspecialchars($product['name']) ?>"
>

<?php if(!empty($product['featured'])): ?>
<span class="product-badge">Featured</span>
<?php endif; ?>

<button class="wishlist-btn">
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

<button
class="add-cart-btn"
onclick="addToCart(<?= $product['id'] ?>)"
>
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

<div class="section-tag">
Testimonials
</div>

<h2 class="section-title">
What Customers <em>Say</em>
</h2>

</div>

<div class="swiper reviews-swiper">

<div class="swiper-wrapper">

<?php

$reviews = [

[
'name'=>'Sarah Johnson',
'rating'=>5,
'text'=>'Absolutely stunning craftsmanship. The colors are beautiful.',
'location'=>'London, UK'
],

[
'name'=>'Michael Chen',
'rating'=>5,
'text'=>'The live workshop was magical and unforgettable.',
'location'=>'Singapore'
],

[
'name'=>'Priya Sharma',
'rating'=>4,
'text'=>'Beautiful fabrics and very fast delivery to India.',
'location'=>'Mumbai, India'
]

];

foreach($reviews as $review):

?>

<div class="swiper-slide">

<div class="review-card">

<div class="review-stars">

<?php
for($i=0;$i<$review['rating'];$i++){
echo '<i class="fas fa-star"></i>';
}
?>

</div>

<p class="review-text">
"<?= $review['text'] ?>"
</p>

<div class="reviewer">

<div class="reviewer-avatar">
<?= strtoupper(substr($review['name'],0,1)) ?>
</div>

<div>

<div style="font-weight:600;">
<?= $review['name'] ?>
</div>

<div style="font-size:0.85rem;color:var(--warm-gray)">
<?= $review['location'] ?>
</div>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<div class="swiper-pagination"
style="margin-top:2rem;position:static;"></div>

</div>

</section>

<!-- FOOTER -->

<footer>

<div class="footer-grid">

<div>

<div class="footer-logo">
Batik<span>SL</span>
</div>

<p style="margin-top:1rem;line-height:1.7;">
Handcrafted batik from the heart of Kandy, Sri Lanka.
Every piece is a living piece of cultural heritage.
</p>

</div>

<div class="footer-col">

<h4>Shop</h4>

<ul>
<li><a href="#">Clothing</a></li>
<li><a href="#">Fabric</a></li>
<li><a href="#">Home Décor</a></li>
</ul>

</div>

<div class="footer-col">

<h4>Support</h4>

<ul>
<li><a href="#">Contact</a></li>
<li><a href="#">Shipping</a></li>
<li><a href="#">Returns</a></li>
</ul>

</div>

<div class="footer-col">

<h4>Connect</h4>

<p>
+94 77 123 4567
</p>

<p style="margin-top:0.5rem;">
hello@batiksl.com
</p>

<div style="margin-top:1rem;display:flex;gap:1rem;font-size:1.2rem;">

<i class="fab fa-facebook"></i>
<i class="fab fa-instagram"></i>
<i class="fab fa-whatsapp"></i>

</div>

</div>

</div>

<div class="footer-bottom">

© 2025 BatikSL. All rights reserved.

</div>

</footer>

<script>

/* NAVBAR SCROLL */

const navbar = document.getElementById('navbar');

window.addEventListener('scroll',()=>{

navbar.classList.toggle(
'scrolled',
window.scrollY > 60
);

});

/* SWIPER */

new Swiper('.reviews-swiper',{

slidesPerView:1,
spaceBetween:24,

pagination:{
el:'.swiper-pagination',
clickable:true
},

breakpoints:{
640:{slidesPerView:2},
1024:{slidesPerView:3}
}

});

/* ADD TO CART */

function addToCart(id){

alert("Product added to cart!");

}

/* WISHLIST */

document.querySelectorAll('.wishlist-btn').forEach(btn=>{

btn.addEventListener('click',()=>{

const icon = btn.querySelector('i');

icon.classList.toggle('far');
icon.classList.toggle('fas');

btn.style.color =
icon.classList.contains('fas')
? '#e05252'
: '#ccc';

});

});

</script>

</body>
</html>