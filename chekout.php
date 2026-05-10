<?php
// checkout.php - 3-Step Checkout Wizard
require_once 'config/database.php';
$step = $_GET['step'] ?? 1;
?>
<!DOCTYPE html>
<html>
<head><title>Checkout | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="flex justify-center mb-8"><div class="flex items-center"><div class="w-8 h-8 rounded-full <?= $step>=1 ? 'bg-teal-600 text-white' : 'bg-gray-300' ?> flex items-center justify-center">1</div><div class="w-16 h-1 <?= $step>=2 ? 'bg-teal-600' : 'bg-gray-300' ?>"></div><div class="w-8 h-8 rounded-full <?= $step>=2 ? 'bg-teal-600 text-white' : 'bg-gray-300' ?> flex items-center justify-center">2</div><div class="w-16 h-1 <?= $step>=3 ? 'bg-teal-600' : 'bg-gray-300' ?>"></div><div class="w-8 h-8 rounded-full <?= $step>=3 ? 'bg-teal-600 text-white' : 'bg-gray-300' ?> flex items-center justify-center">3</div></div></div>

    <?php if($step==1): ?>
    <form method="POST" action="?step=2" class="bg-white rounded-xl shadow-md p-6"><h2 class="text-2xl font-bold mb-4">Contact & Shipping</h2><div class="grid md:grid-cols-2 gap-4"><input type="text" name="name" placeholder="Full Name" class="border rounded p-2" required><input type="email" name="email" placeholder="Email" class="border rounded p-2" required><input type="tel" name="phone" placeholder="Phone" class="border rounded p-2" required><input type="text" name="address_line1" placeholder="Address Line 1" class="border rounded p-2" required><input type="text" name="address_line2" placeholder="Address Line 2" class="border rounded p-2"><input type="text" name="city" placeholder="City" class="border rounded p-2" required><select name="country" class="border rounded p-2"><option>Sri Lanka</option><option>India</option><option>USA</option></select><input type="text" name="postal_code" placeholder="Postal Code" class="border rounded p-2"></div><div class="mt-4"><label class="block font-semibold">Shipping Method</label><select name="shipping_method" class="border rounded p-2 w-full"><option value="standard">Standard (3-5 days) - LKR 500</option><option value="express">Express (1-2 days) - LKR 1000</option></select></div><button type="submit" class="mt-6 bg-teal-600 text-white px-6 py-2 rounded-lg w-full">Continue to Payment</button></form>
    <?php elseif($step==2): ?>
    <div class="bg-white rounded-xl shadow-md p-6"><h2 class="text-2xl font-bold mb-4">Payment</h2><div class="grid md:grid-cols-2 gap-8"><div><div class="border rounded p-4"><div class="mb-3"><input type="text" placeholder="Card Number" class="w-full border rounded p-2"></div><div class="flex gap-2"><input type="text" placeholder="MM/YY" class="w-1/2 border rounded p-2"><input type="text" placeholder="CVV" class="w-1/2 border rounded p-2"></div></div><div class="mt-4"><label><input type="checkbox"> Pay with PayHere (Local payments)</label></div><a href="?step=3" class="block mt-4 bg-teal-600 text-white text-center py-3 rounded-lg">Place Order</a></div><div class="bg-gray-50 p-4 rounded"><h3 class="font-bold">Order Summary</h3><div class="flex justify-between mt-2"><span>2 items</span><span>LKR 9,800</span></div><div class="flex justify-between"><span>Shipping</span><span>LKR 500</span></div><div class="border-t mt-2 pt-2 font-bold flex justify-between"><span>Total</span><span>LKR 10,300</span></div></div></div></div>
    <?php elseif($step==3): ?>
    <div class="bg-white rounded-xl shadow-md p-6 text-center"><i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i><h2 class="text-2xl font-bold mb-2">Order Confirmed!</h2><p class="text-gray-600 mb-4">Order #BATIK-2025-0012</p><div class="bg-gray-50 p-4 rounded text-left"><p><strong>Estimated Delivery:</strong> March 25-28, 2025</p><p><strong>Items:</strong> 2 products</p></div><div class="flex flex-wrap gap-4 justify-center mt-6"><a href="/account.php?tab=orders" class="bg-teal-600 text-white px-6 py-2 rounded-lg">Track Your Order</a><a href="/" class="border border-teal-600 text-teal-600 px-6 py-2 rounded-lg">Continue Shopping</a></div><div class="mt-6 flex justify-center gap-4"><i class="fab fa-facebook text-2xl text-gray-500"></i><i class="fab fa-twitter text-2xl text-gray-500"></i><i class="fab fa-whatsapp text-2xl text-gray-500"></i></div></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>