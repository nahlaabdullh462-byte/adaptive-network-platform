<?php
/**
 * Adaptive Academic Course & Interactive Learning Suite
 * NetLearn Pro (Advanced CCNA Curriculum - Enterprise LMS Interface)
 */
$pageTitle = "المحتوى الأكاديمي والدروس المدمجة";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/recommendations_helper.php';
require_auth('student');

$user_id = $_SESSION['user_id'];
$level_id = isset($_GET['level_id']) ? intval($_GET['level_id']) : 1;

// التحقق من المستوى
$stmt = $pdo->prepare("SELECT * FROM levels WHERE id = ?");
$stmt->execute([$level_id]);
$level = $stmt->fetch();
if (!$level) { header("Location: dashboard.php"); exit(); }

// جلب بيانات الطالب للتحقق من المستويات المعتمدة
$user_stmt = $pdo->prepare("SELECT current_level_id FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_current_level = (int)$user_stmt->fetchColumn();

// جلب نتائج الاختبارات المعتمدة لكل مستوى
$passed_levels_stmt = $pdo->prepare("SELECT level_id, score FROM exam_results WHERE user_id = ? AND status = 'passed'");
$passed_levels_stmt->execute([$user_id]);
$passed_levels_map = $passed_levels_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// جلب الوحدات التعليمية لهذا المستوى
$mod_stmt = $pdo->prepare("SELECT * FROM study_modules WHERE level_id = ? ORDER BY id ASC");
$mod_stmt->execute([$level_id]);
$modules = $mod_stmt->fetchAll();

// تسجيل التقدم الأكاديمي تلقائياً
if (!empty($modules)) {
    $prog_stmt = $pdo->prepare("INSERT IGNORE INTO learning_progress (user_id, module_id, is_completed) VALUES (?, ?, 1)");
    foreach ($modules as $m) { $prog_stmt->execute([$user_id, $m['id']]); }
}

// جلب كافة المستويات
$all_levels = $pdo->query("SELECT id, level_name, level_order FROM levels ORDER BY level_order ASC")->fetchAll();

// حساب تقدم القراءة الإجمالي
$total_modules_all = (int)$pdo->query("SELECT COUNT(*) FROM study_modules")->fetchColumn();
$comp_stmt = $pdo->prepare("SELECT COUNT(*) FROM learning_progress WHERE user_id = ? AND is_completed = 1");
$comp_stmt->execute([$user_id]);
$completed_modules = (int)$comp_stmt->fetchColumn();
$read_progress = $total_modules_all > 0 ? min(100, round(($completed_modules / $total_modules_all) * 100)) : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ── حاوية مساحة التعلم التكيفية ── */
.study-container {
    max-width: 1240px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* ── ترويسة الدرس الفاخرة عالية التباين (Nebula Luxe Header) ── */
.study-header {
    background: linear-gradient(135deg, #1A1A3E 0%, #2A1758 45%, #0B192C 100%);
    border: 1.5px solid rgba(139, 92, 246, 0.35);
    border-radius: var(--radius-xl);
    padding: 34px 38px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 50px -10px rgba(26, 26, 62, 0.35), 0 0 35px rgba(91, 79, 232, 0.15);
}
.study-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 12% 25%, rgba(91, 79, 232, 0.3) 0%, transparent 45%),
                radial-gradient(circle at 88% 85%, rgba(6, 182, 212, 0.22) 0%, transparent 45%);
    pointer-events: none;
}
.study-header * {
    position: relative;
    z-index: 1;
}

/* فرض التباين الكامل باللون الأبيض على جميع نصوص الترويسة */
.study-header h1,
.study-header h2,
.study-header h3,
.study-header p,
.study-header span,
.study-header strong {
    color: #FFFFFF !important;
}

.study-header h1 {
    font-size: 1.95rem;
    font-weight: 900;
    line-height: 1.35;
    margin: 10px 0 12px;
    letter-spacing: -0.01em;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
}

.study-header p {
    font-size: 0.98rem;
    line-height: 1.7;
    opacity: 0.9;
    max-width: 620px;
}

/* ── شريط التنقل بين المستويات الأربعة الأنيق (Segmented Control) ── */
.level-tabs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
@media (max-width: 860px) {
    .level-tabs-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .level-tabs-grid { grid-template-columns: 1fr; }
}

.level-tab-card {
    background: var(--bg-surface-card);
    border: 1.5px solid rgba(208, 213, 229, 0.6);
    border-radius: var(--radius-lg);
    padding: 14px 16px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-shadow: var(--shadow-subtle);
}
.level-tab-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-card);
    border-color: rgba(91, 79, 232, 0.35);
}
.level-tab-card.active {
    background: #FFFFFF;
    border-color: var(--brand-primary);
    box-shadow: 0 8px 24px rgba(91, 79, 232, 0.18);
}
.level-tab-card.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 20px;
    right: 20px;
    height: 3px;
    background: var(--brand-gradient);
    border-radius: var(--radius-full);
}

.level-tab-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.level-tab-badge {
    font-size: 0.74rem;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: var(--radius-full);
    font-family: var(--font-mono);
}
.level-tab-title {
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--text-heading);
    line-height: 1.35;
}

/* ── البطاقة التفاعلية الرئيسية للوحدة ── */
.module-card {
    background: var(--bg-surface-card);
    border: 1px solid rgba(208, 213, 229, 0.6);
    border-radius: var(--radius-xl);
    margin-bottom: 26px;
    overflow: hidden;
    box-shadow: var(--shadow-card);
    transition: all 0.3s ease;
}

.module-header {
    padding: 22px 28px;
    background: rgba(255, 255, 255, 0.7);
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}

.module-summary-box {
    padding: 16px 28px;
    background: rgba(91, 79, 232, 0.04);
    border-right: 4px solid var(--brand-primary);
    border-bottom: 1px solid rgba(208, 213, 229, 0.5);
    font-size: 0.95rem;
    color: var(--text-heading);
    line-height: 1.7;
}

.module-body-content {
    padding: 32px 30px;
}

/* تنسيق المقالات والأقسام الأكاديمية */
.study-guide-content {
    line-height: 1.85;
    font-size: 1.02rem;
    color: var(--text-body);
    transition: font-size 0.2s ease;
}
.study-guide-content h3 {
    color: var(--text-heading);
    font-size: 1.3rem;
    font-weight: 800;
    margin: 32px 0 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-right: 4px solid var(--brand-primary);
    padding-right: 12px;
}
.study-guide-content h4 {
    color: var(--text-heading);
    font-size: 1.12rem;
    font-weight: 700;
    margin: 20px 0 10px;
}
.study-guide-content p {
    margin-bottom: 16px;
}
.study-guide-content ul, .study-guide-content ol {
    padding-right: 22px;
    margin-bottom: 18px;
}
.study-guide-content li {
    margin-bottom: 10px;
}

.callout-box {
    background: rgba(240, 243, 250, 0.9);
    border: 1px solid rgba(208, 213, 229, 0.8);
    border-right: 4px solid #06B6D4;
    border-radius: var(--radius-md);
    padding: 20px 22px;
    margin: 22px 0;
}
.callout-box h4 {
    margin-top: 0;
    color: #0369A1;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── المختبر التفاعلي المدمج (In-Lesson Interactive Simulators) ── */
.interactive-lab-container {
    background: #FFFFFF;
    border: 2px solid rgba(91, 79, 232, 0.2);
    border-radius: var(--radius-xl);
    padding: 28px;
    margin: 32px 0;
    box-shadow: 0 12px 35px -8px rgba(91, 79, 232, 0.1);
}
.interactive-lab-title {
    font-size: 1.22rem;
    font-weight: 800;
    color: var(--text-heading);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1.5px solid var(--border-subtle);
}

/* أداة OSI التفاعلية (المستوى 1) */
.osi-layer-row {
    background: var(--bg-surface-solid);
    border: 1.5px solid var(--border-main);
    border-radius: var(--radius-md);
    padding: 12px 18px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.osi-layer-row:hover, .osi-layer-row.active {
    background: var(--brand-primary-light);
    border-color: var(--brand-primary);
    transform: translateX(-4px);
    box-shadow: 0 4px 14px rgba(91, 79, 232, 0.12);
}
.osi-layer-num {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--brand-gradient);
    color: #FFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-family: var(--font-mono);
}
.osi-layer-details {
    display: none;
    background: #FFFFFF;
    border: 1px solid var(--brand-primary-border);
    border-radius: var(--radius-md);
    padding: 16px 20px;
    margin-top: -6px;
    margin-bottom: 12px;
    font-size: 0.92rem;
    animation: slideDown 0.3s ease forwards;
}

/* أداة Subnet Calculator التفاعلية (المستوى 2) */
.subnet-calc-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    background: rgba(244, 246, 251, 0.8);
    border: 1px solid var(--border-main);
    border-radius: var(--radius-lg);
    padding: 22px;
    margin-top: 14px;
}
@media (max-width: 700px) {
    .subnet-calc-box { grid-template-columns: 1fr; }
}

