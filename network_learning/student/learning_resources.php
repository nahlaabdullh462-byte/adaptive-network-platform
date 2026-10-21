<?php
$pageTitle = "مركز الموارد التعليمية";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
require_auth('student');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_viewed'])) {
    $rid = intval($_POST['resource_id']);
    $done = intval($_POST['is_completed'] ?? 0);
    $pdo->prepare("INSERT INTO resource_views (user_id, resource_id, is_completed) VALUES (?,?,?) ON DUPLICATE KEY UPDATE is_completed = GREATEST(is_completed, ?)")
        ->execute([$user_id, $rid, $done, $done]);
    if (!$done) $pdo->prepare("UPDATE level_resources SET view_count = view_count + 1 WHERE id=?")->execute([$rid]);
    echo json_encode(['ok' => true]);
    exit;
}

$u_stmt = $pdo->prepare("SELECT current_level_id FROM users WHERE id=?");
$u_stmt->execute([$user_id]);
$current_level_id = (int)$u_stmt->fetchColumn();

$filter_level = isset($_GET['level']) ? intval($_GET['level']) : $current_level_id;
$filter_type  = $_GET['type'] ?? '';
$filter_lang  = $_GET['lang'] ?? '';

$levels = $pdo->prepare("SELECT id, level_name, level_order FROM levels WHERE level_order <= (SELECT level_order FROM levels WHERE id=?) ORDER BY level_order ASC");
$levels->execute([$current_level_id]);
$levels = $levels->fetchAll();

$where = ['lr.is_active=1', 'lr.level_id=?'];
$params = [$filter_level];
if ($filter_type) { $where[] = 'lr.resource_type=?'; $params[] = $filter_type; }
if ($filter_lang) { $where[] = 'lr.resource_lang=?';  $params[] = $filter_lang; }

