<?php
$pageTitle = "نظام التجاوز مع المعالجة الدقيقة - Precision Remediation";
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
    // محاولة جلب أي سجل نشط
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

// جلب بيانات المستخدم لمعرفة مستواه المحفوظ
$user_stmt = $pdo->prepare("SELECT u.*, l.level_name as current_level_name FROM users u JOIN levels l ON u.current_level_id = l.id WHERE u.id = ?");
$user_stmt->execute([$user_id]);
$user_info = $user_stmt->fetch();

$topic_id = (int)$remediation['topic_id'];
$concept_capsule = get_topic_micro_capsule($topic_id, $remediation['concept_name']);

// جلب أو تجهيز سؤال التحقق الفوري
$verification_q_id = (int)($remediation['verification_question_id'] ?? 0);
if ($verification_q_id === 0) {
    $exclude_ids = array_filter([(int)$remediation['initial_question_id']]);
    $ver_q = get_remediation_verification_question($pdo, $topic_id, $exclude_ids);
    if ($ver_q) {
        $verification_q_id = (int)$ver_q['id'];
        $pdo->prepare("UPDATE remediation_records SET verification_question_id = ?, remediation_status = 'review_pending' WHERE id = ?")
            ->execute([$verification_q_id, $rem_id]);
    }
} else {
    $q_stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $q_stmt->execute([$verification_q_id]);
    $ver_q = $q_stmt->fetch();
}

$evaluation_result = null;

// معالجة إرسال إجابة سؤال التحقق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_verification'])) {
    verify_csrf_token();
    $chosen_option = trim($_POST['verification_answer'] ?? '');
    
    if (in_array($chosen_option, ['A', 'B', 'C', 'D']) && $ver_q) {
        $is_correct = ($chosen_option === $ver_q['correct_option']);
        
        if ($is_correct) {
            // الحالة الأولى: أجاب صح -> كبوة تركيز (Slip)
            // طنش الغلطة الأولى وخليهو في مستواهو خبير وسجّلها كـ careless_mistake
            $pdo->prepare("
                UPDATE remediation_records 
                SET verification_answer = ?, is_verification_correct = 1, 
                    remediation_status = 'resolved_slip', error_classification = 'careless_mistake' 
                WHERE id = ?
            ")->execute([$chosen_option, $rem_id]);

            $pdo->prepare("UPDATE users SET remediation_status = 'clear' WHERE id = ?")->execute([$user_id]);
            
            $evaluation_result = [
                'status' => 'resolved_slip',
                'type' => 'careless_mistake',
                'title' => 'رائع جداً! كانت مجرد كبوة تركيز (Slip)',
                'message' => 'أجبت عن سؤال التحقق بدقة متناهية. خوارزمية المنصة التكيفية الذكية سجلت الحالة كـ (Careless Mistake / سهو تركيز)، وتجاوزت الخطأ الأول تماماً مع الحفاظ على مستواك المتقدم كـ «' . clean_output($user_info['current_level_name']) . '» دون أي إنزال!',
                'correct_option' => $ver_q['correct_option'],
                'explanation' => $ver_q['explanation']
            ];
        } else {
            // الحالة الثانية: غلط تاني في نفس المفهوم -> ثغرة حقيقية (Knowledge Gap)
            // ما تنزلو مستوى كامل، افتح ليهو مسار جانبي (Side Quest)
            $pdo->prepare("
                UPDATE remediation_records 
                SET verification_answer = ?, is_verification_correct = 0, 
                    remediation_status = 'side_quest_active', error_classification = 'knowledge_gap' 
                WHERE id = ?
            ")->execute([$chosen_option, $rem_id]);

            $pdo->prepare("UPDATE users SET remediation_status = 'side_quest_active' WHERE id = ?")->execute([$user_id]);

            $evaluation_result = [
                'status' => 'side_quest_active',
                'type' => 'knowledge_gap',
                'title' => 'رصد ثغرة معرفية حقيقية — تم فتح مسار جانبي (Side Quest)!',
                'message' => 'تكرار الخطأ في هذا المفهوم الأساسي يدل على وجود ثغرة تحتاج لترسيخ سريع. عملاً بفلسفة «الخبير ما بيرجع ورا»، لن نقوم بإنزال مستواك إلى مبتدئ، بل فتحنا لك مساراً جانبياً قصيراً (Side Quest) مكوناً من 3-4 أسئلة مركزة لتقوية هذه الثغرة وإغلاقها قبل متابعة تقدمك.',
                'correct_option' => $ver_q['correct_option'],
                'explanation' => $ver_q['explanation']
            ];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.remediation-container {
    max-width: 860px;
    margin: 0 auto 40px;
}

.remediation-hero {
    background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%);
    border-radius: var(--radius-xl);
    padding: 36px 32px;
    color: #FFFFFF;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 16px 40px -10px rgba(67, 56, 202, 0.35);
}
.remediation-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 85% 20%, rgba(129, 140, 248, 0.25) 0%, transparent 60%);
    pointer-events: none;
}

.capsule-box {
    background: #FFFFFF;
    border: 2px solid rgba(91, 79, 232, 0.25);
    border-radius: var(--radius-xl);
    padding: 30px;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px -8px rgba(0, 0, 0, 0.06);
    position: relative;
}

.timer-circle-widget {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(238, 242, 255, 0.85);
    border: 1px solid rgba(91, 79, 232, 0.3);
    padding: 8px 18px;
    border-radius: var(--radius-full);
    font-weight: 700;
    color: var(--brand-primary);
}

.rule-pill {
    background: #F0FDF4;
    border-right: 4px solid #10B981;
    padding: 14px 18px;
    border-radius: 8px;
    margin: 16px 0;
    font-size: 0.92rem;
    color: #065F46;
}

.slip-warning-pill {
    background: #FFFBEB;
    border-right: 4px solid #F59E0B;
    padding: 14px 18px;
    border-radius: 8px;
    margin: 14px 0;
    font-size: 0.92rem;
    color: #92400E;
}

.option-radio-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    border-radius: var(--radius-md);
    border: 2px solid var(--border-main);
    background: var(--bg-surface-solid);
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 12px;
}
.option-radio-card:hover {
    border-color: var(--brand-primary);
    background: rgba(91, 79, 232, 0.03);
}
.option-radio-card input[type="radio"]:checked + .opt-body {
    color: var(--brand-primary);
    font-weight: 700;
}
.option-radio-card:has(input[type="radio"]:checked) {
    border-color: var(--brand-primary);
    background: rgba(91, 79, 232, 0.06);
    box-shadow: 0 4px 14px rgba(91, 79, 232, 0.12);
}
</style>

