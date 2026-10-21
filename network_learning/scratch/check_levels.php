<?php
require "config/db.php";
$levels = $pdo->query("SELECT id, level_name, level_order FROM levels ORDER BY level_order")->fetchAll();
foreach ($levels as $l) { echo $l["id"]." | ".$l["level_name"]." | order:".$l["level_order"]."\n"; }
echo "---\n";
$q = $pdo->query("SELECT level_id, COUNT(*) as cnt FROM questions GROUP BY level_id")->fetchAll();
foreach ($q as $r) { echo "Level ".$r["level_id"]." => ".$r["cnt"]." questions\n"; }
