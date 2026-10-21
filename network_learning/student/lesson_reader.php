<?php
$pageTitle = "قارئ الدروس";
require_once "../config/db.php";
require_once "../config/security.php";
require_auth("student");

$user_id   = $_SESSION["user_id"];
$lesson_id = intval($_GET["id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_done"])) {
    $pct  = intval($_POST["pct"] ?? 0);
    $done = $pct >= 90 ? 1 : 0;
    $pdo->prepare("INSERT INTO lesson_reads (user_id,lesson_id,is_completed,read_percentage) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE is_completed=GREATEST(is_completed,?), read_percentage=GREATEST(read_percentage,?)")
        ->execute([$user_id, $lesson_id, $done, $pct, $done, $pct]);
    echo json_encode(["ok"=>true,"done"=>$done]);
    exit;
}

if (!$lesson_id) { header("Location: learning_resources.php"); exit; }

$ls = $pdo->prepare("SELECT ll.*, l.level_name, l.level_order FROM level_lessons ll JOIN levels l ON l.id=ll.level_id WHERE ll.id=? AND ll.is_active=1");
$ls->execute([$lesson_id]);
$lesson = $ls->fetch();
if (!$lesson) { header("Location: learning_resources.php"); exit; }

$rd = $pdo->prepare("SELECT * FROM lesson_reads WHERE user_id=? AND lesson_id=?");
$rd->execute([$user_id, $lesson_id]);
$read_state = $rd->fetch();

$others = $pdo->prepare("SELECT id,title,estimated_read_minutes,is_featured, (SELECT is_completed FROM lesson_reads WHERE user_id=? AND lesson_id=ll.id) as done FROM level_lessons ll WHERE level_id=? AND is_active=1 AND id!=? ORDER BY sort_order ASC LIMIT 5");
$others->execute([$user_id, $lesson["level_id"], $lesson_id]);
$others = $others->fetchAll();

