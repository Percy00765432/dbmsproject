<?php
// Strict error reporting and output buffering
declare(strict_types=1);
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

// Session and authentication
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Database configuration
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new RuntimeException('Method not allowed', 405);
    }

    // Validate authentication
    if (!isset($_SESSION['user_id'])) {
        throw new RuntimeException('Authentication required', 401);
    }

    // Validate admin privileges
    if (!isAdmin()) {
        error_log('Access denied for user: ' . ($_SESSION['email'] ?? 'unknown'));
        throw new RuntimeException('Admin privileges required', 403);
    }

    // Validate card_id parameter
    if (!isset($_GET['card_id']) || !ctype_digit($_GET['card_id'])) {
        throw new RuntimeException('Invalid card ID', 400);
    }

    $cardId = (int)$_GET['card_id'];

    // Database query
    $stmt = $pdo->prepare("
        SELECT 
            card_id,
            card_number,
            CAST(balance AS DECIMAL(10,2)) AS balance,
            status,
            expiry_date
        FROM metro_cards 
        WHERE card_id = ?
        LIMIT 1
    ");
    $stmt->execute([$cardId]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$card) {
        throw new RuntimeException('Card not found', 404);
    }

    // Prepare response
    $response = [
        'card_id' => (int)$card['card_id'],
        'card_number' => $card['card_number'],
        'balance' => (float)$card['balance'], // Force float type
        'status' => $card['status'],
        'expiry_date' => $card['expiry_date'] ?? null,
        'success' => true
    ];

    // Clean output and send response
    ob_end_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($response, JSON_NUMERIC_CHECK);

} catch (RuntimeException $e) {
    // Handle known exceptions
    ob_end_clean();
    http_response_code($e->getCode());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'success' => false
    ]);

} catch (Throwable $e) {
    // Handle all other errors
    ob_end_clean();
    error_log('System error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal server error',
        'code' => 500,
        'success' => false
    ]);
}

exit;
?>