<?php
// story.php - Artisan Storytelling Page
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html>
<head><title>Our Story | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="relative h-96 bg-cover bg-center" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/images/artisan-story-header.jpg');"><div class="absolute inset-0 flex items-center justify-center"><h1 class="text-5xl md:text-6xl text-white font-bold">25 Years of Craft</h1></div></div>

<div class="container mx-auto px-4 py-16 max-w-4xl">
    <!-- Timeline -->
    <div class="relative border-l-4 border-teal-500 ml-4 pl-8 space-y-12">
        <div><div class="absolute -left-3 w-6 h-6 bg-teal-500 rounded-full"></div><h3 class="text-xl font-bold">1999 - The Beginning</h3><p>Malini opens her first small workshop in Kandy with 3 looms.</p><img src="/images/timeline-1999.jpg" class="mt-2 rounded-lg w-48"></div>
        <div><div class="absolute -left-3 w-6 h-6 bg-teal-500 rounded-full"></div><h3 class="text-xl font-bold">2010 - Natural Dyes Revolution</h3><p>Transition to 100% natural, eco-friendly dyes from local plants.</p></div>
        <div><div class="absolute -left-3 w-6 h-6 bg-teal-500 rounded-full"></div><h3 class="text-xl font-bold">2018 - Global Recognition</h3><p>Featured in UNESCO's list of Intangible Cultural Heritage.</p></div>
        <div><div class="absolute -left-3 w-6 h-6 bg-teal-500 rounded-full"></div><h3 class="text-xl font-bold">2024 - Training Center</h3><p>Opened a training center for young artisans.</p></div>
    </div>

    <!-- Process Section -->
    <h2 class="text-3xl font-bold text-center mt-16 mb-8">The Batik Process</h2>
    <div class="grid md:grid-cols-4 gap-6">
        <div class="text-center"><div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto"><i class="fas fa-fill-drip text-2xl text-amber-700"></i></div><h3 class="font-bold mt-3">1. Waxing</h3><p class="text-sm">Hot wax applied with canting tool</p></div>
        <div class="text-center"><div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto"><i class="fas fa-tint text-2xl text-blue-700"></i></div><h3 class="font-bold mt-3">2. Dyeing</h3><p class="text-sm">Natural color immersion</p></div>
        <div class="text-center"><div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto"><i class="fas fa-water text-2xl text-green-700"></i></div><h3 class="font-bold mt-3">3. Washing</h3><p class="text-sm">Removing wax in boiling water</p></div>
        <div class="text-center"><div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto"><i class="fas fa-iron text-2xl text-purple-700"></i></div><h3 class="font-bold mt-3">4. Finishing</h3><p class="text-sm">Ironing and quality check</p></div>
    </div>

    <!-- BTS Photo Grid -->
    <h2 class="text-3xl font-bold text-center mt-16 mb-8">Behind the Scenes</h2>
    <div class="grid md:grid-cols-3 gap-4"><img src="/images/bts1.jpg" class="rounded-lg h-64 w-full object-cover bg-gray-200"><img src="/images/bts2.jpg" class="rounded-lg h-64 w-full object-cover bg-gray-200"><img src="/images/bts3.jpg" class="rounded-lg h-64 w-full object-cover bg-gray-200"></div>

    <!-- Video Section -->
    <div class="mt-16 bg-gray-900 rounded-xl p-4 text-center"><div class="aspect-video bg-gray-800 rounded flex items-center justify-center"><button class="w-20 h-20 bg-teal-600 rounded-full flex items-center justify-center"><i class="fas fa-play text-white text-2xl ml-1"></i></button></div><p class="text-white mt-3">Watch: The Art of Batik (4 min)</p></div>

    <!-- CTA -->
    <div class="teal-bg text-white rounded-xl p-8 text-center mt-12"><h2 class="text-2xl font-bold mb-2">Own a piece of this heritage</h2><a href="/catalog.php" class="inline-block bg-white teal-text px-6 py-2 rounded-full mt-2 font-semibold">Shop Now</a></div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>