$pdo->prepare("INSERT IGNORE INTO lesson_reads (user_id,lesson_id,read_percentage) VALUES (?,?,0)")->execute([$user_id, $lesson_id]);
$pdo->prepare("UPDATE level_lessons SET view_count=view_count+1 WHERE id=?")->execute([$lesson_id]);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo clean_output($lesson["title"]); ?> | NetLearn Pro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--bg:#F8FAFD;--surface:#FFFFFF;--border:rgba(208,213,229,0.6);--text:#1E293B;--text-muted:#64748B;--brand:#5B4FE8;--brand2:#06B6D4;--success:#10B981;--warning:#F59E0B;--topbar-h:60px;--sidebar-w:290px;--font:"Cairo",sans-serif;--mono:"JetBrains Mono",monospace;--radius:12px;}
[data-dark="1"]{--bg:#0F172A;--surface:#1E293B;--border:rgba(51,65,85,0.8);--text:#E2E8F0;--text-muted:#94A3B8;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:var(--font);background:var(--bg);color:var(--text);transition:background .3s,color .3s;}
.lr-top{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,0.96);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);height:var(--topbar-h);display:flex;align-items:center;justify-content:space-between;padding:0 24px;gap:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:background .3s;}
[data-dark="1"] .lr-top{background:rgba(15,23,42,0.97);}
.read-progress-bar{position:fixed;top:var(--topbar-h);left:0;right:0;z-index:99;height:3px;background:rgba(91,79,232,0.15);}
.read-progress-fill{height:100%;background:linear-gradient(90deg,#5B4FE8,#06B6D4);width:0%;transition:width 0.2s ease;}
.lr-layout{display:flex;padding-top:calc(var(--topbar-h)+3px);min-height:100vh;}
.lr-sidebar{width:var(--sidebar-w);flex-shrink:0;position:sticky;top:calc(var(--topbar-h)+3px);height:calc(100vh - var(--topbar-h) - 3px);overflow-y:auto;padding:20px 16px;border-left:1px solid var(--border);background:var(--surface);}
.lr-sidebar::-webkit-scrollbar{width:4px}.lr-sidebar::-webkit-scrollbar-thumb{background:rgba(91,79,232,.3);border-radius:10px;}
.lr-main{flex:1;max-width:780px;margin:0 auto;padding:36px 28px 80px;}
/* Article styling */
.lesson-article{font-size:1.05rem;line-height:2;color:var(--text);}
.lesson-article h2{font-size:1.5rem;font-weight:900;color:var(--text);margin:36px 0 14px;padding-bottom:10px;border-bottom:2px solid var(--brand);}
.lesson-article h3{font-size:1.15rem;font-weight:800;color:var(--text);margin:24px 0 10px;}
.lesson-article h4{font-size:1rem;font-weight:800;color:var(--brand);margin:18px 0 7px;}
.lesson-article p{margin-bottom:14px;}
.lesson-article ul,.lesson-article ol{padding-right:22px;margin-bottom:14px;}
.lesson-article li{margin-bottom:7px;}
.lesson-article code{background:rgba(91,79,232,0.08);color:var(--brand);padding:2px 6px;border-radius:6px;font-family:var(--mono);font-size:0.88em;}
.lesson-article pre{background:rgba(15,23,42,0.93);color:#E2E8F0;padding:16px 18px;border-radius:var(--radius);font-family:var(--mono);font-size:0.86rem;overflow-x:auto;margin-bottom:16px;line-height:1.7;border:1px solid rgba(91,79,232,0.25);}
[data-dark="1"] .lesson-article pre{background:rgba(0,0,0,0.55);}
.lesson-article table{width:100%;border-collapse:collapse;margin-bottom:18px;border-radius:var(--radius);overflow:hidden;}
.lesson-article th{background:linear-gradient(135deg,#5B4FE8,#4338CA);color:#FFF;padding:10px 13px;font-size:0.86rem;font-weight:700;text-align:right;}
.lesson-article td{padding:9px 13px;border-bottom:1px solid var(--border);font-size:0.88rem;vertical-align:top;}
.lesson-article tr:nth-child(even) td{background:rgba(91,79,232,0.03);}
.info-box,.warning-box{border-radius:var(--radius);padding:14px 18px;margin:16px 0;font-size:0.92rem;line-height:1.7;}
.info-box{background:rgba(6,182,212,0.08);border-right:4px solid var(--brand2);}
.warning-box{background:rgba(245,158,11,0.08);border-right:4px solid var(--warning);}
[data-dark="1"] .info-box{background:rgba(6,182,212,0.1);}
[data-dark="1"] .warning-box{background:rgba(245,158,11,0.1);}
/* TOC */
.toc-item{display:flex;align-items:center;gap:7px;padding:7px 9px;border-radius:8px;cursor:pointer;font-size:0.83rem;color:var(--text-muted);font-weight:600;transition:all 0.2s;text-decoration:none;}
.toc-item:hover,.toc-item.active{background:rgba(91,79,232,0.08);color:var(--brand);}
.toc-dot{width:5px;height:5px;border-radius:50%;background:var(--border);flex-shrink:0;transition:all 0.2s;}
.toc-item.active .toc-dot{background:var(--brand);}
.other-lesson{display:flex;gap:9px;align-items:flex-start;padding:9px;border-radius:9px;cursor:pointer;transition:background 0.2s;text-decoration:none;}
.other-lesson:hover{background:rgba(91,79,232,0.06);}
.other-lesson-icon{width:34px;height:34px;border-radius:8px;background:rgba(91,79,232,0.1);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.done-banner{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#10B981,#059669);color:#FFF;padding:13px 26px;border-radius:50px;font-weight:800;font-size:0.92rem;box-shadow:0 8px 24px rgba(16,185,129,0.35);display:none;align-items:center;gap:10px;z-index:200;animation:popIn 0.4s cubic-bezier(0.34,1.56,0.64,1);}
@keyframes popIn{from{opacity:0;transform:translateX(-50%) scale(.8)}to{opacity:1;transform:translateX(-50%) scale(1)}}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:8px;font-weight:700;font-size:0.83rem;cursor:pointer;text-decoration:none;border:1.5px solid transparent;font-family:var(--font);transition:all 0.2s;}
.btn-brand{background:var(--brand);color:#FFF;border-color:var(--brand);}
.btn-ghost{background:transparent;color:var(--text-muted);border-color:var(--border);}
.btn-ghost:hover{background:rgba(91,79,232,0.06);color:var(--brand);border-color:var(--brand);}
.btn-icon{width:35px;height:35px;padding:0;justify-content:center;border-radius:9px;}
@media(max-width:900px){.lr-sidebar{display:none;}.lr-main{padding:20px 14px 60px;}}
</style>
</head>
<body>
<div class="lr-top">
    <div style="display:flex;align-items:center;gap:11px;">
        <a href="learning_resources.php?level=<?php echo $lesson["level_id"]; ?>" class="btn btn-ghost btn-icon" title="رجوع">
            <i class="fa-solid fa-arrow-right"></i>
        </a>
        <div>
            <div style="font-size:0.68rem;color:var(--brand);font-weight:800;letter-spacing:0.05em;">LESSON READER</div>
            <div style="font-weight:800;font-size:0.86rem;color:var(--text);max-width:380px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo clean_output($lesson["title"]); ?></div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:7px;">
        <span style="font-size:0.75rem;color:var(--text-muted);" id="read-pct-label">0%</span>
        <span style="font-size:0.75rem;color:var(--text-muted);"><i class="fa-regular fa-clock"></i> <?php echo $lesson["estimated_read_minutes"]; ?> دقيقة</span>
        <button onclick="toggleDark()" class="btn btn-ghost btn-icon"><i class="fa-solid fa-moon" id="dark-icon"></i></button>
        <button onclick="increaseFontSize()" class="btn btn-ghost btn-icon"><i class="fa-solid fa-text-height"></i></button>
        <a href="learning_resources.php?level=<?php echo $lesson["level_id"]; ?>" class="btn btn-ghost"><i class="fa-solid fa-books"></i> الموارد</a>
    </div>
</div>
<div class="read-progress-bar"><div class="read-progress-fill" id="read-fill"></div></div>
<div class="lr-layout">
<aside class="lr-sidebar">
    <div style="margin-bottom:16px;">
        <span style="display:inline-block;background:rgba(91,79,232,0.1);color:var(--brand);padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:800;margin-bottom:8px;"><?php echo clean_output($lesson["level_name"]); ?></span>
        <div style="font-weight:900;font-size:0.9rem;color:var(--text);line-height:1.5;margin-bottom:6px;"><?php echo clean_output($lesson["title"]); ?></div>
        <div style="font-size:0.74rem;color:var(--text-muted);"><i class="fa-solid fa-user-tie"></i> <?php echo clean_output($lesson["author_name"]); ?></div>
    </div>
    <div style="background:rgba(91,79,232,0.06);border:1px solid rgba(91,79,232,0.15);border-radius:10px;padding:11px;margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:5px;">
            <span>تقدم القراءة</span>
            <span style="color:var(--brand);" id="sidebar-pct"><?php echo $read_state ? ($read_state["read_percentage"] ?? 0) : 0; ?>%</span>
        </div>
        <div style="height:5px;background:rgba(208,213,229,0.3);border-radius:10px;overflow:hidden;">
            <div id="sidebar-fill" style="height:100%;background:linear-gradient(90deg,#5B4FE8,#06B6D4);border-radius:10px;width:<?php echo $read_state ? ($read_state["read_percentage"] ?? 0) : 0; ?>%;transition:width .5s;"></div>
        </div>
        <?php if ($read_state && $read_state["is_completed"]): ?><div style="color:#10B981;font-size:0.74rem;font-weight:800;margin-top:5px;">✅ أكملت هذا الدرس</div><?php endif; ?>
    </div>
    <div style="font-size:0.76rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">فهرس المحتوى</div>
    <div id="toc-list" style="margin-bottom:20px;"></div>
    <?php if (!empty($others)): ?>
    <div style="font-size:0.76rem;font-weight:900;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;border-top:1px solid var(--border);padding-top:14px;">دروس أخرى</div>
    <?php foreach ($others as $o): ?>
    <a href="lesson_reader.php?id=<?php echo $o["id"]; ?>" class="other-lesson">
        <div class="other-lesson-icon"><?php echo $o["done"] ? "✅" : '<i class="fa-solid fa-book-open"></i>'; ?></div>
        <div>
            <div style="font-size:0.8rem;font-weight:700;color:var(--text);line-height:1.4;"><?php echo clean_output($o["title"]); ?></div>
            <div style="font-size:0.7rem;color:var(--text-muted);margin-top:2px;"><i class="fa-regular fa-clock"></i> <?php echo $o["estimated_read_minutes"]; ?> دقيقة</div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</aside>
<main class="lr-main" id="lesson-main">
    <div style="margin-bottom:28px;padding-bottom:24px;border-bottom:1px solid var(--border);">
        <div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:12px;">
            <span style="background:rgba(91,79,232,0.1);color:var(--brand);padding:3px 11px;border-radius:20px;font-size:0.7rem;font-weight:800;"><i class="fa-solid fa-layer-group"></i> <?php echo clean_output($lesson["level_name"]); ?></span>
            <span style="background:rgba(16,185,129,0.1);color:#059669;padding:3px 11px;border-radius:20px;font-size:0.7rem;font-weight:800;"><i class="fa-regular fa-clock"></i> <?php echo $lesson["estimated_read_minutes"]; ?> دقيقة</span>
            <span style="background:rgba(245,158,11,0.1);color:#D97706;padding:3px 11px;border-radius:20px;font-size:0.7rem;font-weight:800;"><i class="fa-solid fa-eye"></i> <?php echo $lesson["view_count"]; ?> مشاهدة</span>
        </div>
        <h1 style="font-size:1.7rem;font-weight:900;color:var(--text);line-height:1.4;margin-bottom:10px;"><?php echo clean_output($lesson["title"]); ?></h1>
        <?php if ($lesson["summary"]): ?><p style="font-size:0.98rem;color:var(--text-muted);line-height:1.7;"><?php echo clean_output($lesson["summary"]); ?></p><?php endif; ?>
        <div style="font-size:0.74rem;color:var(--text-muted);margin-top:8px;"><i class="fa-solid fa-user-pen"></i> <?php echo clean_output($lesson["author_name"]); ?> · <?php echo date("d/m/Y", strtotime($lesson["created_at"])); ?></div>
    </div>
    <article class="lesson-article" id="lesson-content"><?php echo $lesson["content"]; ?></article>
    <div style="margin-top:44px;padding-top:24px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-weight:800;font-size:0.97rem;color:var(--text);margin-bottom:3px;">هل أتممت قراءة هذا الدرس؟</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">سيُسجّل نظامياً في مسارك التعليمي</div>
        </div>
        <button onclick="markDone()" id="btn-done" class="btn btn-brand" style="padding:11px 22px;">
            <i class="fa-solid fa-circle-check"></i>
            <span id="btn-done-label">أتممت هذا الدرس</span>
        </button>
    </div>
</main>
</div>
<div class="done-banner" id="done-banner">
    <i class="fa-solid fa-trophy"></i>
    <span id="done-msg">🎉 أحسنت! سُجّل الدرس مكتملاً</span>
</div>
<script>
const LESSON_ID = <?php echo $lesson_id; ?>;
const CSRF = "<?php echo get_csrf_token(); ?>";
let fontSize = 1.05;
let isDark = localStorage.getItem("lr-dark") === "1";
let lastPct = <?php echo $read_state ? ($read_state["read_percentage"] ?? 0) : 0; ?>;
let isDone = <?php echo ($read_state && $read_state["is_completed"]) ? "true" : "false"; ?>;
let saveTimer = null;

if (isDark) document.body.dataset.dark = "1";

function toggleDark() {
    isDark = !isDark;
    document.body.dataset.dark = isDark ? "1" : "0";
    localStorage.setItem("lr-dark", isDark ? "1" : "0");
    document.getElementById("dark-icon").className = isDark ? "fa-solid fa-sun" : "fa-solid fa-moon";
}

function increaseFontSize() {
    fontSize = fontSize >= 1.3 ? 1.0 : fontSize + 0.07;
    document.querySelector(".lesson-article").style.fontSize = fontSize + "rem";
}

function buildTOC() {
    const headings = document.querySelectorAll("#lesson-content h2, #lesson-content h3");
    const toc = document.getElementById("toc-list");
    headings.forEach((h, i) => {
        h.id = "h" + i;
        const level = h.tagName === "H2" ? 0 : 1;
        const a = document.createElement("a");
        a.href = "#h" + i;
        a.className = "toc-item";
        a.style.paddingRight = (9 + level * 12) + "px";
        a.innerHTML = "<span class=\"toc-dot\"></span><span>" + h.textContent.substring(0,48) + "</span>";
        a.onclick = e => { e.preventDefault(); h.scrollIntoView({behavior:"smooth"}); };
        toc.appendChild(a);
    });
}
buildTOC();

function getScrollPct() {
    const el = document.getElementById("lesson-main");
    const total = el.scrollHeight - window.innerHeight;
    if (total <= 0) return 100;
    const scrolled = Math.max(0, -el.getBoundingClientRect().top);
    return Math.min(100, Math.round((scrolled / total) * 100));
}

function updateRead() {
    const pct = getScrollPct();
    document.getElementById("read-fill").style.width = pct + "%";
    document.getElementById("read-pct-label").textContent = pct + "%";
    document.getElementById("sidebar-pct").textContent = pct + "%";
    document.getElementById("sidebar-fill").style.width = pct + "%";
    const hs = document.querySelectorAll("#lesson-content h2,#lesson-content h3");
    let ai = 0;
    hs.forEach((h,i) => { if (h.getBoundingClientRect().top < window.innerHeight * 0.4) ai = i; });
    document.querySelectorAll(".toc-item").forEach((a,i) => a.classList.toggle("active", i === ai));
    if (pct > lastPct + 4) {
        lastPct = pct;
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => saveProgress(pct), 3000);
        if (pct >= 90 && !isDone) markDone(true);
    }
}
window.addEventListener("scroll", updateRead, {passive:true});

function saveProgress(pct) {
    const fd = new FormData();
    fd.append("mark_done","1"); fd.append("pct",pct); fd.append("csrf_token",CSRF);
    fetch("lesson_reader.php?id="+LESSON_ID, {method:"POST",body:fd}).catch(()=>{});
}

function markDone(auto) {
    if (isDone) return;
    isDone = true;
    saveProgress(100);
    const btn = document.getElementById("btn-done");
    document.getElementById("btn-done-label").textContent = "✅ تم التسجيل كمكتمل";
    btn.style.background = "#10B981"; btn.disabled = true;
    document.getElementById("sidebar-fill").style.width = "100%";
    document.getElementById("sidebar-pct").textContent = "100%";
    const banner = document.getElementById("done-banner");
    document.getElementById("done-msg").textContent = auto ? "🎉 أتممت القراءة — سُجّل مكتملاً!" : "🏆 أحسنت! سُجّل هذا الدرس مكتملاً";
    banner.style.display = "flex";
    setTimeout(() => { banner.style.opacity="0"; banner.style.transition="opacity .6s"; setTimeout(()=>banner.style.display="none",600); }, 4000);
}

if (isDone) {
    document.getElementById("btn-done-label").textContent = "✅ مكتمل بالفعل";
    document.getElementById("btn-done").style.background = "#10B981";
    document.getElementById("btn-done").disabled = true;
}
</script>
</body>
</html>
