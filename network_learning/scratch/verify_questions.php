<?php
require_once __DIR__ . '/../config/db.php';

// Verify student questions for level 1
$stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE level_id = 1");
$stmt->execute();
$c1 = $stmt->fetchColumn();

// Verify placement test question distribution
$stmt2 = $pdo->query("SELECT level_id, COUNT(*) FROM questions GROUP BY level_id");
$dist = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR);

echo "Level 1 question count: {$c1}\n";
echo "Full distribution: " . json_encode($dist) . "\n";
