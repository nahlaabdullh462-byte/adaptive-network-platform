<?php
$pageTitle = "مركز القيادة والتحكم الأكاديمي للمشرف";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_auth('admin');

// 1. الإحصائيات العامة الشاملة
$total_students = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_questions = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$total_exams = (int)$pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'completed'")->fetchColumn();
$passed_exams = (int)$pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'completed' AND passed = 1")->fetchColumn();

// دعم جدول exam_results إن كانت المحاولات في الجدول القديم
if ($total_exams === 0) {
    $total_exams = (int)$pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn();
    $passed_exams = (int)$pdo->query("SELECT COUNT(*) FROM exam_results WHERE score >= 75")->fetchColumn();
}

$overall_pass_rate = ($total_exams > 0) ? round(($passed_exams / $total_exams) * 100) : 0;
$total_labs = (int)$pdo->query("SELECT COUNT(*) FROM lab_attempts")->fetchColumn();

// 2. تقرير وتوزيع الطلاب حسب المستويات الأربعة
$level_reports = $pdo->query("
    SELECT l.id, l.level_name, l.level_order, l.passing_score,
           COUNT(u.id) as student_count,
           COALESCE(ROUND(AVG(a.score)), 0) as avg_score,
           COUNT(a.id) as total_attempts,
           SUM(CASE WHEN a.passed = 1 THEN 1 ELSE 0 END) as passed_count
    FROM levels l
    LEFT JOIN users u ON u.current_level_id = l.id AND u.role = 'student'
    LEFT JOIN exam_attempts a ON a.level_id = l.id AND a.status = 'completed'
    GROUP BY l.id, l.level_name, l.level_order, l.passing_score
    ORDER BY l.level_order ASC
")->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_student_counts = [];
$chart_avg_scores = [];
$chart_pass_rates = [];

foreach ($level_reports as $r) {
    $chart_labels[] = $r['level_name'];
    $chart_student_counts[] = (int)$r['student_count'];
    $chart_avg_scores[] = (int)$r['avg_score'];
    $att = (int)$r['total_attempts'];
    $chart_pass_rates[] = ($att > 0) ? round(($r['passed_count'] / $att) * 100) : 0;
}

// 3. تحليل مصفوفة الفجوات المعرفية التكيفية (Adaptive Knowledge Gaps)
$weak_topics = $pdo->query("
    SELECT 
        t.id as topic_id,
        t.topic_name,
        l.id as level_id,
        l.level_name,
        COUNT(aq.id) as total_answers,
        SUM(CASE WHEN aq.is_correct = 0 THEN 1 ELSE 0 END) as error_count,
        ROUND((SUM(CASE WHEN aq.is_correct = 0 THEN 1 ELSE 0 END) / COUNT(aq.id)) * 100) as error_rate
    FROM attempt_questions aq
    JOIN questions q ON aq.question_id = q.id
    JOIN topics t ON q.topic_id = t.id
    JOIN levels l ON q.level_id = l.id
    GROUP BY t.id, t.topic_name, l.id, l.level_name
    HAVING error_count > 0
    ORDER BY error_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$top_weak_topic_name = !empty($weak_topics) ? $weak_topics[0]['topic_name'] : 'لا توجد فجوات حرجة';

// 4. سجل أحدث المحاولات المنجزة
$recent_attempts = $pdo->query("
    SELECT a.*, u.full_name, u.email, l.level_name 
    FROM exam_attempts a 
    JOIN users u ON a.user_id = u.id 
    LEFT JOIN levels l ON a.level_id = l.id 
    ORDER BY a.id DESC 
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* أنماط مخصصة للوحة القيادة الإدارية الفاخرة */
.admin-command-hud {
    background: linear-gradient(135deg, rgba(26, 26, 62, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%);
    border: 1px solid rgba(91, 79, 232, 0.25);
    border-radius: var(--radius-xl);
    padding: 30px 32px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.35);
}
.admin-command-hud::before {
    content: '';
    position: absolute;
    top: -40%;
    left: -20%;
    width: 60%;
    height: 180%;
    background: radial-gradient(circle, rgba(91, 79, 232, 0.18) 0%, transparent 65%);
    pointer-events: none;
}
.admin-command-hud::after {
    content: '';
    position: absolute;
    bottom: -40%;
    right: -10%;
    width: 50%;
    height: 160%;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, transparent 65%);
    pointer-events: none;
}
.kpi-card-v2 {
    background: var(--bg-surface-card);
    border: 1px solid rgba(208, 213, 229, 0.65);
    border-radius: var(--radius-lg);
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    backdrop-filter: blur(16px);
}
.kpi-card-v2:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: rgba(91, 79, 232, 0.35);
}
.kpi-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #FFF;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.filter-pill-btn {
    background: transparent;
    border: 1px solid var(--border-main);
    color: var(--text-muted);
    padding: 6px 14px;
    border-radius: var(--radius-full);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.filter-pill-btn.active, .filter-pill-btn:hover {
    background: var(--brand-primary);
    color: #FFF;
    border-color: var(--brand-primary);
    box-shadow: 0 4px 12px rgba(91, 79, 232, 0.25);
}
@media print {
    .app-sidebar, .app-topbar, .app-footer, .no-print {
        display: none !important;
    }
    .app-workspace {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<!-- ========================================================================= -->
<!-- 1. شريط القيادة التنفيذي الأعلى (Executive Command HUD)                     -->
<!-- ========================================================================= -->
<div class="admin-command-hud">
    <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
        <div style="max-width: 680px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(91, 79, 232, 0.2); border: 1px solid rgba(165, 180, 252, 0.3); padding: 5px 16px; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 700; color: #A5B4FC; margin-bottom: 14px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 8px #10B981;"></span>
                <i class="fa-solid fa-server"></i>
                <span>محرك الرقابة الأكاديمية والذكاء التكيفي: نشط ومباشر</span>
            </div>

            <h1 style="color: #FFFFFF; font-size: 1.9rem; font-weight: 900; margin-bottom: 8px; font-family: var(--font-heading); letter-spacing: -0.02em;">
                مركز القيادة والتحكم الأكاديمي <span style="color: #38BDF8;">NetLearn Pro</span>
            </h1>

            <p style="color: rgba(255, 255, 255, 0.72); font-size: 0.95rem; line-height: 1.65; margin-bottom: 0;">
                متابعة آنية لمسارات تقدم الطلاب، وتحليل فجوات الإتقان في بروتوكولات الشبكات، وتوزيع أداء الأسئلة المتوافقة مع معايير <strong>Cisco CCNA 200-301</strong>.
            </p>
        </div>

        <!-- أزرار الإجراءات السريعة في الهيدر -->
        <div class="no-print" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="manage_questions.php" class="btn btn-primary" style="font-weight: 700; padding: 11px 22px; border-radius: 10px; box-shadow: 0 6px 20px rgba(91, 79, 232, 0.35);">
                <i class="fa-solid fa-database"></i> بنك الأسئلة (<?php echo $total_questions; ?>)
            </a>
            <a href="manage_students.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.08); color: #FFF; border-color: rgba(255,255,255,0.18); font-weight: 700; padding: 11px 20px; border-radius: 10px;">
                <i class="fa-solid fa-users"></i> إدارة الطلاب (<?php echo $total_students; ?>)
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="background: rgba(255,255,255,0.08); color: #FFF; border-color: rgba(255,255,255,0.18); font-weight: 700; padding: 11px 18px; border-radius: 10px;" title="طباعة التقرير التنفيذي">
                <i class="fa-solid fa-print"></i> طباعة التقرير
            </button>
        </div>
    </div>

    <!-- شريط معلومات النظام التشغيلية الصغير -->
    <div style="position: relative; z-index: 2; margin-top: 24px; padding-top: 18px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; gap: 24px; flex-wrap: wrap; font-size: 0.8rem; color: rgba(255, 255, 255, 0.55);">
        <div><i class="fa-solid fa-microchip" style="color: #38BDF8;"></i> الخادم: <strong>Apache 2.4 / PHP 7.4.9</strong></div>
        <div><i class="fa-solid fa-database" style="color: #818CF8;"></i> قاعدة البيانات: <strong>MySQL MariaDB</strong></div>
        <div><i class="fa-solid fa-shield-halved" style="color: #10B981;"></i> أمان الجلسات: <strong>CSRF & Session Regenerate نشط</strong></div>
        <div><i class="fa-solid fa-clock" style="color: #F59E0B;"></i> توقيت المنصة: <strong><?php echo date('Y-m-d H:i'); ?></strong></div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. شبكة بطاقات الأداء الست (6 Key Performance Indicator Cards)           -->
<!-- ========================================================================= -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <!-- كرت 1: الطلاب المسجلين -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">الطلاب المسجلين</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #5B4FE8, #818CF8);">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>
        <div style="font-size: 2.1rem; font-weight: 900; color: var(--text-heading); font-family: var(--font-mono); line-height: 1;">
            <?php echo $total_students; ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--brand-primary); font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-circle-check"></i> حسابات أكاديمية معتمدة
        </div>
    </div>

    <!-- كرت 2: بنك الأسئلة المعتمدة -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">بنك الأسئلة التكيفي</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #06B6D4, #3B82F6);">
                <i class="fa-solid fa-database"></i>
            </div>
        </div>
        <div style="font-size: 2.1rem; font-weight: 900; color: var(--text-heading); font-family: var(--font-mono); line-height: 1;">
            <?php echo $total_questions; ?>
        </div>
        <div style="font-size: 0.78rem; color: #0284C7; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-layer-group"></i> عبر المستويات الأربعة
        </div>
    </div>

    <!-- كرت 3: جلسات الاختبار المنجزة -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">الاختبارات المنجزة</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
        </div>
        <div style="font-size: 2.1rem; font-weight: 900; color: var(--text-heading); font-family: var(--font-mono); line-height: 1;">
            <?php echo $total_exams; ?>
        </div>
        <div style="font-size: 0.78rem; color: #D97706; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-bolt"></i> محاولات مقيمة بالخادم
        </div>
    </div>

    <!-- كرت 4: نسبة الاجتياز العامة -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">نسبة الاجتياز العامة</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #10B981, #059669);">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
        <div style="font-size: 2.1rem; font-weight: 900; color: #059669; font-family: var(--font-mono); line-height: 1;">
            <?php echo $overall_pass_rate; ?>%
        </div>
        <div style="font-size: 0.78rem; color: #059669; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-shield-halved"></i> معيار الاجتياز الرسمي >= 75%
        </div>
    </div>

    <!-- كرت 5: تمارين المعامل والمحاكاة -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">محاكاة المختبرات الحية</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
                <i class="fa-solid fa-terminal"></i>
            </div>
        </div>
        <div style="font-size: 2.1rem; font-weight: 900; color: var(--text-heading); font-family: var(--font-mono); line-height: 1;">
            <?php echo $total_labs; ?>
        </div>
        <div style="font-size: 0.78rem; color: #7C3AED; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
            <i class="fa-solid fa-flask"></i> Cisco CLI & Subnetting
        </div>
    </div>

    <!-- كرت 6: تنبيه الفجوة المعرفية التكيفية -->
    <div class="kpi-card-v2">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.86rem; color: var(--text-muted); font-weight: 700;">أكثر موضوع يحتاج تعزيزاً</span>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #F43F5E, #E11D48);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
        <div style="font-size: 1.15rem; font-weight: 800; color: #E11D48; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($top_weak_topic_name); ?>">
            <?php echo htmlspecialchars($top_weak_topic_name); ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-top: 8px;">
            توصية المحرك الذكي للمشرف
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- 3. لوحة الرسوم البيانية التفاعلية المزدوجة (Dual Analytics Charts)          -->
<!-- ========================================================================= -->
<div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 24px; margin-bottom: 30px;">
    
    <!-- الرسم البياني 1: توزيع الطلاب حسب المستوى -->
    <div class="academic-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-chart-pie" style="color: var(--brand-primary);"></i> توزيع الطلاب على المسار التكيفي
                </h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0;">نسبة تموضع الطلاب الحالية عبر المستويات الأربعة.</p>
            </div>
            <span class="badge badge-primary" style="font-size: 0.75rem;">مباشر</span>
        </div>

        <div style="height: 270px; position: relative;">
            <canvas id="levelsDoughnutChart"></canvas>
        </div>

        <!-- أشرطة نسب مصغرة أسفل الرسم البياني -->
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 8px;">
            <?php 
            $lvl_colors = ['#5B4FE8', '#06B6D4', '#F59E0B', '#10B981'];
            foreach ($level_reports as $idx => $lr): 
                $pct = ($total_students > 0) ? round(($lr['student_count'] / $total_students) * 100) : 0;
                $col = $lvl_colors[$idx % count($lvl_colors)];
            ?>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.84rem;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 3px; background: <?php echo $col; ?>;"></span>
                        <span style="font-weight: 600; color: var(--text-body);"><?php echo htmlspecialchars($lr['level_name']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-family: var(--font-mono); font-weight: 700; color: var(--text-heading);"><?php echo $lr['student_count']; ?> طالب</span>
                        <span style="font-family: var(--font-mono); font-size: 0.78rem; color: var(--text-muted); width: 38px; text-align: left;"><?php echo $pct; ?>%</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- الرسم البياني 2: مقارنة متوسط الدرجات ومعدلات الاجتياز -->
    <div class="academic-card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-heading); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-chart-column" style="color: var(--brand-secondary);"></i> مؤشرات الإتقان ومتوسط الدرجات
                </h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0;">مقارنة متوسط درجات الاختبارات ومعدلات الاجتياز لكل مستوى.</p>
            </div>
            <span class="badge" style="background: rgba(6, 182, 212, 0.1); color: var(--brand-secondary); border: 1px solid rgba(6, 182, 212, 0.25); font-size: 0.75rem;">CCNA Analytics</span>
        </div>

        <div style="height: 310px; position: relative;">
            <canvas id="levelsBarChart"></canvas>
        </div>

        <div style="margin-top: 16px; padding: 12px 16px; background: rgba(91, 79, 232, 0.05); border-radius: var(--radius-sm); border: 1px solid rgba(91, 79, 232, 0.12); font-size: 0.82rem; color: var(--text-body); display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-info" style="color: var(--brand-primary); font-size: 16px;"></i>
            <span>توضح هذه المؤشرات مدى صعوبة كل مستوى لمساعدة المشرف على تعديل التوصيات التعليمية أو دعم الطلاب في المفاهيم المعقدة.</span>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- 4. مصفوفة فجوات المعرفة التكيفية (Adaptive Knowledge Gap Matrix)            -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 24px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(244, 63, 94, 0.1); color: #E11D48; padding: 3px 12px; border-radius: var(--radius-full); font-size: 0.78rem; font-weight: 700; margin-bottom: 6px;">
                <i class="fa-solid fa-brain"></i> الذكاء الاصطناعي التكيفي (AI Knowledge Tracing)
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-heading); margin-bottom: 4px;">
                مصفوفة فجوات المعرفة والأخطاء الشائعة بين الطلاب
            </h3>
            <p style="font-size: 0.86rem; color: var(--text-muted); margin-bottom: 0;">
                تحليل تلقائي مستخرج من سجل إجابات الطلاب في <code>attempt_questions</code> لتحديد المواضيع التي واجهوا فيها صعوبة متكررة.
            </p>
        </div>
        <a href="manage_questions.php" class="btn btn-secondary btn-sm" style="font-weight: 700;">
            <i class="fa-solid fa-plus"></i> إضافة أسئلة تقوية
        </a>
    </div>

    <?php if (empty($weak_topics)): ?>
        <div style="text-align: center; padding: 40px 20px; background: rgba(16, 185, 129, 0.05); border-radius: var(--radius-md); border: 1px dashed rgba(16, 185, 129, 0.3);">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #10B981; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 12px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h4 style="font-size: 1.1rem; color: #065F46; font-weight: 800; margin-bottom: 4px;">لا توجد فجوات معرفية حرجة مسجلة</h4>
            <p style="color: #047857; font-size: 0.88rem; margin: 0;">إجابات الطلاب حتى الآن تظهر استيعاباً متزناً أو لم تُسجل أخطاء كافية لتوليد تقرير فجوات.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="academic-table" style="box-shadow: none; margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 25%;">الموضوع الأكاديمي</th>
                        <th style="width: 20%;">المستوى التابع له</th>
                        <th style="width: 15%;">الأخطاء المسجلة</th>
                        <th style="width: 20%;">معدل الخطأ</th>
                        <th style="width: 20%;">التوصية التكيفية للمشرف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weak_topics as $wt): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--text-heading); font-size: 0.94rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-triangle-exclamation" style="color: #E11D48; font-size: 14px;"></i>
                                    <?php echo htmlspecialchars($wt['topic_name']); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(91, 79, 232, 0.1); color: var(--brand-primary); font-weight: 700;">
                                    <?php echo htmlspecialchars($wt['level_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-family: var(--font-mono); font-weight: 800; color: #E11D48;">
                                    <?php echo $wt['error_count']; ?>
                                </span>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">من أصل <?php echo $wt['total_answers']; ?> إجابة</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 8px; background: rgba(226, 232, 240, 0.8); border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo min(100, $wt['error_rate']); ?>%; height: 100%; background: linear-gradient(90deg, #F59E0B, #E11D48);"></div>
                                    </div>
                                    <span style="font-family: var(--font-mono); font-size: 0.84rem; font-weight: 800; color: #E11D48;">
                                        <?php echo $wt['error_rate']; ?>%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <a href="manage_questions.php?filter_level=<?php echo $wt['level_id']; ?>" class="btn btn-secondary btn-sm" style="font-size: 0.78rem; padding: 4px 10px;">
                                    <i class="fa-solid fa-magnifying-glass"></i> استعراض الأسئلة
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 5. سجل أحدث محاولات الطلاب المباشر (Live Student Activity Stream)          -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 24px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
        <div>
            <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-heading); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--brand-primary);"></i> سجل النشاط والتقييمات المباشرة
            </h3>
            <p style="font-size: 0.86rem; color: var(--text-muted); margin-bottom: 0;">أحدث جلسات الاختبارات ومحاولات الترقية المنفذة في المنصة.</p>
        </div>

        <!-- أزرار تصفية فورية في الجدول -->
        <div class="no-print" style="display: flex; gap: 8px;">
            <button type="button" class="filter-pill-btn active" onclick="filterRecentTable('all', this)">الكل</button>
            <button type="button" class="filter-pill-btn" onclick="filterRecentTable('passed', this)">الناجحون</button>
            <button type="button" class="filter-pill-btn" onclick="filterRecentTable('failed', this)">يحتاج مراجعة</button>
        </div>
    </div>

    <?php if (empty($recent_attempts)): ?>
        <p style="color: var(--text-muted); font-size: 0.92rem; text-align: center; padding: 30px 0; margin: 0;">لا توجد محاولات تقييم مسجلة حديثاً في النظام.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="academic-table" id="recent-attempts-table" style="box-shadow: none; margin: 0;">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>نوع الاختبار / المستوى</th>
                        <th>الدرجة والنسبة</th>
                        <th>الإجابات الصحيحة</th>
                        <th>حالة الاعتماد</th>
                        <th>التوقيت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_attempts as $at): 
                        $isPassed = (bool)$at['passed'];
                        $rowType = $isPassed ? 'passed' : 'failed';
                    ?>
                        <tr data-status="<?php echo $rowType; ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--brand-gradient); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;">
                                        <?php echo mb_substr($at['full_name'], 0, 1, 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <strong style="color: var(--text-heading); display: block; line-height: 1.2;"><?php echo htmlspecialchars($at['full_name']); ?></strong>
                                        <span style="font-size: 0.74rem; color: var(--text-muted); font-family: var(--font-mono);"><?php echo htmlspecialchars($at['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-body);">
                                    <?php echo htmlspecialchars($at['attempt_type'] === 'placement' ? 'اختبار تحديد المستوى' : ($at['level_name'] ?? 'اختبار مستوى')); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 60px; height: 6px; background: rgba(226, 232, 240, 0.8); border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?php echo min(100, $at['score']); ?>%; height: 100%; background: <?php echo $isPassed ? '#10B981' : '#F43F5E'; ?>;"></div>
                                    </div>
                                    <strong style="font-family: var(--font-mono); font-size: 0.95rem; color: <?php echo $isPassed ? '#059669' : '#E11D48'; ?>;">
                                        <?php echo $at['score']; ?>%
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: var(--font-mono); font-weight: 700; color: var(--text-body);">
                                    <?php echo $at['correct_answers']; ?>
                                </span>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">/ <?php echo $at['total_questions']; ?> سؤال</span>
                            </td>
                            <td>
                                <span class="badge <?php echo $isPassed ? 'badge-success' : 'badge-danger'; ?>" style="font-weight: 700;">
                                    <?php if ($isPassed): ?>
                                        <i class="fa-solid fa-check"></i> اجتياز معتمد
                                    <?php else: ?>
                                        <i class="fa-solid fa-xmark"></i> لم يجتز
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-muted);">
                                <?php echo date('Y-m-d H:i', strtotime($at['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 6. برمجة الرسوم البيانية التفاعلية Chart.js                               -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const primaryColor = '#5B4FE8';
    const secondaryColor = '#06B6D4';
    const accentColor = '#8B5CF6';
    const amberColor = '#F59E0B';
    const emeraldColor = '#10B981';

    // 1. رسم بياني لتوزيع الطلاب (Doughnut Chart)
    const ctxDoughnut = document.getElementById('levelsDoughnutChart');
    if (ctxDoughnut) {
        new Chart(ctxDoughnut.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_student_counts); ?>,
                    backgroundColor: [
                        primaryColor,
                        secondaryColor,
                        amberColor,
                        emeraldColor
                    ],
                    borderWidth: 3,
                    borderColor: '#FFFFFF',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            color: '#475569',
                            font: { family: 'Cairo', size: 12, weight: '600' },
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + context.parsed + ' طالب';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. رسم بياني لمقارنة متوسط الدرجات ومعدلات الاجتياز (Grouped Bar Chart)
    const ctxBar = document.getElementById('levelsBarChart');
    if (ctxBar) {
        new Chart(ctxBar.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [
                    {
                        label: 'متوسط الدرجات (%)',
                        data: <?php echo json_encode($chart_avg_scores); ?>,
                        backgroundColor: 'rgba(91, 79, 232, 0.85)',
                        borderColor: primaryColor,
                        borderWidth: 1.5,
                        borderRadius: 8,
                        barPercentage: 0.65
                    },
                    {
                        label: 'معدل الاجتياز (%)',
                        data: <?php echo json_encode($chart_pass_rates); ?>,
                        backgroundColor: 'rgba(6, 182, 212, 0.85)',
                        borderColor: secondaryColor,
                        borderWidth: 1.5,
                        borderRadius: 8,
                        barPercentage: 0.65
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            font: { family: 'JetBrains Mono', size: 11 },
                            callback: function(v) { return v + '%'; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Cairo', size: 11, weight: '600' } }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        rtl: true,
                        labels: {
                            font: { family: 'Cairo', size: 12, weight: '600' },
                            color: '#475569',
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
});

// تصفية أسطر جدول المحاولات المباشرة بالـ JavaScript
function filterRecentTable(status, btn) {
    document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('#recent-attempts-table tbody tr');
    rows.forEach(r => {
        if (status === 'all' || r.getAttribute('data-status') === status) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>