$res_stmt = $pdo->prepare("
    SELECT lr.*,
        COALESCE(rv.is_completed, 0) as is_completed,
        CASE WHEN rv.viewed_at IS NOT NULL THEN 1 ELSE 0 END as was_viewed
    FROM level_resources lr
    LEFT JOIN resource_views rv ON rv.resource_id = lr.id AND rv.user_id = ?
    WHERE " . implode(' AND ', $where) . "
    ORDER BY lr.is_featured DESC, lr.sort_order ASC, lr.id ASC
");
$res_stmt->execute(array_merge([$user_id], $params));
$resources = $res_stmt->fetchAll();

$my_stats = $pdo->prepare("
    SELECT COUNT(DISTINCT lr.id) as total_res,
        COUNT(DISTINCT CASE WHEN rv.resource_id IS NOT NULL THEN lr.id END) as viewed,
        COUNT(DISTINCT CASE WHEN rv.is_completed=1 THEN lr.id END) as completed
    FROM level_resources lr
    LEFT JOIN resource_views rv ON rv.resource_id=lr.id AND rv.user_id=?
    WHERE lr.level_id=? AND lr.is_active=1
");
$my_stats->execute([$user_id, $filter_level]);
$my_stats = $my_stats->fetch();

$type_icons  = ['video'=>'🎬','pdf'=>'📄','article'=>'📰','interactive'=>'🖥️','quiz'=>'✍️','audio'=>'🎧'];
$type_labels = ['video'=>'فيديو','pdf'=>'PDF','article'=>'مقالة','interactive'=>'تفاعلي','quiz'=>'اختبار','audio'=>'صوتي'];
$lang_labels = ['ar'=>'🇸🇦 عربي','en'=>'🇬🇧 إنجليزي','mixed'=>'🌐 مختلط'];

// جلب الدروس النصية للمستوى المحدد
$lessons_stmt = $pdo->prepare("
    SELECT ll.*, COALESCE(lr.is_completed,0) as done, COALESCE(lr.read_percentage,0) as pct
    FROM level_lessons ll
    LEFT JOIN lesson_reads lr ON lr.lesson_id=ll.id AND lr.user_id=?
    WHERE ll.level_id=? AND ll.is_active=1
    ORDER BY ll.sort_order ASC, ll.id ASC
");
$lessons_stmt->execute([$user_id, $filter_level]);
$text_lessons = $lessons_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<style>
.res-hub{max-width:1100px;margin:0 auto;}
.res-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:18px;}
.rcard{background:var(--bg-surface-card);border:1px solid rgba(208,213,229,.5);border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-card);transition:all .25s ease;cursor:pointer;position:relative;}
.rcard:hover{box-shadow:var(--shadow-card-hover);transform:translateY(-3px);}
.rcard.completed{border-color:rgba(16,185,129,.4);}
.rcard.completed::after{content:'✅ مكتمل';position:absolute;top:10px;left:10px;background:rgba(16,185,129,.92);color:#FFF;font-size:.66rem;font-weight:800;padding:3px 10px;border-radius:20px;}
.rcard.featured::before{content:'⭐';position:absolute;top:10px;right:10px;background:rgba(245,158,11,.9);color:#FFF;font-size:.7rem;font-weight:800;padding:3px 8px;border-radius:20px;}
.rcard-thumb{width:100%;height:170px;object-fit:cover;}
.rcard-thumb-ph{width:100%;height:170px;background:linear-gradient(135deg,#1A1A3E,#2D2B6F);display:flex;align-items:center;justify-content:center;font-size:52px;color:rgba(255,255,255,.15);}
.rcard-body{padding:14px 16px;}
.rcard-title{font-weight:800;font-size:.95rem;color:var(--text-heading);margin-bottom:5px;line-height:1.5;}
.rcard-desc{font-size:.82rem;color:var(--text-muted);line-height:1.6;margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.rcard-meta{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px;}
.rtag{padding:2px 9px;border-radius:20px;font-size:.68rem;font-weight:700;border:1px solid transparent;display:inline-flex;align-items:center;gap:3px;}
.rtag-video{background:rgba(239,68,68,.1);color:#EF4444;border-color:rgba(239,68,68,.25);}
.rtag-pdf{background:rgba(245,158,11,.1);color:#D97706;border-color:rgba(245,158,11,.25);}
.rtag-article{background:rgba(16,185,129,.1);color:#059669;border-color:rgba(16,185,129,.25);}
.rtag-interactive{background:rgba(91,79,232,.1);color:#5B4FE8;border-color:rgba(91,79,232,.25);}
.rtag-quiz{background:rgba(6,182,212,.1);color:#0891B2;border-color:rgba(6,182,212,.25);}
.rtag-audio{background:rgba(168,85,247,.1);color:#7C3AED;border-color:rgba(168,85,247,.25);}
.rtag-ar{background:rgba(16,185,129,.08);color:#059669;border-color:rgba(16,185,129,.2);}
.rtag-en{background:rgba(59,130,246,.08);color:#2563EB;border-color:rgba(59,130,246,.2);}
.rtag-mixed{background:rgba(245,158,11,.08);color:#D97706;border-color:rgba(245,158,11,.2);}
/* Viewer */
.vov{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:3000;display:flex;flex-direction:column;}
.vtop{background:rgba(15,23,42,.98);padding:0 22px;height:56px;display:flex;align-items:center;justify-content:space-between;gap:14px;border-bottom:1px solid rgba(91,79,232,.25);flex-shrink:0;}
.vbody{flex:1;overflow:hidden;}
.vframe{width:100%;height:100%;border:none;background:#0F172A;}
/* Progress */
.prog-card{background:var(--bg-surface-card);border:1px solid rgba(208,213,229,.45);border-radius:var(--radius-md);padding:16px 20px;margin-bottom:18px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;}
.pv{font-size:1.4rem;font-weight:900;font-family:var(--font-mono);color:var(--brand-primary);}
.pl{font-size:.74rem;color:var(--text-muted);margin-top:2px;}
.pbg{height:7px;background:rgba(208,213,229,.3);border-radius:10px;overflow:hidden;margin-top:6px;}
.pfill{height:100%;background:var(--brand-gradient);border-radius:10px;transition:width .6s ease;}
</style>

<div class="res-hub">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.4rem;color:var(--text-heading);font-weight:900;margin-bottom:4px;"><i class="fa-solid fa-books" style="color:var(--brand-primary);"></i> مركز الموارد التعليمية</h1>
        <p style="color:var(--text-muted);font-size:.86rem;">فيديوهات · PDF · مقالات · تفاعلي — كل ما تحتاجه هنا</p>
    </div>
    <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right"></i> لوحة القيادة</a>
</div>

<?php $pct = $my_stats['total_res'] > 0 ? round(($my_stats['completed']/$my_stats['total_res'])*100) : 0; ?>
<div class="prog-card">
    <div><div class="pv"><?= $my_stats['total_res'] ?></div><div class="pl">إجمالي</div></div>
    <div><div class="pv" style="color:#F59E0B;"><?= $my_stats['viewed'] ?></div><div class="pl">شاهدت</div></div>
    <div><div class="pv" style="color:#10B981;"><?= $my_stats['completed'] ?></div><div class="pl">أكملت</div></div>
    <div style="flex:1;min-width:180px;">
        <div style="display:flex;justify-content:space-between;font-size:.78rem;font-weight:700;"><span>الإتمام</span><span style="color:var(--brand-primary);"><?= $pct ?>%</span></div>
        <div class="pbg"><div class="pfill" style="width:<?= $pct ?>%;"></div></div>
    </div>
</div>

<!-- تبويبات المستويات -->
<div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px;">
    <?php foreach ($levels as $lv): ?>
    <a href="?level=<?= $lv['id'] ?>&type=<?= urlencode($filter_type) ?>&lang=<?= urlencode($filter_lang) ?>"
       class="btn btn-sm" style="<?= $filter_level===$lv['id']?'background:var(--brand-primary);color:#FFF;border-color:var(--brand-primary);':'background:var(--bg-surface-card);' ?>">
        <?= clean_output($lv['level_name']) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- فلتر -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px;align-items:center;">
    <span style="font-size:.78rem;font-weight:700;color:var(--text-muted);">نوع:</span>
    <?php foreach([''=>'الكل','video'=>'🎬','pdf'=>'📄','article'=>'📰','interactive'=>'🖥️','quiz'=>'✍️','audio'=>'🎧'] as $k=>$v): $a=($filter_type===$k); ?>
    <a href="?level=<?= $filter_level ?>&type=<?= urlencode($k) ?>&lang=<?= urlencode($filter_lang) ?>"
       style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:.74rem;font-weight:700;text-decoration:none;border:1px solid <?= $a?'var(--brand-primary)':'var(--border-main)' ?>;background:<?= $a?'var(--brand-primary)':'var(--bg-surface-card)' ?>;color:<?= $a?'#FFF':'var(--text-muted)' ?>;"><?= $v ?></a>
    <?php endforeach; ?>
    <span style="font-size:.78rem;font-weight:700;color:var(--text-muted);margin-right:6px;">لغة:</span>
    <?php foreach([''=>'الكل','ar'=>'🇸🇦','en'=>'🇬🇧','mixed'=>'🌐'] as $k=>$v): $a=($filter_lang===$k); ?>
    <a href="?level=<?= $filter_level ?>&type=<?= urlencode($filter_type) ?>&lang=<?= urlencode($k) ?>"
       style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:.74rem;font-weight:700;text-decoration:none;border:1px solid <?= $a?'var(--brand-primary)':'var(--border-main)' ?>;background:<?= $a?'var(--brand-primary)':'var(--bg-surface-card)' ?>;color:<?= $a?'#FFF':'var(--text-muted)' ?>;"><?= $v ?></a>
    <?php endforeach; ?>
</div>

<!-- قسم الدروس النصية -->
<?php if (!empty($text_lessons)): ?>
<div style="margin-bottom:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h2 style="font-size:1.1rem;font-weight:900;color:var(--text-heading);">
            <i class="fa-solid fa-book-open-reader" style="color:var(--brand-primary);"></i>
            الدروس النصية — اقرأها داخل المنصة
        </h2>
        <span style="font-size:0.8rem;color:var(--text-muted);"><?= count($text_lessons) ?> درس</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px;">
        <?php foreach ($text_lessons as $ls): ?>
        <a href="lesson_reader.php?id=<?= $ls['id'] ?>" style="text-decoration:none;">
        <div class="rcard" style="cursor:pointer;<?= $ls['done']?'border-color:rgba(16,185,129,.4);':'' ?>">
            <div style="height:80px;background:linear-gradient(135deg,#1A1A3E 0%,#2D2B6F 60%,#1e3a5f 100%);display:flex;align-items:center;justify-content:space-between;padding:0 20px;">
                <span style="font-size:32px;opacity:.25;">📖</span>
                <?php if ($ls['done']): ?>
                    <span style="background:rgba(16,185,129,.9);color:#FFF;font-size:.66rem;font-weight:800;padding:3px 10px;border-radius:20px;">✅ مكتمل</span>
                <?php elseif ($ls['pct'] > 0): ?>
                    <span style="background:rgba(245,158,11,.85);color:#FFF;font-size:.66rem;font-weight:800;padding:3px 10px;border-radius:20px;"><?= $ls['pct'] ?>% مقروء</span>
                <?php else: ?>
                    <span style="background:rgba(91,79,232,.85);color:#FFF;font-size:.66rem;font-weight:800;padding:3px 10px;border-radius:20px;">📖 درس نصي</span>
                <?php endif; ?>
            </div>
            <div class="rcard-body">
                <div class="rcard-meta">
                    <span class="rtag" style="background:rgba(91,79,232,.1);color:var(--brand-primary);border-color:rgba(91,79,232,.25);">📖 مقالة / درس</span>
                    <span class="rtag" style="background:rgba(16,185,129,.08);color:#059669;border-color:rgba(16,185,129,.2);"><i class="fa-regular fa-clock"></i> <?= $ls['estimated_read_minutes'] ?> دقيقة</span>
                </div>
                <div class="rcard-title"><?= clean_output($ls['title']) ?></div>
                <?php if (!empty($ls['summary'])): ?><div class="rcard-desc"><?= clean_output($ls['summary']) ?></div><?php endif; ?>
                <?php if ($ls['pct'] > 0 && !$ls['done']): ?>
                <div style="margin-top:8px;height:4px;background:rgba(208,213,229,.3);border-radius:10px;overflow:hidden;">
                    <div style="height:100%;width:<?= $ls['pct'] ?>%;background:linear-gradient(90deg,#5B4FE8,#06B6D4);border-radius:10px;"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- البطاقات (فيديوهات، PDF، تفاعلي) -->
<?php if (!empty($text_lessons) || !empty($resources)): ?>
<h2 style="font-size:1.1rem;font-weight:900;color:var(--text-heading);margin-bottom:14px;">
    <i class="fa-solid fa-play-circle" style="color:var(--brand-primary);"></i>
    موارد الفيديو والـ PDF والتفاعلية
</h2>
<?php endif; ?>
<?php if (empty($resources)): ?>
<div class="academic-card" style="text-align:center;padding:50px 30px;">
    <div style="font-size:52px;margin-bottom:12px;">📭</div>
    <h3 style="color:var(--text-heading);">لا توجد موارد وسائط لهذا المستوى بعد</h3>
    <p style="color:var(--text-muted);font-size:.88rem;">الدروس النصية متاحة أعلاه</p>
</div>
<?php else: ?>
<div class="res-grid">
    <?php foreach ($resources as $r): ?>
    <div class="rcard <?= $r['is_completed']?'completed':'' ?> <?= $r['is_featured']?'featured':'' ?>"
         id="rcard-<?= $r['id'] ?>" onclick='openRes(<?= htmlspecialchars(json_encode($r),ENT_QUOTES) ?>)'>
        <?php if (!empty($r['thumbnail_url'])): ?>
            <img src="<?= htmlspecialchars($r['thumbnail_url']) ?>" class="rcard-thumb" alt=""
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="rcard-thumb-ph" style="display:none;"><?= $type_icons[$r['resource_type']]??'📚' ?></div>
        <?php else: ?>
            <div class="rcard-thumb-ph"><?= $type_icons[$r['resource_type']]??'📚' ?></div>
        <?php endif; ?>
        <div class="rcard-body">
            <div class="rcard-meta">
                <span class="rtag rtag-<?= $r['resource_type'] ?>"><?= $type_icons[$r['resource_type']] ?> <?= $type_labels[$r['resource_type']] ?></span>
                <span class="rtag rtag-<?= $r['resource_lang'] ?>"><?= $lang_labels[$r['resource_lang']]??'' ?></span>
                <?php if (!empty($r['duration_label'])): ?><span class="rtag" style="background:rgba(91,79,232,.06);color:var(--brand-primary);border-color:rgba(91,79,232,.15);"><i class="fa-solid fa-clock"></i> <?= clean_output($r['duration_label']) ?></span><?php endif; ?>
                <?php if ($r['was_viewed'] && !$r['is_completed']): ?><span class="rtag" style="background:rgba(245,158,11,.08);color:#D97706;border-color:rgba(245,158,11,.2);">شاهدت</span><?php endif; ?>
            </div>
            <div class="rcard-title"><?= clean_output($r['title']) ?></div>
            <?php if (!empty($r['description'])): ?><div class="rcard-desc"><?= clean_output($r['description']) ?></div><?php endif; ?>
            <?php if (!empty($r['author_name'])): ?><div style="font-size:.76rem;color:var(--text-muted);"><i class="fa-solid fa-user-tie"></i> <?= clean_output($r['author_name']) ?></div><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Viewer Modal -->
<div id="vov" class="vov" style="display:none;">
    <div class="vtop">
        <div style="display:flex;align-items:center;gap:10px;overflow:hidden;">
            <span id="vtbadge" class="rtag" style="font-size:.74rem;padding:4px 12px;white-space:nowrap;"></span>
            <span id="vtitle" style="font-weight:700;color:#FFF;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:480px;"></span>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button id="bcomplete" onclick="toggleDone()" class="btn btn-sm" style="background:rgba(16,185,129,.15);color:#10B981;border:1px solid rgba(16,185,129,.3);">
                <i class="fa-solid fa-circle-check"></i> <span id="blabel">إتمام</span>
            </button>
            <a id="vext" href="#" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> فتح خارجي</a>
            <button onclick="closeV()" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> إغلاق</button>
        </div>
    </div>
    <div class="vbody" id="vbody"></div>
</div>

<script>
let curRid=null, curDone=false;
const TI=<?= json_encode($type_icons) ?>, TL=<?= json_encode($type_labels) ?>;

function openRes(r){
    curRid=r.id; curDone=r.is_completed==1;
    document.getElementById('vtitle').textContent=r.title;
    const bd=document.getElementById('vtbadge');
    bd.textContent=(TI[r.resource_type]||'📚')+' '+(TL[r.resource_type]||'');
    document.getElementById('vext').href=r.url;
    const bl=document.getElementById('blabel');
    bl.textContent=curDone?'✅ مكتمل':'إتمام';
    document.getElementById('bcomplete').style.background=curDone?'rgba(16,185,129,.3)':'rgba(16,185,129,.15)';

    const body=document.getElementById('vbody');
    body.innerHTML='';

    if(r.resource_type==='video'){
        const ym=r.url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/);
        const eu=ym?'https://www.youtube.com/embed/'+ym[1]+'?autoplay=1&rel=0':r.url;
        body.innerHTML='<iframe class="vframe" src="'+eu+'" allow="autoplay;fullscreen" allowfullscreen></iframe>';
    } else if(r.resource_type==='pdf'){
        const pv=r.url.startsWith('http')?'https://docs.google.com/viewer?url='+encodeURIComponent(r.url)+'&embedded=true':r.url;
        body.innerHTML='<div style="display:flex;flex-direction:column;height:100%;background:#1e1e2e;"><div style="padding:8px 16px;background:rgba(245,158,11,.1);border-bottom:1px solid rgba(245,158,11,.2);display:flex;align-items:center;gap:10px;"><span style="color:#D97706;font-weight:700;font-size:.82rem;">📄 عارض PDF داخل المنصة</span><a href="'+r.url+'" target="_blank" class="btn btn-sm" style="background:rgba(245,158,11,.15);color:#D97706;border:1px solid rgba(245,158,11,.3);margin-right:auto;"><i class="fa-solid fa-download"></i> تحميل</a></div><iframe class="vframe" src="'+pv+'" style="flex:1;"></iframe></div>';
    } else if(r.resource_type==='audio'){
        body.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100%;background:linear-gradient(135deg,#1A1A3E,#2D2B6F);"><div style="text-align:center;max-width:550px;width:100%;padding:36px;"><div style="font-size:72px;margin-bottom:18px;">🎧</div><h3 style="color:#FFF;margin-bottom:14px;">'+r.title+'</h3><audio controls autoplay style="width:100%;border-radius:12px;"><source src="'+r.url+'"></audio></div></div>';
    } else {
        body.innerHTML='<iframe class="vframe" src="'+r.url+'" sandbox="allow-scripts allow-same-origin allow-forms allow-popups" allow="fullscreen"></iframe>';
    }

    document.getElementById('vov').style.display='flex';
    document.body.style.overflow='hidden';
    markV(r.id,0);
}

function markV(rid,done){
    const fd=new FormData();
    fd.append('mark_viewed','1');fd.append('resource_id',rid);fd.append('is_completed',done);
    fd.append('csrf_token','<?= get_csrf_token() ?>');
    fetch('learning_resources.php',{method:'POST',body:fd}).catch(()=>{});
}

function toggleDone(){
    curDone=!curDone; markV(curRid,curDone?1:0);
    document.getElementById('blabel').textContent=curDone?'✅ مكتمل':'إتمام';
    document.getElementById('bcomplete').style.background=curDone?'rgba(16,185,129,.3)':'rgba(16,185,129,.15)';
    const c=document.getElementById('rcard-'+curRid);
    if(c) curDone?c.classList.add('completed'):c.classList.remove('completed');
}

function closeV(){
    document.getElementById('vov').style.display='none';
    document.getElementById('vbody').innerHTML='';
    document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeV();});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
