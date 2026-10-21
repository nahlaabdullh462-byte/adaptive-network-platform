<?php
/**
 * Security & Helper Engine - NetAdaptive Platform
 * تأمين الجلسات، التشفير، الحماية من CSRF، وتطهير البيانات
 */

// بدء الجلسة بإعدادات أمان مشددة إن لم تكن قد بدأت
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * توليد أو جلب رمز CSRF لحماية النماذج
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * طباعة حقل مخفي لرمز CSRF في النماذج
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * التحقق من صحة رمز CSRF
 */
function verify_csrf_token($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif; direction:rtl; text-align:center; padding:50px; background:#0b1120; color:#f87171;">
                <h2><i class="fa-solid fa-shield-halved"></i> خطأ في التحقق من الأمان (CSRF Token Mismatch)</h2>
                <p>انتهت صلاحية الجلسة أو تم إرسال الطلب بشكل غير موثوق. يرجى إعادة المحاولة.</p>
                <a href="javascript:history.back()" style="color:#38bdf8;">العودة للخلف</a>
             </div>');
    }
    return true;
}

/**
 * تطهير النصوص لمنع هجمات XSS
 */
function clean_output($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * تشفير كلمة المرور بـ Bcrypt
 */
function hash_password_secure($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 11]);
}

/**
 * التحقق من كلمة المرور مع دعم الحسابات القديمة غير المشفرة والترقية التلقائية
 */
function verify_and_migrate_password($pdo, $user, $plainPassword) {
    $stored = $user['password'] ?? '';
    
    // إذا كانت كلمة المرور مشفرة بنجاح
    if (password_verify($plainPassword, $stored)) {
        return true;
    }
    
    // دعم التوافقية لحسابات النص الصريح القديمة
    if ($stored === $plainPassword) {
        // ترقية كلمة المرور فوراً لتصبح مشفرة بأمان
        $newHash = hash_password_secure($plainPassword);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$newHash, $user['id']]);
        return true;
    }
    
    return false;
}

/**
 * فحص صلاحيات تسجيل الدخول
 */
function require_auth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    if ($role !== null && ($_SESSION['user_role'] ?? '') !== $role) {
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit();
    }
}
