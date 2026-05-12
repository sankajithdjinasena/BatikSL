<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.php');
    exit;
}

// Sanitize inputs
$user_id          = (int) $_SESSION['user_id'];
$session_date     = $_POST['session_date']     ?? '';
$session_time     = $_POST['session_time']     ?? '';
$group_size       = (int) ($_POST['group_size'] ?? 1);
$special_requests = trim($_POST['special_requests'] ?? '');

// Validate
$errors = [];
if (empty($session_date))           $errors[] = 'Please select a date.';
if (empty($session_time))           $errors[] = 'Please select a time slot.';
if ($group_size < 1 || $group_size > 8) $errors[] = 'Group size must be between 1 and 8.';

if (!empty($errors)) {
    // You could store errors in session and redirect back
    session_start();
    $_SESSION['booking_errors'] = $errors;
    header('Location: booking.php');
    exit;
}

// Calculate deposit
$price_per_person = 9000;
$total            = $group_size * $price_per_person;
$deposit_amount   = round($total * 0.30, 2);

// Insert into DB
$stmt = $pdo->prepare("
    INSERT INTO bookings 
        (user_id, session_date, session_time, group_size, special_requests, deposit_amount, status)
    VALUES 
        (?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->execute([
    $user_id,
    $session_date,
    $session_time,
    $group_size,
    $special_requests,
    $deposit_amount
]);

$booking_id = $pdo->lastInsertId();

// Redirect to confirmation
header("Location: booking_confirm.php?id=$booking_id");
exit;