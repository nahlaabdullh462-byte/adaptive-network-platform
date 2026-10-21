<?php
$pageTitle = "نتيجة الاختبار والتغذية الراجعة التفسيرية";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/recommendations_helper.php';
require_auth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['attempt_token'])) {
    header("Location: dashboard.php"); exit();
}

$user_id = $_SESSION['user_id'];
$attempt_token = trim($_POST['attempt_token'] ?? $_GET['attempt_token'] ?? '');
if (empty($attempt_token)) { header("Location: dashboard.php"); exit(); }

$att_stmt = $pdo->prepare("SELECT a.*, l.level_name FROM exam_attempts a JOIN levels l ON a.level_id = l.id WHERE a.attempt_token = ? AND a.user_id = ?");
$att_stmt->execute([$attempt_token, $user_id]);
$attempt = $att_stmt->fetch();
if (!$attempt) { header("Location: dashboard.php"); exit(); }

$level_id = (int)$attempt['level_id'];
$level_name = $attempt['level_name'];
$weak_topics = [];

if ($attempt['status'] === 'completed' || $attempt['status'] === 'timed_out') {
    $rev_stmt = $pdo->prepare("SELECT aq.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.explanation, q.topic_id, t.topic_name FROM attempt_questions aq JOIN questions q ON aq.question_id = q.id LEFT JOIN topics t ON q.topic_id = t.id WHERE aq.attempt_id = ? ORDER BY aq.question_order ASC");
    $rev_stmt->execute([$attempt['id']]);
    $reviewed_questions = $rev_stmt->fetchAll();
    $score_percentage = (int)$attempt['score'];
    $correct_count = (int)$attempt['correct_answers'];
    $total_questions = (int)$attempt['total_questions'];
    $passed = (bool)$attempt['passed'];
} else {
    verify_csrf_token();
    $user_answers = $_POST['answers'] ?? [];
    $start_ts = strtotime($attempt['start_time']);
    $elapsed = time() - $start_ts;
    $is_timed_out = ($elapsed > ($attempt['max_duration_seconds'] + 45));
    $attempt_status = $is_timed_out ? 'timed_out' : 'completed';

    $att_q_stmt = $pdo->prepare("SELECT aq.id as att_q_id, aq.question_id, q.correct_option, q.topic_id FROM attempt_questions aq JOIN questions q ON aq.question_id = q.id WHERE aq.attempt_id = ? ORDER BY aq.question_order ASC");
    $att_q_stmt->execute([$attempt['id']]);
    $attempt_questions_rows = $att_q_stmt->fetchAll();

    $total_questions = count($attempt_questions_rows);
    $correct_count = 0;

    $up_ans = $pdo->prepare("UPDATE attempt_questions SET selected_option = ?, is_correct = ?, answered_at = NOW() WHERE id = ?");
    foreach ($attempt_questions_rows as $row) {
        $qid = $row['question_id'];
        $chosen = isset($user_answers[$qid]) && in_array($user_answers[$qid], ['A','B','C','D']) ? $user_answers[$qid] : null;
        $is_correct = ($chosen !== null && $chosen === $row['correct_option']);
        if ($is_correct) { $correct_count++; } else { if (!empty($row['topic_id'])) { $weak_topics[$row['topic_id']] = ($weak_topics[$row['topic_id']] ?? 0) + 1; } }
        $up_ans->execute([$chosen, $is_correct ? 1 : 0, $row['att_q_id']]);
    }

    $score_percentage = ($total_questions > 0) ? round(($correct_count / $total_questions) * 100) : 0;
    $passed = ($score_percentage >= 75);

    $up_attempt = $pdo->prepare("UPDATE exam_attempts SET score = ?, correct_answers = ?, passed = ?, status = ?, end_time = NOW() WHERE id = ?");
    $up_attempt->execute([$score_percentage, $correct_count, $passed ? 1 : 0, $attempt_status, $attempt['id']]);

    $chk_res = $pdo->prepare("SELECT score FROM exam_results WHERE user_id = ? AND level_id = ?");
    $chk_res->execute([$user_id, $level_id]);
    $prev_score = $chk_res->fetchColumn();

    if ($prev_score === false) {
        $pdo->prepare("INSERT INTO exam_results (user_id, level_id, score, status) VALUES (?, ?, ?, ?)")->execute([$user_id, $level_id, $score_percentage, $passed ? 'passed' : 'failed']);
    } else {
        $highest = max((int)$prev_score, $score_percentage);
        $pdo->prepare("UPDATE exam_results SET score = ?, status = ? WHERE user_id = ? AND level_id = ?")->execute([$highest, $highest >= 75 ? 'passed' : 'failed', $user_id, $level_id]);
    }

    if ($passed) {
        $all_levels = $pdo->query("SELECT id FROM levels ORDER BY level_order ASC")->fetchAll(PDO::FETCH_COLUMN);
        $next_level = null;
        foreach ($all_levels as $lvl_id) {
            $c_res = $pdo->prepare("SELECT score FROM exam_results WHERE user_id = ? AND level_id = ?");
            $c_res->execute([$user_id, $lvl_id]);
            $sc = $c_res->fetchColumn();
            if ($sc === false || $sc < 75) { $next_level = $lvl_id; break; }
        }
        
        // جلب المستوى الحالي والمستوى المحفوظ لضمان عدم الرجوع للوراء
        $u_info = $pdo->prepare("SELECT current_level_id, highest_mastered_level_id FROM users WHERE id = ?");
        $u_info->execute([$user_id]);
        $u_curr = $u_info->fetch();
        $curr_lid = (int)($u_curr['current_level_id'] ?? 1);
        $high_lid = (int)($u_curr['highest_mastered_level_id'] ?? 1);

        if ($next_level) {
            // ترقية المستوى فقط إن كان أعلى، دون إنزال الخبير أبداً
            $target_lvl = max($next_level, $curr_lid, $high_lid);
            $pdo->prepare("UPDATE users SET current_level_id = ?, highest_mastered_level_id = GREATEST(highest_mastered_level_id, ?) WHERE id = ?")
                ->execute([$target_lvl, $target_lvl, $user_id]);
        }
    }

    $rev_stmt = $pdo->prepare("SELECT aq.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.explanation, q.image_url, q.topic_id, t.topic_name FROM attempt_questions aq JOIN questions q ON aq.question_id = q.id LEFT JOIN topics t ON q.topic_id = t.id WHERE aq.attempt_id = ? ORDER BY aq.question_order ASC");
    $rev_stmt->execute([$attempt['id']]);
    $reviewed_questions = $rev_stmt->fetchAll();
}

