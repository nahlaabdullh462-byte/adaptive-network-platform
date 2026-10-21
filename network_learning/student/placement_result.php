<?php
$pageTitle = "نتيجة اختبار تحديد المستوى";
require_once '../config/db.php';
require_once '../config/security.php';
require_auth('student');

$user_id   = $_SESSION['user_id'];
$level_id  = intval($_GET['level_id']  ?? 1);
$score     = intval($_GET['score']     ?? 0);
$det_level = intval($_GET['determined'] ?? $level_id);

// جلب بيانات الطالب
$u = $pdo->prepare("SELECT u.*, l.level_name, l.level_order FROM users u JOIN levels l ON l.id=u.current_level_id WHERE u.id=?");
$u->execute([$user_id]);
$user = $u->fetch();

// جلب المستوى المحدد
$lev = $pdo->prepare("SELECT * FROM levels WHERE id=?");
$lev->execute([$level_id]);
$level = $lev->fetch();

// جلب مستوى الشخص المحدد (قبل +1)
$det_lev = $pdo->prepare("SELECT * FROM levels WHERE id=?");
$det_lev->execute([$det_level]);
$determined_level = $det_lev->fetch();

// التوصيات الذكية: موارد من مستوى الطالب الحالي (featured أولاً)
$recs = $pdo->prepare("
    SELECT lr.*, COALESCE(rv.is_completed,0) as done
    FROM level_resources lr
    LEFT JOIN resource_views rv ON rv.resource_id=lr.id AND rv.user_id=?
    WHERE lr.level_id=? AND lr.is_active=1
    ORDER BY lr.is_featured DESC, lr.sort_order ASC
    LIMIT 6
");
$recs->execute([$user_id, $level_id]);
$recommendations = $recs->fetchAll();

// آخر نتائج الاختبارات
$history = $pdo->prepare("
    SELECT ea.*, l.level_name FROM exam_attempts ea
    JOIN levels l ON l.id=ea.level_id OR ea.level_id IS NULL
    WHERE ea.user_id=? AND ea.status='completed'
    ORDER BY ea.id DESC LIMIT 5
");
$history->execute([$user_id]);
$history = $history->fetchAll();

$type_icons  = ['video'=>'🎬','pdf'=>'📄','article'=>'📰','interactive'=>'🖥️','quiz'=>'✍️','audio'=>'🎧'];
$type_labels = ['video'=>'فيديو','pdf'=>'PDF','article'=>'مقالة','interactive'=>'تفاعلي','quiz'=>'اختبار','audio'=>'صوتي'];

require_once '../includes/header.php';
?>
<style>
.result-wrap { max-width: 860px; margin: 0 auto; }

/* بطاقة النتيجة الرئيسية */
.result-hero {
    background: linear-gradient(135deg,#1A1A3E 0%,#2D2B6F 60%,#1e3a5f 100%);
    border-radius: var(--radius-xl);
    padding: 48px 36px;
    text-align: center;
    margin-bottom: 28px;
    position: relative; overflow: hidden;
    box-shadow: 0 20px 60px rgba(91,79,232,0.25);
}
.result-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(circle at 30% 20%, rgba(91,79,232,0.25) 0%, transparent 60%),
                radial-gradient(circle at 70% 80%, rgba(6,182,212,0.15) 0%, transparent 60%);
}
.result-hero > * { position: relative; z-index: 1; }
.score-ring {
    width: 130px; height: 130px; border-radius: 50%;
    background: conic-gradient(#5B4FE8 <?= $score * 3.6 ?>deg, rgba(255,255,255,0.1) 0);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 0 0 4px rgba(91,79,232,0.3), 0 0 40px rgba(91,79,232,0.4);
}
.score-inner { width:104px; height:104px; border-radius:50%; background:#1A1A3E; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.score-num { font-size:2.2rem; font-weight:900; color:#FFF; font-family:var(--font-mono); line-height:1; }
.score-label { font-size:0.7rem; color:rgba(255,255,255,0.6); margin-top:2px; }

.level-reveal {
    background: rgba(91,79,232,0.15); border:1px solid rgba(91,79,232,0.3);
    border-radius: var(--radius-lg); padding:16px 28px; display:inline-block; margin:16px 0;
}
.level-reveal .lname { font-size:1.3rem; font-weight:900; color:#FFF; }
.level-reveal .lsub  { font-size:0.82rem; color:rgba(255,255,255,0.6); margin-top:4px; }

/* بطاقات التوصيات */
.rec-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.rec-card {
    background:var(--bg-surface-card); border:1px solid rgba(208,213,229,0.45);
    border-radius:var(--radius-lg); overflow:hidden;
    box-shadow:var(--shadow-card); transition:all 0.22s ease; cursor:pointer;
}
.rec-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-card-hover); }
.rec-thumb { width:100%; height:140px; object-fit:cover; background:linear-gradient(135deg,#1A1A3E,#2D2B6F); }
.rec-thumb-ph { width:100%; height:140px; background:linear-gradient(135deg,#1A1A3E,#2D2B6F); display:flex; align-items:center; justify-content:center; font-size:42px; color:rgba(255,255,255,0.15); }
.rec-body { padding:12px 14px; }
.rec-title { font-weight:800; font-size:0.9rem; color:var(--text-heading); margin-bottom:6px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.rtag2 { display:inline-flex; align-items:center; gap:3px; padding:2px 8px; border-radius:20px; font-size:0.67rem; font-weight:700; border:1px solid transparent; }
.rtag2-video{background:rgba(239,68,68,0.1);color:#EF4444;border-color:rgba(239,68,68,0.25);}
.rtag2-pdf{background:rgba(245,158,11,0.1);color:#D97706;border-color:rgba(245,158,11,0.25);}
.rtag2-article{background:rgba(16,185,129,0.1);color:#059669;border-color:rgba(16,185,129,0.25);}
.rtag2-interactive{background:rgba(91,79,232,0.1);color:#5B4FE8;border-color:rgba(91,79,232,0.25);}

/* الـ viewer بسيط */
.res-overlay { position:fixed;inset:0;background:rgba(0,0,0,.84);z-index:3000;display:flex;flex-direction:column; }
.res-top { background:rgba(15,23,42,.98);padding:0 20px;height:54px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(91,79,232,.25);flex-shrink:0; }
.res-body { flex:1; overflow:hidden; }
.res-iframe { width:100%;height:100%;border:none;background:#0F172A; }
</style>

<div class="result-wrap">

<!-- بطاقة النتيجة الرئيسية -->
<div class="result-hero">
    <div class="score-ring">
        <div class="score-inner">
            <div class="score-num"><?= $score ?>%</div>
            <div class="score-label">النتيجة</div>
        </div>
    </div>

    <div style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin-bottom:8px;">
        <i class="fa-solid fa-brain" style="color:#8B5CF6;"></i>
        اكتشف الذكاء الاصطناعي أنك تُتقن حتى:
    </div>
    <?php if ($determined_level): ?>
    <div class="level-reveal">
        <div class="lname">✅ <?= clean_output($determined_level['level_name']) ?></div>
        <div class="lsub">المستوى المُتقن</div>
    </div>
    <?php endif; ?>

    <div style="color:rgba(255,255,255,0.5);font-size:0.82rem;margin:8px 0 18px;">
        ⬇️ لذا سيبدأ تعلمك من:
    </div>
    <div style="background:linear-gradient(135deg,#5B4FE8,#06B6D4);color:#FFF;padding:14px 32px;border-radius:var(--radius-lg);display:inline-block;font-weight:900;font-size:1.1rem;">
        🎯 <?= clean_output($level['level_name'] ?? 'المستوى الأول') ?>
    </div>

    <div style="margin-top:22px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <a href="learning_resources.php?level=<?= $level_id ?>" class="btn btn-primary">
            <i class="fa-solid fa-books"></i> ابدأ موارد هذا المستوى
        </a>
        <a href="exam.php?level_id=<?= $level_id ?>" class="btn btn-secondary">
            <i class="fa-solid fa-file-signature"></i> اختبار المستوى
        </a>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fa-solid fa-house"></i> لوحة القيادة
        </a>
    </div>
</div>

<!-- التوصيات الذكية -->
<?php if (!empty($recommendations)): ?>
<div class="academic-card" style="margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h2 style="font-size:1.1rem;font-weight:900;color:var(--text-heading);margin-bottom:3px;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--brand-primary);"></i>
                المصادر المقترحة لمستواك
            </h2>
            <p style="font-size:0.82rem;color:var(--text-muted);">اخترها لك الذكاء الاصطناعي بناءً على مستواك المحدد</p>
        </div>
        <a href="learning_resources.php?level=<?= $level_id ?>" class="btn btn-secondary btn-sm">عرض الكل</a>
    </div>
    <div class="rec-grid">
        <?php foreach ($recommendations as $r): ?>
        <div class="rec-card" onclick="openRec(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
            <?php if (!empty($r['thumbnail_url'])): ?>
                <img src="<?= htmlspecialchars($r['thumbnail_url']) ?>" class="rec-thumb" alt=""
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="rec-thumb-ph" style="display:none;"><?= $type_icons[$r['resource_type']] ?? '📚' ?></div>
            <?php else: ?>
                <div class="rec-thumb-ph"><?= $type_icons[$r['resource_type']] ?? '📚' ?></div>
            <?php endif; ?>
            <div class="rec-body">
                <div style="display:flex;gap:5px;margin-bottom:6px;flex-wrap:wrap;">
                    <span class="rtag2 rtag2-<?= $r['resource_type'] ?>"><?= $type_icons[$r['resource_type']] ?> <?= $type_labels[$r['resource_type']] ?></span>
                    <?php if ($r['is_featured']): ?><span class="rtag2" style="background:rgba(245,158,11,.1);color:#D97706;border-color:rgba(245,158,11,.25);">⭐ مميز</span><?php endif; ?>
                    <?php if ($r['done']): ?><span class="rtag2" style="background:rgba(16,185,129,.1);color:#059669;border-color:rgba(16,185,129,.25);">✅ أكملت</span><?php endif; ?>
                </div>
                <div class="rec-title"><?= clean_output($r['title']) ?></div>
                <?php if (!empty($r['duration_label'])): ?>
                <div style="font-size:0.74rem;color:var(--text-muted);"><i class="fa-solid fa-clock"></i> <?= clean_output($r['duration_label']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- .result-wrap -->

<!-- Viewer -->
<div id="res-overlay" class="res-overlay" style="display:none;">
    <div class="res-top">
        <div style="display:flex;align-items:center;gap:10px;overflow:hidden;">
            <span id="rb-badge" style="padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:800;white-space:nowrap;background:rgba(91,79,232,.15);color:var(--brand-primary);border:1px solid rgba(91,79,232,.25);"></span>
            <span id="rb-title" style="font-weight:700;color:#FFF;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:450px;"></span>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a id="rb-ext" href="#" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> فتح خارجي</a>
            <button onclick="closeRec()" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> إغلاق</button>
        </div>
    </div>
    <div class="res-body" id="rb-body"></div>
</div>

<script>
const TI = <?= json_encode($type_icons) ?>;
const TL = <?= json_encode($type_labels) ?>;

function openRec(r) {
    document.getElementById('rb-title').textContent = r.title;
    document.getElementById('rb-badge').textContent = (TI[r.resource_type]||'📚')+' '+(TL[r.resource_type]||'');
    document.getElementById('rb-ext').href = r.url;
    const body = document.getElementById('rb-body');
    body.innerHTML = '';
    if (r.resource_type === 'video') {
        const ym = r.url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/);
        const eu = ym ? 'https://www.youtube.com/embed/'+ym[1]+'?autoplay=1&rel=0' : r.url;
        body.innerHTML = '<iframe class="res-iframe" src="'+eu+'" allow="autoplay;fullscreen" allowfullscreen></iframe>';
    } else if (r.resource_type === 'pdf') {
        const pv = r.url.startsWith('http') ? 'https://docs.google.com/viewer?url='+encodeURIComponent(r.url)+'&embedded=true' : r.url;
        body.innerHTML = '<iframe class="res-iframe" src="'+pv+'"></iframe>';
    } else {
        body.innerHTML = '<iframe class="res-iframe" src="'+r.url+'" sandbox="allow-scripts allow-same-origin allow-forms allow-popups"></iframe>';
    }
    document.getElementById('res-overlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRec() {
    document.getElementById('res-overlay').style.display = 'none';
    document.getElementById('rb-body').innerHTML = '';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeRec(); });
</script>

<?php require_once '../includes/footer.php'; ?>
