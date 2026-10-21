<?php
require_once __DIR__ . '/../config/db.php';

$tables = ['users', 'questions', 'topics', 'levels', 'exam_attempts', 'attempt_questions', 'lab_attempts', 'learning_progress'];

foreach ($tables as $t) {
    try {
        $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        echo "=== Table: $t ===\n";
        foreach ($cols as $c) {
            echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Table $t error: " . $e->getMessage() . "\n";
    }
}
