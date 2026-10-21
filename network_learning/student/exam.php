<?php
$pageTitle = "اختبار المستوى التكيفي";
require_once '../config/db.php';
require_once '../config/security.php';
require_auth('student');

$user_id = $_SESSION['user_id'];
$level_id = isset($_GET['level_id']) ? intval($_GET['level_id']) : 0;

// 1. التحقق من صلاحية المستوى
$user_stmt = $pdo->prepare("SELECT current_level_id FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_current_level = (int)$user_stmt->fetchColumn();

$level_stmt = $pdo->prepare("SELECT * FROM levels WHERE id = ?");
$level_stmt->execute([$level_id]);
$level = $level_stmt->fetch();

if (!$level) { header("Location: dashboard.php"); exit(); }
if ($level_id > $user_current_level) { header("Location: dashboard.php?error=level_locked"); exit(); }

$questions_stmt = $pdo->prepare("SELECT * FROM questions WHERE level_id = ? ORDER BY id ASC");
$questions_stmt->execute([$level_id]);
$questions = $questions_stmt->fetchAll();

if (count($questions) === 0) {
    require_once '../includes/header.php';
    echo "<div class='academic-card' style='text-align:center;padding:50px 30px;margin:40px auto;max-width:600px;'>
            <div style='width:70px;height:70px;border-radius:50%;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:28px;color:#F59E0B;'><i class='fa-solid fa-triangle-exclamation'></i></div>
            <h3 style='margin-bottom:8px;color:var(--text-heading);'>لا توجد أسئلة مضافة لهذا المستوى</h3>
            <p style='color:var(--text-muted);margin-bottom:20px;'>يرجى التواصل مع المشرف لإضافة أسئلة.</p>
            <a href='dashboard.php' class='btn btn-primary'><i class='fa-solid fa-house'></i> العودة</a>
          </div>";
    require_once '../includes/footer.php';
    exit();
}

$duration_minutes = $questions[0]['duration_minutes'] ?? 15;
$max_duration_seconds = $duration_minutes * 60;
$now = time();

// 2. إدارة المحاولة
$active_attempt_stmt = $pdo->prepare("
    SELECT * FROM exam_attempts 
    WHERE user_id = ? AND level_id = ? AND attempt_type = 'level_exam' AND status = 'in_progress'
    ORDER BY id DESC LIMIT 1
");
$active_attempt_stmt->execute([$user_id, $level_id]);
$current_attempt = $active_attempt_stmt->fetch();

if ($current_attempt) {
    $start_ts = strtotime($current_attempt['start_time']);
    $elapsed = $now - $start_ts;
    if ($elapsed > ($current_attempt['max_duration_seconds'] + 30)) {
        $pdo->prepare("UPDATE exam_attempts SET status = 'timed_out', end_time = NOW() WHERE id = ?")->execute([$current_attempt['id']]);
        $current_attempt = null;
    }
}

if (!$current_attempt) {
    $attempt_token = bin2hex(random_bytes(32));
    $ins = $pdo->prepare("INSERT INTO exam_attempts (user_id, level_id, attempt_type, start_time, max_duration_seconds, total_questions, status, attempt_token) VALUES (?, ?, 'level_exam', NOW(), ?, ?, 'in_progress', ?)");
    $ins->execute([$user_id, $level_id, $max_duration_seconds, count($questions), $attempt_token]);
    $attempt_id = $pdo->lastInsertId();
    $q_ins = $pdo->prepare("INSERT INTO attempt_questions (attempt_id, question_id, question_order) VALUES (?, ?, ?)");
    foreach ($questions as $idx => $q) { $q_ins->execute([$attempt_id, $q['id'], $idx + 1]); }
    $remaining_seconds = $max_duration_seconds;
} else {
    $attempt_token = $current_attempt['attempt_token'];
    $start_ts = strtotime($current_attempt['start_time']);
    $remaining_seconds = max(0, $current_attempt['max_duration_seconds'] - ($now - $start_ts));
}

$examMode = true;
require_once '../includes/header.php';
?>

<style>
/* ===== وضع الامتحان - Full Focus Exam Mode ===== */
.exam-shell { min-height: 100vh; background: var(--bg-app); }

.exam-topbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    background: rgba(15,23,42,0.97); backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(91,79,232,0.25);
    padding: 0 32px; height: 64px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.3);
}
.exam-topbar-brand { display: flex; align-items: center; gap: 12px; }
.exam-topbar-brand .level-name { font-weight: 800; font-size: 1rem; color: #FFFFFF; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.exam-topbar-brand .exam-badge { background: linear-gradient(135deg,#5B4FE8,#06B6D4); color: #FFF; font-size: 0.7rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.05em; white-space: nowrap; }
.exam-topbar-center { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; max-width: 360px; }
.exam-topbar-center .progress-label { font-size: 0.78rem; color: rgba(255,255,255,0.6); font-weight: 600; }
.exam-topbar-center .progress-track { width: 100%; height: 5px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
.exam-topbar-center .progress-track .fill { height: 100%; background: linear-gradient(90deg,#5B4FE8,#06B6D4); border-radius: 10px; transition: width 0.5s ease; }
.exam-topbar-actions { display: flex; align-items: center; gap: 12px; }
.exam-timer-pill { display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 30px; font-weight: 800; font-family: var(--font-mono); font-size: 1.1rem; transition: all 0.3s ease; white-space: nowrap; }
.exam-timer-pill.safe   { background: rgba(16,185,129,0.12); color: #10B981; border: 1px solid rgba(16,185,129,0.3); }
.exam-timer-pill.warning{ background: rgba(245,158,11,0.12); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); animation: timerPulse 1.5s ease-in-out infinite; }
.exam-timer-pill.danger { background: rgba(244,63,94,0.12);  color: #F43F5E; border: 1px solid rgba(244,63,94,0.3);  animation: timerPulse 0.8s ease-in-out infinite; }
@keyframes timerPulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

.exam-content {
    padding-top: 84px; padding-bottom: 48px;
    max-width: 820px; margin: 0 auto; width: 100%;
    padding-left: 24px; padding-right: 24px;
}

/* شريط التنقل بين الأسئلة */
.question-nav {
    display: flex; flex-wrap: wrap; gap: 8px;
    padding: 14px 18px; background: var(--bg-surface-card);
    border: 1px solid rgba(208,213,229,0.4);
    border-radius: var(--radius-md); box-shadow: var(--shadow-subtle);
    margin-bottom: 18px;
}
.q-nav-dot {
    width: 38px; height: 38px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; font-weight: 700; cursor: pointer;
    border: 1.5px solid var(--border-main); color: var(--text-muted);
    background: rgba(255,255,255,0.6); transition: all 0.2s ease;
}
.q-nav-dot:hover { border-color: var(--brand-primary); color: var(--brand-primary); transform: scale(1.1); }
.q-nav-dot.active { background: var(--brand-primary); color: #FFF; border-color: var(--brand-primary); box-shadow: 0 4px 12px rgba(91,79,232,0.3); }
.q-nav-dot.answered { background: rgba(16,185,129,0.12); color: #10B981; border-color: rgba(16,185,129,0.4); }
.q-nav-dot.answered.active { background: var(--brand-primary); color: #FFF; border-color: var(--brand-primary); }

/* بطاقة السؤال - الأهم: display:none افتراضياً */
.exam-question-card {
    background: var(--bg-surface-card);
    border: 1px solid rgba(208,213,229,0.45);
    border-radius: var(--radius-xl);
    padding: 36px; margin-bottom: 20px;
    box-shadow: var(--shadow-card);
    display: none;  /* مخفية افتراضياً */
}
.exam-question-card.visible {
    display: block;
    animation: slideUpFade 0.35s ease forwards;
}
@keyframes slideUpFade { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* رأس السؤال */
.q-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px; padding-bottom: 16px;
    border-bottom: 1px solid var(--border-subtle);
}
.q-num-badge {
    width: 44px; height: 44px; border-radius: 10px;
    background: var(--brand-gradient);
    display: flex; align-items: center; justify-content: center;
    color: #FFF; font-weight: 900; font-size: 1rem;
}
/* نص السؤال */
.q-text {
    font-size: 1.13rem; color: var(--text-heading);
    font-weight: 700; line-height: 1.85; margin-bottom: 26px;
}
/* خيارات الإجابة */
.exam-option {
    display: flex; align-items: center; gap: 16px;
    padding: 16px 20px; margin-bottom: 12px;
    background: rgba(237,240,248,0.45);
    border: 2px solid transparent;
    border-radius: var(--radius-md); cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
}
.exam-option input[type="radio"] { display: none; }
.exam-option:hover {
    background: rgba(91,79,232,0.05);
    border-color: rgba(91,79,232,0.25);
    transform: translateX(-4px);
}
.exam-option.selected {
    background: rgba(91,79,232,0.08);
    border-color: var(--brand-primary);
    box-shadow: 0 4px 16px rgba(91,79,232,0.1);
}
.opt-letter {
    width: 40px; height: 40px; min-width: 40px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.95rem;
    background: rgba(208,213,229,0.4); color: var(--text-body);
    transition: all 0.2s ease;
}
.exam-option.selected .opt-letter {
    background: var(--brand-primary); color: #FFF;
    box-shadow: 0 4px 12px rgba(91,79,232,0.35);
}
.opt-text { font-size: 0.98rem; color: var(--text-heading); line-height: 1.6; }

/* أزرار التنقل داخل البطاقة */
.exam-nav-buttons {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 28px; padding-top: 20px;
    border-top: 1px solid var(--border-subtle);
}

/* بطاقة التسليم */
.exam-submit-card {
    background: var(--bg-surface-card);
    border: 1px solid rgba(208,213,229,0.4);
    border-radius: var(--radius-xl);
    padding: 26px 34px; margin-top: 8px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 20px; flex-wrap: wrap;
    box-shadow: var(--shadow-card);
}
</style>

<div class="exam-shell">

<!-- ===== شريط الامتحان الثابت ===== -->
<div class="exam-topbar">
    <div class="exam-topbar-brand">
        <img src="../assets/img/icon.svg" alt="Logo" style="width:36px;height:36px;border-radius:9px;">
        <div>
            <div style="font-size:0.68rem;color:#38BDF8;font-weight:700;font-family:var(--font-mono);letter-spacing:0.06em;">EXAM MODE</div>
            <div class="level-name"><?php echo clean_output($level['level_name']); ?></div>
        </div>
        <span class="exam-badge">اختبار المستوى</span>
    </div>

    <div class="exam-topbar-center">
        <div class="progress-label">
            <span id="answered-count">0</span> / <?php echo count($questions); ?> إجابة &nbsp;|&nbsp; <?php echo $duration_minutes; ?> دقيقة
        </div>
        <div class="progress-track">
            <div class="fill" id="progress-fill" style="width:0%;"></div>
        </div>
    </div>

    <div class="exam-topbar-actions">
        <div class="exam-timer-pill safe" id="timer-pill">
            <i class="fa-solid fa-clock"></i>
            <span id="timer-display"><?php echo sprintf("%02d:%02d", floor($remaining_seconds / 60), $remaining_seconds % 60); ?></span>
        </div>
        <a href="dashboard.php" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من إلغاء الاختبار؟ ستُفقد إجاباتك.')">
            <i class="fa-solid fa-xmark"></i> إلغاء
        </a>
    </div>
</div>

<!-- ===== منطقة المحتوى ===== -->
<div class="exam-content">

    <!-- شريط التنقل السريع -->
    <div class="question-nav" id="question-nav">
        <?php foreach ($questions as $i => $q): ?>
            <div class="q-nav-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                 onclick="goToQuestion(<?php echo $i; ?>)"
                 id="nav-dot-<?php echo $i; ?>">
                <?php echo $i + 1; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- نموذج الاختبار -->
    <form id="exam-form" action="process_exam.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="level_id" value="<?php echo $level_id; ?>">
        <input type="hidden" name="attempt_token" value="<?php echo clean_output($attempt_token); ?>">

        <?php foreach ($questions as $index => $q): ?>
        <div class="exam-question-card <?php echo $index === 0 ? 'visible' : ''; ?>"
             id="question-<?php echo $index; ?>"
             data-index="<?php echo $index; ?>">

            <!-- رأس السؤال -->
            <div class="q-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="q-num-badge"><?php echo $index + 1; ?></div>
                    <div>
                        <div style="font-size:0.78rem;color:var(--text-muted);font-weight:600;">
                            سؤال <?php echo $index + 1; ?> من <?php echo count($questions); ?>
                        </div>
                        <?php if (!empty($q['topic_id'])): ?>
                        <div style="font-size:0.75rem;color:var(--brand-secondary);font-weight:600;margin-top:2px;">
                            <i class="fa-solid fa-tag"></i> <?php echo clean_output($q['domain_name'] ?? ''); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <?php
                    $diff = $q['difficulty'] ?? 'medium';
                    $diff_color = $diff === 'hard' ? '#F43F5E' : ($diff === 'easy' ? '#10B981' : '#F59E0B');
                    $diff_text  = $diff === 'hard' ? 'متقدم' : ($diff === 'easy' ? 'أساسي' : 'متوسط');
                    ?>
                    <span class="badge" style="background:<?php echo $diff_color; ?>18;color:<?php echo $diff_color; ?>;border-color:<?php echo $diff_color; ?>40;">
                        <?php echo $diff_text; ?>
                    </span>
                </div>
            </div>

            <!-- نص السؤال -->
            <div class="q-text"><?php echo clean_output($q['question_text']); ?></div>

            <!-- مخطط / صورة السؤال -->
            <?php if (!empty($q['image_url'])): ?>
            <div style="margin:16px 0 22px;background:rgba(15,23,42,0.96);padding:14px;border-radius:var(--radius-md);border:1px solid rgba(91,79,232,0.35);text-align:center;">
                <div style="font-size:0.74rem;color:#38BDF8;font-weight:700;margin-bottom:10px;font-family:var(--font-mono);">
                    <i class="fa-solid fa-diagram-project"></i> NETWORK DIAGRAM
                </div>
                <img src="../<?php echo htmlspecialchars($q['image_url']); ?>" alt="Diagram"
                     style="max-width:100%;height:auto;max-height:420px;border-radius:8px;cursor:pointer;"
                     onclick="window.open(this.src,'_blank')" title="انقر للتكبير">
            </div>
            <?php endif; ?>

            <!-- خيارات الإجابة -->
            <?php
            $opts = [
                'A' => ['letter' => 'أ', 'text' => $q['option_a']],
                'B' => ['letter' => 'ب', 'text' => $q['option_b']],
                'C' => ['letter' => 'ج', 'text' => $q['option_c']],
                'D' => ['letter' => 'د', 'text' => $q['option_d']],
            ];
            foreach ($opts as $key => $opt):
                if (empty($opt['text'])) continue;
            ?>
            <label class="exam-option"
                   id="opt-<?php echo $q['id']; ?>-<?php echo $key; ?>"
                   onclick="selectOption(<?php echo $q['id']; ?>, '<?php echo $key; ?>', <?php echo $index; ?>)">
                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $key; ?>">
                <div class="opt-letter"><?php echo $opt['letter']; ?></div>
                <div class="opt-text"><?php echo clean_output($opt['text']); ?></div>
            </label>
            <?php endforeach; ?>

            <!-- أزرار التنقل (داخل البطاقة) -->
            <div class="exam-nav-buttons">
                <?php if ($index > 0): ?>
                    <button type="button" class="btn btn-secondary" onclick="goToQuestion(<?php echo $index - 1; ?>)">
                        <i class="fa-solid fa-arrow-right"></i> السابق
                    </button>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <span style="font-size:0.82rem;color:var(--text-muted);font-weight:600;">
                    <?php echo $index + 1; ?> / <?php echo count($questions); ?>
                </span>

                <?php if ($index < count($questions) - 1): ?>
                    <button type="button" class="btn btn-primary" onclick="goToQuestion(<?php echo $index + 1; ?>)">
                        التالي <i class="fa-solid fa-arrow-left"></i>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="confirmSubmit()"
                            style="background:linear-gradient(135deg,#10B981,#059669);border:none;">
                        <i class="fa-solid fa-circle-check"></i> إنهاء وتسليم
                    </button>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </form>

    <!-- بطاقة التسليم النهائي -->
    <div class="exam-submit-card">
        <div>
            <div style="font-weight:700;color:var(--text-heading);margin-bottom:4px;">جاهز للتسليم؟</div>
            <div style="font-size:0.85rem;color:var(--text-muted);">
                <i class="fa-solid fa-circle-info" style="color:var(--brand-primary);"></i>
                أجبت على <strong id="final-answered-count">0</strong> من <?php echo count($questions); ?> سؤال.
            </div>
        </div>
        <button type="button" onclick="confirmSubmit()" class="btn btn-primary btn-lg"
                style="background:linear-gradient(135deg,#10B981,#059669);border:none;min-width:200px;">
            <i class="fa-solid fa-paper-plane"></i> تسليم الاختبار
        </button>
    </div>

</div><!-- .exam-content -->
</div><!-- .exam-shell -->

<!-- تضمين محرك الصوت للمؤثرات التفاعلية -->
<script src="../assets/js/sound_effects.js"></script>

<script>
const TOTAL_Q = <?php echo count($questions); ?>;
let currentQ = 0;
let answeredSet = new Set();
let remaining = <?php echo (int)$remaining_seconds; ?>;

function goToQuestion(idx) {
    if (window.SoundFX) SoundFX.play('click');
    // إخفاء كل البطاقات
    document.querySelectorAll('.exam-question-card').forEach(c => c.classList.remove('visible'));
    // إظهار البطاقة المطلوبة
    const target = document.getElementById('question-' + idx);
    if (target) target.classList.add('visible');

    // تحديث النقاط
    document.querySelectorAll('.q-nav-dot').forEach(d => d.classList.remove('active'));
    const dot = document.getElementById('nav-dot-' + idx);
    if (dot) dot.classList.add('active');

    currentQ = idx;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectOption(qid, val, qIndex) {
    if (window.SoundFX) SoundFX.play('click');
    document.querySelectorAll(`[id^="opt-${qid}-"]`).forEach(o => o.classList.remove('selected'));
    const chosen = document.getElementById(`opt-${qid}-${val}`);
    if (chosen) chosen.classList.add('selected');

    const radio = document.querySelector(`input[name="answers[${qid}]"][value="${val}"]`);
    if (radio) radio.checked = true;

    answeredSet.add(qIndex);
    const dot = document.getElementById('nav-dot-' + qIndex);
    if (dot) dot.classList.add('answered');

    updateProgress();

    // انتقال تلقائي للسؤال التالي
    if (qIndex < TOTAL_Q - 1) {
        setTimeout(() => goToQuestion(qIndex + 1), 650);
    }
}

function updateProgress() {
    const c = answeredSet.size;
    document.getElementById('answered-count').textContent = c;
    document.getElementById('final-answered-count').textContent = c;
    document.getElementById('progress-fill').style.width = ((c / TOTAL_Q) * 100) + '%';
}

function confirmSubmit() {
    if (window.SoundFX) SoundFX.play('levelUp');
    const unanswered = TOTAL_Q - answeredSet.size;
    const msg = unanswered > 0
        ? `لديك ${unanswered} سؤال بدون إجابة. هل تريد التسليم رغم ذلك؟`
        : 'هل أنت متأكد من تسليم الاختبار؟';
    if (confirm(msg)) document.getElementById('exam-form').submit();
}

// المؤقت
const timerDisplay = document.getElementById('timer-display');
const timerPill    = document.getElementById('timer-pill');
const examForm     = document.getElementById('exam-form');

const countdown = setInterval(function() {
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    timerDisplay.textContent = (mins < 10 ? '0' + mins : mins) + ':' + (secs < 10 ? '0' + secs : secs);

    timerPill.className = 'exam-timer-pill';
    if (remaining <= 60)       timerPill.classList.add('danger');
    else if (remaining <= 180) timerPill.classList.add('warning');
    else                       timerPill.classList.add('safe');

    if (--remaining < 0) {
        clearInterval(countdown);
        alert('انتهى وقت الاختبار! سيتم تسليم إجاباتك تلقائياً.');
        examForm.submit();
    }
}, 1000);

// اختصارات لوحة المفاتيح
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft'  && currentQ < TOTAL_Q - 1) goToQuestion(currentQ + 1);
    if (e.key === 'ArrowRight' && currentQ > 0)           goToQuestion(currentQ - 1);
    if (e.key >= '1' && e.key <= '4') {
        const keys = ['A','B','C','D'];
        const card = document.getElementById('question-' + currentQ);
        if (!card) return;
        const radios = card.querySelectorAll('input[type="radio"]');
        const ri = parseInt(e.key) - 1;
        if (radios[ri]) {
            const qid = radios[ri].name.replace('answers[','').replace(']','');
            selectOption(parseInt(qid), keys[ri], currentQ);
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
