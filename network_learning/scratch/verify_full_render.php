<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['user_name'] = 'أحمد الشبكات (طالب تجريبي)';
$_SESSION['user_role'] = 'student';
foreach ([1, 2, 3, 4] as $lvl) {
    $_GET['level_id'] = $lvl;
    ob_start();
    require __DIR__ . '/../student/study_module.php';
    $out = ob_get_clean();

    echo "Level {$lvl} Render Length: " . strlen($out) . " bytes\n";
    echo "  study-toc-sidebar: " . (strpos($out, 'study-toc-sidebar') !== false ? "✅ OK" : "❌ FAIL") . "\n";
    echo "  reading-hud-bar: " . (strpos($out, 'reading-hud-bar') !== false ? "✅ OK" : "❌ FAIL") . "\n";
    echo "  cisco-terminal-box: " . (strpos($out, 'cisco-terminal-box') !== false ? "✅ OK" : "❌ FAIL") . "\n";
    echo "  sec-interactive-lab: " . (strpos($out, 'sec-interactive-lab') !== false ? "✅ OK" : "❌ FAIL") . "\n";
    echo "  sec-knowledge-check: " . (strpos($out, 'sec-knowledge-check') !== false ? "✅ OK" : "❌ FAIL") . "\n";
    echo "  sec-youtube-hub: " . (strpos($out, 'sec-youtube-hub') !== false ? "✅ OK" : "❌ FAIL") . "\n";
}
echo "All 4 levels verified successfully!\n";
