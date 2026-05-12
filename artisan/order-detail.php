<?php
// artisan/ajax/order-detail.php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'artisan') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

$artisanId = (int)$_SESSION['user_id'];
$orderId   = (int)($_GET['id'] ?? 0);

if (!$orderId) { echo json_encode(['success'=>false,'message'=>'Missing order ID']); exit; }

// Verify ownership
$stmt = $pdo->prepare("
    SELECT o.* FROM orders o
    JOIN order_items oi ON oi.order_id=o.id
    JOIN products p ON p.id=oi.product_id
    WHERE o.id=:oid AND p.artisan_id=:aid LIMIT 1
");
$stmt->execute(['oid'=>$orderId,'aid'=>$artisanId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }

// Items (only this artisan's products)
$iStmt = $pdo->prepare("
    SELECT oi.quantity, oi.unit_price, p.name AS product_name, p.sku,
           (SELECT pi.image_path FROM product_images pi WHERE pi.product_id=p.id ORDER BY pi.sort_order LIMIT 1) AS thumb
    FROM order_items oi
    JOIN products p ON p.id=oi.product_id
    WHERE oi.order_id=:oid AND p.artisan_id=:aid
");
$iStmt->execute(['oid'=>$orderId,'aid'=>$artisanId]);
$items = $iStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success'=>true,'order'=>$order,'items'=>$items]);