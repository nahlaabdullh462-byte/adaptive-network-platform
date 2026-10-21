<?php
$pageTitle = "تحديث كلمة المرور الإلزامية";
require_once '../config/db.php';
require_once '../config/security.php';
require_auth();

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $special_chars = '!@#$%^&*()_+-=[]{}|;:\'",.<>/?~`';

    if (empty($new_pass) || empty($confirm_pass)) {
        $message = "يرجى تعبئة كافة الحقول المطلوبة!";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "كلمة المرور وتأكيدها غير متطابقين!";
    } elseif (strlen($new_pass) < 8 || !preg_match('/[0-9]/', $new_pass) || strpbrk($new_pass, $special_chars) === false) {
        $message = "كلمة المرور يجب ألا تقل عن 8 خانات وتحتوي على أرقام ورموز خاصة!";
    } else {
        $hashed = hash_password_secure($new_pass);
        $up = $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
        $up->execute([$hashed, $user_id]);

        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/dashboard.php?pass_updated=1");
        } else {
            header("Location: dashboard.php?pass_updated=1");
        }
        exit();
    }
}

require_once '../includes/header.php';
?>

<div style="max-width: 500px; margin: 40px auto;">
    <div class="academic-card" style="border-top: 4px solid var(--primary); padding: 35px 28px;">
        <div style="text-align: center; margin-bottom: 22px;">
            <div style="font-size: 38px; color: var(--primary); margin-bottom: 10px;">
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 style="font-size: 1.45rem; color: var(--text-primary); margin-bottom: 6px;">تعيين كلمة مرور جديدة</h1>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0;">
                تمت إعادة تعيين كلمة مرورك بكلمة مؤقتة؛ يرجى تحديد كلمة مرور خاصة وجديدة لمتابعة الدخول بأمان.
            </p>
        </div>

        <?php if ($message): ?>
            <div class="academic-alert alert-<?php echo $message_type; ?>">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><?php echo clean_output($message); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>كلمة المرور الجديدة:</label>
                <input type="password" name="new_password" class="form-control" placeholder="8 خانات على الأقل مع رموز خاصة" required style="direction: ltr; text-align: left;">
            </div>

            <div class="form-group">
                <label>تأكيد كلمة المرور الجديدة:</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="أعد إدخال كلمة المرور" required style="direction: ltr; text-align: left;">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
                <i class="fa-solid fa-shield-check"></i> حفظ كلمة المرور ومتابعة الدخول
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
