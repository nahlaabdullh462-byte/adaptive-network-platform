<?php
$pageTitle = "مركز إدارة الموارد التعليمية الذكي";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_auth('admin');

$message = '';
$message_type = 'success';

$levels = $pdo->query("SELECT id, level_name, level_order FROM levels ORDER BY level_order ASC")->fetchAll();

// 1. إضافة مصدر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    verify_csrf_token();
    $level_id = intval($_POST['level_id']);
    $title    = trim($_POST['title'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $type     = in_array($_POST['resource_type']??'',['video','pdf','article','interactive','quiz','audio'])?$_POST['resource_type']:'video';
    $lang     = in_array($_POST['resource_lang']??'',['ar','en','mixed'])?$_POST['resource_lang']:'ar';
    $url      = trim($_POST['url'] ?? '');
    $is_ext   = isset($_POST['is_external']) ? 1 : 0;
    $dur      = trim($_POST['duration_label'] ?? '');
    $thumb    = trim($_POST['thumbnail_url'] ?? '');
    $author   = trim($_POST['author_name'] ?? '');
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort     = intval($_POST['sort_order'] ?? 0);

    // YouTube thumbnail auto-extract
    if ($type === 'video' && empty($thumb) && !empty($url)) {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/', $url, $m);
        if (!empty($m[1])) {
            $thumb = "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
            $url   = "https://www.youtube.com/embed/{$m[1]}";
            $is_ext = 0;
        }
    }

    if (!empty($title) && !empty($url) && $level_id > 0) {
        $pdo->prepare("INSERT INTO level_resources (level_id,title,description,resource_type,resource_lang,url,is_external,duration_label,thumbnail_url,author_name,sort_order,is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$level_id,$title,$desc,$type,$lang,$url,$is_ext,$dur?:null,$thumb?:null,$author?:null,$sort,$featured]);
        $message = "✅ تمت إضافة «{$title}» بنجاح!";
    } else {
        $message = "يرجى تعبئة العنوان والرابط واختيار المستوى!";
        $message_type = 'danger';
    }
}

// 2. تعديل مصدر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resource'])) {
    verify_csrf_token();
    $rid    = intval($_POST['edit_id']);
    $lid    = intval($_POST['level_id']);
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $type   = in_array($_POST['resource_type']??'',['video','pdf','article','interactive','quiz','audio'])?$_POST['resource_type']:'video';
    $lang   = in_array($_POST['resource_lang']??'',['ar','en','mixed'])?$_POST['resource_lang']:'ar';
    $url    = trim($_POST['url'] ?? '');
    $is_ext = isset($_POST['is_external']) ? 1 : 0;
    $dur    = trim($_POST['duration_label'] ?? '');
    $thumb  = trim($_POST['thumbnail_url'] ?? '');
    $author = trim($_POST['author_name'] ?? '');
    $feat   = isset($_POST['is_featured']) ? 1 : 0;
    $sort   = intval($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($title) && !empty($url)) {
        $pdo->prepare("UPDATE level_resources SET level_id=?,title=?,description=?,resource_type=?,resource_lang=?,url=?,is_external=?,duration_label=?,thumbnail_url=?,author_name=?,sort_order=?,is_featured=?,is_active=? WHERE id=?")
            ->execute([$lid,$title,$desc,$type,$lang,$url,$is_ext,$dur?:null,$thumb?:null,$author?:null,$sort,$feat,$active,$rid]);
        $message = "✅ تم تحديث المصدر بنجاح!";
    }
}

// 3. حذف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_resource'])) {
    verify_csrf_token();
    $rid = intval($_POST['resource_id']);
    $pdo->prepare("DELETE FROM level_resources WHERE id=?")->execute([$rid]);
    $pdo->prepare("DELETE FROM resource_views WHERE resource_id=?")->execute([$rid]);
    $message = "🗑️ تم الحذف.";
}

// 4. تبديل نشاط
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_resource'])) {
    verify_csrf_token();
    $pdo->prepare("UPDATE level_resources SET is_active = 1 - is_active WHERE id=?")->execute([intval($_POST['resource_id'])]);
    $message = "تم تغيير الحالة.";
}

