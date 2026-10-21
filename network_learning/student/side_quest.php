<?php
$pageTitle = "المسار الجانبي لمعالجة الثغرة - Side Quest";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../includes/precision_remediation_helper.php';
require_auth('student');

$user_id = (int)$_SESSION['user_id'];
$rem_id = (int)($_POST['rem_id'] ?? $_GET['rem_id'] ?? 0);

// جلب سجل المعالجة الدقيقة
if ($rem_id > 0) {
    $stmt = $pdo->prepare("SELECT r.*, l.level_name FROM remediation_records r JOIN levels l ON r.level_id = l.id WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$rem_id, $user_id]);
    $remediation = $stmt->fetch();
} else {
    $remediation = get_active_user_remediation($pdo, $user_id);
    if ($remediation) {
        $rem_id = (int)$remediation['id'];
        $lvl_stmt = $pdo->prepare("SELECT level_name FROM levels WHERE id = ?");
        $lvl_stmt->execute([$remediation['level_id']]);
        $remediation['level_name'] = $lvl_stmt->fetchColumn();
    }
}

if (!$remediation) {
    header("Location: dashboard.php");
    exit();
}

$user_stmt = $pdo->prepare("SELECT u.*, l.level_name as current_level_name FROM users u JOIN levels l ON u.current_level_id = l.id WHERE u.id = ?");
$user_stmt->execute([$user_id]);
$user_info = $user_stmt->fetch();

$topic_id = (int)$remediation['topic_id'];
$concept_capsule = get_topic_micro_capsule($topic_id, $remediation['concept_name']);

// جلب 4 أسئلة مركزة للمسار الجانبي
$questions = get_side_quest_questions($pdo, $topic_id, 4);

$quest_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_side_quest'])) {
    verify_csrf_token();
    $answers = $_POST['answers'] ?? [];

    $total_q = count($questions);
    $correct_q = 0;
    $details = [];

    foreach ($questions as $q) {
        $qid = (int)$q['id'];
        $chosen = isset($answers[$qid]) && in_array($answers[$qid], ['A', 'B', 'C', 'D']) ? $answers[$qid] : null;
        $is_correct = ($chosen !== null && $chosen === $q['correct_option']);

        if ($is_correct) {
            $correct_q++;
        }

        $details[] = [
            'question' => $q,
            'chosen' => $chosen,
            'is_correct' => $is_correct
        ];
    }

    $score = ($total_q > 0) ? round(($correct_q / $total_q) * 100) : 0;
    $is_passed = ($score >= 75);

    if ($is_passed) {
        $pdo->prepare("
            UPDATE remediation_records 
            SET remediation_status = 'side_quest_completed', side_quest_score = ? 
            WHERE id = ?
        ")->execute([$score, $rem_id]);

        $pdo->prepare("UPDATE users SET remediation_status = 'clear' WHERE id = ?")->execute([$user_id]);
    }

    $quest_result = [
        'score' => $score,
        'correct' => $correct_q,
        'total' => $total_q,
        'is_passed' => $is_passed,
        'details' => $details
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.side-quest-container {
    max-width: 860px;
    margin: 0 auto 40px;
}

.quest-hero {
    background: linear-gradient(135deg, #78350F 0%, #B45309 40%, #D97706 100%);
    border-radius: var(--radius-xl);
    padding: 36px 32px;
    color: #FFFFFF;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 16px 40px -10px rgba(180, 83, 9, 0.35);
}
.quest-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 80% 30%, rgba(254, 240, 138, 0.2) 0%, transparent 50%);
    pointer-events: none;
}

.quest-question-card {
    background: #FFFFFF;
    border-radius: var(--radius-lg);
    border: 1.5px solid var(--border-main);
    padding: 24px;
    margin-bottom: 18px;
    box-shadow: var(--shadow-card);
}
</style>

<div class="side-quest-container">

    <!-- Hero Header -->
    <div class="quest-hero">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 12px;">
            <span class="badge" style="background: rgba(255, 255, 255, 0.22); color: #FFFFFF; backdrop-filter: blur(8px); padding: 6px 14px; font-size: 0.82rem;">
                <i class="fa-solid fa-gamepad"></i> المسار الجانبي التكيفي (Side Quest)
            </span>
            <span class="badge" style="background: rgba(0, 0, 0, 0.25); color: #FFFFFF; border: 1px solid rgba(255, 255, 255, 0.2);">
                المستوى المحفوظ: <?php echo clean_output($user_info['current_level_name']); ?>
            </span>
        </div>

        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0 0 10px; line-height: 1.4;">
            مسار جانبي: تقوية مفهوم «<?php echo clean_output($concept_capsule['concept_name']); ?>»
        </h1>
        <p style="font-size: 0.96rem; opacity: 0.92; line-height: 1.65; margin: 0; max-width: 720px;">
            وفق فلسفة المنصة: <strong>الخبير لا يرجع للوراء</strong>. أجب عن هذه الأسئلة المركزة الأربعة بنسبة 75% فما فوق لإغلاق الثغرة وتثبيت كفاءتك والعودة لمسارك المتقدم مباشرة.
        </p>
    </div>

    <?php if ($quest_result): ?>
        <!-- نتائج المسار الجانبي -->
        <?php if ($quest_result['is_passed']): ?>
            <div class="academic-card" style="border-top: 5px solid #10B981; text-align: center; padding: 40px 30px; margin-bottom: 30px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: #DCFCE7; color: #10B981; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 18px;">
                    <i class="fa-solid fa-award"></i>
                </div>
                <h2 style="color: #065F46; font-size: 1.6rem; margin-bottom: 10px; font-weight: 800;">
                    ألف مبروك! تم إغلاق الثغرة المعرفية بنجاح (<?php echo $quest_result['score']; ?>%)
                </h2>
                <p style="font-size: 1.05rem; color: var(--text-body); max-width: 650px; margin: 0 auto 24px; line-height: 1.65;">
                    أثبتّ الآن استيعابك الكامل للمفهوم الأساسي. تم اعتماد مسارك المتقدم ورفع أي تعليق عن تقدمك الأكاديمي.
                </p>
                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-house"></i> العودة للوحة القيادة
                    </a>
                    <a href="study_module.php?level_id=<?php echo (int)$user_info['current_level_id']; ?>" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-book-open"></i> استكمال دراسة المستوى المتقدم
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="academic-card" style="border-top: 5px solid #EF4444; text-align: center; padding: 36px 30px; margin-bottom: 30px;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #FEE2E2; color: #EF4444; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                    <i class="fa-solid fa-rotate-right"></i>
                </div>
                <h2 style="color: #991B1B; font-size: 1.45rem; margin-bottom: 8px; font-weight: 800;">
                    حققت <?php echo $quest_result['score']; ?>% — تحتاج إلى 75% لاجتياز المسار الجانبي
                </h2>
                <p style="font-size: 0.95rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 20px;">
                    راجع التفسيرات العلمية بالأسفل ثم أعد المحاولة، مستواك كخبير ما زال محفوظاً ولم يتغير.
                </p>
                <a href="side_quest.php?rem_id=<?php echo $rem_id; ?>" class="btn btn-primary">
                    <i class="fa-solid fa-arrows-rotate"></i> إعادة محاولة المسار الجانبي
                </a>
            </div>
        <?php endif; ?>

        <!-- مراجعة الأسئلة مع التفسيرات -->
        <h3 style="font-size: 1.2rem; color: var(--text-heading); margin-bottom: 16px;">
            <i class="fa-solid fa-clipboard-check" style="color: var(--brand-primary);"></i> مراجعة الإجابات والتفسيرات الهندسية:
        </h3>
        <?php foreach ($quest_result['details'] as $idx => $d): ?>
            <div class="quest-question-card" style="border-right: 5px solid <?php echo $d['is_correct'] ? '#10B981' : '#EF4444'; ?>;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-weight: 700; color: var(--text-heading);">سؤال #<?php echo $idx + 1; ?></span>
                    <span class="badge <?php echo $d['is_correct'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $d['is_correct'] ? 'إجابة صحيحة' : 'إجابة خاطئة'; ?>
                    </span>
                </div>
                <p style="font-size: 1rem; color: var(--text-heading); margin-bottom: 14px; line-height: 1.6;">
                    <?php echo clean_output($d['question']['question_text']); ?>
                </p>
                <div style="background: #F8FAFC; padding: 14px; border-radius: 8px; font-size: 0.88rem; color: var(--text-muted); line-height: 1.6;">
                    <strong style="color: var(--text-heading); display: block; margin-bottom: 4px;">التفسير:</strong>
                    <?php echo clean_output($d['question']['explanation']); ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>

        <!-- نموذج حل المسار الجانبي -->
        <form method="POST" action="side_quest.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="rem_id" value="<?php echo $rem_id; ?>">

            <?php foreach ($questions as $index => $q): ?>
                <div class="quest-question-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span class="badge badge-secondary" style="font-size: 0.8rem;">سؤال مركز #<?php echo $index + 1; ?> من <?php echo count($questions); ?></span>
                        <span style="font-size: 0.82rem; color: var(--text-muted);"><?php echo clean_output($concept_capsule['concept_name']); ?></span>
                    </div>

                    <h3 style="font-size: 1.08rem; color: var(--text-heading); line-height: 1.6; margin-bottom: 16px;">
                        <?php echo clean_output($q['question_text']); ?>
                    </h3>

                    <?php if (!empty($q['image_url'])): ?>
                        <div style="margin: 12px 0 16px; text-align: center; background: #0F172A; padding: 10px; border-radius: 8px;">
                            <img src="../<?php echo htmlspecialchars($q['image_url']); ?>" alt="Diagram" style="max-width: 100%; max-height: 280px; border-radius: 6px;">
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label class="option-select-label">
                            <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required>
                            <span><strong>(أ)</strong> <?php echo clean_output($q['option_a']); ?></span>
                        </label>
                        <label class="option-select-label">
                            <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B" required>
                            <span><strong>(ب)</strong> <?php echo clean_output($q['option_b']); ?></span>
                        </label>
                        <label class="option-select-label">
                            <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C" required>
                            <span><strong>(ج)</strong> <?php echo clean_output($q['option_c']); ?></span>
                        </label>
                        <label class="option-select-label">
                            <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D" required>
                            <span><strong>(د)</strong> <?php echo clean_output($q['option_d']); ?></span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 24px;">
                <button type="submit" name="submit_side_quest" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border: none;">
                    <i class="fa-solid fa-check-double"></i> تسليم المسار الجانبي والتحقق من إغلاق الثغرة
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fa-solid fa-house"></i> تأجيل للوحة القيادة
                </a>
            </div>
        </form>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
