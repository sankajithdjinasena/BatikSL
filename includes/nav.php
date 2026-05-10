<?php
// includes/nav.php - Shared Navigation Component
?>
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-2"><i class="fas fa-palette text-2xl teal-text"></i><span class="text-xl font-bold text-gray-800">Batik<span class="teal-text">SL</span></span></div>
        <div class="hidden md:flex space-x-8"><a href="/" class="text-gray-700 hover:text-teal-600">Home</a><a href="/catalog.php" class="text-gray-700 hover:text-teal-600">Shop</a><a href="/story.php" class="text-gray-700 hover:text-teal-600">Our Story</a><a href="/booking.php" class="text-gray-700 hover:text-teal-600">Live Session</a><a href="/account.php" class="text-gray-700 hover:text-teal-600">Account</a></div>
        <div class="flex items-center space-x-4"><i class="fas fa-search text-gray-600"></i><a href="/wishlist.php"><i class="far fa-heart text-gray-600"></i></a><a href="/cart.php" class="relative"><i class="fas fa-shopping-bag text-gray-600"></i><span class="absolute -top-2 -right-2 bg-teal-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span></a><a href="/login.php" class="hidden md:block bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">Sign In</a></div>
    </div>
</nav>