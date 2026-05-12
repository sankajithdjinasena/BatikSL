<?php
// artisan/ajax/update-order-status.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

$artisanId = (int)$_SESSION['user_id'];
$body    = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($body['order_id'] ?? 0);
$status  = trim($body['status'] ?? '');
$allowed = ['pending','processing','shipped','delivered','cancelled'];

if (!$orderId || !in_array($status, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Invalid parameters']); exit;
}

// Verify artisan owns at least one item in this order
$check = $pdo->prepare("
    SELECT o.id FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN products p ON p.id=oi.product_id
    WHERE o.id=:oid AND p.artisan_id=:aid LIMIT 1
");
$check->execute(['oid'=>$orderId,'aid'=>$artisanId]);
if (!$check->fetch()) {
    echo json_encode(['success'=>false,'message'=>'Order not found']); exit;
}

$pdo->prepare("UPDATE orders SET status=:s WHERE id=:id")->execute(['s'=>$status,'id'=>$orderId]);

echo json_encode(['success'=>true,'status'=>$status]);