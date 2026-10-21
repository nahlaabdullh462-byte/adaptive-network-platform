<?php
$pageTitle = "إدارة بنك الأسئلة والمحتوى التقييمي";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_auth('admin');

$message = '';
$message_type = 'success';

// 1. إضافة سؤال جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    verify_csrf_token();

    $level_id = intval($_POST['level_id']);
    $topic_id = !empty($_POST['topic_id']) ? intval($_POST['topic_id']) : null;
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = in_array($_POST['correct_option'] ?? '', ['A', 'B', 'C', 'D']) ? $_POST['correct_option'] : 'A';
    $difficulty = in_array($_POST['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $_POST['difficulty'] : 'medium';
    $domain_name = trim($_POST['domain_name'] ?? '');
    $explanation = trim($_POST['explanation'] ?? '');
    $image_url = !empty($_POST['image_url']) ? trim($_POST['image_url']) : null;

    if (!empty($question_text) && !empty($option_a) && !empty($option_b)) {
        $stmt = $pdo->prepare("
            INSERT INTO questions (level_id, topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, duration_minutes, difficulty, domain_name, explanation, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 15, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$level_id, $topic_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty, $domain_name, $explanation, $image_url])) {
            $message = "تمت إضافة السؤال بنجاح وتحديث بنك الأسئلة المعتمد!";
        }
    } else {
        $message = "يرجى إدخال نص السؤال والخيارات الأساسية على الأقل!";
        $message_type = 'danger';
    }
}

// 2. تعديل سؤال قائم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    verify_csrf_token();
    $edit_id = intval($_POST['edit_id']);
    $level_id = intval($_POST['level_id']);
    $topic_id = !empty($_POST['topic_id']) ? intval($_POST['topic_id']) : null;
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = in_array($_POST['correct_option'] ?? '', ['A', 'B', 'C', 'D']) ? $_POST['correct_option'] : 'A';
    $difficulty = in_array($_POST['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $_POST['difficulty'] : 'medium';
    $domain_name = trim($_POST['domain_name'] ?? '');
    $explanation = trim($_POST['explanation'] ?? '');
    $image_url = !empty($_POST['image_url']) ? trim($_POST['image_url']) : null;

    $up_stmt = $pdo->prepare("
        UPDATE questions 
        SET level_id = ?, topic_id = ?, question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?, difficulty = ?, domain_name = ?, explanation = ?, image_url = ?
        WHERE id = ?
    ");
    $up_stmt->execute([$level_id, $topic_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty, $domain_name, $explanation, $image_url, $edit_id]);
    $message = "تم تحديث بيانات السؤال وشروحاته التفسيرية بنجاح!";
}

// 3. حذف سؤال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf_token();
    $delete_id = intval($_POST['delete_id']);
    $pdo->prepare("DELETE FROM attempt_questions WHERE question_id = ?")->execute([$delete_id]);
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$delete_id]);
    $message = "تم حذف السؤال من بنك الأسئلة المعتمد بنجاح!";
}

// 4. الفلاتر والبحث
$filter_level = isset($_GET['filter_level']) ? intval($_GET['filter_level']) : 0;
$filter_diff = trim($_GET['filter_diff'] ?? '');
$search_q = trim($_GET['search'] ?? '');

$levels = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$topics = $pdo->query("SELECT * FROM topics ORDER BY level_id ASC, order_num ASC")->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات بنك الأسئلة
$total_q_count = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$easy_count = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE difficulty = 'easy'")->fetchColumn();
$med_count = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE difficulty = 'medium'")->fetchColumn();
$hard_count = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE difficulty = 'hard'")->fetchColumn();

$query_sql = "
    SELECT q.*, l.level_name, l.level_order, t.topic_name 
    FROM questions q 
    JOIN levels l ON q.level_id = l.id 
    LEFT JOIN topics t ON q.topic_id = t.id
    WHERE 1=1
";
$params = [];