<div class="remediation-container">

    <!-- Hero Header -->
    <div class="remediation-hero">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 14px;">
            <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #FFFFFF; backdrop-filter: blur(8px); padding: 6px 14px; font-size: 0.82rem;">
                <i class="fa-solid fa-microchip"></i> محرك التعليم التكيفي الذكي
            </span>
            <span class="badge" style="background: #10B981; color: #FFFFFF; font-weight: 700;">
                <i class="fa-solid fa-shield-halved"></i> مستواك الحالي محفوظ: <?php echo clean_output($user_info['current_level_name']); ?>
            </span>
        </div>

        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0 0 10px; line-height: 1.4;">
            نظام التجاوز مع المعالجة الدقيقة (Precision Remediation)
        </h1>
        <p style="font-size: 0.98rem; opacity: 0.9; line-height: 1.65; margin: 0; max-width: 720px;">
            لاحظنا أنك أخطأت في مفهوم الـ <strong style="color: #FDE047;">«<?php echo clean_output($concept_capsule['concept_name']); ?>»</strong> وهو مفهوم أساسي.
            بدلاً من إنزالك للمبتدئ، سنقوم بمراجعة سريعة لمدة <strong>30 ثانية</strong> يتبعها سؤال تحقق فوري لحسم الموقف ذكياً.
        </p>
    </div>

    <?php if ($evaluation_result): ?>
        <!-- ========================================================================= -->
        <!-- شاشة نتيجة سؤال التحقق الفوري (الحالتين: Slip أو Side Quest)              -->
        <!-- ========================================================================= -->
        <?php if ($evaluation_result['status'] === 'resolved_slip'): ?>
            <!-- الحالة الأولى: Slip مؤكد -->
            <div class="academic-card" style="border-top: 5px solid #10B981; text-align: center; padding: 42px 30px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: #DCFCE7; color: #10B981; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 style="color: #065F46; font-size: 1.6rem; margin-bottom: 12px; font-weight: 800;">
                    <?php echo $evaluation_result['title']; ?>
                </h2>
                <div style="display: inline-block; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 20px; padding: 4px 16px; color: #047857; font-size: 0.85rem; font-family: var(--font-code); font-weight: 700; margin-bottom: 16px;">
                    التصنيف الذكي: careless_mistake (غلطة تركيز عابرة)
                </div>
                <p style="font-size: 1.05rem; color: var(--text-body); max-width: 650px; margin: 0 auto 24px; line-height: 1.7;">
                    <?php echo $evaluation_result['message']; ?>
                </p>

                <div style="background: #F8FAFC; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 18px; max-width: 650px; margin: 0 auto 28px; text-align: right;">
                    <strong style="color: var(--text-heading); display: block; margin-bottom: 6px;">
                        <i class="fa-solid fa-lightbulb" style="color: #F59E0B;"></i> التفسير العلمي للإجابة:
                    </strong>
                    <div style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6;">
                        <?php echo clean_output($evaluation_result['explanation']); ?>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-house"></i> الانتقال للوحة القيادة
                    </a>
                    <a href="study_module.php?level_id=<?php echo (int)$user_info['current_level_id']; ?>" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-arrow-left"></i> متابعة دراسة مستواك المتقدم
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- الحالة الثانية: ثغرة حقيقية -> فتح Side Quest -->
            <div class="academic-card" style="border-top: 5px solid #F59E0B; text-align: center; padding: 42px 30px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: inline-flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px;">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <h2 style="color: #92400E; font-size: 1.6rem; margin-bottom: 12px; font-weight: 800;">
                    <?php echo $evaluation_result['title']; ?>
                </h2>
                <div style="display: inline-block; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 20px; padding: 4px 16px; color: #B45309; font-size: 0.85rem; font-family: var(--font-code); font-weight: 700; margin-bottom: 16px;">
                    التصنيف الذكي: knowledge_gap (ثغرة مفهومية محددة)
                </div>
                <p style="font-size: 1.05rem; color: var(--text-body); max-width: 650px; margin: 0 auto 24px; line-height: 1.7;">
                    <?php echo $evaluation_result['message']; ?>
                </p>

                <div class="academic-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(91, 79, 232, 0.08) 100%); border: 1.5px dashed #F59E0B; max-width: 650px; margin: 0 auto 28px; text-align: right; padding: 22px;">
                    <div style="font-weight: 800; color: #92400E; font-size: 1.05rem; margin-bottom: 6px;">
                        <i class="fa-solid fa-compass"></i> مهمتك الجانبية المخصصة:
                    </div>
                    <p style="font-size: 0.92rem; color: var(--text-muted); margin: 0 0 12px; line-height: 1.6;">
                        عبارة عن <strong>3-4 أسئلة مركزة</strong> عن مفهوم «<?php echo clean_output($concept_capsule['concept_name']); ?>» فقط.
                        بمجرد تحقيق 75% فيها، سيتم إغلاق الثغرة واعتماد مسارك المتقدم فوراً دون دراسة المنهج كله من البداية.
                    </p>
                    <div style="font-size: 0.85rem; color: #059669; font-weight: 700;">
                        <i class="fa-solid fa-shield-check"></i> مستواك «<?php echo clean_output($user_info['current_level_name']); ?>» لن يتأثر وسيبقى متاحاً لك.
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="side_quest.php?rem_id=<?php echo $rem_id; ?>" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border: none; box-shadow: 0 8px 24px rgba(217, 119, 6, 0.35);">
                        <i class="fa-solid fa-play"></i> بدء المسار الجانبي الآن (Side Quest)
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg">
                        <i class="fa-solid fa-clock"></i> تأجيل وإكمال لاحقاً
                    </a>
                </div>
            </div>

        <?php endif; ?>

    <?php else: ?>

        <!-- ========================================================================= -->
        <!-- بطاقة كبسولة الـ 30 ثانية الأكاديمية التفاعلية                             -->
        <!-- ========================================================================= -->
        <div class="capsule-box" id="capsule-section">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(91, 79, 232, 0.1); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-stopwatch-20"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                            كبسولة المراجعة المركزة (30 ثانية)
                        </h2>
                        <span style="font-size: 0.82rem; color: var(--text-muted);">
                            المفهوم: <?php echo clean_output($concept_capsule['concept_name']); ?>
                        </span>
                    </div>
                </div>

                <!-- مؤقت 30 ثانية -->
                <div class="timer-circle-widget">
                    <i class="fa-solid fa-hourglass-half fa-spin" style="--fa-animation-duration: 2s;"></i>
                    <span>مؤقت الاستيعاب:</span>
                    <span id="countdown-timer" style="font-family: var(--font-code); font-size: 1.15rem; font-weight: 900; color: #4338CA;">30s</span>
                </div>
            </div>

            <!-- نص الشرح 30 ثانية -->
            <div style="font-size: 1rem; color: var(--text-body); line-height: 1.75; margin-bottom: 16px; background: #F8FAFC; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                <?php echo clean_output($concept_capsule['summary_30s']); ?>
            </div>

            <!-- القاعدة الذهبية -->
            <div class="rule-pill">
                <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="fa-solid fa-key"></i> القاعدة الذهبية لتفادي الخطأ (Golden Rule):
                </strong>
                <span><?php echo clean_output($concept_capsule['golden_rule']); ?></span>
            </div>

            <!-- الهفوة الشائعة (Common Slip) -->
            <div class="slip-warning-pill">
                <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> سبب الخطأ الشائع لدى الخبراء (The Common Slip):
                </strong>
                <span><?php echo clean_output($concept_capsule['common_slip']); ?></span>
            </div>

            <div style="text-align: center; margin-top: 22px;">
                <button type="button" id="btn-ready" class="btn btn-primary btn-lg" onclick="scrollToQuestion()">
                    <i class="fa-solid fa-arrow-down"></i> استوعبت المفهوم، انتقل لسؤال التحقق الفوري
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- سؤال التحقق الفوري (Verification Question)                                -->
        <!-- ========================================================================= -->
        <?php if ($ver_q): ?>
            <div class="academic-card" id="question-section" style="border-top: 4px solid var(--brand-primary); padding: 32px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <span class="badge badge-primary">
                        <i class="fa-solid fa-circle-question"></i> سؤال التحقق الفوري (Verification Gate)
                    </span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-family: var(--font-code);">
                        سؤال تشخيصي #1 من 1
                    </span>
                </div>

                <h3 style="font-size: 1.15rem; color: var(--text-heading); line-height: 1.6; margin-bottom: 20px;">
                    <?php echo clean_output($ver_q['question_text']); ?>
                </h3>

                <?php if (!empty($ver_q['image_url'])): ?>
                    <div style="margin: 14px 0 20px; text-align: center; background: #0F172A; padding: 12px; border-radius: 8px;">
                        <img src="../<?php echo htmlspecialchars($ver_q['image_url']); ?>" alt="Diagram" style="max-width: 100%; max-height: 320px; border-radius: 6px;">
                    </div>
                <?php endif; ?>

                <form method="POST" action="precision_remediation.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="rem_id" value="<?php echo $rem_id; ?>">

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
                        <label class="option-radio-card">
                            <input type="radio" name="verification_answer" value="A" required style="margin-top: 4px;">
                            <div class="opt-body">
                                <strong>(أ)</strong> <?php echo clean_output($ver_q['option_a']); ?>
                            </div>
                        </label>

                        <label class="option-radio-card">
                            <input type="radio" name="verification_answer" value="B" required style="margin-top: 4px;">
                            <div class="opt-body">
                                <strong>(ب)</strong> <?php echo clean_output($ver_q['option_b']); ?>
                            </div>
                        </label>

                        <label class="option-radio-card">
                            <input type="radio" name="verification_answer" value="C" required style="margin-top: 4px;">
                            <div class="opt-body">
                                <strong>(ج)</strong> <?php echo clean_output($ver_q['option_c']); ?>
                            </div>
                        </label>

                        <label class="option-radio-card">
                            <input type="radio" name="verification_answer" value="D" required style="margin-top: 4px;">
                            <div class="opt-body">
                                <strong>(د)</strong> <?php echo clean_output($ver_q['option_d']); ?>
                            </div>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <button type="submit" name="submit_verification" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-paper-plane"></i> إرسال الإجابة وحسم التقييم التكيفي
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary" onclick="return confirm('هل تريد العودة للوحة القيادة وإكمال المعالجة لاحقاً؟')">
                            <i class="fa-solid fa-house"></i> تأجيل
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
let remainingSeconds = 30;
const timerEl = document.getElementById('countdown-timer');

if (timerEl) {
    const countdownInterval = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(countdownInterval);
            timerEl.innerText = "0s (جاهز)";
            timerEl.style.color = "#10B981";
        } else {
            timerEl.innerText = remainingSeconds + "s";
        }
    }, 1000);
}

function scrollToQuestion() {
    const qSection = document.getElementById('question-section');
    if (qSection) {
        qSection.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