// جلب الموارد مع فلترة
$fl = isset($_GET['level']) ? intval($_GET['level']) : 0;
$ft = $_GET['type'] ?? '';
$fw = ['1=1']; $fp = [];
if ($fl > 0) { $fw[] = 'lr.level_id=?'; $fp[] = $fl; }
if ($ft)     { $fw[] = 'lr.resource_type=?'; $fp[] = $ft; }
$ws = implode(' AND ', $fw);

$resources = $pdo->prepare("
    SELECT lr.*, l.level_name,
        (SELECT COUNT(*) FROM resource_views rv WHERE rv.resource_id=lr.id) as real_views,
        (SELECT COUNT(*) FROM resource_views rv WHERE rv.resource_id=lr.id AND rv.is_completed=1) as completions
    FROM level_resources lr
    JOIN levels l ON lr.level_id=l.id
    WHERE {$ws}
    ORDER BY lr.level_id ASC, lr.is_featured DESC, lr.sort_order ASC, lr.id DESC
");
$resources->execute($fp);
$resources = $resources->fetchAll();

$stats = $pdo->query("SELECT COUNT(*) total, SUM(is_active) active, SUM(is_featured) featured,
    SUM(CASE WHEN resource_type='video' THEN 1 ELSE 0 END) videos,
    SUM(CASE WHEN resource_type='pdf'   THEN 1 ELSE 0 END) pdfs,
    SUM(CASE WHEN resource_type='article' THEN 1 ELSE 0 END) articles,
    SUM(CASE WHEN resource_type='interactive' THEN 1 ELSE 0 END) interact
    FROM level_resources")->fetch();

// تجميع حسب المستوى
$grouped = [];
foreach ($resources as $r) {
    $grouped[$r['level_id']]['name'] = $r['level_name'];
    $grouped[$r['level_id']]['items'][] = $r;
}

$type_icons  = ['video'=>'🎬','pdf'=>'📄','article'=>'📰','interactive'=>'🖥️','quiz'=>'✍️','audio'=>'🎧'];
$type_labels = ['video'=>'فيديو','pdf'=>'PDF','article'=>'مقالة','interactive'=>'تفاعلي','quiz'=>'اختبار','audio'=>'صوتي'];
$lang_labels = ['ar'=>'🇸🇦 عربي','en'=>'🇬🇧 إنجليزي','mixed'=>'🌐 مختلط'];

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.res-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;}
.resource-card{background:var(--bg-surface-card);border:1px solid rgba(208,213,229,.5);border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-card);transition:all .25s ease;position:relative;}
.resource-card:hover{box-shadow:var(--shadow-card-hover);transform:translateY(-2px);}
.resource-card.inactive{opacity:.55;}
.resource-card.featured::before{content:'⭐ مميز';position:absolute;top:10px;right:10px;z-index:5;background:linear-gradient(135deg,#F59E0B,#D97706);color:#FFF;font-size:.7rem;font-weight:800;padding:2px 10px;border-radius:20px;}
.res-thumb{width:100%;height:170px;object-fit:cover;}
.res-thumb-ph{width:100%;height:170px;background:linear-gradient(135deg,#1A1A3E,#2D2B6F);display:flex;align-items:center;justify-content:center;font-size:52px;color:rgba(255,255,255,.2);}
.res-body{padding:16px 18px;}
.res-title{font-weight:800;font-size:.97rem;color:var(--text-heading);margin-bottom:5px;line-height:1.5;}
.res-desc{font-size:.83rem;color:var(--text-muted);line-height:1.6;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.res-meta{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:10px;}
.rtag{padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700;border:1px solid transparent;}
.rtag-video{background:rgba(239,68,68,.1);color:#EF4444;border-color:rgba(239,68,68,.25);}
.rtag-pdf{background:rgba(245,158,11,.1);color:#D97706;border-color:rgba(245,158,11,.25);}
.rtag-article{background:rgba(16,185,129,.1);color:#059669;border-color:rgba(16,185,129,.25);}
.rtag-interactive{background:rgba(91,79,232,.1);color:#5B4FE8;border-color:rgba(91,79,232,.25);}
.rtag-quiz{background:rgba(6,182,212,.1);color:#0891B2;border-color:rgba(6,182,212,.25);}
.rtag-audio{background:rgba(168,85,247,.1);color:#7C3AED;border-color:rgba(168,85,247,.25);}
.rtag-ar{background:rgba(16,185,129,.08);color:#059669;border-color:rgba(16,185,129,.2);}
.rtag-en{background:rgba(59,130,246,.08);color:#2563EB;border-color:rgba(59,130,246,.2);}
.rtag-mixed{background:rgba(245,158,11,.08);color:#D97706;border-color:rgba(245,158,11,.2);}
.res-stats{display:flex;gap:14px;font-size:.76rem;color:var(--text-muted);padding-top:10px;border-top:1px solid var(--border-subtle);}
.res-actions{display:flex;gap:7px;margin-top:12px;}
.stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px;margin-bottom:24px;}
.sbox{background:var(--bg-surface-card);border:1px solid rgba(208,213,229,.45);border-radius:var(--radius-md);padding:14px 16px;text-align:center;}
.sbox .sv{font-size:1.6rem;font-weight:900;font-family:var(--font-mono);color:var(--brand-primary);}
.sbox .sl{font-size:.76rem;color:var(--text-muted);margin-top:3px;}
/* Modal */
.mov{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(4px);}
.mbox{background:var(--bg-surface-card);border-radius:var(--radius-xl);width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,.35);}
.mhdr{padding:22px 26px 16px;border-bottom:1px solid var(--border-subtle);display:flex;justify-content:space-between;align-items:center;}
.mbdy{padding:22px 26px;}
</style>

<div style="max-width:1280px;margin:0 auto;">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.5rem;color:var(--text-heading);font-weight:900;margin-bottom:4px;">
            <i class="fa-solid fa-layer-group" style="color:var(--brand-primary);"></i>
            مركز إدارة الموارد التعليمية
        </h1>
        <p style="color:var(--text-muted);font-size:.88rem;">أضف فيديوهات · ملفات PDF · مقالات · محتوى تفاعلي لكل مستوى</p>
    </div>
    <button onclick="openAdd()" class="btn btn-primary"><i class="fa-solid fa-plus"></i> مصدر جديد</button>
</div>

<?php if ($message): ?>
<div class="academic-alert alert-<?= $message_type==='danger'?'danger':'success' ?>" style="margin-bottom:18px;">
    <i class="fa-solid fa-<?= $message_type==='danger'?'triangle-exclamation':'circle-check' ?>"></i>
    <div><?= clean_output($message) ?></div>
</div>
<?php endif; ?>

<!-- إحصائيات -->
<div class="stat-row">
    <div class="sbox"><div class="sv"><?= $stats['total']??0 ?></div><div class="sl">إجمالي</div></div>
    <div class="sbox"><div class="sv" style="color:#10B981;"><?= $stats['active']??0 ?></div><div class="sl">نشطة</div></div>
    <div class="sbox"><div class="sv" style="color:#EF4444;"><?= $stats['videos']??0 ?></div><div class="sl">فيديوهات</div></div>
    <div class="sbox"><div class="sv" style="color:#D97706;"><?= $stats['pdfs']??0 ?></div><div class="sl">ملفات PDF</div></div>
    <div class="sbox"><div class="sv" style="color:#059669;"><?= $stats['articles']??0 ?></div><div class="sl">مقالات</div></div>
    <div class="sbox"><div class="sv" style="color:#5B4FE8;"><?= $stats['interact']??0 ?></div><div class="sl">تفاعلي</div></div>
    <div class="sbox"><div class="sv" style="color:#F59E0B;"><?= $stats['featured']??0 ?></div><div class="sl">مميزة</div></div>
</div>

<!-- فلتر -->
<div class="academic-card" style="padding:14px 20px;margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label style="font-size:.78rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:4px;">المستوى</label>
            <select name="level" class="form-control" style="min-width:160px;padding:7px 10px;">
                <option value="">كل المستويات</option>
                <?php foreach ($levels as $lv): ?>
                <option value="<?= $lv['id'] ?>" <?= $fl===$lv['id']?'selected':'' ?>><?= clean_output($lv['level_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:4px;">النوع</label>
            <select name="type" class="form-control" style="padding:7px 10px;">
                <option value="">كل الأنواع</option>
                <option value="video" <?= $ft==='video'?'selected':'' ?>>🎬 فيديو</option>
                <option value="pdf" <?= $ft==='pdf'?'selected':'' ?>>📄 PDF</option>
                <option value="article" <?= $ft==='article'?'selected':'' ?>>📰 مقالة</option>
                <option value="interactive" <?= $ft==='interactive'?'selected':'' ?>>🖥️ تفاعلي</option>
                <option value="quiz" <?= $ft==='quiz'?'selected':'' ?>>✍️ اختبار</option>
                <option value="audio" <?= $ft==='audio'?'selected':'' ?>>🎧 صوتي</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> تصفية</button>
        <a href="manage_resources.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-right"></i></a>
    </form>
</div>

<!-- الموارد مجمعة حسب المستوى -->
<?php if (empty($resources)): ?>
<div class="academic-card" style="text-align:center;padding:60px 30px;">
    <div style="font-size:60px;margin-bottom:14px;">📚</div>
    <h3 style="color:var(--text-heading);margin-bottom:8px;">لا توجد موارد تعليمية بعد</h3>
    <p style="color:var(--text-muted);margin-bottom:18px;">ابدأ بإضافة فيديوهات أو ملفات PDF أو مقالات لكل مستوى</p>
    <button onclick="openAdd()" class="btn btn-primary"><i class="fa-solid fa-plus"></i> إضافة أول مصدر</button>
</div>
<?php else: ?>
<?php foreach ($grouped as $lvl_id => $grp): ?>
<div style="margin-bottom:32px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="width:34px;height:34px;background:var(--brand-gradient);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#FFF;font-weight:900;font-size:.85rem;"><?= count($grp['items']) ?></div>
        <div>
            <h2 style="font-size:1.05rem;color:var(--text-heading);font-weight:800;margin:0;"><?= clean_output($grp['name']) ?></h2>
            <span style="font-size:.76rem;color:var(--text-muted);"><?= count($grp['items']) ?> مصدر</span>
        </div>
        <button onclick="openAdd(<?= $lvl_id ?>)" class="btn btn-secondary btn-sm" style="margin-right:auto;"><i class="fa-solid fa-plus"></i> إضافة لهذا المستوى</button>
    </div>
    <div class="res-grid">
        <?php foreach ($grp['items'] as $r): ?>
        <div class="resource-card <?= !$r['is_active']?'inactive':'' ?> <?= $r['is_featured']?'featured':'' ?>">
            <?php if (!empty($r['thumbnail_url'])): ?>
                <img src="<?= htmlspecialchars($r['thumbnail_url']) ?>" class="res-thumb" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="res-thumb-ph" style="display:none;"><?= $type_icons[$r['resource_type']]??'📚' ?></div>
            <?php else: ?>
                <div class="res-thumb-ph"><?= $type_icons[$r['resource_type']]??'📚' ?></div>
            <?php endif; ?>
            <div class="res-body">
                <div class="res-meta">
                    <span class="rtag rtag-<?= $r['resource_type'] ?>"><?= $type_icons[$r['resource_type']] ?> <?= $type_labels[$r['resource_type']] ?></span>
                    <span class="rtag rtag-<?= $r['resource_lang'] ?>"><?= $lang_labels[$r['resource_lang']]??$r['resource_lang'] ?></span>
                    <?php if (!$r['is_active']): ?><span class="rtag" style="background:rgba(100,100,100,.1);color:#6b7280;border-color:rgba(100,100,100,.2);">معطل</span><?php endif; ?>
                    <?php if (!empty($r['duration_label'])): ?><span class="rtag" style="background:rgba(91,79,232,.06);color:var(--brand-primary);border-color:rgba(91,79,232,.15);"><i class="fa-solid fa-clock"></i> <?= clean_output($r['duration_label']) ?></span><?php endif; ?>
                </div>
                <div class="res-title"><?= clean_output($r['title']) ?></div>
                <?php if (!empty($r['description'])): ?><div class="res-desc"><?= clean_output($r['description']) ?></div><?php endif; ?>
                <?php if (!empty($r['author_name'])): ?><div style="font-size:.78rem;color:var(--text-muted);margin-bottom:6px;"><i class="fa-solid fa-user-tie"></i> <?= clean_output($r['author_name']) ?></div><?php endif; ?>
                <div class="res-stats">
                    <span><i class="fa-solid fa-eye"></i> <?= $r['real_views'] ?></span>
                    <span><i class="fa-solid fa-circle-check"></i> <?= $r['completions'] ?> أكملوا</span>
                </div>
                <div class="res-actions">
                    <button onclick='openEdit(<?= htmlspecialchars(json_encode($r),ENT_QUOTES) ?>)' class="btn btn-secondary btn-sm" style="flex:1;"><i class="fa-solid fa-pen"></i> تعديل</button>
                    <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="معاينة"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('تغيير حالة المصدر؟');"><?= csrf_field() ?><input type="hidden" name="resource_id" value="<?= $r['id'] ?>"><button type="submit" name="toggle_resource" class="btn btn-sm" style="background:<?= $r['is_active']?'rgba(245,158,11,.1)':'rgba(16,185,129,.1)' ?>;color:<?= $r['is_active']?'#D97706':'#059669' ?>;border:1px solid <?= $r['is_active']?'rgba(245,158,11,.3)':'rgba(16,185,129,.3)' ?>;"><i class="fa-solid fa-<?= $r['is_active']?'pause':'play' ?>"></i></button></form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('حذف هذا المصدر نهائياً؟');"><?= csrf_field() ?><input type="hidden" name="resource_id" value="<?= $r['id'] ?>"><button type="submit" name="delete_resource" class="btn btn-sm" style="background:rgba(244,63,94,.08);color:#F43F5E;border:1px solid rgba(244,63,94,.2);"><i class="fa-solid fa-trash"></i></button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<!-- Modal -->
<div id="rmodal" class="mov" style="display:none;" onclick="if(event.target===this)closeM()">
<div class="mbox">
    <div class="mhdr">
        <h3 id="mtitle" style="font-size:1.05rem;font-weight:800;color:var(--text-heading);margin:0;"></h3>
        <button onclick="closeM()" style="background:none;border:none;font-size:1.3rem;color:var(--text-muted);cursor:pointer;">×</button>
    </div>
    <div class="mbdy">
    <form id="rform" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" id="feid" name="edit_id" value="">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">المستوى *</label>
                <select id="fl" name="level_id" class="form-control" required>
                    <?php foreach ($levels as $lv): ?>
                    <option value="<?= $lv['id'] ?>"><?= clean_output($lv['level_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">نوع المصدر *</label>
                <select id="ftype" name="resource_type" class="form-control" onchange="upHint()">
                    <option value="video">🎬 فيديو</option>
                    <option value="pdf">📄 ملف PDF</option>
                    <option value="article">📰 مقالة / موقع</option>
                    <option value="interactive">🖥️ تفاعلي</option>
                    <option value="quiz">✍️ اختبار قصير</option>
                    <option value="audio">🎧 صوتي / بودكاست</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:12px;">
            <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">عنوان المصدر *</label>
            <input type="text" id="ftitle" name="title" class="form-control" placeholder="مثال: شرح نموذج OSI بالتفصيل" required>
        </div>

        <div style="margin-bottom:12px;">
            <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">الرابط * <small id="uhint" style="color:var(--brand-secondary);">(رابط YouTube أو URL مباشر)</small></label>
            <input type="text" id="furl" name="url" class="form-control" placeholder="https://..." oninput="autoThumb()" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">اللغة</label>
                <select id="flang" name="resource_lang" class="form-control">
                    <option value="ar">🇸🇦 عربي</option>
                    <option value="en">🇬🇧 إنجليزي</option>
                    <option value="mixed">🌐 مختلط</option>
                </select>
            </div>
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">المدة / الحجم</label>
                <input type="text" id="fdur" name="duration_label" class="form-control" placeholder="18 دقيقة أو 42 صفحة">
            </div>
        </div>

        <div style="margin-bottom:12px;">
            <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">وصف مختصر</label>
            <textarea id="fdesc" name="description" class="form-control" rows="2" style="resize:vertical;" placeholder="ما الذي سيتعلمه الطالب؟"></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">اسم المنشئ / المؤلف</label>
                <input type="text" id="fauthor" name="author_name" class="form-control" placeholder="مثال: Eng. Ahmed">
            </div>
            <div>
                <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">ترتيب العرض</label>
                <input type="number" id="fsort" name="sort_order" class="form-control" value="0" min="0">
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:.82rem;font-weight:700;color:var(--text-muted);display:block;margin-bottom:5px;">صورة مصغرة (Thumbnail) <small style="color:var(--brand-secondary);">— تُستخرج تلقائياً من YouTube</small></label>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" id="fthumb" name="thumbnail_url" class="form-control" placeholder="https://img.youtube.com/vi/...">
                <img id="tprev" src="" style="width:72px;height:46px;object-fit:cover;border-radius:6px;display:none;border:1px solid var(--border-subtle);" onerror="this.style.display='none'">
            </div>
        </div>

        <div style="display:flex;gap:18px;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-weight:600;font-size:.88rem;">
                <input type="checkbox" id="ffeat" name="is_featured" style="width:15px;height:15px;"> ⭐ مصدر مميز
            </label>
            <label id="activerow" style="display:flex;align-items:center;gap:7px;cursor:pointer;font-weight:600;font-size:.88rem;">
                <input type="checkbox" id="fact" name="is_active" checked style="width:15px;height:15px;"> ✅ نشط ومرئي
            </label>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary" style="flex:1;" id="sbtn"><i class="fa-solid fa-cloud-arrow-up"></i> حفظ</button>
            <button type="button" onclick="closeM()" class="btn btn-secondary">إلغاء</button>
        </div>
    </form>
    </div>
</div>
</div>

<script>
function openAdd(lvlId=null){
    document.getElementById('mtitle').innerHTML='<i class="fa-solid fa-plus-circle" style="color:var(--brand-primary);"></i> إضافة مصدر تعليمي جديد';
    document.getElementById('sbtn').innerHTML='<i class="fa-solid fa-cloud-arrow-up"></i> حفظ المصدر';
    document.getElementById('rform').reset();
    document.getElementById('feid').value='';
    setAction('add');
    document.getElementById('activerow').style.display='none';
    document.getElementById('tprev').style.display='none';
    if(lvlId) document.getElementById('fl').value=lvlId;
    document.getElementById('rmodal').style.display='flex';
}
function openEdit(r){
    document.getElementById('mtitle').innerHTML='<i class="fa-solid fa-pen" style="color:var(--brand-primary);"></i> تعديل المصدر';
    document.getElementById('sbtn').innerHTML='<i class="fa-solid fa-check"></i> حفظ التعديلات';
    document.getElementById('feid').value=r.id;
    document.getElementById('fl').value=r.level_id;
    document.getElementById('ftype').value=r.resource_type;
    document.getElementById('ftitle').value=r.title;
    document.getElementById('furl').value=r.url;
    document.getElementById('flang').value=r.resource_lang;
    document.getElementById('fdur').value=r.duration_label||'';
    document.getElementById('fdesc').value=r.description||'';
    document.getElementById('fauthor').value=r.author_name||'';
    document.getElementById('fsort').value=r.sort_order;
    document.getElementById('fthumb').value=r.thumbnail_url||'';
    document.getElementById('ffeat').checked=r.is_featured==1;
    document.getElementById('fact').checked=r.is_active==1;
    document.getElementById('activerow').style.display='flex';
    if(r.thumbnail_url){const i=document.getElementById('tprev');i.src=r.thumbnail_url;i.style.display='block';}
    setAction('update');
    document.getElementById('rmodal').style.display='flex';
}
function setAction(mode){
    const f=document.getElementById('rform');
    ['add_resource','update_resource'].forEach(n=>{const e=f.querySelector('[name="'+n+'"]');if(e)e.remove();});
    const i=document.createElement('input');i.type='hidden';i.name=mode+'_resource';i.value='1';f.appendChild(i);
}
function closeM(){document.getElementById('rmodal').style.display='none';}
function autoThumb(){
    const u=document.getElementById('furl').value;
    const m=u.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/);
    if(m&&!document.getElementById('fthumb').value){
        const t='https://img.youtube.com/vi/'+m[1]+'/hqdefault.jpg';
        document.getElementById('fthumb').value=t;
        const i=document.getElementById('tprev');i.src=t;i.style.display='block';
    }
}
document.getElementById('fthumb').addEventListener('input',function(){
    if(this.value){const i=document.getElementById('tprev');i.src=this.value;i.style.display='block';}
});
const hints={video:'رابط YouTube أو فيديو مباشر',pdf:'رابط PDF — يُعرض داخل المنصة تلقائياً',article:'رابط المقالة أو الموقع',interactive:'Packet Tracer, GNS3, SimulIDE...',quiz:'Kahoot, Google Forms...',audio:'رابط البودكاست أو الملف الصوتي'};
function upHint(){document.getElementById('uhint').textContent='('+hints[document.getElementById('ftype').value]+')';}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
