<?php
// catalog.php - Product Catalog with Sidebar Filters
require_once 'config/database.php';

$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 100000;

$query = "SELECT p.*, pi.image_path FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.sort_order = 0 WHERE p.status=1";
$params = [];

if($category && $category != 'all') {
    $query .= " AND p.category = :category";
    $params['category'] = $category;
}
if($minPrice) {
    $query .= " AND p.price >= :minPrice";
    $params['minPrice'] = $minPrice;
}
if($maxPrice && $maxPrice < 100000) {
    $query .= " AND p.price <= :maxPrice";
    $params['maxPrice'] = $maxPrice;
}

switch($sort) {
    case 'price_low': $query .= " ORDER BY p.price ASC"; break;
    case 'price_high': $query .= " ORDER BY p.price DESC"; break;
    case 'popular': $query .= " ORDER BY p.id DESC"; break;
    default: $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Pagination
$page = $_GET['page'] ?? 1;
$perPage = 12;
$total = count($products);
$totalPages = ceil($total / $perPage);
$products = array_slice($products, ($page-1)*$perPage, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Batik | BatikSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
<?php include 'includes/nav.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="md:w-1/4 bg-white p-6 rounded-xl shadow-sm h-fit">
            <h3 class="font-bold text-lg mb-4">Filters</h3>
            <div class="mb-6">
                <label class="font-semibold block mb-2">Category</label>
                <select id="categoryFilter" class="w-full border rounded-lg p-2" onchange="applyFilters()">
                    <option value="">All</option>
                    <option value="fabric" <?= $category=='fabric'?'selected':'' ?>>Fabric</option>
                    <option value="clothing" <?= $category=='clothing'?'selected':'' ?>>Clothing</option>
                    <option value="home_decor" <?= $category=='home_decor'?'selected':'' ?>>Home Decor</option>
                    <option value="accessories" <?= $category=='accessories'?'selected':'' ?>>Accessories</option>
                </select>
            </div>
            <div class="mb-6">
                <label class="font-semibold block mb-2">Price Range</label>
                <div class="flex gap-2">
                    <input type="number" id="minPrice" placeholder="Min" value="<?= $minPrice ?>" class="w-1/2 border rounded p-2">
                    <input type="number" id="maxPrice" placeholder="Max" value="<?= $maxPrice ?>" class="w-1/2 border rounded p-2">
                </div>
            </div>
            <div class="mb-6">
                <label class="font-semibold block mb-2">Sort By</label>
                <select id="sortBy" class="w-full border rounded-lg p-2" onchange="applyFilters()">
                    <option value="newest" <?= $sort=='newest'?'selected':'' ?>>Newest</option>
                    <option value="price_low" <?= $sort=='price_low'?'selected':'' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort=='price_high'?'selected':'' ?>>Price: High to Low</option>
                    <option value="popular" <?= $sort=='popular'?'selected':'' ?>>Popular</option>
                </select>
            </div>
            <button onclick="resetFilters()" class="w-full bg-gray-200 py-2 rounded-lg">Reset</button>
        </aside>

        <!-- Product Grid -->
        <div class="md:w-3/4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($products as $product):
                    $stockStatus = $product['stock'] > 10 ? 'In Stock' : ($product['stock'] > 0 ? 'Low Stock' : 'Out of Stock');
                    $stockColor = $product['stock'] > 10 ? 'green' : ($product['stock'] > 0 ? 'orange' : 'red');
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition group">
                    <div class="relative">
                        <img src="<?= $product['image_path'] ?? '/images/placeholder.jpg' ?>" alt="<?= $product['name'] ?>" class="h-64 w-full object-cover">
                        <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:text-red-500"><i class="far fa-heart"></i></button>
                        <span class="absolute bottom-2 left-2 bg-<?= $stockColor ?>-500 text-white text-xs px-2 py-1 rounded"><?= $stockStatus ?></span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg"><?= $product['name'] ?></h3>
                        <p class="teal-text font-bold text-xl">LKR <?= number_format($product['price']) ?></p>
                        <button onclick="addToCart(<?= $product['id'] ?>)" class="mt-3 w-full bg-teal-600 hover:bg-teal-700 text-white py-2 rounded-lg transition">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="flex justify-center mt-8 space-x-2">
                <?php for($i=1;$i<=$totalPages;$i++): ?>
                <a href="?page=<?= $i ?>&category=<?= $category ?>&sort=<?= $sort ?>" class="px-4 py-2 border rounded <?= $i==$page ? 'bg-teal-600 text-white' : 'bg-white' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    let category = document.getElementById('categoryFilter').value;
    let minPrice = document.getElementById('minPrice').value;
    let maxPrice = document.getElementById('maxPrice').value;
    let sort = document.getElementById('sortBy').value;
    window.location.href = `?category=${category}&min_price=${minPrice}&max_price=${maxPrice}&sort=${sort}`;
}
function resetFilters() {
    window.location.href = 'catalog.php';
}
function addToCart(id) { alert('Added to cart!'); }
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>