<?php
require_once __DIR__ . '/../config/db.php';

$rows = $pdo->query("SELECT * FROM questions ORDER BY level_id ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

$sql = "\n-- ==============================================================================\n";
$sql .= "-- بنك الأسئلة الأكاديمي الشامل (68 سؤالاً معتمداً لمشروع التخرج)\n";
$sql .= "-- ==============================================================================\n";

$current_level = 0;
foreach ($rows as $r) {
    if ($r['level_id'] != $current_level) {
        $current_level = $r['level_id'];
        $sql .= "\n-- أسئلة المستوى {$current_level}:\n";
    }
    
    $q_text = addslashes($r['question_text']);
    $opt_a = addslashes($r['option_a']);
    $opt_b = addslashes($r['option_b']);
    $opt_c = addslashes($r['option_c']);
    $opt_d = addslashes($r['option_d']);
    $corr = $r['correct_option'];
    $exp = addslashes($r['explanation']);
    $diff = $r['difficulty'];
    $dom = addslashes($r['domain_name'] ?? '');
    $top = $r['topic_id'] ? $r['topic_id'] : 'NULL';
    
    $sql .= "INSERT INTO `questions` (`level_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `difficulty`, `domain_name`) VALUES ";
    $sql .= "({$r['level_id']}, {$top}, '{$q_text}', '{$opt_a}', '{$opt_b}', '{$opt_c}', '{$opt_d}', '{$corr}', '{$exp}', '{$diff}', '{$dom}');\n";
}

file_put_contents(__DIR__ . '/questions_export.sql', $sql);
echo "Exported " . count($rows) . " questions to questions_export.sql\n";