.subnet-result-card {
    background: #FFFFFF;
    border: 1px solid rgba(208, 213, 229, 0.6);
    border-radius: var(--radius-md);
    padding: 14px 18px;
}
.subnet-result-label {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 700;
    text-transform: uppercase;
}
.subnet-result-val {
    font-family: var(--font-mono);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--brand-primary);
    direction: ltr;
    text-align: left;
    margin-top: 4px;
}

/* ── اختبار الفهم السريع (Instant Knowledge Check) ── */
.quiz-widget {
    background: radial-gradient(circle at top right, rgba(91, 79, 232, 0.05), transparent 70%),
                rgba(255, 255, 255, 0.95);
    border: 2px solid rgba(91, 79, 232, 0.25);
    border-radius: var(--radius-xl);
    padding: 30px;
    margin: 35px 0 20px;
    box-shadow: 0 15px 40px -10px rgba(91, 79, 232, 0.12);
}
.quiz-card-q {
    background: var(--bg-surface-solid);
    border: 1.5px solid var(--border-main);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    margin-bottom: 20px;
    transition: all 0.25s ease;
}
.quiz-opt-btn {
    display: block;
    width: 100%;
    text-align: right;
    padding: 12px 18px;
    margin: 8px 0;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-main);
    background: #FFFFFF;
    cursor: pointer;
    font-size: 0.94rem;
    font-weight: 600;
    color: var(--text-heading);
    transition: all 0.2s ease;
}
.quiz-opt-btn:hover {
    border-color: var(--brand-primary);
    background: rgba(91, 79, 232, 0.04);
    transform: translateX(-4px);
}
.quiz-opt-btn.correct {
    background: #ECFDF5 !important;
    border-color: #10B981 !important;
    color: #065F46 !important;
    font-weight: 800;
}
.quiz-opt-btn.wrong {
    background: #FFF1F2 !important;
    border-color: #F43F5E !important;
    color: #9F1239 !important;
}
.quiz-feedback {
    display: none;
    margin-top: 12px;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    font-size: 0.88rem;
    line-height: 1.6;
}

/* ── جسور الانتقال للمختبرات الحية ── */
.lab-shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 24px;
}
@media (max-width: 650px) {
    .lab-shortcuts-grid { grid-template-columns: 1fr; }
}
.lab-shortcut-item {
    background: #FFFFFF;
    border: 1.5px solid rgba(208, 213, 229, 0.7);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-subtle);
}
.lab-shortcut-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-card-hover);
    border-color: var(--brand-primary);
}
</style>

