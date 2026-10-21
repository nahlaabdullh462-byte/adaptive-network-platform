<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query("
    SELECT q.id, q.level_id, q.topic_id, t.topic_name, q.question_text, q.correct_option, q.explanation
    FROM questions q
    JOIN topics t ON q.topic_id = t.id
    WHERE q.level_id IN (1, 2)
    ORDER BY q.level_id, q.topic_id, q.id
");
$rows = $stmt->fetchAll();

foreach ($rows as $r) {
    echo "ID: {$r['id']} | Lvl: {$r['level_id']} | Topic: {$r['topic_name']} ({$r['topic_id']})\n";
    echo "  Q: " . mb_substr($r['question_text'], 0, 70) . "...\n";
    echo "  Correct: {$r['correct_option']} | Expl: " . mb_substr($r['explanation'], 0, 60) . "...\n\n";
}
