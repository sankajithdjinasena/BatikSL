<?php
// artisan/ajax/update-stock.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

$artisanId = (int)$_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true);
$productId = (int)($body['product_id'] ?? 0);
$stock     = (int)($body['stock'] ?? -1);

if (!$productId || $stock < 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid parameters']); exit;
}

// Verify ownership
$check = $pdo->prepare("SELECT id FROM products WHERE id=:pid AND artisan_id=:aid");
$check->execute(['pid'=>$productId,'aid'=>$artisanId]);
if (!$check->fetch()) {
    echo json_encode(['success'=>false,'message'=>'Product not found']); exit;
}

$stmt = $pdo->prepare("UPDATE products SET stock=:s WHERE id=:id");
$stmt->execute(['s'=>$stock,'id'=>$productId]);

echo json_encode(['success'=>true,'stock'=>$stock]);