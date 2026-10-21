<?php
/**
 * اختبار تحديد المستوى التكيفي الذكي
 * Adaptive Placement Test — Binary Search Level Detection
 *
 * الخوارزمية:
 * 1. يبدأ من المستوى الأوسط (2)
 * 2. إذا أجاب صح >= 60% → يرتفع للمستوى الأعلى
 * 3. إذا أجاب خطأ >= 60% → يبقى في المستوى الأدنى
 * 4. كل جولة 5 أسئلة من المستوى الحالي
 * 5. ينتهي بعد 3 جولات أو إذا تحقق المستوى
 */
$pageTitle = "اختبار تحديد المستوى التكيفي الذكي";
require_once '../config/db.php';
require_once '../config/security.php';
require_auth('student');

$user_id = $_SESSION['user_id'];
$now = time();

// ======================================
// معالجة AJAX: تسجيل إجابة سؤال واحد
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_answer'])) {
    verify_csrf_token();
    header('Content-Type: application/json');

    $att_token = trim($_POST['attempt_token'] ?? '');
    $qid       = intval($_POST['question_id'] ?? 0);
    $chosen    = in_array($_POST['answer'] ?? '', ['A','B','C','D']) ? $_POST['answer'] : null;

    $att = $pdo->prepare("SELECT * FROM exam_attempts WHERE attempt_token=? AND user_id=? AND status='in_progress'")->execute([$att_token, $user_id]);
    $att = $pdo->prepare("SELECT * FROM exam_attempts WHERE attempt_token=? AND user_id=? AND status='in_progress'");
    $att->execute([$att_token, $user_id]);
    $att = $att->fetch();

    if (!$att || !$chosen) { echo json_encode(['error'=>'invalid']); exit; }

    $correct_q = $pdo->prepare("SELECT correct_option FROM questions WHERE id=?");
    $correct_q->execute([$qid]);
    $correct_opt = $correct_q->fetchColumn();
    $is_correct = ($chosen === $correct_opt) ? 1 : 0;

    $pdo->prepare("UPDATE attempt_questions SET selected_option=?, is_correct=?, answered_at=NOW() WHERE attempt_id=? AND question_id=?")
        ->execute([$chosen, $is_correct, $att['id'], $qid]);

    echo json_encode(['ok'=>true, 'correct'=>$is_correct, 'correct_option'=>$correct_opt]);
    exit;
}

// ======================================
// معالجة: إنهاء الاختبار وتحديد المستوى
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_placement'])) {
    verify_csrf_token();
    $att_token = trim($_POST['attempt_token'] ?? '');

    $att = $pdo->prepare("SELECT * FROM exam_attempts WHERE attempt_token=? AND user_id=?");
    $att->execute([$att_token, $user_id]);
    $att = $att->fetch();

    if (!$att) { header("Location: dashboard.php"); exit; }

    // حساب النتيجة لكل مستوى
    $results = $pdo->prepare("
        SELECT q.level_id, l.level_name, l.level_order,
            COUNT(*) as total,
            SUM(aq.is_correct) as correct
        FROM attempt_questions aq
        JOIN questions q ON aq.question_id = q.id
        JOIN levels l ON q.level_id = l.id
        WHERE aq.attempt_id = ?
        GROUP BY q.level_id, l.level_name, l.level_order
        ORDER BY l.level_order ASC
    ");
    $results->execute([$att['id']]);
    $level_scores = $results->fetchAll();

    // خوارزمية تحديد المستوى الذكية
    // المستوى الأعلى الذي يتجاوز 60% = مستوى الطالب
    $determined_level_id = 1; // افتراضي: المستوى الأول
    $determined_level_name = '';

    foreach ($level_scores as $ls) {
        $pct = $ls['total'] > 0 ? ($ls['correct'] / $ls['total']) * 100 : 0;
        if ($pct >= 60) {
            $determined_level_id = $ls['level_id'];
            $determined_level_name = $ls['level_name'];
        }
    }

    // الحصول على أعلى مستوى في المستويات بترتيب
    // المستوى المحدد = المستوى الأعلى الذي اجتازه
    // +1 للمستوى الذي يجب أن يبدأ منه التعلم
    $next_level_stmt = $pdo->prepare("SELECT id FROM levels WHERE level_order > (SELECT level_order FROM levels WHERE id=?) ORDER BY level_order ASC LIMIT 1");
    $next_level_stmt->execute([$determined_level_id]);
    $next_level_id = $next_level_stmt->fetchColumn();
    $start_level_id = $next_level_id ?: $determined_level_id;

    // تحديث مستوى الطالب
    $u_info = $pdo->prepare("SELECT current_level_id, highest_mastered_level_id FROM users WHERE id=?");
    $u_info->execute([$user_id]);
    $u_curr = $u_info->fetch();
    $curr_lid = (int)($u_curr['current_level_id'] ?? 1);
    $high_lid = (int)($u_curr['highest_mastered_level_id'] ?? 1);

    // لا نُنزل مستوى أحد - فقط نرفع
    $target = max($start_level_id, $curr_lid, $high_lid);

    $pdo->prepare("UPDATE users SET current_level_id=?, highest_mastered_level_id=GREATEST(highest_mastered_level_id,?) WHERE id=?")
        ->execute([$target, $target, $user_id]);

    // إغلاق المحاولة
    $total_q   = count($level_scores) > 0 ? array_sum(array_column($level_scores, 'total')) : 0;
    $total_cor = array_sum(array_column($level_scores, 'correct'));
    $score     = $total_q > 0 ? round(($total_cor / $total_q) * 100) : 0;

    $pdo->prepare("UPDATE exam_attempts SET status='completed', score=?, correct_answers=?, total_questions=?, passed=1, end_time=NOW() WHERE id=?")
        ->execute([$score, $total_cor, $total_q, $att['id']]);

    // إعادة توجيه مع المستوى المحدد
    header("Location: placement_result.php?level_id={$target}&score={$score}&determined={$determined_level_id}");
    exit;
}

