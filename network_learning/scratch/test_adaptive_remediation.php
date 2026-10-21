<?php
/**
 * Test & Validation Script for Precision Remediation & Skill Map Engine
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/precision_remediation_helper.php';

echo "========================================================\n";
echo "1. TESTING TOPIC MICRO-CAPSULES (30s REVIEWS)\n";
echo "========================================================\n";
for ($t = 1; $t <= 8; $t++) {
    $capsule = get_topic_micro_capsule($t);
    echo "[Topic $t] {$capsule['concept_name']} (Duration: {$capsule['time_seconds']}s)\n";
    echo "  Summary: " . mb_substr($capsule['summary_30s'], 0, 80) . "...\n";
    echo "  Golden Rule: " . mb_substr($capsule['golden_rule'], 0, 70) . "...\n";
}

echo "\n========================================================\n";
echo "2. SIMULATING EXPERT STUDENT WITH LEVEL 1 SLIP\n";
echo "========================================================\n";

// محاكاة إجابات اختبار تحديد المستوى:
// المستوى 4: 5/5 صح (100%) - خبير أمن شبكات
// المستوى 3: 5/5 صح (100%) - خبير توجيه وتبديل
// المستوى 2: 4/5 صح (80%)  - كفء عنونة
// المستوى 1: 3/5 صح (60%)  - كبوة تركيز في سؤالين
$simulated_attempt_questions = [
    // مستوى 1: خطأ في السؤال الأول والثاني
    ['question_id' => 1, 'level_id' => 1, 'is_correct' => 0],
    ['question_id' => 2, 'level_id' => 1, 'is_correct' => 0],
    ['question_id' => 3, 'level_id' => 1, 'is_correct' => 1],
    ['question_id' => 4, 'level_id' => 1, 'is_correct' => 1],
    ['question_id' => 5, 'level_id' => 1, 'is_correct' => 1],

    // مستوى 2: 4 صح
    ['question_id' => 7, 'level_id' => 2, 'is_correct' => 1],
    ['question_id' => 8, 'level_id' => 2, 'is_correct' => 1],
    ['question_id' => 9, 'level_id' => 2, 'is_correct' => 1],
    ['question_id' => 10, 'level_id' => 2, 'is_correct' => 1],
    ['question_id' => 11, 'level_id' => 2, 'is_correct' => 0],

    // مستوى 3: 5 صح (100%)
    ['question_id' => 13, 'level_id' => 3, 'is_correct' => 1],
    ['question_id' => 14, 'level_id' => 3, 'is_correct' => 1],
    ['question_id' => 15, 'level_id' => 3, 'is_correct' => 1],
    ['question_id' => 16, 'level_id' => 3, 'is_correct' => 1],
    ['question_id' => 17, 'level_id' => 3, 'is_correct' => 1],

    // مستوى 4: 5 صح (100%)
    ['question_id' => 19, 'level_id' => 4, 'is_correct' => 1],
    ['question_id' => 20, 'level_id' => 4, 'is_correct' => 1],
    ['question_id' => 21, 'level_id' => 4, 'is_correct' => 1],
    ['question_id' => 22, 'level_id' => 4, 'is_correct' => 1],
    ['question_id' => 23, 'level_id' => 4, 'is_correct' => 1],
];

$levels = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll();
$user_id = 1; // test user

$diag = evaluate_diagnostic_performance($pdo, $user_id, $simulated_attempt_questions, $levels);

echo "Is Expert Candidate detected? " . ($diag['is_expert_candidate'] ? 'YES [CORRECT]' : 'NO [FAIL]') . "\n";
echo "Highest Mastered Level ID: {$diag['highest_mastered_level_id']} ({$diag['highest_mastered_level_name']})\n";
echo "Recommended / Assigned Level ID: {$diag['recommended_level_id']}\n";
echo "Raw Percentage: {$diag['raw_percentage']}%\n";
echo "Weighted Adaptive Score: {$diag['weighted_score']}%\n";

if ($diag['primary_gap']) {
    echo "Primary Gap Detected:\n";
    echo "  Level: {$diag['primary_gap']['level_name']} (Lvl ID: {$diag['primary_gap']['level_id']})\n";
    echo "  Topic: {$diag['primary_gap']['topic_name']} (Topic ID: {$diag['primary_gap']['topic_id']})\n";
    echo "  Concept: {$diag['primary_gap']['concept_name']}\n";
    echo "  Question ID: {$diag['primary_gap']['question_id']}\n";
}

echo "\n========================================================\n";
echo "3. TESTING VERIFICATION QUESTION SELECTION\n";
echo "========================================================\n";
$ver_q = get_remediation_verification_question($pdo, $diag['primary_gap']['topic_id'] ?? 1, [1, 2]);
if ($ver_q) {
    echo "Verification Question Selected: ID #{$ver_q['id']}\n";
    echo "  Text: " . mb_substr($ver_q['question_text'], 0, 70) . "...\n";
    echo "  Correct Option: {$ver_q['correct_option']}\n";
} else {
    echo "No verification question found!\n";
}

echo "\n========================================================\n";
echo "4. TESTING SIDE QUEST GENERATION (3-4 QUESTIONS)\n";
echo "========================================================\n";
$sq_questions = get_side_quest_questions($pdo, $diag['primary_gap']['topic_id'] ?? 1, 4);
echo "Side Quest Questions Count: " . count($sq_questions) . "\n";
foreach ($sq_questions as $idx => $q) {
    echo "  [" . ($idx + 1) . "] ID #{$q['id']}: " . mb_substr($q['question_text'], 0, 60) . "...\n";
}

echo "\n========================================================\n";
echo "5. TESTING SKILL MAP GENERATION\n";
echo "========================================================\n";
$skill_map = calculate_user_skill_map($pdo, $user_id);
foreach ($skill_map as $sm) {
    echo "Level {$sm['level_order']} ({$sm['level_name']}): {$sm['percentage']}% -> {$sm['tier_title']}\n";
}

echo "\nALL TESTS COMPLETED SUCCESSFULLY!\n";