if ($filter_level > 0) {
    $query_sql .= " AND q.level_id = ?";
    $params[] = $filter_level;
}
if (!empty($filter_diff)) {
    $query_sql .= " AND q.difficulty = ?";
    $params[] = $filter_diff;
}
if (!empty($search_q)) {
    $query_sql .= " AND (q.question_text LIKE ? OR q.domain_name LIKE ?)";
    $params[] = "%$search_q%";
    $params[] = "%$search_q%";
}

$query_sql .= " ORDER BY q.id DESC";
$q_stmt = $pdo->prepare($query_sql);
$q_stmt->execute($params);
$questions = $q_stmt->fetchAll(PDO::FETCH_ASSOC);

// قائمة المخططات الهندسية المعتمدة للأسئلة
$available_diagrams = [
    'assets/img/diag-osi-model.jpg' => 'نموذج طبقات الشبكة OSI & TCP/IP Reference Model',
    'assets/img/diag-cisco-router-ports.svg' => 'المنافذ والواجهات الفيزيائية لراوترات وسويتشات Cisco Interfaces',
    'assets/img/diag-packet-capture.jpg' => 'تحليل حزم Wireshark ومصافحة TCP 3-Way Handshake',
    'assets/img/diag-subnetting-cidr.svg' => 'مخطط تقسيم العناوين الفرعية IPv4 Subnetting & CIDR /26',
    'assets/img/diag-network-topology.jpg' => 'طوبولوجيا شبكة المؤسسات OSPF Area 0 Enterprise Topology',
    'assets/img/diag-spanning-tree.svg' => 'بروتوكول الشجرة الممتدة STP 802.1D والمنافذ المحجوبة Blocked Ports',
    'assets/img/diag-vlan-trunking.svg' => 'عزل الشبكات الافتراضية VLANs وبروتوكول 802.1Q Trunking',
    'assets/img/diag-firewall-dmz.svg' => 'أمن المحيط وجدار الحماية ومنطقة DMZ Architecture',
    'assets/img/diag-vpn-ipsec.svg' => 'نفق الاتصال المشفر Site-to-Site IPsec VPN & ESP Tunnel',
    'assets/img/diag-ipv6-header.svg' => 'ترويسة بروتوكول الجيل السادس IPv6 Header Structure (40 Bytes)',
];

// جلب السؤال المطلوب تعديله إن كان محدداً
$editing_q = null;
if (isset($_GET['edit'])) {
    $e_id = intval($_GET['edit']);
    $e_stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $e_stmt->execute([$e_id]);
    $editing_q = $e_stmt->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
    <div>
        <span class="badge badge-primary" style="margin-bottom: 6px;">
            <i class="fa-solid fa-database"></i> إدارة المحتوى التقييمي
        </span>
        <h1 style="font-size: 1.7rem; margin-bottom: 4px; color: var(--text-heading); font-weight: 800;">
            بنك الأسئلة والاختبارات التكيفية
        </h1>
        <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 0;">
            إدارة الأسئلة التقييمية، الشروحات التفسيرية، وضبط معايير الصعوبة المتوافقة مع <strong>Cisco CCNA 200-301</strong>.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="toggleCreator()" class="btn btn-primary btn-sm" style="font-weight: 700;">
            <i class="fa-solid fa-plus"></i> إضافة سؤال جديد
        </button>
        <a href="dashboard.php" class="btn btn-secondary btn-sm" style="font-weight: 700;">
            <i class="fa-solid fa-chart-line"></i> لوحة الإحصائيات
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="academic-alert alert-<?php echo $message_type; ?>" style="border-radius: var(--radius-sm); margin-bottom: 20px;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px;"></i>
        <div><?php echo $message; ?></div>
    </div>
<?php endif; ?>

<!-- شريط إحصائيات بنك الأسئلة السريع -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 25px;">
    <div class="academic-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(91,79,232,0.12); color: var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">إجمالي الأسئلة</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); font-family: var(--font-mono);"><?php echo $total_q_count; ?></div>
        </div>
    </div>

    <div class="academic-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16,185,129,0.12); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">أساسي (Easy)</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #059669; font-family: var(--font-mono);"><?php echo $easy_count; ?></div>
        </div>
    </div>

    <div class="academic-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(245,158,11,0.12); color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
            <i class="fa-solid fa-bolt"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">متوسط (Medium)</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #D97706; font-family: var(--font-mono);"><?php echo $med_count; ?></div>
        </div>
    </div>

    <div class="academic-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(244,63,94,0.12); color: #E11D48; display: flex; align-items: center; justify-content: center; font-size: 18px;">
            <i class="fa-solid fa-fire"></i>
        </div>
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">متقدم (Hard)</div>
            <div style="font-size: 1.4rem; font-weight: 800; color: #E11D48; font-family: var(--font-mono);"><?php echo $hard_count; ?></div>
        </div>
    </div>