<div class="study-container">

    <!-- 1. ترويسة المستوى عالية التباين مع عناصر الذكاء الاصطناعي -->
    <div class="study-header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.14); backdrop-filter:blur(8px); padding:6px 16px; border-radius:var(--radius-full); font-size:0.82rem; font-weight:800; margin-bottom:12px; border:1px solid rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-graduation-cap"></i> المسار الأكاديمي التكيفي — المستوى <?php echo $level['level_order']; ?>
                </div>

                <h1><?php echo clean_output($level['level_name']); ?></h1>

                <p>
                    محتوى أكاديمي هندسي مجهز لمعايير Cisco CCNA 200-301. تفاعل مع المحاكيات الحية أدناه، ثم أجب على اختبار الفهم السريع لاجتياز المستوى.
                </p>
            </div>

            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <!-- زر القراءة الصوتية بالذكاء الاصطناعي -->
                <button type="button" id="tts-btn" onclick="toggleAudioReader()" class="btn" style="background:rgba(255,255,255,0.12); color:#FFF !important; border:1.5px solid rgba(255,255,255,0.3); font-weight:700; padding:12px 18px; border-radius:var(--radius-md); backdrop-filter:blur(8px);">
                    <i class="fa-solid fa-volume-high" id="tts-icon"></i> <span id="tts-text">استمع صوتياً</span>
                </button>

                <!-- زر بدء الاختبار المعتمد -->
                <a href="exam.php?level_id=<?php echo $level['id']; ?>" class="btn" style="background:#FFFFFF; color:#1A1A3E !important; font-weight:900; padding:13px 26px; border-radius:var(--radius-md); box-shadow:0 8px 24px rgba(0,0,0,0.25);">
                    <i class="fa-solid fa-bolt" style="color:var(--brand-primary);"></i> بدء اختبار المستوى
                </a>
            </div>
        </div>

        <!-- شريط التقدم الأكاديمي الإجمالي -->
        <div style="margin-top:24px; padding-top:18px; border-top:1px solid rgba(255,255,255,0.15);">
            <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:8px;">
                <span style="font-weight:700;"><i class="fa-solid fa-chart-line"></i> إجمالي تقدم القراءة بالمنصة</span>
                <span style="font-family:var(--font-mono); font-weight:800;"><?php echo $read_progress; ?>% مكتمل</span>
            </div>
            <div class="progress-bar-container" style="background:rgba(255,255,255,0.15); height:8px;">
                <div class="progress-bar-fill" style="width:<?php echo $read_progress; ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- 2. شريط التنقل المتطور بين المستويات الأربعة -->
    <div class="level-tabs-grid">
        <?php foreach ($all_levels as $lvl_item): ?>
            <?php 
                $is_active = ($lvl_item['id'] == $level_id);
                $is_passed = isset($passed_levels_map[$lvl_item['id']]);
                $is_current = ($lvl_item['id'] == $user_current_level);
            ?>
            <a href="study_module.php?level_id=<?php echo $lvl_item['id']; ?>" class="level-tab-card <?php echo $is_active ? 'active' : ''; ?>">
                <div class="level-tab-top">
                    <span style="font-family:var(--font-mono); font-weight:800; font-size:0.82rem; color:var(--brand-primary);">
                        LEVEL <?php echo $lvl_item['level_order']; ?>
                    </span>
                    <?php if ($is_passed): ?>
                        <span class="level-tab-badge" style="background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0;">
                            معتمد ✓
                        </span>
                    <?php elseif ($is_current): ?>
                        <span class="level-tab-badge" style="background:var(--brand-primary-light); color:var(--brand-primary); border:1px solid var(--brand-primary-border);">
                            حالي ⚡
                        </span>
                    <?php else: ?>
                        <span class="level-tab-badge" style="background:rgba(208,213,229,0.3); color:var(--text-muted);">
                            متاح
                        </span>
                    <?php endif; ?>
                </div>

                <div class="level-tab-title">
                    <?php echo clean_output($lvl_item['level_name']); ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- 2.5 شريط أدوات القراءة التفاعلي للمهندس (Reading HUD Bar) -->
    <div class="reading-hud-bar">
        <div class="reading-hud-progress-wrap">
            <span style="font-size:0.84rem; font-weight:700; color:var(--text-heading); display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-book-reader" style="color:var(--brand-primary);"></i> تقدم القراءة:
            </span>
            <div class="reading-hud-progress-bar">
                <div class="reading-hud-progress-fill" id="hud-progress-fill"></div>
            </div>
            <span class="reading-hud-pct" id="hud-pct">0%</span>
        </div>

        <div class="reading-hud-actions">
            <!-- عداد وقت القراءة -->
            <div class="hud-btn" style="cursor:default;" title="وقت القراءة المقدر">
                <i class="fa-regular fa-clock" style="color:var(--brand-primary);"></i>
                <span>~<?php echo !empty($modules) ? (int)$modules[0]['estimated_minutes'] : 15; ?> دقيقة</span>
            </div>

            <!-- التحكم بحجم الخط -->
            <button type="button" class="hud-btn" onclick="adjustFontSize(-1)" title="تصغير الخط">
                <i class="fa-solid fa-font"></i>-
            </button>
            <button type="button" class="hud-btn" onclick="adjustFontSize(0)" title="الخط الافتراضي">
                A
            </button>
            <button type="button" class="hud-btn" onclick="adjustFontSize(1)" title="تكبير الخط">
                <i class="fa-solid fa-font"></i>+
            </button>

            <!-- زر طباعة / ملخص الدرس -->
            <button type="button" class="hud-btn" onclick="window.print()" title="طباعة أو تصدير ملخص الدرس">
                <i class="fa-solid fa-print"></i> <span>طباعة الدليل</span>
            </button>

            <!-- زر الاستماع الصوتي -->
            <button type="button" class="hud-btn" id="tts-hud-btn" onclick="toggleAudioReader()" title="قراءة صوتية للدرس">
                <i class="fa-solid fa-volume-high" id="tts-hud-icon"></i> <span id="tts-hud-text">استماع صوتي</span>
            </button>
        </div>
    </div>

    <!-- 3. التوزيع الشبكي المزدوج (Dual-Column LMS Layout) -->
    <div class="study-layout-grid">

        <!-- العمود الجانبي: الفهرس التفاعلي والملاحة المنهجية (Sticky TOC Sidebar) -->
        <aside class="study-toc-sidebar">
            <div class="toc-sidebar-header">
                <div style="width:36px; height:36px; border-radius:var(--radius-sm); background:var(--brand-primary-light); color:var(--brand-primary); display:flex; align-items:center; justify-content:center; font-size:1.05rem;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h4 class="toc-sidebar-title">فهرس المحاضرة</h4>
                    <span style="font-size:0.75rem; color:var(--text-muted);">تنقل سلس ومباشر بين الأقسام</span>
                </div>
            </div>

            <ul class="toc-links-list">
                <li>
                    <a href="#sec-overview" class="toc-item-link active">
                        <span class="toc-item-icon"><i class="fa-solid fa-bullseye"></i></span>
                        <span>أهداف التعلم الهندسية</span>
                    </a>
                </li>
                <?php if ($level['level_order'] == 1): ?>
                    <li>
                        <a href="#sec-osi-stack" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-layer-group"></i></span>
                            <span>الطبقات السبع (OSI Stack)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-encapsulation" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-box-open"></i></span>
                            <span>تشريح الحزمة والتغليف</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-domains" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-network-wired"></i></span>
                            <span>مجالات التصادم والبث</span>
                        </a>
                    </li>
                <?php elseif ($level['level_order'] == 2): ?>
                    <li>
                        <a href="#sec-subnetting-math" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-calculator"></i></span>
                            <span>قواعد حساب Subnetting</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-cidr-table" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-table"></i></span>
                            <span>جدول CIDR والحالات الطرفية</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-vlans-trunking" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-diagram-project"></i></span>
                            <span>شبكات VLANs و Trunking</span>
                        </a>
                    </li>
                <?php elseif ($level['level_order'] == 3): ?>
                    <li>
                        <a href="#sec-admin-distance" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-compass"></i></span>
                            <span>جدول المسافات الإدارية (AD)</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-ospf-architecture" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-diagram-nested"></i></span>
                            <span>معمارية OSPF ومراحل الجوار</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-stp-mechanics" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-sitemap"></i></span>
                            <span>بروتوكول الشجرة STP</span>
                        </a>
                    </li>
                <?php elseif ($level['level_order'] == 4): ?>
                    <li>
                        <a href="#sec-acl-architecture" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <span>معمارية قوائم ACL</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-firewall-dynamics" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-fire-extinguisher"></i></span>
                            <span>جدران الحماية Stateful & NGFW</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sec-vpn-cryptography" class="toc-item-link">
                            <span class="toc-item-icon"><i class="fa-solid fa-lock"></i></span>
                            <span>أنفاق IPsec VPN والأمان</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="#sec-cisco-cli" class="toc-item-link">
                        <span class="toc-item-icon"><i class="fa-solid fa-terminal"></i></span>
                        <span>أوامر سيسكو CLI الحقيقية</span>
                    </a>
                </li>
                <li>
                    <a href="#sec-exam-tips" class="toc-item-link">
                        <span class="toc-item-icon"><i class="fa-solid fa-lightbulb"></i></span>
                        <span>نصائح CCNA وبيئات العمل</span>
                    </a>
                </li>
                <li>
                    <a href="#sec-interactive-lab" class="toc-item-link">
                        <span class="toc-item-icon"><i class="fa-solid fa-flask"></i></span>
                        <span>المختبر التفاعلي المدمج</span>
                    </a>
                </li>
                <li>
                    <a href="#sec-knowledge-check" class="toc-item-link">
                        <span class="toc-item-icon"><i class="fa-solid fa-bolt"></i></span>
                        <span>اختبار قياس الاستيعاب</span>
                    </a>
                </li>
                <li>
                    <a href="#sec-youtube-hub" class="toc-item-link">
                        <span class="toc-item-icon"><i class="fa-brands fa-youtube"></i></span>
                        <span>كورسات يوتيوب الموصى بها</span>
                    </a>
                </li>
            </ul>

            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--border-subtle); display:flex; flex-direction:column; gap:8px;">
                <a href="exam.php?level_id=<?php echo $level['id']; ?>" class="btn btn-primary btn-sm" style="width:100%; justify-content:center; font-weight:800; padding:10px 14px;">
                    <i class="fa-solid fa-bolt"></i> خوض اختبار المستوى
                </a>
                <a href="dashboard.php" class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; padding:10px 14px;">
                    <i class="fa-solid fa-house"></i> لوحة التحكم
                </a>
            </div>
        </aside>

        <!-- العمود الرئيسي: المحتوى الدراسي والبطاقات والمختبرات -->
        <main class="study-main-content">
            <?php if (empty($modules)): ?>
                <div class="academic-card" style="text-align:center; padding:50px 30px;">
                    <h3 style="color:var(--text-heading); margin-bottom:6px;">محتوى هذا المستوى قيد التجهيز</h3>
                    <p style="color:var(--text-muted);">يرجى التحقق لاحقاً.</p>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $mi => $mod): ?>
                    <div class="module-card">
                        
                        <div class="module-header">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:var(--brand-gradient); display:flex; align-items:center; justify-content:center; color:#FFF; font-weight:900; font-size:1.1rem; box-shadow:0 4px 14px rgba(91,79,232,0.25);">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <div>
                                    <h2 style="font-size:1.3rem; color:var(--text-heading); font-weight:800; margin:0 0 4px;">
                                        <?php echo clean_output($mod['title']); ?>
                                    </h2>
                                    <div style="font-size:0.84rem; color:var(--text-muted); display:flex; align-items:center; gap:12px;">
                                        <span><i class="fa-solid fa-clock"></i> وقت القراءة المقدر: <strong><?php echo $mod['estimated_minutes']; ?> دقيقة</strong></span>
                                        <span>•</span>
                                        <span><i class="fa-solid fa-circle-check" style="color:#10B981;"></i> معتمد لمنهج CCNA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="module-summary-box">
                            <strong style="color:var(--brand-primary);"><i class="fa-solid fa-compass"></i> ملخص الوحدة الهندسية:</strong>
                            <?php echo clean_output($mod['summary']); ?>
                        </div>

                        <div class="module-body-content">
                            <div class="study-guide-content" id="lesson-text-body">
                                <?php echo $mod['content_html']; ?>
                            </div>

                            <!-- ============================================================= -->
                            <!-- 4. المختبرات التفاعلية المصغرة بحسب المستوى (Interactive Labs)   -->
                            <!-- ============================================================= -->
                            <div id="sec-interactive-lab">
                                <?php if ($level['level_order'] == 1): ?>
                                    <!-- أداة المستوى 1: مستكشف طبقات OSI السبع التفاعلي -->
                                    <div class="interactive-lab-container">
                                        <div class="interactive-lab-title">
                                            <span><i class="fa-solid fa-layer-group" style="color:var(--brand-primary);"></i> مستكشف طبقات OSI ومحاكي التغليف الحي (Interactive Layer Clicker)</span>
                                            <span class="badge badge-primary"><i class="fa-solid fa-hand-pointer"></i> انقر على أي طبقة للتفاصيل</span>
                                        </div>

                                        <p style="font-size:0.92rem; color:var(--text-muted); margin-bottom:18px;">
                                            انقر على أي من الطبقات السبع لعرض بروتوكولاتها، ووحدة البيانات (PDU)، والجهاز الهندسي العامل بها، أو جرب تشغيل محاكاة التغليف:
                                        </p>

                                        <div style="display:flex; justify-content:flex-end; margin-bottom:14px;">
                                            <button type="button" onclick="runEncapsulationDemo()" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-play" style="color:#10B981;"></i> محاكاة تدفق التغليف (Encapsulation Flow)
                                            </button>
                                        </div>

                                        <div id="osi-interactive-list">
                                            <div class="osi-layer-row" onclick="toggleOsiDetails(7)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">7</div>
                                                    <strong>طبقة التطبيقات (Application Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:var(--brand-primary);">PDU: Data</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-7">
                                                <strong>الوظيفة:</strong> الواجهة المباشرة للمستخدم والتطبيقات.<br>
                                                <strong>البروتوكولات:</strong> HTTP, HTTPS, DNS, DHCP, FTP, SSH, SMTP.<br>
                                                <strong>الأجهزة:</strong> أجهزة الحواسيب، خوادم الويب، الجدران النارية NGFW.
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(6)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">6</div>
                                                    <strong>طبقة التقديم (Presentation Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:var(--brand-primary);">PDU: Data</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-6">
                                                <strong>الوظيفة:</strong> تشفير وضغط وترجمة تنسيقات البيانات وضمان قراءتها.<br>
                                                <strong>التنسيقات:</strong> SSL/TLS, JPEG, ASCII, MP4, GIF.
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(5)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">5</div>
                                                    <strong>طبقة الجلسة (Session Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:var(--brand-primary);">PDU: Data</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-5">
                                                <strong>الوظيفة:</strong> إدارة ومزامنة وإنهاء جلسات الاتصال والتفاوض بين البرامج.<br>
                                                <strong>الآليات:</strong> RPC, NetBIOS, Session Checkpoints.
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(4)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">4</div>
                                                    <strong>طبقة النقل (Transport Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:#8B5CF6;">PDU: Segment</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-4">
                                                <strong>الوظيفة:</strong> نقل البيانات من طرف لطرف مع التحكم بالتدفق وإعادة الإرسال.<br>
                                                <strong>البروتوكولات:</strong> TCP (الموثوق عبر 3-Way Handshake) و UDP (السريع بدون اتصال).<br>
                                                <strong>العنونة:</strong> أرقام المنافذ (Port Numbers: 80, 443, 53...).
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(3)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">3</div>
                                                    <strong>طبقة الشبكة (Network Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:#06B6D4;">PDU: Packet</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-3">
                                                <strong>الوظيفة:</strong> التوجيه المنطقي واختيار أفضل مسار لحزم البيانات عبر الشبكات.<br>
                                                <strong>البروتوكولات:</strong> IPv4, IPv6, ICMP, OSPF, BGP.<br>
                                                <strong>الأجهزة:</strong> جهاز الراوتر (Router)، ومبدلات الطبقة الثالثة (Layer 3 Switch).
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(2)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">2</div>
                                                    <strong>طبقة ربط البيانات (Data Link Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:#10B981;">PDU: Frame</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-2">
                                                <strong>الوظيفة:</strong> العنونة الفيزيائية واكتشاف أخطاء الإرسال عبر حقل الـ FCS.<br>
                                                <strong>البروتوكولات:</strong> Ethernet (802.3), Wi-Fi (802.11), PPP, ARP.<br>
                                                <strong>الأجهزة:</strong> المبدل (Switch)، وبطاقة الشبكة (NIC)، وجدول عناوين الـ MAC.
                                            </div>

                                            <div class="osi-layer-row" onclick="toggleOsiDetails(1)">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <div class="osi-layer-num">1</div>
                                                    <strong>الطبقة الفيزيائية (Physical Layer)</strong>
                                                </div>
                                                <span style="font-family:var(--font-mono); font-size:0.85rem; color:#F59E0B;">PDU: Bits</span>
                                            </div>
                                            <div class="osi-layer-details" id="osi-details-1">
                                                <strong>الوظيفة:</strong> تحويل الإشارات إلى نبضات كهربائية أو ضوئية أو موجات لاسلكية.<br>
                                                <strong>الوسائط والأجهزة:</strong> كابلات UTP/STP, كابلات الألياف الضوئية (Fiber), الموزع القديم (Hub).
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($level['level_order'] == 2): ?>
                                    <!-- أداة المستوى 2: حاسبة تقطيع الشبكات الحية (Live Subnet Calculator) -->
                                    <div class="interactive-lab-container">
                                        <div class="interactive-lab-title">
                                            <span><i class="fa-solid fa-calculator" style="color:var(--brand-primary);"></i> حاسبة Subnetting التفاعلية الحية (Real-Time Subnet Engine)</span>
                                            <span class="badge badge-success"><i class="fa-solid fa-bolt"></i> حساب فوري</span>
                                        </div>

                                        <p style="font-size:0.92rem; color:var(--text-muted); margin-bottom:16px;">
                                            أدخل عنوان IPv4 واختر بادئة CIDR لاختبار حسابات الشبكة وعناوين البث وعدد الأجهزة الصالحة مباشرة:
                                        </p>

                                        <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center; margin-bottom:16px;">
                                            <div style="flex:2; min-width:200px;">
                                                <label style="font-size:0.84rem; font-weight:700; color:var(--text-heading); display:block; margin-bottom:6px;">عنوان IP المستهدف:</label>
                                                <input type="text" id="calc-ip" class="form-control" value="192.168.10.130" oninput="runSubnetCalc()" style="direction:ltr; text-align:left; font-family:var(--font-mono); font-weight:700;">
                                            </div>
                                            <div style="flex:1; min-width:140px;">
                                                <label style="font-size:0.84rem; font-weight:700; color:var(--text-heading); display:block; margin-bottom:6px;">بادئة القناع (CIDR):</label>
                                                <select id="calc-cidr" class="form-control" onchange="runSubnetCalc()" style="font-family:var(--font-mono); font-weight:700;">
                                                    <option value="24">/24 (255.255.255.0)</option>
                                                    <option value="25">/25 (255.255.255.128)</option>
                                                    <option value="26" selected>/26 (255.255.255.192)</option>
                                                    <option value="27">/27 (255.255.255.224)</option>
                                                    <option value="28">/28 (255.255.255.248)</option>
                                                    <option value="29">/29 (255.255.255.248)</option>
                                                    <option value="30">/30 (255.255.255.252)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="subnet-calc-box">
                                            <div class="subnet-result-card">
                                                <div class="subnet-result-label">عنوان الشبكة (Network ID):</div>
                                                <div class="subnet-result-val" id="res-net-id">192.168.10.128</div>
                                            </div>
                                            <div class="subnet-result-card">
                                                <div class="subnet-result-label">عنوان البث العام (Broadcast):</div>
                                                <div class="subnet-result-val" id="res-bcast">192.168.10.191</div>
                                            </div>
                                            <div class="subnet-result-card">
                                                <div class="subnet-result-label">نطاق الأجهزة الصالحة (Usable Range):</div>
                                                <div class="subnet-result-val" id="res-range">192.168.10.129 - 192.168.10.190</div>
                                            </div>
                                            <div class="subnet-result-card">
                                                <div class="subnet-result-label">عدد الأجهزة الصالحة (Usable Hosts):</div>
                                                <div class="subnet-result-val" id="res-hosts">62 جهازاً</div>
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($level['level_order'] == 3): ?>
                                    <!-- أداة المستوى 3: حاسبة تكلفة OSPF ومقارن المسافات الإدارية -->
                                    <div class="interactive-lab-container">
                                        <div class="interactive-lab-title">
                                            <span><i class="fa-solid fa-route" style="color:var(--brand-primary);"></i> محاكي تكلفة OSPF ومقارن المسافات الإدارية (Routing Decision Engine)</span>
                                            <span class="badge badge-primary">OSPF Metrics</span>
                                        </div>

                                        <p style="font-size:0.92rem; color:var(--text-muted); margin-bottom:16px;">
                                            اختر نوع وسرعة منفذ الراوتر لمشاهدة حساب التكلفة (Cost) تلقائياً بناءً على معادلة سيسكو: <code>Cost = 100 Mbps / Interface Bandwidth</code>:
                                        </p>

                                        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
                                            <button type="button" onclick="calcOspfCost(10, '10 Mbps Ethernet')" class="btn btn-secondary btn-sm">Ethernet (10M)</button>
                                            <button type="button" onclick="calcOspfCost(100, 'FastEthernet (100M)')" class="btn btn-secondary btn-sm">FastEthernet (100M)</button>
                                            <button type="button" onclick="calcOspfCost(1000, 'GigabitEthernet (1G)')" class="btn btn-secondary btn-sm">GigabitEthernet (1G)</button>
                                            <button type="button" onclick="calcOspfCost(10000, 'TenGigabitEthernet (10G)')" class="btn btn-secondary btn-sm">10-Gigabit (10G)</button>
                                        </div>

                                        <div class="subnet-result-card" style="margin-bottom:18px;">
                                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                                <div>
                                                    <div class="subnet-result-label">المنفذ المختار: <strong id="ospf-port-name" style="color:var(--text-heading);">FastEthernet (100M)</strong></div>
                                                    <div style="font-size:0.86rem; color:var(--text-muted); margin-top:2px;">المعادلة: <code>Cost = 100,000,000 / Bandwidth</code></div>
                                                </div>
                                                <div class="subnet-result-val" id="ospf-cost-val" style="font-size:1.6rem; color:#10B981;">Cost = 1</div>
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($level['level_order'] == 4): ?>
                                    <!-- أداة المستوى 4: محاكي فحص قواعد الجدران النارية و Extended ACL -->
                                    <div class="interactive-lab-container">
                                        <div class="interactive-lab-title">
                                            <span><i class="fa-solid fa-shield-halved" style="color:var(--brand-primary);"></i> محاكي فحص قواعد جدران الحماية وقوائم ACL (Live Packet Evaluator)</span>
                                            <span class="badge badge-danger">Extended ACL Lab</span>
                                        </div>

                                        <div style="background:#0F172A; color:#38BDF8; font-family:var(--font-mono); padding:14px 18px; border-radius:var(--radius-sm); font-size:0.88rem; direction:ltr; text-align:left; margin-bottom:16px;">
                                            R1(config)# access-list 101 permit tcp 192.168.1.0 0.0.0.255 any eq 80<br>
                                            R1(config)# ! Note: Implicit Deny Any at the end
                                        </div>

                                        <p style="font-size:0.92rem; color:var(--text-muted); margin-bottom:14px;">
                                            جرّب إرسال حزمة بيانات لمعرفة هل سيتم تمريرها (Permit) أم إسقاطها (Deny):
                                        </p>

                                        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                                            <button type="button" onclick="testAclPacket('192.168.1.50', 80, true)" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-globe"></i> اختبار تصفح ويب (IP: 192.168.1.50, Port: 80)
                                            </button>
                                            <button type="button" onclick="testAclPacket('192.168.1.50', 22, false)" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-terminal"></i> اختبار اتصال SSH (IP: 192.168.1.50, Port: 22)
                                            </button>
                                            <button type="button" onclick="testAclPacket('10.0.0.5', 80, false)" class="btn btn-secondary btn-sm">
                                                <i class="fa-solid fa-user-xmark"></i> اختبار شبكة غريبة (IP: 10.0.0.5, Port: 80)
                                            </button>
                                        </div>

                                        <div id="acl-test-result" style="display:none; padding:14px 18px; border-radius:var(--radius-md); font-size:0.92rem; font-weight:700;"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ============================================================= -->
                            <!-- 5. اختبار قياس الاستيعاب الفوري (Instant Knowledge Check)       -->
                            <!-- ============================================================= -->
                            <div class="quiz-widget" id="sec-knowledge-check">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                                    <div>
                                        <span class="badge badge-warning" style="margin-bottom:6px;">
                                            <i class="fa-solid fa-bolt"></i> التقييم اللحظي الذكي
                                        </span>
                                        <h3 style="margin:0; font-size:1.25rem; color:var(--text-heading); font-weight:800;">
                                            اختبار قياس الاستيعاب السريع (Knowledge Check)
                                        </h3>
                                    </div>
                                    <div style="text-align:left;">
                                        <div style="font-size:0.8rem; color:var(--text-muted); font-weight:700;">نقاط الاستيعاب</div>
                                        <div id="quiz-score-badge" style="font-family:var(--font-mono); font-weight:900; font-size:1.3rem; color:var(--brand-primary);">
                                            0 / 3
                                        </div>
                                    </div>
                                </div>

                                <p style="font-size:0.92rem; color:var(--text-muted); margin-bottom:22px;">
                                    أجب على الأسئلة الثلاثة التالية للتحقق الفوري من استيعابك للمفاهيم قبل خوض الاختبار المعتمد:
                                </p>

                                <?php if ($level['level_order'] == 1): ?>
                                    <!-- أسئلة المستوى 1 -->
                                    <div class="quiz-card-q" id="qc-1">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            1. ما هي وحدة البيانات (PDU) الخاصة بطبقة النقل (Transport Layer)؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'A', false, 'خطأ: Packet هي وحدة البيانات لطبقة الشبكة (Layer 3).')">A) Packet (الحزمة)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'B', true, 'صحيح 100%! في طبقة النقل تسمى وحدة البيانات Segment.')">B) Segment (المقطع)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'C', false, 'خطأ: Frame هي وحدة البيانات لطبقة ربط البيانات (Layer 2).')">C) Frame (الإطار)</button>
                                        <div class="quiz-feedback" id="qfb-1"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-2">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            2. ما الذي يميز منافذ المبدل (Switch) مقارنة بالموزع القديم (Hub)؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'A', true, 'صحيح تماماً! يوفر المبدل مجال تصادم (Collision Domain) مستقل لكل منفذ من منافذه.')">A) يفصل مجالات التصادم لكل منفذ</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'B', false, 'خطأ: المبدل يمرر رسائل البث العام Broadcast لجميع المنافذ افتراضياً.')">B) يفصل مجالات البث العام تلقائياً</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'C', false, 'خطأ: المبدل يعمل في الطبقة الثانية بينما Hub في الطبقة الأولى.')">C) يعمل في الطبقة الفيزيائية فقط</button>
                                        <div class="quiz-feedback" id="qfb-2"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-3">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            3. ما هو بروتوكول المصافحة الثلاثية المسؤول عن النقل الموثوق للبيانات؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'A', false, 'خطأ: بروتوكول UDP غير موثوق ولا ينفذ مصافحة ثلاثية.')">A) بروتوكول UDP</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'B', true, 'صحيح! بروتوكول TCP يعتمد على المصافحة الثلاثية (SYN, SYN-ACK, ACK) لضمان تسليم البيانات.')">B) بروتوكول TCP</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'C', false, 'خطأ: ICMP بروتوكول تشخيصي (مثل Ping) ولا يدير تدفق البيانات.')">C) بروتوكول ICMP</button>
                                        <div class="quiz-feedback" id="qfb-3"></div>
                                    </div>

                                <?php elseif ($level['level_order'] == 2): ?>
                                    <!-- أسئلة المستوى 2 -->
                                    <div class="quiz-card-q" id="qc-1">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            1. كم عدد الأجهزة الصالحة للاستخدام في شبكة بقناع /28؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'A', false, 'خطأ: 16 هو إجمالي العناوين قبل طرح عنوان الشبكة وعنوان البث.')">A) 16 جهازاً</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'B', true, 'صحيح! 2^(32-28) - 2 = 16 - 2 = 14 جهازاً صالحاً.')">B) 14 جهازاً صالحاً</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'C', false, 'خطأ: 30 جهازاً يخص قناع /27.')">C) 30 جهازاً</button>
                                        <div class="quiz-feedback" id="qfb-1"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-2">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            2. ما هو عنوان الشبكة للمضيف 10.10.15.65 بقناع /26 (255.255.255.192)؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'A', false, 'خطأ: 10.10.15.0 هو بداية الشبكة الفرعية الأولى.')">A) 10.10.15.0</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'B', true, 'صحيح تماماً! حجم القفزة 64، وتبدأ الشبكة المعنية عند 10.10.15.64.')">B) 10.10.15.64</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'C', false, 'خطأ: 128 هي الشبكة التالية.')">C) 10.10.15.128</button>
                                        <div class="quiz-feedback" id="qfb-2"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-3">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            3. ما هو المعيار الدولي المفتوح لوسم شبكات الـ VLAN عبر منافذ الـ Trunk؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'A', false, 'خطأ: ISL بروتوكول قديم خاص بسيسكو.')">A) Cisco ISL</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'B', true, 'صحيح! معيار IEEE 802.1Q (Dot1Q) هو المعيار الصناعي المعتمد عالمياً.')">B) IEEE 802.1Q</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'C', false, 'خطأ: 802.3 هو معيار الإيثرنت الأساسي.')">C) IEEE 802.3</button>
                                        <div class="quiz-feedback" id="qfb-3"></div>
                                    </div>

                                <?php elseif ($level['level_order'] == 3): ?>
                                    <!-- أسئلة المستوى 3 -->
                                    <div class="quiz-card-q" id="qc-1">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            1. إذا توفر مسار لنفس الشبكة عبر التوجيه الثابت (Static) و OSPF، فأيهما يختاره الراوتر؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'A', true, 'صحيح! المسافة الإدارية (AD) للمسار الثابت هي 1 بينما OSPF هي 110، والأقل هو الفائز.')">A) المسار الثابت لأن مسافته الإدارية (AD=1) أقل</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'B', false, 'خطأ: الراوتر لا يفضل OSPF على Static لاختلاف الـ AD.')">B) مسار OSPF لأن خوارزميته ديناميكية</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'C', false, 'خطأ: لا يتم توزيع الأحمال بينهما.')">C) يتم توزيع الأحمال بينهما</button>
                                        <div class="quiz-feedback" id="qfb-1"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-2">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            2. ما هي منطقة العمود الفقري الإلزامية في بروتوكول OSPF؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'A', true, 'صحيح! Area 0 (Backbone Area) هي المنطقة المركزية الإلزامية في OSPF.')">A) Area 0 (Backbone Area)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'B', false, 'خطأ: Area 1 ليست منطقة العمود الفقري.')">B) Area 1</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'C', false, 'خطأ: Area 100 منطقة فرعية.')">C) Area 100</button>
                                        <div class="quiz-feedback" id="qfb-2"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-3">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            3. ما هو الغرض الهندسي الأساسي من تشغيل بروتوكول STP؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'A', false, 'خطأ: STP لا يضاعف سرعة المنافذ.')">A) مضاعفة سرعة التبديل</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'B', true, 'صحيح 100%! يمنع STP حدوث حلقات التبديل (Switching Loops) وعواصف البث.')">B) منع حلقات التبديل وعواصف البث</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'C', false, 'خطأ: تشفير الحزم وظيفة IPsec/MACsec.')">C) تشفير الإطارات بين المنافذ</button>
                                        <div class="quiz-feedback" id="qfb-3"></div>
                                    </div>

                                <?php elseif ($level['level_order'] == 4): ?>
                                    <!-- أسئلة المستوى 4 -->
                                    <div class="quiz-card-q" id="qc-1">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            1. أين يوصى هندسياً بوضع قوائم التحكم بالوصول الموسعة (Extended ACL)؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'A', false, 'خطأ: وضع القائمة قرب الوجهة هو المعيار لـ Standard ACL فقط.')">A) بالقرب من الوجهة (Destination)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'B', true, 'صحيح! توضع Extended ACL بالقرب من المصدر (Source) لتوفير سعة القنوات ومنع الحزم فور نشوئها.')">B) بالقرب من المصدر (Source) قدر الإمكان</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(1, 'C', false, 'خطأ: الموضع الهندسي حاسم في كفاءة التوجيه.')">C) على أي راوتر دون أي فارق</button>
                                        <div class="quiz-feedback" id="qfb-1"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-2">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            2. ما هي التقنية التي تتيح لآلاف الأجهزة الداخلية مشاركة IP عمومي واحد عبر أرقام المنافذ؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'A', false, 'خطأ: Static NAT يحتاج عنوان IP عام لكل جهاز داخلي.')">A) Static NAT (1-to-1)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'B', true, 'صحيح! تقنية PAT (Port Address Translation / NAT Overload) تعين رقم منفذ فريد لكل جلسة.')">B) PAT (Port Address Translation / NAT Overload)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(2, 'C', false, 'خطأ: Proxy ARP ليس مخصصاً لمشاركة الـ IP.')">C) Proxy ARP</button>
                                        <div class="quiz-feedback" id="qfb-2"></div>
                                    </div>

                                    <div class="quiz-card-q" id="qc-3">
                                        <div style="font-weight:700; color:var(--text-heading); margin-bottom:10px;">
                                            3. ما هو المبدأ الأساسي الذي تنطلق منه معمارية الأمان الحديثة Zero Trust؟
                                        </div>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'A', true, 'صحيح! Zero Trust تنطلق من مبدأ: Never Trust, Always Verify — لا تثق تلقائياً بأي مستخدم أو جهاز.')">A) عدم الثقة التلقائية والتحقق المستمر (Never Trust, Always Verify)</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'B', false, 'خطأ: الثقة بالمستخدمين الداخليين هي الثغرة التي تعالجها Zero Trust.')">B) الثقة التامة بجميع موظفي الشبكة الداخلية</button>
                                        <button type="button" class="quiz-opt-btn" onclick="checkAnswer(3, 'C', false, 'خطأ: كلمات المرور البسيطة تخالف المعايير الأمنية.')">C) الاعتماد على كلمات المرور المكونة من 4 أرقام</button>
                                        <div class="quiz-feedback" id="qfb-3"></div>
                                    </div>
                                <?php endif; ?>

                                <div id="quiz-congrats-box" style="display:none; text-align:center; padding:20px; background:#ECFDF5; border:1.5px solid #A7F3D0; border-radius:var(--radius-lg); margin-top:20px;">
                                    <div style="font-size:1.8rem; color:#10B981; margin-bottom:6px;"><i class="fa-solid fa-award"></i></div>
                                    <h4 style="margin:0 0 6px; color:#065F46; font-weight:800;">ممتاز! استوعبت كافة مفاهيم هذا المستوى بنجاح</h4>
                                    <p style="color:#047857; font-size:0.92rem; margin-bottom:14px;">أنت الآن مؤهل تماماً لخوض الاختبار الرسمي المعتمد واعتماد المستوى في سجلك الأكاديمي.</p>
                                    <a href="exam.php?level_id=<?php echo $level['id']; ?>" class="btn btn-primary" style="font-weight:800; padding:12px 28px;">
                                        <i class="fa-solid fa-play"></i> خوض الاختبار المعتمد الآن
                                    </a>
                                </div>
                            </div>

                            <!-- ============================================================= -->
                            <!-- 6. جسور التطبيق العملي والمختبرات الحية (Lab Shortcuts)        -->
                            <!-- ============================================================= -->
                            <div style="margin-top:36px;" id="sec-lab-shortcuts">
                                <h4 style="font-size:1.15rem; font-weight:800; color:var(--text-heading); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa-solid fa-flask" style="color:var(--brand-primary);"></i> المختبرات التفاعلية المرتبطة بهذا الدرس:
                                </h4>

                                <div class="lab-shortcuts-grid">
                                    <?php if ($level['level_order'] == 1): ?>
                                        <a href="osi_explorer.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(91,79,232,0.1); color:var(--brand-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">محلل طبقات OSI السبع</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">محاكاة تدفق الحزم والتغليف عبر الطبقات</div>
                                            </div>
                                        </a>
                                        <a href="packet_sniffer.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(6,182,212,0.1); color:#06B6D4; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-microchip"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">محلل الحزم (Wireshark Lab)</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">فحص حزم HTTP و DNS والـ Handshake الحية</div>
                                            </div>
                                        </a>
                                    <?php elseif ($level['level_order'] == 2): ?>
                                        <a href="subnet_simulator.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(16,185,129,0.1); color:#10B981; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-network-wired"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">مختبر Subnetting الحي</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">تمثيل بصري للـ 32 بت وتحديات حية</div>
                                            </div>
                                        </a>
                                        <a href="vlsm_calculator.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(139,92,246,0.1); color:#8B5CF6; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-calculator"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">حاسبة تقسيم الشبكات VLSM</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">توزيع بدون هدر مع رسم خريطة الذاكرة</div>
                                            </div>
                                        </a>
                                    <?php elseif ($level['level_order'] == 3): ?>
                                        <a href="cisco_cli.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(91,79,232,0.1); color:var(--brand-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-terminal"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">محاكي أوامر سيسكو CLI</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">أوامر OSPF و Routing Table الحية</div>
                                            </div>
                                        </a>
                                        <a href="network_topology.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(244,63,94,0.1); color:#F43F5E; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-diagram-project"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">محاكي مسار الحزم الحي</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">تتبع مسار الحزم بين الراوترات والمبدلات</div>
                                            </div>
                                        </a>
                                    <?php elseif ($level['level_order'] == 4): ?>
                                        <a href="cisco_cheatsheet.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(13,148,136,0.1); color:#0D9488; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-book-bookmark"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">موسوعة أوامر سيسكو Hub</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">أوامر الأمان وقوائم ACL و Port Security</div>
                                            </div>
                                        </a>
                                        <a href="cisco_cli.php" class="lab-shortcut-item">
                                            <div style="width:44px; height:44px; border-radius:var(--radius-sm); background:rgba(91,79,232,0.1); color:var(--brand-primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                                                <i class="fa-solid fa-terminal"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight:800; color:var(--text-heading); font-size:0.96rem;">محاكي أوامر سيسكو CLI</div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);">تطبيق إعدادات أمان المنافذ وجدران الحماية</div>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

    </div> <!-- نهاية التوزيع الشبكي المزدوج .study-layout-grid -->

    <!-- 6.5 مسار الكورسات المرئية المقترح لهذا المستوى (YouTube Adaptive Hub) -->
    <div id="sec-youtube-hub" style="margin-top:20px;">
        <?php 
        render_adaptive_video_hub(
            $pdo, 
            $level_id, 
            null, 
            "شروحات وسلاسل يوتيوب المقترحة لدراسة «" . clean_output($level['level_name']) . "»", 
            "اخترنا لك نخبة من أفضل شروحات الفيديو التفاعلية وقوائم التشغيل لشرح وتطبيق مواضيع هذا المستوى خطوة بخطوة:"
        ); 
        ?>
    </div>

    <!-- 7. صندوق الانتقال للاختبار الأكاديمي -->
    <div class="next-step-card" style="background:#FFFFFF; border:2px solid rgba(91,79,232,0.25); border-radius:var(--radius-xl); padding:40px 30px; text-align:center; box-shadow:var(--shadow-card); margin-top:28px;">
        <div style="width:64px; height:64px; border-radius:50%; background:var(--brand-gradient); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#FFF; font-size:26px; box-shadow:0 8px 20px rgba(91,79,232,0.3);">
            <i class="fa-solid fa-trophy"></i>
        </div>
        <h3 style="margin-bottom:8px; color:var(--text-heading); font-size:1.4rem; font-weight:900;">
            جاهز لاعتماد المستوى في سجلك الأكاديمي؟
        </h3>
        <p style="color:var(--text-muted); font-size:0.96rem; max-width:620px; margin:0 auto 26px; line-height:1.7;">
            خض الاختبار المعتمد المكون من أسئلة CCNA التخصصية للحصول على التقييم الأكاديمي وفتح المستوى التالي في مسارك التكيفي.
        </p>
        <div style="display:flex; justify-content:center; gap:14px; flex-wrap:wrap;">
            <a href="exam.php?level_id=<?php echo $level['id']; ?>" class="btn btn-primary btn-lg" style="font-weight:800; padding:15px 32px; border-radius:var(--radius-md);">
                <i class="fa-solid fa-bolt"></i> خوض اختبار المستوى الآن
            </a>
            <a href="dashboard.php" class="btn btn-secondary btn-lg" style="font-weight:700; padding:15px 24px; border-radius:var(--radius-md);">
                <i class="fa-solid fa-chart-pie"></i> العودة للوحة التقدم
            </a>
        </div>
    </div>

