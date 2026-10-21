<?php
session_start();
$pageTitle = "تسجيل الدخول - منصة التعلم التكيفي الذكي للشبكات";

require_once 'config/db.php';
require_once 'config/security.php';

// إذا كان المستخدم مسجلاً دخوله بالفعل، يتم توجيهه
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}

$message = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "يرجى إدخال البريد الإلكتروني وكلمة المرور!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "صيغة البريد الإلكتروني المدخلة غير صحيحة!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verify_and_migrate_password($pdo, $user, $password)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // إذا كان مفروضاً عليه تغيير كلمة المرور بعد إعادة التعيين
            if (!empty($user['must_change_password'])) {
                header("Location: student/change_password.php");
                exit();
            }

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة!";
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

        <!-- الهيدر لشاشة الدخول -->
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(91, 79, 232, 0.07); border: 1px solid rgba(197, 194, 247, 0.5); padding: 5px 16px; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 700; color: var(--brand-primary); margin-bottom: 18px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 6px #10B981;"></span>
                <span>بوابة الدخول الذكية المعتمدة</span>
            </div>

            <img src="assets/img/icon.svg" alt="NetLearn Pro Emblem" style="width: 68px; height: 68px; border-radius: 18px; margin: 0 auto 16px; display: block; box-shadow: 0 8px 26px rgba(91, 79, 232, 0.38); transition: transform 0.3s ease;">
            
            <h1 style="font-size: 1.7rem; color: var(--text-heading); margin-bottom: 6px; font-weight: 800;">تسجيل الدخول الآمن</h1>
            <p style="color: var(--text-muted); font-size: 0.94rem; margin-bottom: 0;">أدخل بيانات حسابك للمتابعة في بيئة هندسة الشبكات التكيفية.</p>
        </div>

        <?php if (isset($_GET['registered'])): ?>
            <div class="academic-alert alert-success" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
                <div>تم إنشاء حسابك الأكاديمي بنجاح! يمكنك الآن تسجيل الدخول مباشرة.</div>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="academic-alert alert-danger" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px;"></i>
                <div><?php echo clean_output($message); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="login-form">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label style="font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-envelope" style="color: var(--brand-primary);"></i> البريد الإلكتروني:
                </label>
                <input type="email" id="email-input" name="email" class="form-control" placeholder="name@domain.edu" required style="direction: ltr; text-align: left;">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="margin: 0; font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-lock" style="color: var(--brand-primary);"></i> كلمة المرور:
                    </label>
                    <button type="button" onclick="togglePasswordVisibility('login-pass')" style="background: none; border: none; color: var(--brand-primary); cursor: pointer; font-size: 0.82rem; font-family: var(--font-sans); font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-eye" id="eye-icon"></i> <span id="eye-text">إظهار</span>
                    </button>
                </div>
                <input type="password" id="login-pass" name="password" class="form-control" placeholder="••••••••" required style="direction: ltr; text-align: left;">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px; font-weight: 700; border-radius: var(--radius-sm);">
                <i class="fa-solid fa-right-to-bracket"></i> الدخول إلى حسابي
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-subtle); font-size: 0.92rem;">
            <span style="color: var(--text-muted);">ليس لديك حساب مسجل بعد؟</span>
            <a href="register.php" style="font-weight: 700; margin-right: 6px; color: var(--brand-primary);">
                <i class="fa-solid fa-user-plus"></i> إنشاء حساب جديد
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