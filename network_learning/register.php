<?php
session_start();
$pageTitle = "إنشاء حساب جديد - منصة التعلم التكيفي الذكي للشبكات";

require_once 'config/db.php';
require_once 'config/security.php';

// إذا كان المستخدم مسجلاً دخوله بالفعل، يتم توجيهه
if (isset($_SESSION['user_id'])) {
    header("Location: student/dashboard.php");
    exit();
}

$message = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $special_chars = '!@#$%^&*()_+-=[]{}|;:\'",.<>/?~`';

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "يرجى تعبئة كافة الحقول المطلوبة!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "يرجى إدخال بريد إلكتروني صحيح ومعتمد!";
    } elseif (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || strpbrk($password, $special_chars) === false) {
        $message = "كلمة المرور يجب أن تتكون من 8 خانات على الأقل وتحتوي على أرقام ورموز خاصة (@, #, $)!";
    } else {
        // التحقق من عدم تكرار البريد الإلكتروني
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "البريد الإلكتروني المدخل مسجل بالفعل في المنصة!";
        } else {
            // تشفير كلمة المرور بـ Bcrypt بأعلى معايير الأمان
            $hashedPassword = hash_password_secure($password);
            
            $insert_stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, current_level_id) VALUES (?, ?, ?, 'student', 1)");
            if ($insert_stmt->execute([$full_name, $email, $hashedPassword])) {
                $new_user_id = (int)$pdo->lastInsertId();

                // تسجيل الدخول التلقائي بعد إنشاء الحساب مباشرة (بدون حاجة لإعادة الكتابة)
                session_regenerate_id(true);
                $_SESSION['user_id']   = $new_user_id;
                $_SESSION['user_role'] = 'student';
                $_SESSION['user_name'] = $full_name;

                header("Location: student/dashboard.php?welcome=1");
                exit();
            } else {
                $message = "حدث خطأ غير متوقع أثناء إنشاء الحساب، يرجى المحاولة لاحقاً.";
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-shell">
    
    <div class="auth-card">
        
        <a href="index.php" class="auth-back-link">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة للصفحة الرئيسية</span>
        </a>

        <div style="text-align: center; margin-bottom: 28px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(91, 79, 232, 0.07); border: 1px solid rgba(197, 194, 247, 0.5); padding: 5px 16px; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 700; color: var(--brand-primary); margin-bottom: 18px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 6px #10B981;"></span>
                <span>تسجيل طالب جديد في المسار التكيفي</span>
            </div>

            <img src="assets/img/icon.svg" alt="NetLearn Pro Emblem" style="width: 68px; height: 68px; border-radius: 18px; margin: 0 auto 16px; display: block; box-shadow: 0 8px 26px rgba(91, 79, 232, 0.38); transition: transform 0.3s ease;">
            
            <h1 style="font-size: 1.7rem; color: var(--text-heading); margin-bottom: 6px; font-weight: 800;">إنشاء حساب طالب جديد</h1>
            <p style="color: var(--text-muted); font-size: 0.94rem; margin-bottom: 0;">انضم للمنصة الأكاديمية التكيفية وابدأ مسارك في هندسة الشبكات.</p>
        </div>

        <?php if ($message): ?>
            <div class="academic-alert alert-danger" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px;"></i>
                <div><?php echo clean_output($message); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label style="font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user" style="color: var(--brand-primary);"></i> الاسم الكامل (الطالب):
                </label>
                <input type="text" name="full_name" class="form-control" placeholder="مثال: أحمد عبد الله اليماني" required value="<?php echo clean_output($_POST['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label style="font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-envelope" style="color: var(--brand-primary);"></i> البريد الإلكتروني الأكاديمي أو الشخصي:
                </label>
                <input type="email" name="email" class="form-control" placeholder="student@university.edu" required style="direction: ltr; text-align: left;" value="<?php echo clean_output($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="margin: 0; font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-lock" style="color: var(--brand-primary);"></i> كلمة المرور الآمنة:
                    </label>
                    <button type="button" onclick="togglePasswordVisibility('reg-pass')" style="background: none; border: none; color: var(--brand-primary); cursor: pointer; font-size: 0.82rem; font-family: var(--font-sans); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-eye" id="eye-icon"></i> <span id="eye-text">إظهار</span>
                    </button>
                </div>
                <input type="password" id="reg-pass" name="password" class="form-control" placeholder="8 خانات على الأقل (أرقام ورموز خاصة)" required style="direction: ltr; text-align: left;">
                <span style="font-size: 0.78rem; color: var(--text-muted); display: block; margin-top: 6px;">
                    <i class="fa-solid fa-circle-info"></i> يجب أن تتضمن كلمة المرور 8 خانات على الأقل مع رقم ورمز خاص (@, #, $).
                </span>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 15px; font-weight: 700; border-radius: var(--radius-sm);">
                <i class="fa-solid fa-user-check"></i> تأكيد وإنشاء الحساب الأكاديمي
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-subtle); font-size: 0.92rem;">
            <span style="color: var(--text-muted);">لديك حساب بالفعل؟</span>
            <a href="login.php" style="font-weight: 700; margin-right: 6px; color: var(--brand-primary);">
                <i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول
            </a>
        </div>

    </div>

</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById('eye-icon');
        const text = document.getElementById('eye-text');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
            text.textContent = 'إخفاء';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
            text.textContent = 'إظهار';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>