</div>

<!-- السكربتات التفاعلية الشاملة -->
<script>
// 1. القراءة الصوتية بالذكاء الاصطناعي (Speech Synthesis)
let isSpeaking = false;
function toggleAudioReader() {
    if (!('speechSynthesis' in window)) {
        alert('خاصية القراءة الصوتية غير مدعومة في هذا المتصفح.');
        return;
    }

    const icon = document.getElementById('tts-icon');
    const text = document.getElementById('tts-text');
    const hudIcon = document.getElementById('tts-hud-icon');
    const hudText = document.getElementById('tts-hud-text');

    if (isSpeaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        if (icon) icon.className = 'fa-solid fa-volume-high';
        if (text) text.textContent = 'استمع صوتياً';
        if (hudIcon) hudIcon.className = 'fa-solid fa-volume-high';
        if (hudText) hudText.textContent = 'استماع صوتي';
    } else {
        const summary = document.querySelector('.module-summary-box');
        const callout = document.querySelector('.callout-box');
        const bodyText = (summary ? summary.innerText : '') + ' ' + (callout ? callout.innerText : '');
        const utterance = new SpeechSynthesisUtterance(bodyText);
        utterance.lang = 'ar-SA';
        utterance.rate = 0.95;

        utterance.onend = function() {
            isSpeaking = false;
            if (icon) icon.className = 'fa-solid fa-volume-high';
            if (text) text.textContent = 'استمع صوتياً';
            if (hudIcon) hudIcon.className = 'fa-solid fa-volume-high';
            if (hudText) hudText.textContent = 'استماع صوتي';
        };

        window.speechSynthesis.speak(utterance);
        isSpeaking = true;
        if (icon) icon.className = 'fa-solid fa-pause';
        if (text) text.textContent = 'إيقاف مؤقت';
        if (hudIcon) hudIcon.className = 'fa-solid fa-pause';
        if (hudText) hudText.textContent = 'إيقاف مؤقت';
    }
}

