<?php
require_once __DIR__ . '/../config/db.php';
$rows = $pdo->query("SELECT id, level_id, title, LENGTH(content_html) as len FROM study_modules")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "Module ID {$r['id']} (Level {$r['level_id']}): {$r['title']} - {$r['len']} bytes\n";
}
