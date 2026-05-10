<?php
// product.php - Product Detail Page
require_once 'config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, u.name as artisan_name, u.id as artisan_id FROM products p LEFT JOIN users u ON p.artisan_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if(!$product) die('Product not found');

$images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
$images->execute([$id]);
$images = $images->fetchAll();

$variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$variants->execute([$id]);
$variants = $variants->fetchAll();

$related = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$related->execute([$product['category'], $id]);
$related = $related->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $product['name'] ?> | BatikSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
<?php include 'includes/nav.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left: Image Gallery -->
        <div class="lg:w-1/2">
            <div class="bg-white rounded-xl overflow-hidden shadow-md">
                <img id="mainImage" src="<?= $images[0]['image_path'] ?? '/images/placeholder.jpg' ?>" alt="<?= $product['name'] ?>" class="w-full h-96 object-cover">
            </div>
            <div class="flex gap-2 mt-4">
                <?php foreach($images as $idx=>$img): ?>
                <img src="<?= $img['image_path'] ?>" onclick="document.getElementById('mainImage').src='<?= $img['image_path'] ?>'" class="w-20 h-20 object-cover rounded cursor-pointer border-2 hover:border-teal-500">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Product Info -->
        <div class="lg:w-1/2">
            <h1 class="text-3xl font-bold mb-2"><?= $product['name'] ?></h1>
            <p class="text-3xl teal-text font-bold mb-4">LKR <?= number_format($product['price']) ?></p>
            <p class="text-gray-600 mb-4"><?= substr($product['description'], 0, 200) ?>...</p>

            <?php if($variants): ?>
            <div class="mb-4">
                <label class="font-semibold block mb-2">Size</label>
                <select id="variant" class="border rounded-lg p-2 w-48">
                    <?php foreach($variants as $v): ?>
                    <option value="<?= $v['id'] ?>" data-price="<?= $v['price_adjustment'] ?>"><?= $v['size'] ?> (Stock: <?= $v['stock'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="flex items-center gap-4 mb-6">
                <div class="flex border rounded-lg">
                    <button onclick="decrementQty()" class="px-3 py-2 border-r">-</button>
                    <input type="number" id="quantity" value="1" min="1" class="w-16 text-center border-0">
                    <button onclick="incrementQty()" class="px-3 py-2 border-l">+</button>
                </div>
                <button onclick="addToCart(<?= $product['id'] ?>)" class="flex-1 bg-teal-600 text-white py-3 rounded-lg font-semibold hover:bg-teal-700">Add to Cart</button>
                <button class="w-12 h-12 border rounded-lg flex items-center justify-center hover:text-red-500"><i class="far fa-heart text-xl"></i></button>
            </div>

            <!-- Tabs -->
            <div class="border-t pt-6 mt-4">
                <div class="flex gap-6 border-b">
                    <button class="tab-btn active pb-2 font-semibold" data-tab="desc">Description</button>
                    <button class="tab-btn pb-2 font-semibold" data-tab="care">Materials & Care</button>
                    <button class="tab-btn pb-2 font-semibold" data-tab="shipping">Shipping Info</button>
                </div>
                <div id="desc" class="tab-content pt-4"><?= nl2br($product['description']) ?></div>
                <div id="care" class="tab-content hidden pt-4">100% Cotton, hand-dyed with natural pigments. Hand wash cold separately.</div>
                <div id="shipping" class="tab-content hidden pt-4">Free shipping within Sri Lanka on orders over LKR 5000. International shipping available.</div>
            </div>

            <!-- Artisan Profile Card -->
            <div class="bg-gray-100 p-4 rounded-xl flex items-center gap-4 mt-6">
                <div class="w-16 h-16 bg-teal-200 rounded-full flex items-center justify-center text-2xl">👩‍🎨</div>
                <div><p class="text-sm text-gray-500">Handcrafted by</p><p class="font-bold text-lg"><?= $product['artisan_name'] ?></p><p class="text-sm">35+ years of batik expertise</p></div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-16">
        <h2 class="text-2xl font-bold mb-6">You May Also Like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($related as $rel): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <img src="/images/placeholder.jpg" class="h-48 w-full object-cover">
                <div class="p-3"><h3 class="font-semibold"><?= $rel['name'] ?></h3><p class="teal-text">LKR <?= number_format($rel['price']) ?></p><button class="mt-2 text-sm bg-teal-600 text-white px-3 py-1 rounded">Add</button></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function decrementQty(){ let q=document.getElementById('quantity'); if(q.value>1) q.value--; }
function incrementQty(){ let q=document.getElementById('quantity'); q.value++; }
document.querySelectorAll('.tab-btn').forEach(btn=>{ btn.addEventListener('click',()=>{ document.querySelectorAll('.tab-content').forEach(c=>c.classList.add('hidden')); document.getElementById(btn.dataset.tab).classList.remove('hidden'); document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active','border-teal-600','text-teal-600')); btn.classList.add('active','border-teal-600','text-teal-600'); }) });
function addToCart(id){ alert('Added to cart!'); }
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>