$recommendation_topic = null;
if (!empty($weak_topics)) {
    arsort($weak_topics);
    $top_weak_id = array_key_first($weak_topics);
    $t_stmt = $pdo->prepare("SELECT topic_name FROM topics WHERE id = ?");
    $t_stmt->execute([$top_weak_id]);
    $recommendation_topic = $t_stmt->fetchColumn();
}

$wrong_count = $total_questions - $correct_count;

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.result-container { max-width: 900px; margin: 0 auto; }

/* بطاقة النتيجة الرئيسية */
.result-hero {
    border-radius: var(--radius-xl); padding: 44px 36px; text-align: center;
    margin-bottom: 30px; position: relative; overflow: hidden;
    box-shadow: 0 20px 50px -12px rgba(26,26,62,0.15);
}
.result-hero.passed {
    background: linear-gradient(135deg, #065F46 0%, #047857 40%, #0D9488 100%);
    color: #FFFFFF;
}
.result-hero.failed {
    background: linear-gradient(135deg, #9F1239 0%, #BE123C 40%, #E11D48 100%);
    color: #FFFFFF;
}
.result-hero::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.06) 0%, transparent 50%);
    pointer-events: none;
}
.result-hero * { position: relative; z-index: 1; }

/* الدائرة المتحركة */
.score-circle { width: 140px; height: 140px; margin: 0 auto 20px; position: relative; }
.score-circle svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.score-circle .score-text {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}

/* بطاقات المؤشرات */
.result-kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 28px 0; }
.result-kpi {
    background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.15); border-radius: var(--radius-md);
    padding: 18px; text-align: center;
}
.result-kpi-value { font-family: var(--font-mono); font-size: 1.6rem; font-weight: 800; }
.result-kpi-label { font-size: 0.82rem; opacity: 0.85; margin-top: 4px; }

/* بطاقة مراجعة السؤال */
.review-card {
    background: var(--bg-surface-card); border-radius: var(--radius-lg);
    padding: 28px; margin-bottom: 18px;
    box-shadow: var(--shadow-card); transition: all 0.3s ease;
    border: 1px solid rgba(208,213,229,0.5);
}
.review-card:hover { box-shadow: var(--shadow-card-hover); }
.review-card.correct { border-right: 5px solid var(--status-success); }
.review-card.wrong { border-right: 5px solid var(--status-danger); }

.review-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 16px 0; }
.review-opt {
    padding: 12px 16px; border-radius: var(--radius-sm);
    border: 1.5px solid var(--border-main); font-size: 0.92rem;
    display: flex; align-items: center; gap: 10px;
}
.review-opt.is-correct { background: var(--status-success-bg); border-color: var(--status-success-border); color: var(--status-success-text); }
.review-opt.is-wrong { background: var(--status-danger-bg); border-color: var(--status-danger-border); color: var(--status-danger-text); text-decoration: line-through; opacity: 0.7; }