// 2. التحكم بحجم الخط (Font Size Adjuster)
let currentFontSizeState = 0; // -1: small, 0: default, 1: large
function adjustFontSize(dir) {
    const el = document.getElementById('lesson-text-body');
    if (!el) return;
    if (dir === 0) {
        currentFontSizeState = 0;
        el.style.fontSize = '1.02rem';
    } else if (dir === -1) {
        currentFontSizeState = -1;
        el.style.fontSize = '0.92rem';
    } else if (dir === 1) {
        currentFontSizeState = 1;
        el.style.fontSize = '1.16rem';
    }
}

// 3. نسخ أوامر سيسكو إلى الحافظة (Clipboard Copy)
function copyTerminalText(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ تم نسخ أوامر سيسكو بنجاح إلى الحافظة!');
    }).catch(err => {
        console.error('فشل النسخ:', err);
    });
}

// 4. تعقب التقدم بالقراءة والـ ScrollSpy للفهرس التفاعلي
window.addEventListener('scroll', function() {
    // تحديث شريط التقدم العلوي
    const docElem = document.documentElement;
    const docBody = document.body;
    const scrollTop = docElem.scrollTop || docBody.scrollTop;
    const scrollHeight = (docElem.scrollHeight || docBody.scrollHeight) - docElem.clientHeight;
    const pct = scrollHeight > 0 ? Math.min(100, Math.round((scrollTop / scrollHeight) * 100)) : 0;
    
    const fill = document.getElementById('hud-progress-fill');
    const pctText = document.getElementById('hud-pct');
    if (fill) fill.style.width = pct + '%';
    if (pctText) pctText.textContent = pct + '%';

    // تمييز الرابط النشط في الفهرس التفاعلي (ScrollSpy)
    const sections = document.querySelectorAll('[id^="sec-"]');
    let currentId = '';
    sections.forEach(sec => {
        const rect = sec.getBoundingClientRect();
        if (rect.top <= 180 && rect.bottom >= 0) {
            currentId = sec.id;
        }
    });

    if (currentId) {
        document.querySelectorAll('.toc-item-link').forEach(link => {
            if (link.getAttribute('href') === '#' + currentId) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
});

// 5. مستكشف طبقات OSI التفاعلي (المستوى 1)
function toggleOsiDetails(layerNum) {
    const el = document.getElementById('osi-details-' + layerNum);
    if (!el) return;
    const isShown = el.style.display === 'block';
    
    // إغلاق البقية
    for (let i = 1; i <= 7; i++) {
        const other = document.getElementById('osi-details-' + i);
        if (other) other.style.display = 'none';
    }

    if (!isShown) {
        el.style.display = 'block';
    }
}

function runEncapsulationDemo() {
    let current = 7;
    const interval = setInterval(() => {
        toggleOsiDetails(current);
        current--;
        if (current < 1) {
            clearInterval(interval);
        }
    }, 600);
}

// 6. حاسبة Subnetting التفاعلية الحية (المستوى 2)
function runSubnetCalc() {
    const ipInput = document.getElementById('calc-ip');
    const cidrSelect = document.getElementById('calc-cidr');
    if (!ipInput || !cidrSelect) return;

    const ipStr = ipInput.value.trim();
    const cidr = parseInt(cidrSelect.value, 10);
    const parts = ipStr.split('.').map(p => parseInt(p, 10));

    if (parts.length !== 4 || parts.some(p => isNaN(p) || p < 0 || p > 255)) {
        return;
    }

    const hostBits = 32 - cidr;
    const totalHosts = Math.pow(2, hostBits);
    const usableHosts = totalHosts >= 2 ? totalHosts - 2 : 0;

    // تحويل IP إلى عدد 32 بت
    const ipInt = ((parts[0] << 24) >>> 0) + ((parts[1] << 16) >>> 0) + ((parts[2] << 8) >>> 0) + (parts[3] >>> 0);
    const maskInt = hostBits === 0 ? 0xFFFFFFFF : (((0xFFFFFFFF << hostBits) >>> 0));
    const netInt = (ipInt & maskInt) >>> 0;
    const bcastInt = (netInt | (~maskInt >>> 0)) >>> 0;

    const toIp = int => [ (int >>> 24) & 255, (int >>> 16) & 255, (int >>> 8) & 255, int & 255 ].join('.');

    const netId = toIp(netInt);
    const bcast = toIp(bcastInt);
    const firstHost = toIp(netInt + 1);
    const lastHost = toIp(bcastInt - 1);

    const rNet = document.getElementById('res-net-id');
    const rBcast = document.getElementById('res-bcast');
    const rRange = document.getElementById('res-range');
    const rHosts = document.getElementById('res-hosts');

    if (rNet) rNet.textContent = netId;
    if (rBcast) rBcast.textContent = bcast;
    if (rRange) rRange.textContent = firstHost + ' - ' + lastHost;
    if (rHosts) rHosts.textContent = usableHosts.toLocaleString() + ' جهازاً';
}

// 7. محاكي OSPF Cost (المستوى 3)
function calcOspfCost(bwMbps, name) {
    const refBw = 100; // 100 Mbps
    const cost = Math.max(1, Math.round(refBw / bwMbps));
    const portName = document.getElementById('ospf-port-name');
    const costVal = document.getElementById('ospf-cost-val');
    if (portName) portName.textContent = name;
    if (costVal) costVal.textContent = 'Cost = ' + cost;
}

// 8. محاكي Extended ACL (المستوى 4)
function testAclPacket(srcIp, port, isPermit) {
    const resBox = document.getElementById('acl-test-result');
    if (!resBox) return;
    resBox.style.display = 'block';

    if (isPermit) {
        resBox.style.background = '#ECFDF5';
        resBox.style.border = '1.5px solid #A7F3D0';
        resBox.style.color = '#065F46';
        resBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> <strong>PERMITTED (مسموح):</strong> الحزمة القادمة من ' + srcIp + ' على المنفذ ' + port + ' تطابق صراحة قاعدة التصريح: <code>permit tcp 192.168.1.0/24 any eq 80</code>.';
    } else {
        resBox.style.background = '#FFF1F2';
        resBox.style.border = '1.5px solid #FECDD3';
        resBox.style.color = '#9F1239';
        resBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> <strong>DENIED (محجوب):</strong> الحزمة القادمة من ' + srcIp + ' على المنفذ ' + port + ' لم تطابق قاعدة التصريح، فتم إسقاطها بقاعدة الحجب الضمني غير المرئية <code>Implicit Deny Any</code>.';
    }
}

// 9. اختبار قياس الاستيعاب الفوري (Knowledge Check Logic)
let correctAnswersCount = 0;
const answeredQuestions = {};

function checkAnswer(qNum, selectedOpt, isCorrect, explanation) {
    const card = document.getElementById('qc-' + qNum);
    const fb = document.getElementById('qfb-' + qNum);
    if (!card || !fb) return;
    const btns = card.querySelectorAll('.quiz-opt-btn');

    // تعطيل الأزرار
    btns.forEach(btn => {
        btn.disabled = true;
        btn.style.cursor = 'default';
        btn.style.opacity = '0.7';
    });

    // تمييز الإجابة
    btns.forEach(btn => {
        if (btn.textContent.trim().startsWith(selectedOpt + ')')) {
            btn.style.opacity = '1';
            btn.className += isCorrect ? ' correct' : ' wrong';
        }
    });

    // إظهار الشرح
    fb.style.display = 'block';
    fb.style.background = isCorrect ? '#ECFDF5' : '#FFF1F2';
    fb.style.color = isCorrect ? '#065F46' : '#9F1239';
    fb.style.border = isCorrect ? '1px solid #A7F3D0' : '1px solid #FECDD3';
    fb.innerHTML = (isCorrect ? '<i class="fa-solid fa-circle-check"></i> ' : '<i class="fa-solid fa-circle-xmark"></i> ') + explanation;

    if (!answeredQuestions[qNum]) {
        answeredQuestions[qNum] = true;
        if (isCorrect) {
            correctAnswersCount++;
        }
        const badge = document.getElementById('quiz-score-badge');
        if (badge) badge.textContent = correctAnswersCount + ' / 3';

        if (correctAnswersCount === 3) {
            const congrats = document.getElementById('quiz-congrats-box');
            if (congrats) congrats.style.display = 'block';
        }
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
