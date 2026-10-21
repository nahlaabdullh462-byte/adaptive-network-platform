<?php
/**
 * AJAX Endpoint: Save Subnet Lab Practice Attempts
 */
require_once '../config/db.php';
require_once '../config/security.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$exercise_type = trim($data['exercise_type'] ?? 'challenge');
$target_ip = trim($data['target_ip'] ?? '');
$cidr = intval($data['cidr'] ?? 24);
$user_answer = trim($data['user_answer'] ?? '');
$correct_answer = trim($data['correct_answer'] ?? '');
$is_correct = !empty($data['is_correct']) ? 1 : 0;

if (!empty($target_ip) && !empty($user_answer)) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO lab_attempts (user_id, exercise_type, target_ip, cidr, user_answer, correct_answer, is_correct)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $exercise_type, $target_ip, $cidr, $user_answer, $correct_answer, $is_correct]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}