.explanation-box {
    background: var(--brand-primary-light); border: 1px solid var(--brand-primary-border);
    border-radius: var(--radius-sm); padding: 16px 20px; margin-top: 14px;
    font-size: 0.92rem; line-height: 1.7;
}

@media (max-width: 768px) {
    .result-kpi-grid { grid-template-columns: 1fr; }
    .review-options { grid-template-columns: 1fr; }
}
</style>

<div class="result-container">

    <!-- بطاقة النتيجة الرئيسية -->
    <div class="result-hero <?php echo $passed ? 'passed' : 'failed'; ?>">
        
        <!-- الدائرة المتحركة -->
        <div class="score-circle">
            <svg viewBox="0 0 36 36">
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2.5" />
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-dasharray="0, 100" stroke-linecap="round" id="score-arc" />
            </svg>
            <div class="score-text">
                <span style="font-family:var(--font-mono);font-size:2.2rem;font-weight:900;" id="score-number">0</span>
                <span style="font-size:0.78rem;opacity:0.8;">من 100</span>
            </div>
        </div>

        <div style="font-size:48px;margin-bottom:10px;">
            <?php echo $passed ? '<i class="fa-solid fa-trophy"></i>' : '<i class="fa-solid fa-rotate-left"></i>'; ?>
        </div>

        <h1 style="font-size:1.8rem;margin-bottom:10px;font-weight:900;">
            <?php echo $passed ? 'تهانينا! تم اجتياز الاختبار بنجاح' : 'لم يتم تحقيق نسبة الاجتياز'; ?>
        </h1>
        <p style="font-size:1rem;opacity:0.9;max-width:600px;margin:0 auto 24px;line-height:1.65;">
            <?php if ($passed): ?>
                أظهرت إتقاناً لمعايير <strong>«<?php echo clean_output($level_name); ?>»</strong> وتجاوزت نسبة النجاح (75%).
            <?php else: ?>
                نسبة الاجتياز 75%. راجع الشرح أدناه ثم أعد المحاولة بعد دراسة المادة.
            <?php endif; ?>
        </p>

        <!-- مؤشرات الأداء -->
        <div class="result-kpi-grid">
            <div class="result-kpi">
                <div class="result-kpi-value"><?php echo $score_percentage; ?>%</div>
                <div class="result-kpi-label">النسبة المئوية</div>
            </div>
            <div class="result-kpi">
                <div class="result-kpi-value"><?php echo $correct_count; ?> / <?php echo $total_questions; ?></div>
                <div class="result-kpi-label">الإجابات الصحيحة</div>
            </div>
            <div class="result-kpi">
                <div class="result-kpi-value"><?php echo $attempt['status'] === 'timed_out' ? '⏰' : '✅'; ?></div>
                <div class="result-kpi-label"><?php echo $attempt['status'] === 'timed_out' ? 'انتهى الوقت' : 'مكتملة'; ?></div>
            </div>
        </div>

        <?php if ($recommendation_topic): ?>
            <div style="background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);padding:14px 22px;border-radius:var(--radius-md);border:1px solid rgba(255,255,255,0.15);text-align:right;max-width:650px;margin:0 auto 20px;font-size:0.92rem;">
                <i class="fa-solid fa-lightbulb" style="color:#FDE68A;margin-left:6px;"></i>
                <strong>توصية المحرك التكيفي:</strong> ركّز على موضوع «<strong><?php echo clean_output($recommendation_topic); ?></strong>» قبل المحاولة التالية.
            </div>
        <?php endif; ?>

        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
            <a href="dashboard.php" class="btn" style="background:#FFF;color:#1A1A3E !important;font-weight:700;padding:12px 24px;border-radius:var(--radius-sm);box-shadow:0 4px 14px rgba(0,0,0,0.15);">
                <i class="fa-solid fa-house"></i> لوحة القيادة
            </a>
            <a href="study_module.php?level_id=<?php echo $level_id; ?>" class="btn" style="background:rgba(255,255,255,0.15);color:#FFF !important;border:1px solid rgba(255,255,255,0.25);padding:12px 24px;border-radius:var(--radius-sm);backdrop-filter:blur(8px);">
                <i class="fa-solid fa-book-open"></i> مراجعة المادة
            </a>
            <?php if (!$passed): ?>
                <a href="exam.php?level_id=<?php echo $level_id; ?>" class="btn" style="background:rgba(255,255,255,0.15);color:#FFF !important;border:1px solid rgba(255,255,255,0.25);padding:12px 24px;border-radius:var(--radius-sm);backdrop-filter:blur(8px);">
                    <i class="fa-solid fa-rotate-left"></i> إعادة المحاولة
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- مسار الكورسات المرئية المقترح بالذكاء الاصطناعي (YouTube Adaptive Hub) -->
    <?php 
    render_adaptive_video_hub(
        $pdo, 
        $level_id, 
        $recommendation_topic, 
        "المسار المرئي المقترح بالذكاء الاصطناعي (YouTube Adaptive Hub)", 
        $passed 
            ? "لتعميق إتقانك وتثبيت المفاهيم الهندسية، هذه نخبة من أفضل سلاسل وفيديوهات CCNA المعتمدة لهذا المستوى:" 
            : "سد فجوات التعلم: قمنا باختيار هذه الشروحات وقوائم التشغيل خصيصاً لمساعدتك على إتقان نقاط الضعف واجتياز الاختبار بنجاح:"
    ); 
    ?>

    <!-- عنوان المراجعة -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-size:1.3rem;color:var(--text-heading);display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-clipboard-check" style="color:var(--brand-primary);"></i>
            التغذية الراجعة التفسيرية
        </h2>
        <div style="display:flex;gap:8px;">
            <span class="badge badge-success"><i class="fa-solid fa-check"></i> <?php echo $correct_count; ?> صحيح</span>
            <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> <?php echo $wrong_count; ?> خاطئ</span>
        </div>
    </div>

    <!-- مراجعة الأسئلة -->
    <?php foreach ($reviewed_questions as $idx => $rq):
        $is_c = (bool)$rq['is_correct'];
        $options = ['A' => $rq['option_a'], 'B' => $rq['option_b'], 'C' => $rq['option_c'], 'D' => $rq['option_d']];
        $letters = ['A' => 'أ', 'B' => 'ب', 'C' => 'ج', 'D' => 'د'];
    ?>
        <div class="review-card <?php echo $is_c ? 'correct' : 'wrong'; ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:<?php echo $is_c ? 'var(--status-success-bg)' : 'var(--status-danger-bg)'; ?>;color:<?php echo $is_c ? 'var(--status-success)' : 'var(--status-danger)'; ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;">
                        <?php echo $idx + 1; ?>
                    </div>
                    <span style="font-weight:700;color:var(--text-heading);">
                        سؤال #<?php echo $idx + 1; ?>
                        <?php if (!empty($rq['topic_name'])): ?>
                            <span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;"> — <?php echo clean_output($rq['topic_name']); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($is_c): ?>
                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> صحيح</span>
                <?php else: ?>
                    <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> غير صحيح</span>
                <?php endif; ?>
            </div>

            <p style="font-size:1.02rem;color:var(--text-heading);font-weight:600;line-height:1.7;margin-bottom:16px;">
                <?php echo clean_output($rq['question_text']); ?>
            </p>

            <?php if (!empty($rq['image_url'])): ?>
                <div style="margin: 14px 0 18px; background: #0F172A; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid rgba(91, 79, 232, 0.3);">
                    <img src="../<?php echo htmlspecialchars($rq['image_url']); ?>" alt="Diagram" style="max-width: 100%; height: auto; max-height: 380px; border-radius: 6px; cursor: pointer;" onclick="window.open(this.src, '_blank')" title="انقر للتكبير في نافذة مستقلة">
                </div>
            <?php endif; ?>

            <div class="review-options">
                <?php foreach ($options as $key => $text):
                    $cls = '';
                    if ($key === $rq['correct_option']) $cls = 'is-correct';
                    elseif ($key === $rq['selected_option'] && !$is_c) $cls = 'is-wrong';
                ?>
                    <div class="review-opt <?php echo $cls; ?>">
                        <strong>(<?php echo $letters[$key]; ?>)</strong>
                        <span><?php echo clean_output($text); ?></span>
                        <?php if ($key === $rq['correct_option']): ?>
                            <i class="fa-solid fa-circle-check" style="margin-right:auto;color:var(--status-success);"></i>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="explanation-box">
                <strong style="color:var(--brand-primary);display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <i class="fa-solid fa-lightbulb"></i> الشرح الهندسي:
                </strong>
                <?php echo clean_output($rq['explanation']); ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<!-- أنيميشن الدائرة -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const target = <?php echo $score_percentage; ?>;
    const arc = document.getElementById('score-arc');
    const num = document.getElementById('score-number');
    let current = 0;
    const duration = 1500;
    const start = performance.now();
    
    function animate(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        current = Math.round(eased * target);
        
        if (arc) arc.setAttribute('stroke-dasharray', (eased * target) + ', 100');
        if (num) num.textContent = current;
        
        if (progress < 1) requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>