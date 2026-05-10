<?php
// cart.php - Shopping Cart Page with Order Summary
require_once 'config/database.php';
// In a real app, cart would be stored in session/database
$cartItems = [
    ['id'=>1,'name'=>'Traditional Elephant Batik Sarong','price'=>4500,'quantity'=>1,'image'=>'/images/products/elephant-sarong-1.jpg','variant'=>'M'],
    ['id'=>4,'name'=>'Handmade Batik Scarf','price'=>2800,'quantity'=>2,'image'=>'/images/products/silk-scarf.jpg','variant'=>null]
];
$subtotal = array_sum(array_map(function($i){ return $i['price']*$i['quantity']; }, $cartItems));
$shipping = $subtotal > 5000 ? 0 : 500;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html>
<head><title>Shopping Cart | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></head>
<body class="bg-gray-50">
<?php include 'includes/nav.php'; ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
    <?php if(empty($cartItems)): ?>
    <div class="text-center py-16 bg-white rounded-xl"><i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i><p class="text-xl text-gray-500">Your cart is empty</p><a href="/catalog.php" class="inline-block mt-4 bg-teal-600 text-white px-6 py-2 rounded-lg">Shop Now</a></div>
    <?php else: ?>
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3">
            <?php foreach($cartItems as $item): ?>
            <div class="bg-white rounded-xl shadow-sm p-4 flex gap-4 mb-4">
                <img src="<?= $item['image'] ?>" class="w-24 h-24 object-cover rounded">
                <div class="flex-1"><h3 class="font-semibold"><?= $item['name'] ?></h3><?php if($item['variant']): ?><p class="text-sm text-gray-500">Size: <?= $item['variant'] ?></p><?php endif; ?><div class="flex items-center gap-3 mt-2"><button class="border px-2 py-1 rounded">-</button><span><?= $item['quantity'] ?></span><button class="border px-2 py-1 rounded">+</button><button class="text-red-500 ml-4"><i class="far fa-trash-alt"></i> Remove</button></div></div>
                <div class="text-right"><p class="font-bold">LKR <?= number_format($item['price']*$item['quantity']) ?></p><p class="text-sm text-gray-500">LKR <?= number_format($item['price']) ?> each</p></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="lg:w-1/3">
            <div class="bg-white rounded-xl shadow-md p-6 sticky top-24"><h2 class="text-xl font-bold mb-4">Order Summary</h2><div class="space-y-2"><div class="flex justify-between"><span>Subtotal</span><span>LKR <?= number_format($subtotal) ?></span></div><div class="flex justify-between"><span>Shipping</span><span><?= $shipping==0 ? 'Free' : 'LKR '.number_format($shipping) ?></span></div><div class="border-t pt-2 mt-2"><div class="flex justify-between font-bold text-lg"><span>Total</span><span>LKR <?= number_format($total) ?></span></div></div><a href="/checkout.php" class="block w-full bg-teal-600 text-white text-center py-3 rounded-lg mt-4 font-semibold">Proceed to Checkout</a></div></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>