<?php
$pageTitle = "إدارة ومتابعة شؤون الطلاب";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_auth('admin');

$message = '';
$message_type = 'success';

// 1. تعديل مستوى طالب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_level'])) {
    verify_csrf_token();
    $student_id = intval($_POST['student_id']);
    $new_level = intval($_POST['new_level_id']);
    
    $stmt = $pdo->prepare("UPDATE users SET current_level_id = ? WHERE id = ? AND role = 'student'");
    $stmt->execute([$new_level, $student_id]);
    $message = "تم تحديث المستوى الدراسي للطالب بنجاح واعتماده بالمسار التكيفي!";
}

// 2. إعادة تعيين كلمة مرور طالب بنمط آمن
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    verify_csrf_token();
    $student_id = intval($_POST['student_id']);
    
    $secure_bytes = bin2hex(random_bytes(4));
    $temp_pass = "Net@" . ucfirst($secure_bytes) . "!";
    $hashed = hash_password_secure($temp_pass);

    $stmt = $pdo->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ? AND role = 'student'");
    $stmt->execute([$hashed, $student_id]);
    $message = "تمت إعادة تعيين كلمة المرور بنجاح! كلمة المرور المؤقتة هي: <code style='font-size:1.1rem; color:var(--brand-primary); font-weight:800; background:rgba(91,79,232,0.1); padding:2px 8px; border-radius:6px;'>{$temp_pass}</code> (سيلزم الطالب بتغييرها فور الدخول).";
}

// 3. حذف طالب مع حذف سجلاته التابعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    verify_csrf_token();
    $student_id = intval($_POST['student_id']);
    
    $pdo->prepare("DELETE FROM attempt_questions WHERE attempt_id IN (SELECT id FROM exam_attempts WHERE user_id = ?)")->execute([$student_id]);
    $pdo->prepare("DELETE FROM exam_results WHERE user_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM exam_attempts WHERE user_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM lab_attempts WHERE user_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM learning_progress WHERE user_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$student_id]);
    
    $message = "تم حذف حساب الطالب وجميع سجلاته التقييمية ومحاولاته بنجاح!";
}

// 4. جلب المستويات والبيانات الإحصائية
$levels = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// البحث والفلترة
$search = trim($_GET['search'] ?? '');
$filter_level = intval($_GET['filter_level'] ?? 0);

$query_sql = "
    SELECT 
        u.*, 
        l.level_name,
        l.level_order,
        COUNT(DISTINCT a.id) as total_exams,
        COALESCE(ROUND(AVG(a.score)), 0) as avg_score,
        COALESCE(MAX(a.score), 0) as max_score,
        SUM(CASE WHEN a.passed = 1 THEN 1 ELSE 0 END) as passed_exams,
        COUNT(DISTINCT la.id) as total_labs
    FROM users u
    LEFT JOIN levels l ON u.current_level_id = l.id
    LEFT JOIN exam_attempts a ON a.user_id = u.id AND a.status = 'completed'
    LEFT JOIN lab_attempts la ON la.user_id = u.id
    WHERE u.role = 'student'
";
$params = [];

if ($search !== '') {
    $query_sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_level > 0) {
    $query_sql .= " AND u.current_level_id = ?";
    $params[] = $filter_level;
}

$query_sql .= " GROUP BY u.id ORDER BY u.id DESC";

