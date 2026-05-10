<?php
// artisan/dashboard.php - Artisan Dashboard (Protected, role=artisan)
require_once '../config/database.php';
// Check if user is logged in and has artisan role
$artisan = $_SESSION['user'] ?? ['name'=>'Malini Weerasinghe','role'=>'artisan'];
if($artisan['role'] != 'artisan') { header('Location: /login.php'); exit; }
$active = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html>
<head><title>Artisan Dashboard | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"><script src="https://cdn.jsdelivr.net/npm/chart.js"></script></head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-900 text-white flex flex-col"><div class="p-4 border-b border-gray-800"><div class="flex items-center"><i class="fas fa-palette text-2xl mr-2"></i><span class="font-bold text-xl">BatikSL Artisan</span></div></div><nav class="flex-1 p-4 space-y-1"><a href="?page=dashboard" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='dashboard'?'bg-gray-800':'' ?>"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a><a href="?page=products" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='products'?'bg-gray-800':'' ?>"><i class="fas fa-tshirt mr-2"></i> Products</a><a href="?page=orders" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='orders'?'bg-gray-800':'' ?>"><i class="fas fa-shopping-cart mr-2"></i> Orders</a><a href="?page=bookings" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='bookings'?'bg-gray-800':'' ?>"><i class="fas fa-calendar mr-2"></i> Bookings</a><a href="?page=media" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='media'?'bg-gray-800':'' ?>"><i class="fas fa-images mr-2"></i> Media</a><a href="?page=analytics" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='analytics'?'bg-gray-800':'' ?>"><i class="fas fa-chart-line mr-2"></i> Analytics</a><a href="?page=settings" class="block px-3 py-2 rounded hover:bg-gray-800 <?= $active=='settings'?'bg-gray-800':'' ?>"><i class="fas fa-cog mr-2"></i> Settings</a></nav></div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-6">
        <?php if($active=='dashboard'): ?>
        <h1 class="text-2xl font-bold mb-6">Dashboard Overview</h1>
        <div class="grid md:grid-cols-4 gap-4 mb-8"><div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Total Revenue (This Month)</p><p class="text-2xl font-bold">LKR 158,400</p></div><div class="bg-white p-4 rounded shadow"><p class="text-gray-500">New Orders</p><p class="text-2xl font-bold">24</p></div><div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Upcoming Bookings</p><p class="text-2xl font-bold">8</p></div><div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Total Products</p><p class="text-2xl font-bold">42</p></div></div>
        <div class="bg-white p-4 rounded shadow mb-8"><canvas id="revenueChart" height="100"></canvas></div>
        <div class="bg-white p-4 rounded shadow"><h3 class="font-bold mb-3">Recent Orders</h3><table class="w-full"><tr class="border-b"><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th></tr><tr><td>#0012</td><td>Sarah J.</td><td>LKR 9,800</td><td>Processing</td></tr></table></div>
        <script>new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:['Week1','Week2','Week3','Week4'],datasets:[{label:'Revenue (LKR)',data:[25000,42000,38000,53400],borderColor:'#0f766e'}]}});</script>
        <?php elseif($active=='products'): ?>
        <div class="flex justify-between"><h1 class="text-2xl font-bold mb-6">Products</h1><button onclick="openProductModal()" class="bg-teal-600 text-white px-4 py-2 rounded">+ Add New Product</button></div>
        <div class="bg-white rounded shadow overflow-hidden"><table class="w-full"><tr class="bg-gray-100"><th class="p-3 text-left">Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr><tr><td class="p-3"><img src="/images/placeholder.jpg" class="w-12 h-12 object-cover"></td><td>Elephant Sarong</td><td>LKR 4,500</td><td>15</td><td><input type="checkbox" checked></td><td><button class="text-teal-600 mr-2">Edit</button><button class="text-red-500">Delete</button></td></tr></table></div>
        <?php elseif($active=='settings'): ?>
        <h1 class="text-2xl font-bold mb-6">Shop Settings</h1><div class="bg-white p-6 rounded shadow max-w-lg"><div class="mb-4"><label>Shop Name</label><input type="text" class="border rounded p-2 w-full" value="BatikSL Kandy"></div><div class="mb-4"><label>WhatsApp Number</label><input type="text" class="border rounded p-2 w-full" value="+94771234567"></div><div class="mb-4"><label>Instagram Handle</label><input type="text" class="border rounded p-2 w-full" value="@batiksl"></div><div class="mb-4"><label>Email for Notifications</label><input type="email" class="border rounded p-2 w-full" value="orders@batiksl.com"></div><h3 class="font-bold mt-4">Shipping Rates</h3><table class="w-full mt-2"><tr><th>Method</th><th>Rate</th></tr><tr><td><input value="Standard"></td><td><input value="500"></td></tr><tr><td><input value="Express"></td><td><input value="1000"></td></tr></table><button class="mt-4 bg-teal-600 text-white px-4 py-2 rounded">Save Settings</button></div>
        <?php endif; ?>
    </div>
</div>
<script>function openProductModal(){ alert('Modal for adding/editing product with: title, description, price, category, stock, image upload x5, artisan notes'); }</script>
</body>
</html> 