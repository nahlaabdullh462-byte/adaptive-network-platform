<?php
require_once __DIR__ . '/../config/db.php';

echo "=== LEVELS ===\n";
$levels = $pdo->query("SELECT id, level_name, level_order FROM levels ORDER BY level_order")->fetchAll();
print_r($levels);

echo "\n=== TOPICS ===\n";
$topics = $pdo->query("SELECT id, level_id, topic_name FROM topics ORDER BY level_id, order_num")->fetchAll();
print_r($topics);

echo "\n=== QUESTIONS PER LEVEL & TOPIC ===\n";
$q_counts = $pdo->query("
    SELECT q.level_id, l.level_name, q.topic_id, t.topic_name, COUNT(*) as cnt 
    FROM questions q 
    LEFT JOIN levels l ON q.level_id = l.id 
    LEFT JOIN topics t ON q.topic_id = t.id 
    GROUP BY q.level_id, q.topic_id 
    ORDER BY q.level_id, q.topic_id
")->fetchAll();
print_r($q_counts);
