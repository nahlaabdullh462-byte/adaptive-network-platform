<?php
require_once __DIR__ . '/../config/db.php';

try {
    // Check if column already exists
    $cols = $pdo->query("SHOW COLUMNS FROM questions LIKE 'image_url'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE questions ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER domain_name");
        echo "Column 'image_url' added successfully to 'questions' table!\n";
    } else {
        echo "Column 'image_url' already exists in 'questions' table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
