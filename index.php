<?php
// index.php - Home Page
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BatikSL | Handcrafted Batik from Kandy, Sri Lanka</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .hero-bg { background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.5)), url('/images/hero-batik-bg.jpg'); background-size: cover; background-position: center; }
        .teal-bg { background-color: #0f766e; }
        .teal-text { color: #0f766e; }
        .teal-hover:hover { background-color: #0d9488; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-gray-50">

<!-- Header / Navbar -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <i class="fas fa-palette text-2xl teal-text"></i>
            <span class="text-xl font-bold text-gray-800">Batik<span class="teal-text">SL</span></span>
        </div>
        <div class="hidden md:flex space-x-8">
            <a href="index.php" class="text-gray-700 hover:text-teal-600">Home</a>
            <a href="catalog.php" class="text-gray-700 hover:text-teal-600">Shop</a>
            <a href="story.php" class="text-gray-700 hover:text-teal-600">Our Story</a>
            <a href="booking.php" class="text-gray-700 hover:text-teal-600">Live Session</a>
            <a href="account.php" class="text-gray-700 hover:text-teal-600">Account</a>
        </div>
        <div class="flex items-center space-x-4">
            <i class="fas fa-search text-gray-600 cursor-pointer"></i>
            <a href="/wishlist.php" class="relative"><i class="far fa-heart text-gray-600"></i></a>
            <a href="/cart.php" class="relative"><i class="fas fa-shopping-bag text-gray-600"></i><span class="absolute -top-2 -right-2 bg-teal-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span></a>
            <a href="/login.php" class="hidden md:block bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">Sign In</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-bg h-screen flex items-center">
    <div class="container mx-auto px-4 text-center text-white">
        <h1 class="text-5xl md:text-7xl font-bold mb-4">Handcrafted Batik from Kandy, Sri Lanka</h1>
        <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto">Traditional wax-resist dyeing techniques passed down through generations</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/catalog.php" class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3 rounded-full font-semibold transition">Shop Now <i class="fas fa-arrow-right ml-2"></i></a>
            <a href="/booking.php" class="bg-white text-gray-800 hover:bg-gray-100 px-8 py-3 rounded-full font-semibold transition">Book a Live Session <i class="fas fa-calendar-alt ml-2"></i></a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Featured Creations</h2>
        <div class="overflow-x-auto scrollbar-hide">
            <div class="flex space-x-6 w-max px-4">
                <?php
                $stmt = $pdo->query("SELECT p.*, pi.image_path FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.sort_order = 0 WHERE p.status=1 LIMIT 8");
                while($product = $stmt->fetch()):
                ?>
                <div class="w-72 bg-white rounded-xl shadow-md overflow-hidden flex-shrink-0 hover:shadow-xl transition">
                    <img src="<?= $product['image_path'] ?? '/images/placeholder.jpg' ?>" alt="<?= $product['name'] ?>" class="h-64 w-full object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg"><?= $product['name'] ?></h3>
                        <p class="teal-text font-bold text-xl mt-1">LKR <?= number_format($product['price']) ?></p>
                        <button onclick="addToCart(<?= $product['id'] ?>)" class="mt-3 w-full bg-gray-800 hover:bg-teal-600 text-white py-2 rounded-lg transition">Add to Cart</button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<!-- Artisan Story Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="md:w-1/2">
                <span class="text-teal-600 font-semibold">Our Master Artisan</span>
                <h2 class="text-4xl font-bold mt-2 mb-4">Malini Weerasinghe</h2>
                <p class="text-gray-600 mb-2">35+ Years of Experience</p>
                <p class="text-gray-700 leading-relaxed">Malini began learning batik at age 12 from her grandmother in Kandy. Today, she leads a collective of 15 artisans keeping traditional Sri Lankan batik alive. Each piece takes 4-7 days to complete, using natural dyes and hand-stamped copper tools.</p>
                <p class="text-gray-700 mt-4">"Batik is not just art—it's our story woven into every line and color."</p>
                <a href="/story.php" class="inline-block mt-6 text-teal-600 font-semibold hover:underline">Read Full Story →</a>
            </div>
            <div class="md:w-1/2 relative">
                <img src="/images/artisan-malini.jpg" alt="Artisan Malini" class="rounded-2xl shadow-xl w-full">
                <button class="absolute inset-0 m-auto w-20 h-20 bg-teal-600 rounded-full flex items-center justify-center shadow-lg hover:bg-teal-700 transition" onclick="openVideoModal()">
                    <i class="fas fa-play text-white text-2xl ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-12">How It Works</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div><div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl teal-text"></i></div><h3 class="text-xl font-semibold">1. Browse</h3><p class="text-gray-600">Explore our collection of unique batik pieces</p></div>
            <div><div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-shopping-cart text-3xl teal-text"></i></div><h3 class="text-xl font-semibold">2. Order</h3><p class="text-gray-600">Select your favorites and checkout securely</p></div>
            <div><div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-box-open text-3xl teal-text"></i></div><h3 class="text-xl font-semibold">3. Receive</h3><p class="text-gray-600">Hand-delivered worldwide with care</p></div>
        </div>
    </div>
</section>

<!-- Experience Booking Teaser -->
<section class="teal-bg py-16">
    <div class="container mx-auto px-4 text-center text-white">
        <h2 class="text-4xl font-bold mb-4">Make Batik With Us</h2>
        <p class="text-xl mb-8">Join a 2-hour live session at our Kandy workshop. Learn from master artisans.</p>
        <a href="/booking.php" class="bg-white teal-text px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition inline-block">Book Now <i class="fas fa-ticket-alt ml-2"></i></a>
    </div>
</section>

<!-- Customer Reviews Carousel -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">What Our Customers Say</h2>
        <div class="swiper reviews-swiper">
            <div class="swiper-wrapper">
                <?php
                $reviews = [
                    ['name'=>'Sarah Johnson','rating'=>5,'text'=>'Absolutely stunning craftsmanship! The sarong I ordered exceeded all expectations.'],
                    ['name'=>'Michael Chen','rating'=>5,'text'=>'The live session was magical. Learned so much about traditional batik.'],
                    ['name'=>'Priya Sharma','rating'=>4,'text'=>'Beautiful fabrics, shipping was quick to India.']
                ];
                foreach($reviews as $review):
                ?>
                <div class="swiper-slide">
                    <div class="bg-white p-6 rounded-xl shadow-md text-center">
                        <div class="flex justify-center mb-2"><?php for($i=0;$i<$review['rating'];$i++) echo '<i class="fas fa-star text-yellow-400"></i>'; ?></div>
                        <p class="text-gray-600 italic">"{$review['text']}"</p>
                        <p class="font-semibold mt-4">— {$review['name']}</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination mt-4"></div>
        </div>
    </div>
</section>

<!-- Instagram Feed Strip -->
<section class="py-8 bg-white">
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6 px-4">
            <h2 class="text-2xl font-bold">Follow us @BatikSL</h2>
            <a href="#" class="text-teal-600"><i class="fab fa-instagram mr-1"></i> Instagram</a>
        </div>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-1">
            <?php for($i=1;$i<=6;$i++): ?>
            <div class="aspect-square bg-gray-200 flex items-center justify-center"><i class="fab fa-instagram text-3xl text-gray-400"></i><span class="sr-only">Instagram post <?= $i ?></span></div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-300 py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <div><div class="flex items-center space-x-2 mb-4"><i class="fas fa-palette text-2xl text-teal-500"></i><span class="text-xl font-bold text-white">BatikSL</span></div><p class="text-sm">Handcrafted batik from the heart of Kandy, Sri Lanka.</p></div>
            <div><h3 class="text-white font-semibold mb-4">Shop</h3><ul class="space-y-2 text-sm"><li><a href="/catalog.php?category=fabric">Fabric</a></li><li><a href="/catalog.php?category=clothing">Clothing</a></li><li><a href="/catalog.php?category=home_decor">Home Decor</a></li></ul></div>
            <div><h3 class="text-white font-semibold mb-4">Support</h3><ul class="space-y-2 text-sm"><li><a href="#">Contact</a></li><li><a href="#">Shipping Info</a></li><li><a href="#">Returns</a></li></ul></div>
            <div><h3 class="text-white font-semibold mb-4">Connect</h3><div class="flex space-x-4 text-xl"><i class="fab fa-facebook hover:text-teal-500 cursor-pointer"></i><i class="fab fa-instagram hover:text-teal-500 cursor-pointer"></i><i class="fab fa-whatsapp hover:text-teal-500 cursor-pointer"></i></div><p class="mt-4 text-sm">+94 77 123 4567<br>hello@batiksl.com</p></div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm">© 2025 BatikSL. All rights reserved. Handcrafted with ❤️ in Sri Lanka</div>
    </div>
</footer>

<script>
    new Swiper('.reviews-swiper', { slidesPerView: 1, spaceBetween: 20, pagination: { el: '.swiper-pagination', clickable: true }, breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } } });
    function addToCart(productId) { alert('Added to cart! (Demo)'); }
    function openVideoModal() { alert('Video modal would open here (Demo)'); }
</script>
</body>
</html>