// ======================================
// بدء أو استئناف اختبار تحديد المستوى
// ======================================
$active = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id=? AND attempt_type='placement' AND status='in_progress' ORDER BY id DESC LIMIT 1");
$active->execute([$user_id]);
$current_attempt = $active->fetch();

if ($current_attempt) {
    $elapsed = $now - strtotime($current_attempt['start_time']);
    if ($elapsed > ($current_attempt['max_duration_seconds'] + 45)) {
        $pdo->prepare("UPDATE exam_attempts SET status='timed_out', end_time=NOW() WHERE id=?")->execute([$current_attempt['id']]);
        $current_attempt = null;
    }
}

if (!$current_attempt) {
    // اختيار الأسئلة بطريقة تكيفية:
    // من كل مستوى: 4 أسئلة متنوعة الصعوبة (1 سهل، 2 متوسط، 1 صعب)
    $all_questions = [];
    $levels_all = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll();

    foreach ($levels_all as $lvl) {
        // سهل
        $s = $pdo->prepare("SELECT * FROM questions WHERE level_id=? AND difficulty='easy' ORDER BY RAND() LIMIT 1");
        $s->execute([$lvl['id']]); $easy = $s->fetchAll();
        // متوسط
        $s = $pdo->prepare("SELECT * FROM questions WHERE level_id=? AND difficulty='medium' ORDER BY RAND() LIMIT 2");
        $s->execute([$lvl['id']]); $med = $s->fetchAll();
        // صعب
        $s = $pdo->prepare("SELECT * FROM questions WHERE level_id=? AND difficulty='hard' ORDER BY RAND() LIMIT 1");
        $s->execute([$lvl['id']]); $hard = $s->fetchAll();

        $pool = array_merge($easy, $med, $hard);

        // fallback: إذا لم يوجد تنوع، خذ 4 عشوائية
        if (count($pool) < 2) {
            $s = $pdo->prepare("SELECT * FROM questions WHERE level_id=? ORDER BY RAND() LIMIT 4");
            $s->execute([$lvl['id']]); $pool = $s->fetchAll();
        }

        foreach ($pool as $q) {
            $q['_level_name']  = $lvl['level_name'];
            $q['_level_order'] = $lvl['level_order'];
            $all_questions[] = $q;
        }
    }

    if (empty($all_questions)) {
        require_once '../includes/header.php';
        echo "<div class='academic-card' style='text-align:center;padding:50px;max-width:600px;margin:40px auto;'>
            <div style='font-size:48px;margin-bottom:16px;'>🔧</div>
            <h3>بنك الأسئلة قيد التجهيز</h3>
            <p style='color:var(--text-muted);'>لا توجد أسئلة كافية بعد. تواصل مع المشرف.</p>
            <a href='dashboard.php' class='btn btn-primary' style='margin-top:16px;'>لوحة القيادة</a>
          </div>";
        require_once '../includes/footer.php';
        exit;
    }

    // ترتيب الأسئلة: مستوى 1 أولاً ثم 2 ثم 3 ثم 4
    usort($all_questions, function($a, $b) {
        $oa = isset($a['_level_order']) ? (int)$a['_level_order'] : 0;
        $ob = isset($b['_level_order']) ? (int)$b['_level_order'] : 0;
        if ($oa == $ob) return 0;
        return ($oa < $ob) ? -1 : 1;
    });

    $attempt_token   = bin2hex(random_bytes(32));
    $max_duration    = 30 * 60; // 30 دقيقة

    $ins = $pdo->prepare("INSERT INTO exam_attempts (user_id, level_id, attempt_type, start_time, max_duration_seconds, total_questions, status, attempt_token) VALUES (?,NULL,'placement',NOW(),?,?,'in_progress',?)");
    $ins->execute([$user_id, $max_duration, count($all_questions), $attempt_token]);
    $attempt_id = $pdo->lastInsertId();

    $ins_q = $pdo->prepare("INSERT INTO attempt_questions (attempt_id, question_id, question_order) VALUES (?,?,?)");
    foreach ($all_questions as $idx => $q) {
        $ins_q->execute([$attempt_id, $q['id'], $idx + 1]);
    }
    $remaining_seconds = $max_duration;
} else {
    $attempt_id      = $current_attempt['id'];
    $attempt_token   = $current_attempt['attempt_token'];
    $remaining_seconds = max(0, $current_attempt['max_duration_seconds'] - ($now - strtotime($current_attempt['start_time'])));

    $qs = $pdo->prepare("
        SELECT aq.*, q.*, l.level_name as _level_name, l.level_order as _level_order
        FROM attempt_questions aq
        JOIN questions q ON aq.question_id = q.id
        JOIN levels l ON q.level_id = l.id
        WHERE aq.attempt_id = ?
        ORDER BY aq.question_order ASC
    ");
    $qs->execute([$attempt_id]);
    $all_questions = $qs->fetchAll();
}

// إذا لم نملك الأسئلة بعد (جاءت من الإنشاء الجديد)، نجلبها
if (!isset($all_questions) || empty($all_questions)) {
    $qs = $pdo->prepare("
        SELECT aq.*, q.*, l.level_name as _level_name, l.level_order as _level_order
        FROM attempt_questions aq
        JOIN questions q ON aq.question_id = q.id
        JOIN levels l ON q.level_id = l.id
        WHERE aq.attempt_id = ?
        ORDER BY aq.question_order ASC
    ");
    $qs->execute([$attempt_id]);
    $all_questions = $qs->fetchAll();
}

// تجميع الأسئلة حسب المستوى للعرض التكيفي
$grouped_by_level = [];
foreach ($all_questions as $q) {
    $grouped_by_level[$q['_level_order']][] = $q;
}
ksort($grouped_by_level);

$examMode = true;
require_once '../includes/header.php';
?>
<style>
/* ===== اختبار التحديد التكيفي الذكي ===== */
.pt-shell { min-height:100vh; background:var(--bg-app); }

/* الشريط العلوي */
.pt-topbar {
    position:fixed; top:0; left:0; right:0; z-index:200;
    background:rgba(15,23,42,0.98); backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(91,79,232,0.3);
    padding:0 28px; height:62px;
    display:flex; align-items:center; justify-content:space-between; gap:14px;
    box-shadow:0 4px 20px rgba(0,0,0,0.35);
}
.pt-brand { display:flex; align-items:center; gap:10px; }
.pt-badge { background:linear-gradient(135deg,#8B5CF6,#06B6D4); color:#FFF; font-size:0.68rem; font-weight:800; padding:3px 10px; border-radius:20px; letter-spacing:0.06em; }
.pt-timer { display:inline-flex; align-items:center; gap:8px; padding:7px 16px; border-radius:30px; font-weight:800; font-family:var(--font-mono); font-size:1.05rem; }
.pt-timer.safe    { background:rgba(16,185,129,0.12);  color:#10B981; border:1px solid rgba(16,185,129,0.3); }
.pt-timer.warning { background:rgba(245,158,11,0.12);  color:#F59E0B; border:1px solid rgba(245,158,11,0.3); animation:tPulse 1.5s ease-in-out infinite; }
.pt-timer.danger  { background:rgba(244,63,94,0.12);   color:#F43F5E; border:1px solid rgba(244,63,94,0.3); animation:tPulse 0.8s ease-in-out infinite; }
@keyframes tPulse{0%,100%{opacity:1}50%{opacity:0.5}}

/* المحتوى */
.pt-content {
    padding-top:80px; padding-bottom:60px;
    max-width:760px; margin:0 auto;
    padding-left:20px; padding-right:20px;
}

/* شريط التقدم التكيفي */
.adaptive-progress {
    background:var(--bg-surface-card);
    border:1px solid rgba(208,213,229,0.4);
    border-radius:var(--radius-lg); padding:20px 24px;
    margin-bottom:24px; box-shadow:var(--shadow-card);
}
.level-sections { display:flex; gap:6px; margin-top:10px; }
.level-seg {
    flex:1; height:8px; border-radius:20px;
    background:rgba(208,213,229,0.3); overflow:hidden; position:relative;
    transition:all 0.4s ease;
}
.level-seg .seg-fill { height:100%; width:0%; border-radius:20px; transition:width 0.5s ease; }
.level-seg.done .seg-fill { background:linear-gradient(90deg,#10B981,#34D399); width:100%; }
.level-seg.active .seg-fill { background:linear-gradient(90deg,#5B4FE8,#06B6D4); animation:segProgress 0.8s ease forwards; }
.level-seg.future { opacity:0.35; }

/* بطاقة السؤال */
.pt-card {
    background:var(--bg-surface-card);
    border:1px solid rgba(208,213,229,0.4);
    border-radius:var(--radius-xl);
    padding:34px; margin-bottom:18px;
    box-shadow:var(--shadow-card);
    display:none;
    position:relative; overflow:hidden;
}
.pt-card::before {
    content:''; position:absolute; top:0; right:0; width:4px; height:100%;
}
.pt-card[data-level="1"]::before { background:linear-gradient(180deg,#10B981,#34D399); }
.pt-card[data-level="2"]::before { background:linear-gradient(180deg,#F59E0B,#FBBF24); }
.pt-card[data-level="3"]::before { background:linear-gradient(180deg,#5B4FE8,#818CF8); }
.pt-card[data-level="4"]::before { background:linear-gradient(180deg,#EF4444,#F87171); }
.pt-card.visible { display:block; animation:slideCard 0.38s ease forwards; }
@keyframes slideCard { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

/* شارة مستوى السؤال */
.q-level-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:20px; font-size:0.72rem; font-weight:800;
    margin-bottom:14px;
}
.q-level-badge.l1 { background:rgba(16,185,129,0.1); color:#10B981; border:1px solid rgba(16,185,129,0.25); }
.q-level-badge.l2 { background:rgba(245,158,11,0.1); color:#D97706; border:1px solid rgba(245,158,11,0.25); }
.q-level-badge.l3 { background:rgba(91,79,232,0.1);  color:#5B4FE8; border:1px solid rgba(91,79,232,0.25); }
.q-level-badge.l4 { background:rgba(239,68,68,0.1);  color:#EF4444; border:1px solid rgba(239,68,68,0.25); }

.q-text { font-size:1.12rem; color:var(--text-heading); font-weight:700; line-height:1.85; margin-bottom:24px; }

/* خيارات الإجابة */
.pt-option {
    display:flex; align-items:center; gap:14px;
    padding:14px 18px; margin-bottom:10px;
    background:rgba(237,240,248,0.45);
    border:2px solid transparent; border-radius:var(--radius-md);
    cursor:pointer; transition:all 0.2s ease;
    position:relative;
}
.pt-option:hover { background:rgba(91,79,232,0.05); border-color:rgba(91,79,232,0.25); transform:translateX(-3px); }
.pt-option.selected { background:rgba(91,79,232,0.08); border-color:var(--brand-primary); }
.pt-option.correct  { background:rgba(16,185,129,0.1); border-color:#10B981 !important; }
.pt-option.wrong    { background:rgba(239,68,68,0.08); border-color:#EF4444 !important; opacity:0.7; }
.pt-option input { display:none; }
.opt-l { width:38px; height:38px; min-width:38px; border-radius:8px; background:rgba(208,213,229,0.4); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.92rem; transition:all 0.2s; }
.pt-option.selected .opt-l { background:var(--brand-primary); color:#FFF; }
.pt-option.correct  .opt-l { background:#10B981; color:#FFF; }
.pt-option.wrong    .opt-l { background:#EF4444; color:#FFF; }
.opt-t { font-size:0.97rem; color:var(--text-heading); line-height:1.6; }
.feedback-badge { position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:1.1rem; }

/* أزرار التنقل */
.pt-nav { display:flex; justify-content:space-between; align-items:center; margin-top:22px; padding-top:18px; border-top:1px solid var(--border-subtle); }

/* شاشة الملخص التكيفي */
.adaptive-summary {
    background:var(--bg-surface-card); border:1px solid rgba(208,213,229,0.4);
    border-radius:var(--radius-xl); padding:32px 36px; text-align:center;
    display:none; box-shadow:var(--shadow-card);
}
.adaptive-summary.show { display:block; animation:slideCard 0.5s ease; }
.level-score-row {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr));
    gap:12px; margin:20px 0;
}
.level-score-box {
    padding:16px; border-radius:var(--radius-md); border:1px solid transparent;
}
.level-score-box.pass { background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.3); }
.level-score-box.fail { background:rgba(239,68,68,0.06); border-color:rgba(239,68,68,0.2); }
.lsb-pct { font-size:1.6rem; font-weight:900; font-family:var(--font-mono); }
.lsb-name { font-size:0.75rem; color:var(--text-muted); margin-top:4px; }

/* نقاط التنقل */
.q-dots { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:18px; }
.q-dot { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; cursor:pointer; border:1.5px solid var(--border-main); color:var(--text-muted); background:rgba(255,255,255,0.6); transition:all 0.2s; }
.q-dot.active { background:var(--brand-primary); color:#FFF; border-color:var(--brand-primary); }
.q-dot.answered-c { background:rgba(16,185,129,0.15); color:#10B981; border-color:rgba(16,185,129,0.4); }
.q-dot.answered-w { background:rgba(239,68,68,0.1); color:#EF4444; border-color:rgba(239,68,68,0.3); }
</style>

<div class="pt-shell">

<!-- الشريط العلوي -->
<div class="pt-topbar">
    <div class="pt-brand">
        <img src="../assets/img/icon.svg" style="width:34px;height:34px;border-radius:9px;" alt="">
        <div>
            <div style="font-size:0.66rem;color:#8B5CF6;font-weight:800;font-family:var(--font-mono);letter-spacing:0.07em;">ADAPTIVE AI</div>
            <div style="font-weight:800;font-size:0.95rem;color:#FFF;">اختبار تحديد المستوى الذكي</div>
        </div>
        <span class="pt-badge">PLACEMENT TEST</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:0.8rem;color:rgba(255,255,255,0.5);font-weight:600;">
            <span id="answered-count">0</span> / <?= count($all_questions) ?> سؤال
        </span>
        <div class="pt-timer safe" id="pt-timer">
            <i class="fa-solid fa-brain"></i>
            <span id="timer-display"><?= sprintf("%02d:%02d", floor($remaining_seconds/60), $remaining_seconds%60) ?></span>
        </div>
        <a href="dashboard.php" class="btn btn-danger btn-sm" onclick="return confirm('إلغاء الاختبار؟')">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </div>
</div>

<!-- المحتوى الرئيسي -->
<div class="pt-content">

    <!-- شريط التقدم التكيفي الذكي -->
    <div class="adaptive-progress">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <span style="font-size:0.88rem;font-weight:800;color:var(--text-heading);">
                <i class="fa-solid fa-chart-mixed" style="color:var(--brand-primary);"></i>
                المستوى المكتشف حتى الآن: <span id="current-level-label" style="color:var(--brand-primary);">يُحدَّد...</span>
            </span>
            <span style="font-size:0.78rem;color:var(--text-muted);" id="accuracy-label">دقة: —</span>
        </div>
        <div class="level-sections">
            <?php foreach (array_keys($grouped_by_level) as $lo): ?>
            <div class="level-seg future" id="seg-<?= $lo ?>" title="المستوى <?= $lo ?>">
                <div class="seg-fill"></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.68rem;color:var(--text-dim);margin-top:6px;padding:0 2px;">
            <?php foreach ($grouped_by_level as $lo => $qs): ?>
            <span>م<?= $lo ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- نقاط التنقل -->
    <div class="q-dots" id="q-dots">
        <?php $qi = 0; foreach ($all_questions as $q): ?>
        <div class="q-dot <?= $qi===0?'active':'' ?>" id="dot-<?= $qi ?>" onclick="goTo(<?= $qi ?>)" title="سؤال <?= $qi+1 ?>"><?= $qi+1 ?></div>
        <?php $qi++; endforeach; ?>
    </div>

    <!-- نموذج الاختبار المخفي للتسليم -->
    <form id="final-form" method="POST" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="attempt_token" value="<?= clean_output($attempt_token) ?>">
        <input type="hidden" name="finish_placement" value="1">
    </form>

    <!-- الأسئلة -->
    <?php
    $flat_index = 0;
    $level_colors = [1=>'l1', 2=>'l2', 3=>'l3', 4=>'l4'];
    $level_names_short = [1=>'المستوى الأول', 2=>'المستوى الثاني', 3=>'المستوى الثالث', 4=>'المستوى الرابع'];
    foreach ($all_questions as $q):
        $lo = (int)($q['_level_order'] ?? 1);
        $lc = $level_colors[$lo] ?? 'l1';
        $ln = $level_names_short[$lo] ?? 'المستوى';
    ?>
    <div class="pt-card <?= $flat_index===0?'visible':'' ?>"
         id="qcard-<?= $flat_index ?>"
         data-index="<?= $flat_index ?>"
         data-qid="<?= $q['id'] ?>"
         data-level="<?= $lo ?>">

        <div class="q-level-badge <?= $lc ?>">
            <i class="fa-solid fa-layer-group"></i> <?= $ln ?>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">
                سؤال <?= $flat_index+1 ?> من <?= count($all_questions) ?>
            </div>
            <span class="badge" style="background:<?= $q['difficulty']==='hard'?'rgba(239,68,68,0.1)':($q['difficulty']==='easy'?'rgba(16,185,129,0.1)':'rgba(245,158,11,0.1)') ?>;color:<?= $q['difficulty']==='hard'?'#EF4444':($q['difficulty']==='easy'?'#10B981':'#D97706') ?>;border:none;">
                <?= $q['difficulty']==='hard'?'متقدم':($q['difficulty']==='easy'?'أساسي':'متوسط') ?>
            </span>
        </div>

        <div class="q-text"><?= clean_output($q['question_text']) ?></div>

        <?php
        $opts = ['A'=>['أ',$q['option_a']],'B'=>['ب',$q['option_b']],'C'=>['ج',$q['option_c']],'D'=>['د',$q['option_d']]];
        foreach ($opts as $key => [$letter, $text]):
            if (empty($text)) continue;
        ?>
        <label class="pt-option" id="opt-<?= $q['id'] ?>-<?= $key ?>"
               onclick="selectAnswer(<?= $flat_index ?>, <?= $q['id'] ?>, '<?= $key ?>', this)">
            <input type="radio" name="a_<?= $q['id'] ?>" value="<?= $key ?>">
            <div class="opt-l"><?= $letter ?></div>
            <div class="opt-t"><?= clean_output($text) ?></div>
            <span class="feedback-badge" id="fb-<?= $q['id'] ?>-<?= $key ?>" style="display:none;"></span>
        </label>
        <?php endforeach; ?>

        <div class="pt-nav">
            <button type="button" class="btn btn-secondary btn-sm" onclick="goTo(<?= $flat_index-1 ?>)" <?= $flat_index===0?'disabled':'' ?>>
                <i class="fa-solid fa-arrow-right"></i> السابق
            </button>
            <span style="font-size:0.8rem;color:var(--text-muted);">
                <?= $flat_index+1 ?> / <?= count($all_questions) ?>
            </span>
            <?php if ($flat_index < count($all_questions)-1): ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="goTo(<?= $flat_index+1 ?>)">
                التالي <i class="fa-solid fa-arrow-left"></i>
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="submitPlacement()" style="background:linear-gradient(135deg,#10B981,#059669);border:none;">
                <i class="fa-solid fa-brain"></i> إنهاء وتحديد المستوى
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php $flat_index++; endforeach; ?>

    <!-- زر التسليم النهائي -->
    <div style="text-align:center;margin-top:8px;">
        <div style="background:var(--bg-surface-card);border:1px solid rgba(208,213,229,0.4);border-radius:var(--radius-xl);padding:20px 28px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;box-shadow:var(--shadow-card);">
            <div>
                <div style="font-weight:700;color:var(--text-heading);margin-bottom:3px;">جاهز لمعرفة مستواك؟</div>
                <div style="font-size:0.83rem;color:var(--text-muted);">أجبت على <strong id="answered-final">0</strong> من <?= count($all_questions) ?> سؤال</div>
            </div>
            <button onclick="submitPlacement()" class="btn btn-primary" style="background:linear-gradient(135deg,#8B5CF6,#06B6D4);border:none;min-width:200px;">
                <i class="fa-solid fa-brain"></i> اكتشف مستواك الآن
            </button>
        </div>
    </div>

</div><!-- .pt-content -->
</div><!-- .pt-shell -->

<!-- تضمين محرك الصوت للمؤثرات التفاعلية -->
<script src="../assets/js/sound_effects.js"></script>

<script>
const TOTAL = <?= count($all_questions) ?>;
const ATTEMPT_TOKEN = '<?= clean_output($attempt_token) ?>';
const CSRF_TOKEN = '<?= get_csrf_token() ?>';
const LEVEL_ORDERS = <?= json_encode(array_column($all_questions, '_level_order')) ?>;
const QUESTION_IDS = <?= json_encode(array_column($all_questions, 'id')) ?>;

let currentQ = 0;
let answers = {};  // {index: {answer, correct, qid, level_order}}
let submitting = false;

function goTo(idx) {
    if (idx < 0 || idx >= TOTAL) return;
    if (window.SoundFX) SoundFX.play('click');
    document.querySelectorAll('.pt-card').forEach(c => c.classList.remove('visible'));
    document.querySelectorAll('.q-dot').forEach(d => d.classList.remove('active'));
    const card = document.getElementById('qcard-' + idx);
    if (card) card.classList.add('visible');
    const dot = document.getElementById('dot-' + idx);
    if (dot) dot.classList.add('active');
    currentQ = idx;
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function selectAnswer(idx, qid, val, labelEl) {
    if (answers[idx]?.locked) return; // لا تغيير بعد التأكيد
    if (window.SoundFX) SoundFX.play('click');

    // تسليم AJAX فوري للإجابة
    const fd = new FormData();
    fd.append('ajax_answer', '1');
    fd.append('attempt_token', ATTEMPT_TOKEN);
    fd.append('question_id', qid);
    fd.append('answer', val);
    fd.append('csrf_token', CSRF_TOKEN);

    // تغيير مؤقت: تحديد الخيار
    document.querySelectorAll(`[id^="opt-${qid}-"]`).forEach(o => o.classList.remove('selected'));
    labelEl.classList.add('selected');

    fetch('placement_test.php', {method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const isCorrect = data.correct === 1;
            const correctOpt = data.correct_option;

            // تشغيل صوت الإجابة الصحيحة أو الخاطئة
            if (window.SoundFX) {
                if (isCorrect) {
                    SoundFX.play('correct');
                } else {
                    SoundFX.play('alert');
                }
            }

            // إظهار التغذية الراجعة الفورية
            document.querySelectorAll(`[id^="opt-${qid}-"]`).forEach(o => {
                const key = o.id.split('-').pop();
                const fb = document.getElementById(`fb-${qid}-${key}`);
                if (key === correctOpt) {
                    o.classList.add('correct');
                    if (fb) { fb.textContent = '✅'; fb.style.display = 'block'; }
                } else if (key === val && !isCorrect) {
                    o.classList.add('wrong');
                    if (fb) { fb.textContent = '❌'; fb.style.display = 'block'; }
                }
                // تعطيل كل الخيارات بعد الإجابة
                o.style.pointerEvents = 'none';
            });

            // حفظ الإجابة
            answers[idx] = { answer: val, correct: isCorrect, qid: qid, level_order: LEVEL_ORDERS[idx], locked: true };

            // تحديث نقطة التنقل
            const dot = document.getElementById('dot-' + idx);
            if (dot) {
                dot.classList.remove('answered-c', 'answered-w');
                dot.classList.add(isCorrect ? 'answered-c' : 'answered-w');
            }

            // تحديث العداد والتحليل
            updateStats();

            // انتقال تلقائي للسؤال التالي بعد ثانيتين
            if (idx < TOTAL - 1) {
                setTimeout(() => goTo(idx + 1), 1800);
            }
        }
    }).catch(() => {
        // إذا فشل AJAX، نسجل الإجابة محلياً
        answers[idx] = { answer: val, correct: null, qid: qid, level_order: LEVEL_ORDERS[idx] };
        updateStats();
        if (idx < TOTAL - 1) setTimeout(() => goTo(idx + 1), 1000);
    });
}

function updateStats() {
    const answered = Object.keys(answers).length;
    document.getElementById('answered-count').textContent = answered;
    document.getElementById('answered-final').textContent = answered;

    // تحليل تكيفي: احسب نتيجة كل مستوى
    const levelResults = {};
    for (const [idx, a] of Object.entries(answers)) {
        const lo = a.level_order;
        if (!levelResults[lo]) levelResults[lo] = { total: 0, correct: 0 };
        levelResults[lo].total++;
        if (a.correct === true) levelResults[lo].correct++;
    }

    // تحديث شرائط المستويات
    let highestPassed = 0;
    let totalAnswered = 0, totalCorrect = 0;

    for (const [lo, r] of Object.entries(levelResults)) {
        const seg = document.getElementById('seg-' + lo);
        if (!seg) continue;
        const pct = r.total > 0 ? (r.correct / r.total) * 100 : 0;
        const fill = seg.querySelector('.seg-fill');

        totalAnswered += r.total;
        totalCorrect += r.correct;

        seg.classList.remove('done', 'active', 'future');
        if (pct >= 60 && r.total >= 2) {
            seg.classList.add('done');
            highestPassed = Math.max(highestPassed, parseInt(lo));
        } else if (r.total > 0) {
            seg.classList.add('active');
            if (fill) fill.style.width = pct + '%';
        } else {
            seg.classList.add('future');
        }
    }

    // عرض المستوى المكتشف
    const levelLabel = document.getElementById('current-level-label');
    const names = {1:'الأول 🟢', 2:'الثاني 🟡', 3:'الثالث 🟣', 4:'الرابع 🔴'};
    if (highestPassed > 0) {
        levelLabel.textContent = 'المستوى ' + (names[highestPassed] || highestPassed);
    } else if (totalAnswered > 0) {
        labelLevel.textContent = 'يُحدَّد...';
    }

    // دقة إجمالية
    const accEl = document.getElementById('accuracy-label');
    if (totalAnswered > 0) {
        const acc = Math.round((totalCorrect / totalAnswered) * 100);
        accEl.textContent = 'دقة: ' + acc + '%';
    }
}

function submitPlacement() {
    if (submitting) return;
    const answered = Object.keys(answers).length;
    const unanswered = TOTAL - answered;
    let msg = 'هل أنت متأكد؟ سيتم تحديد مستواك الآن.';
    if (unanswered > 0) msg = `لديك ${unanswered} سؤال لم تُجب عليه. هل تريد التسليم رغم ذلك؟`;
    if (!confirm(msg)) return;
    submitting = true;
    document.getElementById('final-form').submit();
}

// المؤقت
let remaining = <?= (int)$remaining_seconds ?>;
const timerEl = document.getElementById('timer-display');
const timerPill = document.getElementById('pt-timer');

const countdown = setInterval(() => {
    const m = Math.floor(remaining / 60), s = remaining % 60;
    timerEl.textContent = (m<10?'0'+m:m)+':'+(s<10?'0'+s:s);
    timerPill.className = 'pt-timer';
    if (remaining <= 60)       timerPill.classList.add('danger');
    else if (remaining <= 300) timerPill.classList.add('warning');
    else                       timerPill.classList.add('safe');
    if (--remaining < 0) { clearInterval(countdown); document.getElementById('final-form').submit(); }
}, 1000);

// اختصارات لوحة المفاتيح
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft'  && currentQ < TOTAL-1) goTo(currentQ+1);
    if (e.key === 'ArrowRight' && currentQ > 0)       goTo(currentQ-1);
    if (['1','2','3','4'].includes(e.key)) {
        const keys = ['A','B','C','D'];
        const card = document.getElementById('qcard-'+currentQ);
        if (!card || answers[currentQ]?.locked) return;
        const qid = QUESTION_IDS[currentQ];
        const lbl = document.getElementById(`opt-${qid}-${keys[parseInt(e.key)-1]}`);
        if (lbl) lbl.click();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
