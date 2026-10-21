<?php
$pageTitle = "لوحة التحكم التكيفية للطالب";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/recommendations_helper.php';
require_once __DIR__ . '/../includes/precision_remediation_helper.php';
require_auth('student');

$user_id = $_SESSION['user_id'];

// التحقق من وجود جلسة معالجة دقيقة نشطة وحساب خريطة المهارات
$active_remediation = get_active_user_remediation($pdo, $user_id);
$skill_map = calculate_user_skill_map($pdo, $user_id);

// 1. جلب بيانات الطالب الحالية ومستواه
$stmt = $pdo->prepare("
    SELECT u.*, l.level_name, l.level_order 
    FROM users u 
    LEFT JOIN levels l ON u.current_level_id = l.id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

$current_level_id = (int)($student['current_level_id'] ?? 1);

// 2. جلب جميع المستويات
$levels = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll();

// 3. جلب نتائج وسجل الاختبارات السابقة المعتمدة
$results_stmt = $pdo->prepare("
    SELECT r.*, l.level_name 
    FROM exam_results r 
    JOIN levels l ON r.level_id = l.id 
    WHERE r.user_id = ? 
    ORDER BY r.id DESC
");
$results_stmt->execute([$user_id]);
$exam_history = $results_stmt->fetchAll();

// 4. جلب سجل المحاولات التفصيلي من جدول exam_attempts
$attempts_stmt = $pdo->prepare("
    SELECT a.*, l.level_name 
    FROM exam_attempts a 
    LEFT JOIN levels l ON a.level_id = l.id 
    WHERE a.user_id = ? 
    ORDER BY a.id DESC 
    LIMIT 6
");
$attempts_stmt->execute([$user_id]);
$recent_attempts = $attempts_stmt->fetchAll();

// 5. حساب مؤشر إتقان مسار المنصة الأكاديمي
$passed_levels_count = 0;
foreach ($levels as $lvl) {
    $chk = $pdo->prepare("SELECT COUNT(*) FROM exam_results WHERE user_id = ? AND level_id = ? AND score >= 75");
    $chk->execute([$user_id, $lvl['id']]);
    if ($chk->fetchColumn() > 0) {
        $passed_levels_count++;
    }
}
$mastery_score = round(($passed_levels_count / max(count($levels), 1)) * 100);

// عدد تمارين المختبر المنجزة
$lab_count = (int)$pdo->query("SELECT COUNT(*) FROM lab_attempts WHERE user_id = $user_id")->fetchColumn();

// جلب آخر موضوع يحتاج تقوية
$weak_topic_stmt = $pdo->prepare("
    SELECT t.topic_name, COUNT(*) as err_count
    FROM attempt_questions aq
    JOIN exam_attempts a ON aq.attempt_id = a.id
    JOIN questions q ON aq.question_id = q.id
    JOIN topics t ON q.topic_id = t.id
    WHERE a.user_id = ? AND aq.is_correct = 0
    GROUP BY t.topic_name
    ORDER BY err_count DESC
    LIMIT 1
");
$weak_topic_stmt->execute([$user_id]);
$weakest_topic = $weak_topic_stmt->fetchColumn();

// ===== التوصيات الذكية: جلب أفضل 4 مصادر للمستوى الحالي غير مكتملة =====
$smart_recs = $pdo->prepare("
    SELECT lr.*, COALESCE(rv.is_completed,0) as done, COALESCE(rv.viewed_at IS NOT NULL,0) as seen
    FROM level_resources lr
    LEFT JOIN resource_views rv ON rv.resource_id=lr.id AND rv.user_id=?
    WHERE lr.level_id=? AND lr.is_active=1 AND COALESCE(rv.is_completed,0)=0
    ORDER BY lr.is_featured DESC, lr.sort_order ASC
    LIMIT 4
");
$smart_recs->execute([$user_id, $current_level_id]);
$smart_recs = $smart_recs->fetchAll();

// إحصاء التقدم في الموارد
$res_progress = $pdo->prepare("
    SELECT COUNT(lr.id) as total,
        SUM(CASE WHEN rv.is_completed=1 THEN 1 ELSE 0 END) as completed
    FROM level_resources lr
    LEFT JOIN resource_views rv ON rv.resource_id=lr.id AND rv.user_id=?
    WHERE lr.level_id=? AND lr.is_active=1
");
$res_progress->execute([$user_id, $current_level_id]);
$res_progress = $res_progress->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ========================================================================= -->
<!-- Hero Banner المخصص للطالب                                                 -->
<!-- ========================================================================= -->
<?php if (isset($_GET['welcome'])): ?>
<div id="welcome-toast" style="position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 9999; background: linear-gradient(135deg, #065F46 0%, #10B981 100%); color: #FFFFFF; padding: 16px 28px; border-radius: 14px; box-shadow: 0 12px 40px rgba(16, 185, 129, 0.5); display: flex; align-items: center; gap: 14px; font-weight: 700; font-size: 1.05rem; animation: slideDown 0.5s ease forwards; min-width: 320px; max-width: 560px;">
    <i class="fa-solid fa-circle-check" style="font-size: 24px;"></i>
    <div>
        <div style="font-size: 1.05rem;">تم إنشاء حسابك بنجاح! 🎉</div>
        <div style="font-size: 0.85rem; opacity: 0.9; font-weight: 500; margin-top: 2px;">مرحباً بك في منصة التعليم التكيفي الذكي للشبكات</div>
    </div>
    <button onclick="document.getElementById('welcome-toast').remove()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 14px; margin-right: auto; flex-shrink: 0;">&times;</button>
</div>
<style>
@keyframes slideDown {
    from { opacity: 0; top: -20px; }
    to   { opacity: 1; top: 24px; }
}
</style>
<script>setTimeout(() => { const t = document.getElementById('welcome-toast'); if(t) t.style.transition = 'opacity 0.5s'; if(t) t.style.opacity = '0'; setTimeout(() => { if(t) t.remove(); }, 500); }, 5000);</script>
<?php endif; ?>
<div class="student-hero-banner">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px; position: relative; z-index: 2;">
        <div style="max-width: 620px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); padding: 5px 16px; border-radius: var(--radius-full); font-size: 0.82rem; font-weight: 700; margin-bottom: 16px; border: 1px solid rgba(255, 255, 255, 0.1);">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 8px #10B981;"></span>
                <i class="fa-solid fa-graduation-cap"></i>
                <span>المسار الأكاديمي التكيفي المعتمد</span>
            </div>

            <h1 style="color: #FFFFFF; font-size: 2rem; margin-bottom: 10px; font-weight: 800;">
                <?php echo isset($_GET['welcome']) ? 'مرحباً بك في منصتنا،' : 'أهلاً بك مجدداً،'; ?> <?php echo clean_output($student['full_name']); ?>! <?php echo isset($_GET['welcome']) ? '🎉' : ''; ?>
            </h1>

            <p style="color: rgba(255, 255, 255, 0.82); font-size: 1rem; margin-bottom: 22px; line-height: 1.65;">
                أنت الآن تدرس <strong style="color: #A5B4FC;">«<?php echo clean_output($student['level_name'] ?? 'المستوى الأول'); ?>»</strong>.
                <?php if ($weakest_topic): ?>
                    يوصي المحرك التكيفي بمراجعة <em>«<?php echo clean_output($weakest_topic); ?>»</em> لتحقيق أعلى جاهزية.
                <?php else: ?>
                    استكمل الدروس التفاعلية وخض اختبار الترقية لاعتماد مستواك الجديد.
                <?php endif; ?>
            </p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="study_module.php?level_id=<?php echo $current_level_id; ?>" class="btn" style="background: #FFFFFF; color: #1A1A3E !important; font-weight: 700; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); padding: 12px 24px; border-radius: var(--radius-sm);">
                    <i class="fa-solid fa-book-open"></i> قراءة الدروس المدمجة
                </a>
                <a href="exam.php?level_id=<?php echo $current_level_id; ?>" class="btn" style="background: rgba(255, 255, 255, 0.12); color: #FFFFFF !important; backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px 24px; border-radius: var(--radius-sm);">
                    <i class="fa-solid fa-file-signature"></i> بدء اختبار المستوى
                </a>
            </div>
        </div>

        <!-- عداد الإتقان الدائري -->
        <div style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: var(--radius-xl); padding: 24px 30px; text-align: center; min-width: 200px;">
            <div style="position: relative; width: 110px; height: 110px; margin: 0 auto 14px;">
                <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="rgba(255, 255, 255, 0.12)" stroke-width="2.5" />
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="url(#progressGrad)" stroke-width="3" stroke-dasharray="<?php echo $mastery_score; ?>, 100" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="progressGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#A5B4FC" />
                            <stop offset="100%" style="stop-color:#06B6D4" />
                        </linearGradient>
                    </defs>
                </svg>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-mono); font-size: 1.5rem; font-weight: 900; color: #FFFFFF;">
                    <?php echo $mastery_score; ?>%
                </div>
            </div>
            <div style="font-size: 0.84rem; font-weight: 700; color: rgba(255, 255, 255, 0.9);">مؤشر إتقان المنصة</div>
            <div style="font-size: 0.74rem; color: rgba(255, 255, 255, 0.6); margin-top: 4px;">
                <?php echo $passed_levels_count; ?> من 4 مستويات مكتملة
            </div>
            <!-- شريط التقدم -->
            <div class="progress-bar-container" style="margin-top: 12px;">
                <div class="progress-bar-fill" style="width: <?php echo $mastery_score; ?>%;"></div>
            </div>
        </div>
    </div>
</div>

<?php if ($active_remediation): ?>
    <!-- ========================================================================= -->
    <!-- تنبيه جلسة المعالجة الدقيقة النشطة (التجاوز الذكي والمسار الجانبي)         -->
    <!-- ========================================================================= -->
    <?php if ($active_remediation['remediation_status'] === 'pending_choice'): ?>
        <div class="cyber-alert cyber-alert-warning" style="margin-bottom: 25px; padding: 22px 24px; border-radius: var(--radius-lg); background: #FFFBEB; border: 2px solid #F59E0B; box-shadow: 0 8px 24px -6px rgba(245, 158, 11, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <strong style="color: #92400E; font-size: 1.1rem; display: block; margin-bottom: 4px;">
                            نظام التجاوز مع المعالجة الدقيقة: رصد تميز متقدم مع هفوة في الأساسيات
                        </strong>
                        <p style="font-size: 0.88rem; color: #78350F; margin: 0; line-height: 1.6;">
                            لاحظ النظام خطأ في مفهوم «<strong><?php echo clean_output($active_remediation['concept_name']); ?></strong>». مستواك المتقدم محفوظ! 
                            هل ترغب في إجراء <strong>مراجعة سريعة (30 ثانية)</strong> لإغلاق الثغرة، أم <strong>الرجوع للمبتدئ</strong>؟
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="precision_remediation.php?rem_id=<?php echo $active_remediation['id']; ?>" class="btn btn-primary btn-sm" style="font-weight: 700; background: #D97706; border-color: #B45309;">
                        <i class="fa-solid fa-play"></i> مراجعة سريعة (30 ثانية)
                    </a>
                    <a href="placement_result.php?attempt_token=rem_<?php echo $active_remediation['id']; ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-gear"></i> خيارات المسار
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($active_remediation['remediation_status'] === 'side_quest_active'): ?>
        <div class="cyber-alert" style="margin-bottom: 25px; padding: 22px 24px; border-radius: var(--radius-lg); background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); border: 2px solid #D97706; box-shadow: 0 8px 24px -6px rgba(217, 119, 6, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #D97706; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; animation: pulse 2s infinite;">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <div>
                        <strong style="color: #78350F; font-size: 1.1rem; display: block; margin-bottom: 4px;">
                            <i class="fa-solid fa-flag-checkered"></i> لديك مسار جانبي نشط (Active Side Quest)!
                        </strong>
                        <p style="font-size: 0.88rem; color: #92400E; margin: 0; line-height: 1.6;">
                            مهمة خاصة لتقوية مفهوم «<strong><?php echo clean_output($active_remediation['concept_name']); ?></strong>». 
                            أنجز هذا المسار القصير المكون من 3-4 أسئلة لإغلاق الثغرة نهائياً واستئناف تقدمك.
                        </p>
                    </div>
                </div>
                <div>
                    <a href="side_quest.php?rem_id=<?php echo $active_remediation['id']; ?>" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #D97706 0%, #B45309 100%); border: none; font-weight: 700; padding: 10px 20px;">
                        <i class="fa-solid fa-play"></i> إكمال المسار الجانبي الآن
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- بطاقات المؤشرات الإحصائية الأربع (KPI Cards)                              -->
<!-- ========================================================================= -->
<div class="grid-4" style="margin-bottom: 30px;">
    
    <div class="stat-kpi-card">
        <div class="stat-kpi-header">
            <span class="stat-kpi-label">المستوى الحالي المعتمد</span>
            <div class="stat-kpi-icon" style="background: rgba(91, 79, 232, 0.1); color: var(--brand-primary);">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
        <div class="stat-kpi-value" style="font-size: 1.35rem; color: var(--brand-primary); font-family: var(--font-sans);">
            مستوى #<?php echo $student['level_order'] ?? 1; ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
            <?php echo clean_output($student['level_name'] ?? 'أساسيات الشبكات'); ?>
        </div>
    </div>

    <div class="stat-kpi-card">
        <div class="stat-kpi-header">
            <span class="stat-kpi-label">المستويات المجتازة بنجاح</span>
            <div class="stat-kpi-icon" style="background: var(--status-success-bg); color: var(--status-success);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
        <div class="stat-kpi-value" style="color: var(--status-success);">
            <?php echo $passed_levels_count; ?> <span style="font-size: 1rem; color: var(--text-muted);">/ 4</span>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
            نسبة النجاح المقررة &gt;= 75%
        </div>
    </div>

    <div class="stat-kpi-card">
        <div class="stat-kpi-header">
            <span class="stat-kpi-label">تمارين مختبر Subnetting</span>
            <div class="stat-kpi-icon" style="background: var(--brand-secondary-light); color: var(--brand-secondary);">
                <i class="fa-solid fa-network-wired"></i>
            </div>
        </div>
        <div class="stat-kpi-value" style="color: var(--brand-secondary);">
            <?php echo $lab_count; ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
            مسألة تطبيقية محلولة
        </div>
    </div>

    <div class="stat-kpi-card">
        <div class="stat-kpi-header">
            <span class="stat-kpi-label">اختبار تحديد المستوى</span>
            <div class="stat-kpi-icon" style="background: var(--accent-amber-light); color: var(--accent-amber);">
                <i class="fa-solid fa-file-signature"></i>
            </div>
        </div>
        <div style="margin-bottom: 4px;">
            <a href="placement_test.php" class="btn btn-secondary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-arrows-rotate"></i> إعادة التشخيص
            </a>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
            لتشخيص المسار تلقائياً
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- خريطة المهارات والكفاءات التكيفية (Adaptive Skill Map)                     -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 30px; padding: 28px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 1.3rem; color: var(--text-heading); margin: 0 0 4px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chart-pie" style="color: var(--brand-primary);"></i>
                خريطة المهارات والكفاءات التكيفية (Adaptive Skill Map)
            </h2>
            <p style="font-size: 0.86rem; color: var(--text-muted); margin: 0;">
                تقييم تشخيصي متعدد الأبعاد لكل مجال شبكي — بدلاً من خفض مستواك، توضح الخريطة نقاط قوتك ونقاط المعالجة الدقيقة:
            </p>
        </div>
        <div>
            <span class="badge badge-primary" style="font-size: 0.82rem; padding: 6px 14px;">
                <i class="fa-solid fa-microchip"></i> تشخيص كفاءات ذكي
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px;">
        <?php foreach ($skill_map as $sm): ?>
            <div style="background: var(--bg-surface-solid); border: 1.5px solid var(--border-subtle); border-top: 4px solid <?php echo $sm['tier_color']; ?>; border-radius: var(--radius-lg); padding: 18px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: all 0.25s ease;">
                <div style="font-size: 0.76rem; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">
                    المستوى #<?php echo $sm['level_order']; ?>
                </div>
                <div style="font-size: 0.92rem; font-weight: 800; color: var(--text-heading); margin-bottom: 8px; min-height: 42px; display: flex; align-items: center; justify-content: center; line-height: 1.4;">
                    <?php echo clean_output($sm['level_name']); ?>
                </div>
                <div style="font-family: var(--font-code); font-size: 1.6rem; font-weight: 900; color: <?php echo $sm['tier_color']; ?>; margin-bottom: 8px;">
                    <?php echo $sm['percentage']; ?>%
                </div>
                
                <!-- شريط التقدم -->
                <div style="background: #E2E8F0; height: 6px; border-radius: 3px; overflow: hidden; margin-bottom: 12px;">
                    <div style="width: <?php echo min(100, $sm['percentage']); ?>%; height: 100%; background: <?php echo $sm['tier_color']; ?>;"></div>
                </div>

                <span class="badge <?php echo $sm['tier_badge']; ?>" style="font-size: 0.72rem; padding: 4px 10px;">
                    <i class="<?php echo $sm['tier_icon']; ?>"></i> <?php echo $sm['tier_title']; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- المختبرات التفاعلية السبعة                                                -->
<!-- ========================================================================= -->
<div style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <div>
            <h2 style="font-size: 1.35rem; color: var(--text-heading); margin-bottom: 4px;">
                <i class="fa-solid fa-flask" style="color: var(--brand-primary); margin-left: 8px;"></i>
                المختبرات التفاعلية وأدوات المحاكاة الحية
            </h2>
            <p style="font-size: 0.86rem; color: var(--text-muted); margin: 0;">
                بيئات عملية مجهزة لمحاكاة سيناريوهات Cisco CCNA وحسابات الشبكات
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px;">
        
        <div class="lab-card-enhanced" style="--lab-color: var(--brand-primary);">
            <div style="border-top: 3px solid var(--brand-primary); position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(91, 79, 232, 0.1); color: var(--brand-primary);">
                    <i class="fa-solid fa-terminal"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">محاكي أوامر سيسكو CLI</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    تدرب على إعداد المنافذ وعناوين IP والتوجيه ببيئة حقيقية مع التكميل التلقائي.
                </p>
            </div>
            <a href="cisco_cli.php" class="btn btn-primary btn-sm" style="width: 100%;">
                <i class="fa-solid fa-play"></i> فتح موجه الأوامر
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #06B6D4; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(6, 182, 212, 0.1); color: #06B6D4;">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">محلل ومفتش الحزم (Wireshark)</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    التقط حزم HTTP و DNS و TCP وفكك ترويساتها مع فحص Hex Dump.
                </p>
            </div>
            <a href="packet_sniffer.php" class="btn btn-cyan btn-sm" style="width: 100%;">
                <i class="fa-solid fa-satellite-dish"></i> فحص الحزم الحية
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #10B981; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">مختبر Subnetting الحي</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    تمثيل الـ 32 بت، حساب أقنعة الشبكة والتحديات الذكية مع تصحيح فوري.
                </p>
            </div>
            <a href="subnet_simulator.php" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-flask"></i> فتح مختبر CIDR
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #8B5CF6; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">حاسبة تقسيم الشبكات VLSM</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    توزيع عناوين IP للأقسام تصاعدياً بلا هدر مع خريطة بصرية للذاكرة.
                </p>
            </div>
            <a href="vlsm_calculator.php" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(139, 92, 246, 0.3);">
                <i class="fa-solid fa-calculator"></i> فتح حاسبة VLSM
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #0D9488; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(13, 148, 136, 0.1); color: #0D9488;">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">موسوعة أوامر سيسكو Hub</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    أكثر من 60 أمراً مصنفاً حسب CCNA مع الصيغة والشرح والنسخ الفوري.
                </p>
            </div>
            <a href="cisco_cheatsheet.php" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(13, 148, 136, 0.3);">
                <i class="fa-solid fa-search"></i> تصفح الأوامر
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #F59E0B; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">محاكي مسار الحزم الحي</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    شاهد تدفق حزم Ping عبر الراوترات والمبدلات مع محاكاة انقطاع الكابلات.
                </p>
            </div>
            <a href="network_topology.php" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(245, 158, 11, 0.3);">
                <i class="fa-solid fa-play"></i> فتح طوبولوجيا الحزم
            </a>
        </div>

        <div class="lab-card-enhanced">
            <div style="border-top: 3px solid #6366F1; position: absolute; top: 0; left: 0; right: 0; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            <div>
                <div class="lab-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366F1;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <strong style="color: var(--text-heading); font-size: 0.98rem; display: block; margin-bottom: 6px;">محلل طبقات OSI السبع</strong>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6;">
                    محاكاة لتغليف وفك تغليف الحزم من Application إلى Bits.
                </p>
            </div>
            <a href="osi_explorer.php" class="btn btn-secondary btn-sm" style="width: 100%; border-color: rgba(99, 102, 241, 0.3);">
                <i class="fa-solid fa-layer-group"></i> فحص الطبقات
            </a>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- المسار التكيفي المعتمد للمستويات الأربعة                                  -->
<!-- ========================================================================= -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 1.35rem; color: var(--text-heading); margin-bottom: 2px;">
            <i class="fa-solid fa-route" style="color: var(--brand-primary); margin-left: 8px;"></i>
            المسار التكيفي المعتمد للمستويات الأربعة
        </h2>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">
            تدرج تدريبي منظم لا يسمح بالانتقال إلا بعد إثبات الإتقان.
        </p>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 35px;">
    <?php 
    $level_meta_map = [
        1 => [
            'img' => '../assets/img/diag-osi-model.jpg',
            'badge' => 'OSI & Physical Media',
            'caption' => 'نموذج OSI ومنافذ سيسكو الفيزيائية'
        ],
        2 => [
            'img' => '../assets/img/diag-subnetting-cidr.svg',
            'badge' => 'Subnetting & VLANs',
            'caption' => 'تقسيم الشبكات وحساب CIDR وعزل VLAN'
        ],
        3 => [
            'img' => '../assets/img/diag-network-topology.jpg',
            'badge' => 'OSPF Routing & STP',
            'caption' => 'طوبولوجيا التوجيه المؤسسية وشجرة STP'
        ],
        4 => [
            'img' => '../assets/img/diag-firewall-dmz.svg',
            'badge' => 'Firewall & IPsec VPN',
            'caption' => 'معمارية أمن المحيط DMZ وأنفاق التشفير'
        ],
    ];

    foreach ($levels as $lvl): 
        $chk_pass = $pdo->prepare("SELECT score FROM exam_results WHERE user_id = ? AND level_id = ? ORDER BY score DESC LIMIT 1");
        $chk_pass->execute([$user_id, $lvl['id']]);
        $best_score = $chk_pass->fetchColumn();

        $is_passed = ($best_score !== false && $best_score >= 75);
        $is_current = ((int)$lvl['id'] === $current_level_id);
        $is_locked = ((int)$lvl['id'] > $current_level_id && !$is_passed);
        
        $card_class = 'level-card-enhanced';
        if ($is_passed) $card_class .= ' is-passed';
        elseif ($is_current) $card_class .= ' is-current';
        elseif ($is_locked) $card_class .= ' is-locked';

        $diag_info = $level_meta_map[(int)$lvl['level_order']] ?? [
            'img' => '../assets/img/diag-network-topology.jpg',
            'badge' => 'Cisco CCNA Track',
            'caption' => 'مخطط توبولوجيا المسار'
        ];
    ?>
        <div class="<?php echo $card_class; ?>">
            
            <!-- صورة الغلاف التوضيحية لمخطط المستوى -->
            <div style="height: 125px; border-radius: var(--radius-md); overflow: hidden; margin-bottom: 14px; position: relative; background: #0f172a; border: 1px solid var(--border-color);">
                <img src="<?php echo $diag_info['img']; ?>" alt="<?php echo $diag_info['caption']; ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.92; transition: transform 0.4s ease;">
                <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.15) 0%, rgba(15,23,42,0.88) 100%);"></div>
                <div style="position: absolute; bottom: 8px; right: 10px; left: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.74rem; color: #E2E8F0; font-weight: 700; text-shadow: 0 1px 3px rgba(0,0,0,0.8);">
                        <i class="fa-solid fa-diagram-project" style="color: #818CF8;"></i> <?php echo $diag_info['caption']; ?>
                    </span>
                    <span class="badge" style="background: rgba(255,255,255,0.18); color: #FFFFFF; font-size: 0.68rem; backdrop-filter: blur(4px);">
                        <?php echo $diag_info['badge']; ?>
                    </span>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span class="badge <?php echo $is_passed ? 'badge-success' : ($is_current ? 'badge-primary' : 'badge-secondary'); ?>">
                    المستوى #<?php echo $lvl['level_order']; ?>
                </span>

                <?php if ($is_passed): ?>
                    <span class="badge badge-success">
                        <i class="fa-solid fa-circle-check"></i> مجتاز (<?php echo $best_score; ?>%)
                    </span>
                <?php elseif ($is_current): ?>
                    <span class="badge badge-primary">
                        <i class="fa-solid fa-spinner fa-spin"></i> قيد الدراسة
                    </span>
                <?php else: ?>
                    <span class="badge" style="background: rgba(208, 213, 229, 0.3); color: var(--text-dim);">
                        <i class="fa-solid fa-lock"></i> يتطلب اجتياز السابق
                    </span>
                <?php endif; ?>
            </div>

            <h3 style="font-size: 1.15rem; color: var(--text-heading); margin-bottom: 8px;">
                <?php echo clean_output($lvl['level_name']); ?>
            </h3>

            <p style="font-size: 0.9rem; color: var(--text-body); margin-bottom: 18px; line-height: 1.6;">
                <?php echo clean_output($lvl['description']); ?>
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                <a href="study_module.php?level_id=<?php echo $lvl['id']; ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-book-open"></i> المحتوى العلمي
                </a>

                <?php if (!$is_locked): ?>
                    <a href="exam.php?level_id=<?php echo $lvl['id']; ?>" class="btn <?php echo $is_passed ? 'btn-outline' : 'btn-primary'; ?> btn-sm">
                        <i class="fa-solid fa-file-signature"></i> <?php echo $is_passed ? 'إعادة الاختبار' : 'بدء الاختبار'; ?>
                    </a>
                <?php else: ?>
                    <span style="font-size: 0.84rem; color: var(--text-dim);">
                        <i class="fa-solid fa-lock"></i> مقفل
                    </span>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<!-- ========================================================================= -->
<!-- مسار الكورسات المرئية المقترح بالذكاء الاصطناعي (YouTube CCNA Hub)       -->
<!-- ========================================================================= -->
<?php 
render_adaptive_video_hub(
    $pdo, 
    $current_level_id, 
    null, 
    "مسارك المرئي المقترح بالذكاء الاصطناعي (YouTube Adaptive Hub)", 
    "كورسات وفيديوهات CCNA المعتمدة لمستواك الحالي — شاهد الشروحات مباشرة أو تصفح سلاسل يوتيوب الكاملة:"
); 
?>

<!-- ========================================================================= -->
<!-- سجل نتائج المحاولات                                                      -->
<!-- ========================================================================= -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 1.35rem; color: var(--text-heading); margin-bottom: 2px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--brand-primary); margin-left: 8px;"></i>
            سجل محاولات الاختبارات والتفسيرات الهندسية
        </h2>
        <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">
            توثيق كامل لكل محاولة مع إمكانية مراجعة شرح كل سؤال.
        </p>
    </div>
</div>

<div class="academic-card" style="padding: 0; overflow: hidden; margin-bottom: 35px;">
    <?php if (empty($recent_attempts)): ?>
        <div style="text-align: center; padding: 48px 24px; color: var(--text-muted);">
            <div style="width: 60px; height: 60px; border-radius: var(--radius-full); background: rgba(91, 79, 232, 0.06); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 24px; color: var(--text-dim);">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <h4 style="color: var(--text-heading); margin-bottom: 6px;">لا توجد محاولات مسجلة بعد</h4>
            <p style="font-size: 0.9rem; margin: 0;">ابدأ بدراسة مادة المستوى الأول أو خض الاختبار التشخيصي لتحديد نقطة انطلاقك.</p>
        </div>
    <?php else: ?>
        <table class="academic-table" style="margin: 0; box-shadow: none; border: none;">
            <thead>
                <tr>
                    <th>توقيت المحاولة</th>
                    <th>الاختبار / المستوى</th>
                    <th>الدرجة المحققة</th>
                    <th>الإجابات الصحيحة</th>
                    <th>حالة الاعتماد</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_attempts as $att): 
                    $type_title = $att['attempt_type'] === 'placement' ? 'اختبار تحديد المستوى' : ($att['level_name'] ?? 'اختبار مستوى');
                    $is_att_pass = (bool)$att['passed'];
                ?>
                    <tr>
                        <td class="code-font" style="font-size: 0.86rem; color: var(--text-muted);">
                            <?php echo date('Y-m-d H:i', strtotime($att['created_at'])); ?>
                        </td>
                        <td>
                            <strong style="color: var(--text-heading);"><?php echo clean_output($type_title); ?></strong>
                        </td>
                        <td>
                            <span class="code-font" style="font-size: 1.1rem; font-weight: 800; color: <?php echo $is_att_pass ? 'var(--status-success-text)' : 'var(--status-danger-text)'; ?>;">
                                <?php echo $att['score']; ?>%
                            </span>
                        </td>
                        <td class="code-font">
                            <?php echo $att['correct_answers']; ?> / <?php echo $att['total_questions']; ?>
                        </td>
                        <td>
                            <?php if ($att['status'] === 'timed_out'): ?>
                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> نفد الوقت</span>
                            <?php elseif ($is_att_pass): ?>
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> اجتياز معتمد</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> لم يجتز</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($att['attempt_type'] === 'placement'): ?>
                                <a href="placement_result.php?attempt_token=<?php echo urlencode($att['attempt_token']); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i> مراجعة
                                </a>
                            <?php else: ?>
                                <a href="process_exam.php?attempt_token=<?php echo urlencode($att['attempt_token']); ?>" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-magnifying-glass"></i> الشرح
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<!-- =========================================================================
     بلوكة التوصيات الذكية بناءً على المستوى الحالي
     ========================================================================= -->
<style>
.smart-rec-card {
    background:var(--bg-surface-card); border:1px solid rgba(208,213,229,0.45);
    border-radius:var(--radius-lg); overflow:hidden;
    box-shadow:var(--shadow-card); transition:all 0.22s ease; cursor:pointer;
    display:flex; gap:0;
}
.smart-rec-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-card-hover); }
.src-thumb { width:88px; min-width:88px; height:88px; object-fit:cover; background:linear-gradient(135deg,#1A1A3E,#2D2B6F); display:flex; align-items:center; justify-content:center; font-size:28px; color:rgba(255,255,255,0.2); flex-shrink:0; }
.src-body { padding:12px 14px; flex:1; overflow:hidden; }
.src-title { font-weight:800; font-size:0.88rem; color:var(--text-heading); margin-bottom:5px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.4; }
.src-meta { display:flex; gap:5px; flex-wrap:wrap; align-items:center; }
.src-tag { padding:2px 7px; border-radius:20px; font-size:0.65rem; font-weight:700; border:1px solid transparent; }
.src-tag-video{background:rgba(239,68,68,0.1);color:#EF4444;border-color:rgba(239,68,68,0.25);}
.src-tag-pdf{background:rgba(245,158,11,0.1);color:#D97706;border-color:rgba(245,158,11,0.25);}
.src-tag-article{background:rgba(16,185,129,0.1);color:#059669;border-color:rgba(16,185,129,0.25);}
.src-tag-interactive{background:rgba(91,79,232,0.1);color:#5B4FE8;border-color:rgba(91,79,232,0.25);}
.src-tag-quiz{background:rgba(6,182,212,0.1);color:#0891B2;border-color:rgba(6,182,212,0.25);}
.src-tag-audio{background:rgba(168,85,247,0.1);color:#7C3AED;border-color:rgba(168,85,247,0.25);}
/* viewer */
.d-res-ov { position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:3000;display:flex;flex-direction:column; }
.d-res-top { background:rgba(15,23,42,.98);padding:0 20px;height:54px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(91,79,232,.25);flex-shrink:0; }
.d-res-body { flex:1;overflow:hidden; }
.d-res-frame { width:100%;height:100%;border:none;background:#0F172A; }
</style>

<?php
$res_total = (int)($res_progress['total'] ?? 0);
$res_done  = (int)($res_progress['completed'] ?? 0);
$res_pct   = $res_total > 0 ? round(($res_done / $res_total) * 100) : 0;
$ti = ['video'=>'🎬','pdf'=>'📄','article'=>'📰','interactive'=>'🖥️','quiz'=>'✍️','audio'=>'🎧'];
$tl = ['video'=>'فيديو','pdf'=>'PDF','article'=>'مقالة','interactive'=>'تفاعلي','quiz'=>'اختبار','audio'=>'صوتي'];
?>
<?php if (!empty($smart_recs)): ?>
<div style="margin-bottom:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="font-size:1.18rem;font-weight:900;color:var(--text-heading);margin-bottom:3px;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--brand-primary);"></i>
                التوصيات الذكية لمستواك الحالي
            </h2>
            <p style="font-size:0.82rem;color:var(--text-muted);margin:0;">
                <?php if ($res_total > 0): ?>
                أكملت <strong><?= $res_done ?></strong> من <strong><?= $res_total ?></strong> مصادر (<?= $res_pct ?>%) — هذه المصادر اختارها النظام لك تلقائياً
                <?php else: ?>
                مصادر تعليمية مُختارة تلقائياً لمستواك الحالي
                <?php endif; ?>
            </p>
        </div>
        <a href="learning_resources.php?level=<?= $current_level_id ?>" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-books"></i> عرض كل الموارد
        </a>
    </div>

    <?php if ($res_total > 0): ?>
    <div style="height:5px;background:rgba(208,213,229,0.3);border-radius:10px;overflow:hidden;margin-bottom:16px;">
        <div style="height:100%;width:<?= $res_pct ?>%;background:var(--brand-gradient);border-radius:10px;transition:width 0.6s ease;"></div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        <?php foreach ($smart_recs as $r): ?>
        <div class="smart-rec-card" onclick='openDashRes(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)'>
            <?php if (!empty($r['thumbnail_url'])): ?>
                <img src="<?= htmlspecialchars($r['thumbnail_url']) ?>" class="src-thumb" alt=""
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="src-thumb" style="display:none;"><?= $ti[$r['resource_type']] ?? '📚' ?></div>
            <?php else: ?>
                <div class="src-thumb"><?= $ti[$r['resource_type']] ?? '📚' ?></div>
            <?php endif; ?>
            <div class="src-body">
                <div class="src-title"><?= clean_output($r['title']) ?></div>
                <div class="src-meta">
                    <span class="src-tag src-tag-<?= $r['resource_type'] ?>"><?= $ti[$r['resource_type']] ?> <?= $tl[$r['resource_type']] ?></span>
                    <?php if ($r['is_featured']): ?><span class="src-tag" style="background:rgba(245,158,11,.1);color:#D97706;border-color:rgba(245,158,11,.25);">⭐ مميز</span><?php endif; ?>
                    <?php if (!empty($r['duration_label'])): ?><span style="font-size:.68rem;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> <?= clean_output($r['duration_label']) ?></span><?php endif; ?>
                    <?php if ($r['seen']): ?><span class="src-tag" style="background:rgba(245,158,11,.08);color:#D97706;border-color:rgba(245,158,11,.2);">شاهدت</span><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Viewer للداشبورد -->
<div id="d-res-ov" class="d-res-ov" style="display:none;">
    <div class="d-res-top">
        <div style="display:flex;align-items:center;gap:10px;overflow:hidden;">
            <span id="drb" style="padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:800;white-space:nowrap;background:rgba(91,79,232,.15);color:var(--brand-primary);border:1px solid rgba(91,79,232,.25);"></span>
            <span id="drt" style="font-weight:700;color:#FFF;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px;"></span>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a id="dre" href="#" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> فتح خارجي</a>
            <button onclick="closeDashRes()" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> إغلاق</button>
        </div>
    </div>
    <div class="d-res-body" id="drb2"></div>
</div>

<script>
const _TI = <?= json_encode($ti) ?>, _TL = <?= json_encode($tl) ?>;
function openDashRes(r){
    document.getElementById('drt').textContent = r.title;
    document.getElementById('drb').textContent = (_TI[r.resource_type]||'📚')+' '+(_TL[r.resource_type]||'');
    document.getElementById('dre').href = r.url;
    const b = document.getElementById('drb2'); b.innerHTML='';
    if(r.resource_type==='video'){
        const ym=r.url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/);
        b.innerHTML='<iframe class="d-res-frame" src="'+(ym?'https://www.youtube.com/embed/'+ym[1]+'?autoplay=1&rel=0':r.url)+'" allow="autoplay;fullscreen" allowfullscreen></iframe>';
    } else if(r.resource_type==='pdf'){
        const pv=r.url.startsWith('http')?'https://docs.google.com/viewer?url='+encodeURIComponent(r.url)+'&embedded=true':r.url;
        b.innerHTML='<iframe class="d-res-frame" src="'+pv+'"></iframe>';
    } else {
        b.innerHTML='<iframe class="d-res-frame" src="'+r.url+'" sandbox="allow-scripts allow-same-origin allow-forms allow-popups"></iframe>';
    }
    document.getElementById('d-res-ov').style.display='flex';
    document.body.style.overflow='hidden';
    // تسجيل المشاهدة AJAX
    const fd=new FormData();
    fd.append('mark_viewed','1');fd.append('resource_id',r.id);fd.append('is_completed','0');
    fd.append('csrf_token','<?= get_csrf_token() ?>');
    fetch('learning_resources.php',{method:'POST',body:fd}).catch(()=>{});
}
function closeDashRes(){
    document.getElementById('d-res-ov').style.display='none';
    document.getElementById('drb2').innerHTML='';
    document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeDashRes();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
