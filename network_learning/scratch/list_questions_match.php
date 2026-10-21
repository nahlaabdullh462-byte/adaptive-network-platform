<?php
require_once __DIR__ . '/../config/db.php';

$questions = $pdo->query("SELECT id, level_id, question_text, domain_name FROM questions ORDER BY level_id ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

echo "Total questions: " . count($questions) . "\n";
foreach ($questions as $q) {
    echo "ID: {$q['id']} | Lvl: {$q['level_id']} | Domain: {$q['domain_name']} | Text: " . mb_substr($q['question_text'], 0, 60, 'UTF-8') . "...\n";
}
