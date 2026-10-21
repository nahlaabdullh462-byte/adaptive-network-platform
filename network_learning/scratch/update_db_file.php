<?php
$dbSql = file_get_contents(__DIR__ . '/../database.sql');
$questionsSql = file_get_contents(__DIR__ . '/questions_export.sql');

$startMarker = "-- ==============================================================================\n-- أسئلة المستوى 1: أساسيات OSI والشبكات\n-- ==============================================================================";
$endMarker = "-- ------------------------------------------------------------------------------\n-- 6. جدول محاولات الاختبار الموثقة (exam_attempts)\n-- ------------------------------------------------------------------------------";

$startPos = strpos($dbSql, $startMarker);
$endPos = strpos($dbSql, $endMarker);

if ($startPos !== false && $endPos !== false) {
    $newDbSql = substr($dbSql, 0, $startPos) . trim($questionsSql) . "\n\n" . substr($dbSql, $endPos);
    file_put_contents(__DIR__ . '/../database.sql', $newDbSql);
    echo "database.sql successfully updated with 68 questions!\n";
} else {
    echo "Marker not found!\n";
}
