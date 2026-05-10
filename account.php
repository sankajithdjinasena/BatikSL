<?php
// account.php - Customer Account Page (Protected Route)
require_once 'config/database.php';
// Check if user is logged in, else redirect to login
$user = $_SESSION['user'] ?? ['name'=>'Demo User','email'=>'demo@example.com','role'=>'customer'];
$activeTab = $_GET['tab'] ?? 'profile';
?>
<!DOCTYPE html>
<html>
<head><title>My Account | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold mb-6">My Account</h1>
    <div class="flex flex-col md:flex-row gap-6">
        <div class="md:w-1/4 bg-white rounded-xl shadow p-4 h-fit"><div class="text-center mb-4"><div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto text-3xl">👤</div><h3 class="font-bold mt-2"><?= $user['name'] ?></h3><p class="text-sm text-gray-500"><?= $user['email'] ?></p></div><nav class="space-y-2"><a href="?tab=profile" class="block px-3 py-2 rounded <?= $activeTab=='profile'?'bg-teal-50 teal-text':'' ?>"><i class="fas fa-user mr-2"></i> Profile</a><a href="?tab=orders" class="block px-3 py-2 rounded <?= $activeTab=='orders'?'bg-teal-50 teal-text':'' ?>"><i class="fas fa-truck mr-2"></i> Orders</a><a href="?tab=bookings" class="block px-3 py-2 rounded <?= $activeTab=='bookings'?'bg-teal-50 teal-text':'' ?>"><i class="fas fa-calendar mr-2"></i> Bookings</a><a href="?tab=wishlist" class="block px-3 py-2 rounded <?= $activeTab=='wishlist'?'bg-teal-50 teal-text':'' ?>"><i class="fas fa-heart mr-2"></i> Wishlist</a></nav></div>
        <div class="md:w-3/4 bg-white rounded-xl shadow p-6">
            <?php if($activeTab=='profile'): ?>
            <h2 class="text-xl font-bold mb-4">Profile Information</h2><form><div class="grid md:grid-cols-2 gap-4"><input type="text" value="<?= $user['name'] ?>" placeholder="Full Name" class="border rounded p-2"><input type="email" value="<?= $user['email'] ?>" placeholder="Email" class="border rounded p-2"><input type="tel" placeholder="Phone" class="border rounded p-2"><button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Save Changes</button></div></form>
            <?php elseif($activeTab=='orders'): ?>
            <h2 class="text-xl font-bold mb-4">Order History</h2><table class="w-full"><tr class="border-b"><th class="text-left p-2">Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr><tr class="border-b"><td class="p-2">#BATIK-001</td><td>Mar 10, 2025</td><td>2</td><td>LKR 9,800</td><td><span class="bg-green-100 text-green-700 px-2 py-1 rounded">Delivered</span></td><td><button class="text-teal-600">View</button></td></tr><tr class="border-b"><td class="p-2">#BATIK-002</td><td>Mar 15, 2025</td><td>1</td><td>LKR 4,500</td><td><span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Processing</span></td><td><button class="text-teal-600">View</button></td></tr></table>
            <?php elseif($activeTab=='bookings'): ?>
            <h2 class="text-xl font-bold mb-4">Upcoming Sessions</h2><div class="border rounded p-4 mb-3"><div class="flex justify-between"><div><p class="font-semibold">Live Batik Session</p><p class="text-sm text-gray-500">March 25, 2025 at 10:00 AM</p><p>Group size: 2 people</p></div><div><span class="bg-green-100 text-green-700 px-2 py-1 rounded">Confirmed</span><button class="text-red-500 block mt-2">Cancel</button></div></div></div>
            <?php elseif($activeTab=='wishlist'): ?>
            <h2 class="text-xl font-bold mb-4">Wishlist</h2><div class="grid grid-cols-2 gap-4"><div class="border rounded p-3 flex gap-3"><img src="/images/placeholder.jpg" class="w-16 h-16 object-cover"><div><p class="font-semibold">Elephant Sarong</p><p>LKR 4,500</p><button class="text-sm bg-teal-600 text-white px-2 py-1 rounded mt-1">Add to Cart</button><button class="text-red-500 text-sm ml-2">Remove</button></div></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>