</div>

<!-- نموذج إضافة / تعديل سؤال (Collapsible Creator) -->
<div class="academic-card" id="questionCreatorCard" style="margin-bottom: 30px; border-top: 4px solid var(--brand-primary); <?php echo $editing_q ? 'display:block;' : 'display:none;'; ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; color: var(--text-heading); font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-<?php echo $editing_q ? 'pen-to-square' : 'circle-plus'; ?>" style="color: var(--brand-primary);"></i>
            <?php echo $editing_q ? 'تعديل بيانات السؤال #' . $editing_q['id'] : 'إضافة سؤال جديد إلى بنك التقييم'; ?>
        </h3>
        <?php if (!$editing_q): ?>
            <button type="button" onclick="toggleCreator()" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                <i class="fa-solid fa-xmark"></i> إخفاء
            </button>
        <?php endif; ?>
    </div>

    <form method="POST">
        <?php echo csrf_field(); ?>
        <?php if ($editing_q): ?>
            <input type="hidden" name="edit_id" value="<?php echo $editing_q['id']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
            <div class="form-group">
                <label style="font-weight: 600;">المستوى الأكاديمي:</label>
                <select name="level_id" class="form-control" required id="select-level">
                    <?php foreach ($levels as $lvl): ?>
                        <option value="<?php echo $lvl['id']; ?>" <?php echo ($editing_q['level_id'] ?? '') == $lvl['id'] ? 'selected' : ''; ?>>
                            مستوى <?php echo $lvl['level_order']; ?>: <?php echo htmlspecialchars($lvl['level_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">مستوى الصعوبة:</label>
                <select name="difficulty" class="form-control" required>
                    <option value="easy" <?php echo ($editing_q['difficulty'] ?? '') === 'easy' ? 'selected' : ''; ?>>أساسي (Easy)</option>
                    <option value="medium" <?php echo ($editing_q['difficulty'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>متوسط (Medium)</option>
                    <option value="hard" <?php echo ($editing_q['difficulty'] ?? '') === 'hard' ? 'selected' : ''; ?>>متقدم (Hard)</option>
                </select>
            </div>

            <div class="form-group">
                <label style="font-weight: 600;">مجال المعرفة (CCNA Domain):</label>
                <input type="text" name="domain_name" class="form-control" placeholder="مثال: Network Fundamentals أو IP Routing" value="<?php echo htmlspecialchars($editing_q['domain_name'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 18px;">
            <label style="font-weight: 600;">نص المسألة أو السؤال الهندسي:</label>
            <textarea name="question_text" class="form-control" rows="3" required placeholder="اكتب نص السؤال بدقة ووضوح..."><?php echo htmlspecialchars($editing_q['question_text'] ?? ''); ?></textarea>
        </div>

        <!-- المخطط الهندسي التوضيحي المرفق للسؤال -->
        <div class="form-group" style="margin-bottom: 18px; background: rgba(91,79,232,0.03); border: 1px dashed rgba(91,79,232,0.3); border-radius: var(--radius-md); padding: 16px;">
            <label style="font-weight: 700; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--brand-primary);"><i class="fa-solid fa-diagram-project"></i> المخطط الهندسي التوضيحي المرفق (Topology / Packet / Header Diagram):</span>
                <span class="badge" style="background: rgba(91,79,232,0.1); color: var(--brand-primary); font-size: 0.72rem;">اختياري لأسئلة الطوبولوجيا والتحليل</span>
            </label>
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 260px;">
                    <select name="image_url" id="diagram_select" class="form-control" onchange="updateDiagramPreview(this.value)">
                        <option value="">-- بدون مخطط توضيحي (سؤال نصي تقليدي) --</option>
                        <?php foreach ($available_diagrams as $diag_path => $diag_title): ?>
                            <option value="<?php echo $diag_path; ?>" <?php echo ($editing_q['image_url'] ?? '') === $diag_path ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($diag_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-info"></i> يتوفر 10 مخططات هندسية مطابقة لشهادات Cisco CCNA لتطوير مهارة القراءة البصرية للشبكات لدى الطلاب.
                    </small>
                </div>
                <div id="diagram_preview_container" style="width: 140px; height: 95px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <img id="diagram_preview_img" src="<?php echo !empty($editing_q['image_url']) ? '../' . htmlspecialchars($editing_q['image_url']) : ''; ?>" alt="Preview" style="<?php echo !empty($editing_q['image_url']) ? 'display:block;' : 'display:none;'; ?> width: 100%; height: 100%; object-fit: contain; padding: 4px;">
                    <span id="diagram_preview_placeholder" style="<?php echo !empty($editing_q['image_url']) ? 'display:none;' : 'display:flex;'; ?> color: var(--text-muted); font-size: 0.75rem; text-align: center; padding: 6px;">بدون مخطط</span>
                </div>
            </div>
        </div>

        <!-- خيارات الاختيار من متعدد -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
            <div class="form-group">
                <label style="font-weight: 600;">الخيار (أ) - Option A:</label>
                <input type="text" name="option_a" class="form-control" required value="<?php echo htmlspecialchars($editing_q['option_a'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label style="font-weight: 600;">الخيار (ب) - Option B:</label>
                <input type="text" name="option_b" class="form-control" required value="<?php echo htmlspecialchars($editing_q['option_b'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label style="font-weight: 600;">الخيار (ج) - Option C:</label>
                <input type="text" name="option_c" class="form-control" required value="<?php echo htmlspecialchars($editing_q['option_c'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label style="font-weight: 600;">الخيار (د) - Option D:</label>
                <input type="text" name="option_d" class="form-control" required value="<?php echo htmlspecialchars($editing_q['option_d'] ?? ''); ?>">
            </div>
        </div>

        <div style="margin-bottom: 18px;">
            <label style="font-weight: 600; display: block; margin-bottom: 6px;">الإجابة الصحيحة المعتمدة:</label>
            <select name="correct_option" class="form-control" style="max-width: 280px; font-weight: 700;" required>
                <option value="A" <?php echo ($editing_q['correct_option'] ?? '') === 'A' ? 'selected' : ''; ?>>الخيار (أ) - Option A</option>
                <option value="B" <?php echo ($editing_q['correct_option'] ?? '') === 'B' ? 'selected' : ''; ?>>الخيار (ب) - Option B</option>
                <option value="C" <?php echo ($editing_q['correct_option'] ?? '') === 'C' ? 'selected' : ''; ?>>الخيار (ج) - Option C</option>
                <option value="D" <?php echo ($editing_q['correct_option'] ?? '') === 'D' ? 'selected' : ''; ?>>الخيار (د) - Option D</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 22px;">
            <label style="font-weight: 600;">الشرح الهندسي والتغذية الراجعة التفسيرية (Explanatory Feedback):</label>
            <textarea name="explanation" class="form-control" rows="3" placeholder="اشرح للطالب سبب صحة الخيار والأساس العلمي للمسألة (يظهر للطالب عند مراجعة الاختبار)..."><?php echo htmlspecialchars($editing_q['explanation'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="<?php echo $editing_q ? 'update_question' : 'add_question'; ?>" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                <i class="fa-solid fa-floppy-disk"></i> <?php echo $editing_q ? 'حفظ التعديلات' : 'إضافة السؤال للبنك'; ?>
            </button>
            <?php if ($editing_q): ?>
                <a href="manage_questions.php" class="btn btn-secondary">إلغاء التعديل</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- شريط البحث والتصفية المطور -->
<div class="academic-card" style="margin-bottom: 25px; padding: 18px 22px;">
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" class="form-control" placeholder="ابحث في نص السؤال أو المجال..." value="<?php echo htmlspecialchars($search_q); ?>">
        </div>

        <div style="min-width: 190px;">
            <select name="filter_level" class="form-control">
                <option value="0">كافة المستويات الأربعة</option>
                <?php foreach ($levels as $l): ?>
                    <option value="<?php echo $l['id']; ?>" <?php echo $filter_level == $l['id'] ? 'selected' : ''; ?>>
                        مستوى <?php echo $l['level_order']; ?>: <?php echo htmlspecialchars($l['level_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width: 140px;">
            <select name="filter_diff" class="form-control">
                <option value="">كافة الصعوبات</option>
                <option value="easy" <?php echo $filter_diff === 'easy' ? 'selected' : ''; ?>>أساسي</option>
                <option value="medium" <?php echo $filter_diff === 'medium' ? 'selected' : ''; ?>>متوسط</option>
                <option value="hard" <?php echo $filter_diff === 'hard' ? 'selected' : ''; ?>>متقدم</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="white-space: nowrap; font-weight: 700;">
            <i class="fa-solid fa-filter"></i> تصفية
        </button>

        <?php if ($filter_level > 0 || !empty($filter_diff) || !empty($search_q)): ?>
            <a href="manage_questions.php" class="btn btn-secondary btn-sm">إلغاء الفلاتر</a>
        <?php endif; ?>
    </form>
</div>

<!-- جدول بنك الأسئلة الأكاديمي المطور -->
<div class="academic-card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
    <div style="padding: 16px 22px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: var(--text-heading); font-size: 1rem;">قائمة الأسئلة المفهرسة في النظام</strong>
        <span class="badge badge-primary"><?php echo count($questions); ?> سؤالاً مطابقاً</span>
    </div>

    <?php if (empty($questions)): ?>
        <div style="text-align: center; padding: 45px 20px; color: var(--text-muted);">
            <i class="fa-solid fa-circle-question" style="font-size: 40px; margin-bottom: 12px; color: var(--text-dim);"></i>
            <p style="margin: 0; font-size: 0.95rem;">لا توجد أسئلة مطابقة للخيارات المحددة في البحث.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="academic-table" style="box-shadow: none; border: none; margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>نص السؤال والمجال الأكاديمي</th>
                        <th>المستوى</th>
                        <th>الصعوبة</th>
                        <th>الإجابة الصحيحة</th>
                        <th>إجراءات السؤال</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td class="code-font" style="font-size: 0.82rem; color: var(--text-muted);"><?php echo $q['id']; ?></td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-heading); margin-bottom: 5px; font-size: 0.94rem; line-height: 1.45;">
                                    <?php echo htmlspecialchars($q['question_text']); ?>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <?php if (!empty($q['domain_name'])): ?>
                                        <span class="badge" style="background: rgba(91,79,232,0.08); color: var(--brand-primary); font-size: 0.72rem; font-weight: 700;">
                                            <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($q['domain_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($q['image_url'])): ?>
                                        <span class="badge" style="background: rgba(14, 165, 233, 0.1); color: #0284c7; font-size: 0.72rem; font-weight: 700;">
                                            <i class="fa-solid fa-image"></i> مخطط مرفق
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($q['explanation'])): ?>
                                        <span style="font-size: 0.74rem; color: #10B981; font-weight: 600;">
                                            <i class="fa-solid fa-check-circle"></i> يحتوي شرحاً تفسيرياً
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(6,182,212,0.1); color: var(--brand-secondary); font-weight: 700;">
                                    مستوى <?php echo $q['level_order']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $q['difficulty'] === 'hard' ? 'badge-danger' : ($q['difficulty'] === 'easy' ? 'badge-success' : 'badge-warning'); ?>" style="font-weight: 700;">
                                    <?php echo $q['difficulty'] === 'hard' ? 'متقدم' : ($q['difficulty'] === 'easy' ? 'أساسي' : 'متوسط'); ?>
                                </span>
                            </td>
                            <td class="code-font" style="font-weight: 800; color: #059669; font-size: 1.05rem;">
                                (<?php echo $q['correct_option']; ?>)
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <!-- زر معاينة السؤال التفاعلي -->
                                    <button type="button" onclick="previewQuestion(<?php echo htmlspecialchars(json_encode($q, JSON_UNESCAPED_UNICODE)); ?>)" class="btn btn-secondary btn-sm" title="معاينة السؤال كما يظهر للطالب" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-eye"></i> معاينة
                                    </button>

                                    <!-- تعديل -->
                                    <a href="manage_questions.php?edit=<?php echo $q['id']; ?>" class="btn btn-outline btn-sm" title="تعديل السؤال">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <!-- حذف -->
                                    <form method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال نهائياً من بنك الأسئلة؟');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="حذف السؤال">
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
<!-- Modal: معاينة السؤال التفاعلية (Question Preview Modal)                     -->
<!-- ========================================================================= -->
<div id="qPreviewModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.7); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--bg-surface-card); border:1px solid rgba(208,213,229,0.8); border-radius:var(--radius-xl); max-width:620px; width:100%; padding:28px; box-shadow:0 25px 60px rgba(0,0,0,0.35); position:relative; max-height:90vh; overflow-y:auto;">
        <button type="button" onclick="closeQPreview()" style="position:absolute; top:20px; left:20px; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap;">
            <span id="qp-level" class="badge badge-primary"></span>
            <span id="qp-diff" class="badge"></span>
            <span id="qp-domain" class="badge" style="background:rgba(6,182,212,0.1); color:var(--brand-secondary);"></span>
        </div>

        <h3 id="qp-text" style="font-size:1.15rem; font-weight:800; color:var(--text-heading); margin-bottom:16px; line-height:1.5;"></h3>

        <!-- صندوق المخطط الهندسي التوضيحي في المعاينة -->
        <div id="qp-diagram-box" style="display:none; margin-bottom:18px; text-align:center; background:#ffffff; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:10px; box-shadow:0 2px 10px rgba(0,0,0,0.04);">
            <img id="qp-diagram-img" src="" alt="Question Diagram" style="max-height:240px; max-width:100%; object-fit:contain; border-radius:6px;">
            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:6px; font-weight:600;"><i class="fa-solid fa-diagram-project" style="color:var(--brand-primary);"></i> المخطط الهندسي التوضيحي المعتمد للمسألة</div>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:22px;">
            <div id="qp-opt-a" style="padding:12px 16px; border-radius:var(--radius-sm); border:1px solid var(--border-main); display:flex; align-items:center; gap:12px;">
                <strong style="width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">A</strong>
                <span id="qp-txt-a" style="flex:1;"></span>
            </div>
            <div id="qp-opt-b" style="padding:12px 16px; border-radius:var(--radius-sm); border:1px solid var(--border-main); display:flex; align-items:center; gap:12px;">
                <strong style="width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">B</strong>
                <span id="qp-txt-b" style="flex:1;"></span>
            </div>
            <div id="qp-opt-c" style="padding:12px 16px; border-radius:var(--radius-sm); border:1px solid var(--border-main); display:flex; align-items:center; gap:12px;">
                <strong style="width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">C</strong>
                <span id="qp-txt-c" style="flex:1;"></span>
            </div>
            <div id="qp-opt-d" style="padding:12px 16px; border-radius:var(--radius-sm); border:1px solid var(--border-main); display:flex; align-items:center; gap:12px;">
                <strong style="width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">D</strong>
                <span id="qp-txt-d" style="flex:1;"></span>
            </div>
        </div>

        <div id="qp-exp-box" style="padding:16px; background:rgba(91,79,232,0.06); border-radius:var(--radius-md); border:1px solid rgba(91,79,232,0.18); margin-bottom:20px;">
            <div style="font-weight:700; color:var(--brand-primary); font-size:0.86rem; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-graduation-cap"></i> الشرح الهندسي والتغذية الراجعة التفسيرية:
            </div>
            <p id="qp-exp-text" style="margin:0; font-size:0.9rem; color:var(--text-body); line-height:1.6;"></p>
        </div>

        <div style="text-align:left;">
            <button type="button" onclick="closeQPreview()" class="btn btn-secondary btn-sm" style="font-weight:700;">
                إغلاق المعاينة
            </button>
        </div>
    </div>
</div>

<script>
function toggleCreator() {
    const card = document.getElementById('questionCreatorCard');
    if (card.style.display === 'none') {
        card.style.display = 'block';
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        card.style.display = 'none';
    }
}

function updateDiagramPreview(val) {
    const img = document.getElementById('diagram_preview_img');
    const placeholder = document.getElementById('diagram_preview_placeholder');
    if (val && val.trim() !== '') {
        img.src = '../' + val;
        img.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
    }
}

function previewQuestion(q) {
    document.getElementById('qp-text').textContent = q.question_text;
    document.getElementById('qp-level').textContent = q.level_name || 'مستوى عام';
    document.getElementById('qp-diff').textContent = q.difficulty === 'hard' ? 'متقدم' : (q.difficulty === 'easy' ? 'أساسي' : 'متوسط');
    document.getElementById('qp-domain').textContent = q.domain_name || 'Cisco CCNA';

    const diagBox = document.getElementById('qp-diagram-box');
    const diagImg = document.getElementById('qp-diagram-img');
    if (q.image_url && q.image_url.trim() !== '') {
        diagImg.src = '../' + q.image_url;
        diagBox.style.display = 'block';
    } else {
        diagBox.style.display = 'none';
    }

    const opts = ['A', 'B', 'C', 'D'];
    opts.forEach(letter => {
        const box = document.getElementById('qp-opt-' + letter.toLowerCase());
        const txt = document.getElementById('qp-txt-' + letter.toLowerCase());
        txt.textContent = q['option_' + letter.toLowerCase()];

        if (q.correct_option === letter) {
            box.style.background = 'rgba(16, 185, 129, 0.1)';
            box.style.borderColor = '#10B981';
            box.style.color = '#065F46';
            box.querySelector('strong').style.background = '#10B981';
            box.querySelector('strong').style.color = '#FFFFFF';
        } else {
            box.style.background = '';
            box.style.borderColor = 'var(--border-main)';
            box.style.color = 'var(--text-body)';
            box.querySelector('strong').style.background = 'rgba(0,0,0,0.06)';
            box.querySelector('strong').style.color = 'var(--text-heading)';
        }
    });

    const expText = document.getElementById('qp-exp-text');
    const expBox = document.getElementById('qp-exp-box');
    if (q.explanation && q.explanation.trim() !== '') {
        expText.textContent = q.explanation;
        expBox.style.display = 'block';
    } else {
        expBox.style.display = 'none';
    }

    const modal = document.getElementById('qPreviewModal');
    modal.style.display = 'flex';
}

function closeQPreview() {
    document.getElementById('qPreviewModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>