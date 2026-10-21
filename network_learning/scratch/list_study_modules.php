<?php
require_once __DIR__ . '/../config/db.php';
$modules = $pdo->query("SELECT id, level_id, title, estimated_minutes FROM study_modules ORDER BY level_id, id")->fetchAll(PDO::FETCH_ASSOC);
echo "Total study modules: " . count($modules) . "\n";
foreach ($modules as $m) {
    echo "  [L{$m['level_id']}] #{$m['id']}: {$m['title']} ({$m['estimated_minutes']} min)\n";
}