$stmt = $pdo->prepare($query_sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات سريعة للرؤوس
$total_students_count = count($students);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- هيدر الصفحة -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    <div>
        <span class="badge badge-primary" style="margin-bottom: 6px;">
            <i class="fa-solid fa-user-graduate"></i> شؤون الطلاب والمسار الأكاديمي
        </span>
        <h1 style="font-size: 1.7rem; margin-bottom: 4px; color: var(--text-heading); font-weight: 800;">
            إدارة ومتابعة أداء الطلاب
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 0;">
            متابعة دقيقة لمستويات الطلاب، متوسط درجاتهم في الاختبارات، وإدارة حساباتهم بأمان.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="dashboard.php" class="btn btn-secondary btn-sm" style="font-weight: 700;">
            <i class="fa-solid fa-chart-line"></i> لوحة الإحصائيات
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="academic-alert alert-success" style="border-radius: var(--radius-sm); margin-bottom: 20px;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
        <div><?php echo $message; ?></div>
    </div>
<?php endif; ?>

<!-- شريط الفلترة والبحث السريع -->
<div class="academic-card" style="margin-bottom: 25px; padding: 18px 22px;">
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="ابحث باسم الطالب أو بريده الإلكتروني..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div style="min-width: 200px;">
            <select name="filter_level" class="form-control">
                <option value="0">كافة المستويات الأكاديمية</option>
                <?php foreach ($levels as $l): ?>
                    <option value="<?php echo $l['id']; ?>" <?php echo $filter_level == $l['id'] ? 'selected' : ''; ?>>
                        مستوى <?php echo $l['level_order']; ?>: <?php echo htmlspecialchars($l['level_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="white-space: nowrap; font-weight: 700;">
            <i class="fa-solid fa-magnifying-glass"></i> تصفية
        </button>

        <?php if ($search !== '' || $filter_level > 0): ?>
            <a href="manage_students.php" class="btn btn-secondary btn-sm">إلغاء التصفية</a>
        <?php endif; ?>
    </form>
</div>

<!-- جدول الطلاب الأكاديمي المطور -->
<div class="academic-card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
    <div style="padding: 16px 22px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: var(--text-heading); font-size: 1rem;">قائمة الطلاب المسجلين بالمنصة</strong>
        <span class="badge badge-primary"><?php echo count($students); ?> طالباً مطابقاً</span>
    </div>

    <?php if (empty($students)): ?>
        <div style="text-align: center; padding: 45px 20px; color: var(--text-muted);">
            <i class="fa-solid fa-users-slash" style="font-size: 40px; margin-bottom: 12px; color: var(--text-dim);"></i>
            <p style="margin: 0; font-size: 0.95rem;">لا يوجد طلاب مطابقين لمعايير البحث الحالية.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="academic-table" style="box-shadow: none; border: none; margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>بيانات الطالب</th>
                        <th>المستوى الدراسي المعتمد</th>
                        <th>مؤشرات الأداء</th>
                        <th>تاريخ الانضمام</th>
                        <th>إجراءات الحساب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $stu): ?>
                        <tr>
                            <td class="code-font" style="font-size: 0.82rem; color: var(--text-muted);">#<?php echo $stu['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 38px; height: 38px; border-radius: 12px; background: var(--brand-gradient); color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.92rem; box-shadow: 0 4px 12px rgba(91, 79, 232, 0.25);">
                                        <?php echo mb_substr($stu['full_name'], 0, 1, 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <strong style="color: var(--text-heading); font-size: 0.95rem; display: block; line-height: 1.2;">
                                            <?php echo htmlspecialchars($stu['full_name']); ?>
                                        </strong>
                                        <span style="font-size: 0.78rem; color: var(--text-muted); font-family: var(--font-mono);">
                                            <?php echo htmlspecialchars($stu['email']); ?>
                                        </span>
                                        <?php if (!empty($stu['must_change_password'])): ?>
                                            <span class="badge badge-warning" style="font-size: 0.68rem; margin-top: 3px; display: inline-block;">
                                                <i class="fa-solid fa-key"></i> بانتظار تغيير كلمة المرور
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" style="display: inline-flex; align-items: center; gap: 6px;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
                                    <select name="new_level_id" class="form-control" style="padding: 5px 8px; font-size: 0.82rem; width: auto; font-weight: 600;">
                                        <?php foreach ($levels as $l): ?>
                                            <option value="<?php echo $l['id']; ?>" <?php echo $stu['current_level_id'] == $l['id'] ? 'selected' : ''; ?>>
                                                مستوى <?php echo $l['level_order']; ?>: <?php echo htmlspecialchars($l['level_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_level" class="btn btn-secondary btn-sm" title="حفظ المستوى وتحديثه في المسار التكيفي">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem;">
                                        <span style="color: var(--text-muted);">الاختبارات:</span>
                                        <strong style="font-family: var(--font-mono); color: var(--brand-primary);"><?php echo $stu['total_exams']; ?></strong>
                                        <span style="color: var(--text-dim);">|</span>
                                        <span style="color: var(--text-muted);">المتوسط:</span>
                                        <strong style="font-family: var(--font-mono); color: <?php echo $stu['avg_score'] >= 75 ? '#059669' : '#D97706'; ?>;"><?php echo $stu['avg_score']; ?>%</strong>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        أعلى درجة: <strong style="color: var(--text-heading); font-family: var(--font-mono);"><?php echo $stu['max_score']; ?>%</strong> 
                                        (اجتاز <?php echo $stu['passed_exams']; ?>)
                                    </div>
                                </div>
                            </td>
                            <td class="code-font" style="font-size: 0.82rem; color: var(--text-muted);">
                                <?php echo date('Y-m-d', strtotime($stu['created_at'])); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <!-- زر عرض تقرير الطالب الأكاديمي -->
                                    <button type="button" onclick="showStudentModal(<?php echo htmlspecialchars(json_encode($stu, JSON_UNESCAPED_UNICODE)); ?>)" class="btn btn-secondary btn-sm" title="استعراض بطاقة أداء الطالب" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-id-card"></i> تقرير
                                    </button>

                                    <!-- إعادة تعيين كلمة المرور -->
                                    <form method="POST" onsubmit="return confirm('هل تريد إعادة تعيين كلمة المرور لهذا الطالب وتوليد كلمة مرور مؤقتة آمنة؟');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
                                        <button type="submit" name="reset_password" class="btn btn-outline btn-sm" title="إعادة تعيين كلمة المرور">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>

                                    <!-- حذف الحساب -->
                                    <form method="POST" onsubmit="return confirm('تحذير نهائي: سيتم حذف هذا الطالب وكافة سجلات اختباراته ومحاولاته بشكل كامل. هل أنت متأكد؟');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
                                        <button type="submit" name="delete_student" class="btn btn-danger btn-sm" title="حذف الطالب">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- Modal: بطاقة أداء الطالب الأكاديمية (Student Performance Modal)              -->
<!-- ========================================================================= -->
<div id="studentModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.65); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--bg-surface-card); border:1px solid rgba(208,213,229,0.8); border-radius:var(--radius-xl); max-width:550px; width:100%; padding:28px; box-shadow:0 25px 60px rgba(0,0,0,0.3); position:relative;">
        <button type="button" onclick="closeStudentModal()" style="position:absolute; top:20px; left:20px; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px; padding-bottom:18px; border-bottom:1px solid var(--border-subtle);">
            <div id="m-avatar" style="width:48px; height:48px; border-radius:14px; background:var(--brand-gradient); color:#FFF; display:flex; align-items:center; justify-content:center; font-size:1.3rem; font-weight:800;">
            </div>
            <div>
                <h3 id="m-name" style="margin:0; font-size:1.2rem; color:var(--text-heading); font-weight:800;"></h3>
                <span id="m-email" style="font-size:0.84rem; color:var(--text-muted); font-family:var(--font-mono);"></span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
            <div style="padding:12px 14px; background:rgba(91,79,232,0.06); border-radius:var(--radius-sm); border:1px solid rgba(91,79,232,0.15);">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">المستوى الحالي</div>
                <strong id="m-level" style="font-size:0.95rem; color:var(--brand-primary);"></strong>
            </div>
            <div style="padding:12px 14px; background:rgba(6,182,212,0.06); border-radius:var(--radius-sm); border:1px solid rgba(6,182,212,0.15);">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">متوسط الدرجات</div>
                <strong id="m-avg" style="font-size:1.1rem; font-family:var(--font-mono); color:var(--brand-secondary);"></strong>
            </div>
            <div style="padding:12px 14px; background:rgba(16,185,129,0.06); border-radius:var(--radius-sm); border:1px solid rgba(16,185,129,0.15);">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">جلسات الاختبارات</div>
                <strong id="m-exams" style="font-size:1.1rem; font-family:var(--font-mono); color:#059669;"></strong>
            </div>
            <div style="padding:12px 14px; background:rgba(245,158,11,0.06); border-radius:var(--radius-sm); border:1px solid rgba(245,158,11,0.15);">
                <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">أعلى نتيجة مسجلة</div>
                <strong id="m-max" style="font-size:1.1rem; font-family:var(--font-mono); color:#D97706;"></strong>
            </div>
        </div>

        <div style="text-align:left;">
            <button type="button" onclick="closeStudentModal()" class="btn btn-secondary btn-sm" style="font-weight:700;">
                إغلاق النافذة
            </button>
        </div>
    </div>
</div>

<script>
function showStudentModal(stu) {
    document.getElementById('m-name').textContent = stu.full_name;
    document.getElementById('m-email').textContent = stu.email;
    document.getElementById('m-avatar').textContent = stu.full_name.charAt(0);
    document.getElementById('m-level').textContent = stu.level_name || 'غير محدد';
    document.getElementById('m-avg').textContent = stu.avg_score + '%';
    document.getElementById('m-exams').textContent = stu.total_exams + ' اختبار (اجتاز ' + stu.passed_exams + ')';
    document.getElementById('m-max').textContent = stu.max_score + '%';

    const modal = document.getElementById('studentModal');
    modal.style.display = 'flex';
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>