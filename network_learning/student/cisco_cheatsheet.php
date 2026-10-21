<?php
session_start();
$pageTitle = "موسوعة أوامر سيسكو التفاعلية (Cisco IOS Command Hub)";

require_once '../config/db.php';
require_once '../config/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'طالب';

require_once '../includes/header.php';
?>

<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(79, 70, 229, 0.08); border: 1px solid rgba(199, 210, 254, 0.8); padding: 5px 14px; border-radius: var(--radius-full); font-size: 0.82rem; font-weight: 700; color: var(--brand-primary); margin-bottom: 8px;">
            <i class="fa-solid fa-book-bookmark"></i>
            <span>مرجع أوامر نظام التشغيل Cisco IOS الشامل | معتمد لـ CCNA 200-301</span>
        </div>
        <h1 style="font-size: 1.85rem; color: var(--text-heading); margin-bottom: 6px;">
            موسوعة أوامر سيسكو التفاعلية (Cisco CLI Command Hub)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.94rem; margin-bottom: 0;">
            دليل أكاديمي سريع للبحث في أوامر الراوتر والسويتش مع شروحات مفصلة، وسياق التنفيذ، والنسخ الفوري أو التشغيل في المحاكي.
        </p>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="cisco_cli.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-terminal"></i> الانتقال لمحاكي الطرفية CLI
        </a>
    </div>
</div>

<!-- ========================================================================= -->
<!-- شريط البحث السريع والفلترة بالأقسام والأنماط (Search & Filter Bar)           -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 22px; margin-bottom: 24px;">
    <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; margin-bottom: 16px;">
        <div style="flex: 1; min-width: 280px; position: relative;">
            <input type="text" id="command-search" oninput="filterCommands()" class="form-control" placeholder="ابحث عن أي أمر (مثال: ip address, vlan, ospf, show ip int, copy running)..." style="padding-right: 40px; font-family: var(--font-mono); font-size: 0.95rem; direction: ltr; text-align: left;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 14px; top: 14px; color: var(--text-muted);"></i>
        </div>

        <div style="display: flex; gap: 8px;">
            <select id="mode-filter" onchange="filterCommands()" class="form-control" style="width: auto; min-width: 170px; font-size: 0.88rem;">
                <option value="">كافة أطوار التنفيذ (All Modes)</option>
                <option value="user">User EXEC (Router>)</option>
                <option value="priv">Privileged EXEC (Router#)</option>
                <option value="global">Global Config (config)#</option>
                <option value="interface">Interface Config (config-if)#</option>
                <option value="line">Line Config (config-line)#</option>
            </select>
        </div>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="category-chips">
        <button type="button" onclick="setCategory('')" class="btn btn-secondary btn-sm category-btn active" id="cat-all">جميع الأقسام</button>
        <button type="button" onclick="setCategory('basic')" class="btn btn-secondary btn-sm category-btn" id="cat-basic">التهيئة الأساسية</button>
        <button type="button" onclick="setCategory('ip')" class="btn btn-secondary btn-sm category-btn" id="cat-ip">العنونة والمنافذ</button>
        <button type="button" onclick="setCategory('routing')" class="btn btn-secondary btn-sm category-btn" id="cat-routing">التوجيه والمسارات</button>
        <button type="button" onclick="setCategory('switching')" class="btn btn-secondary btn-sm category-btn" id="cat-switching">السويتشات والـ VLAN</button>
        <button type="button" onclick="setCategory('security')" class="btn btn-secondary btn-sm category-btn" id="cat-security">الأمان وكلمات المرور</button>
        <button type="button" onclick="setCategory('troubleshoot')" class="btn btn-secondary btn-sm category-btn" id="cat-troubleshoot">التشخيص والفحص</button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- شبكة بطاقات أوامر سيسكو التفاعلية (Commands Grid)                           -->
<!-- ========================================================================= -->
<div id="commands-container" class="grid-2">
    <!-- تُنشأ البطاقات تلقائياً بواسطة JavaScript -->
</div>

<div id="no-results" style="display: none; text-align: center; padding: 50px 20px;" class="academic-card">
    <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: var(--text-dim); margin-bottom: 14px;"></i>
    <h3 style="color: var(--text-heading); margin-bottom: 6px;">لم يتم العثور على أمر مطابق</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem;">جرب كتابة جزء آخر من الأمر أو تغيير فلتر النمط والقسم.</p>
</div>

<!-- إشعار النسخ المنبثق السريع -->
<div id="copy-toast" style="position: fixed; bottom: 25px; left: 50%; transform: translateX(-50%) translateY(100px); background: #1E293B; color: #FFFFFF; padding: 10px 22px; border-radius: var(--radius-full); font-size: 0.88rem; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); z-index: 9999; transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; pointer-events: none;">
    <i class="fa-solid fa-circle-check" style="color: #10B981;"></i>
    <span id="toast-msg">تم نسخ الأمر بنجاح إلى الحافظة!</span>
</div>

<script>
const commandsList = [
    {
        cmd: "enable",
        mode: "user",
        modeLabel: "User EXEC (Router>)",
        category: "basic",
        categoryLabel: "التهيئة الأساسية",
        desc: "الانتقال من وضع المستخدم العادي إلى وضع الامتيازات الكاملة (Privileged EXEC Mode).",
        syntax: "Router> enable",
        example: "Router> enable -> Router#",
        tip: "يطلب هذا الأمر كلمة مرور التمكين (Enable Secret) إن كانت مفعلة لحماية الجهاز."
    },
    {
        cmd: "configure terminal",
        mode: "priv",
        modeLabel: "Privileged EXEC (Router#)",
        category: "basic",
        categoryLabel: "التهيئة الأساسية",
        desc: "الدخول إلى وضع الإعدادات الشاملة للجهاز (Global Configuration Mode).",
        syntax: "Router# configure terminal",
        example: "Router# config t -> Router(config)#",
        tip: "يمكن اختصاره بكتابة 'conf t' في أنظمة Cisco IOS الحقيقية."
    },
    {
        cmd: "hostname <name>",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "basic",
        categoryLabel: "التهيئة الأساسية",
        desc: "تعيين الاسم التعريفي الفريد للموجه أو المحول داخل طوبولوجيا الشبكة.",
        syntax: "Router(config)# hostname Core-Router-01",
        example: "Router(config)# hostname R1 -> R1(config)#",
        tip: "من المعايير القياسية في سيسكو تسمية كل جهاز باسم يعكس دوره وموقعه الجغرافي."
    },
    {
        cmd: "ip address <ip> <subnet_mask>",
        mode: "interface",
        modeLabel: "Interface Config (config-if)#",
        category: "ip",
        categoryLabel: "العنونة والمنافذ",
        desc: "تعيين عنوان IPv4 وقناع الشبكة الفرعية على منفذ مادي أو منطقي (SVI).",
        syntax: "Router(config-if)# ip address 192.168.1.1 255.255.255.0",
        example: "Router(config-if)# ip address 10.0.0.1 255.255.255.252",
        tip: "يجب دائماً تفعيل المنفذ بعد إعطائه العنوان عبر كتابة أمر 'no shutdown'."
    },
    {
        cmd: "no shutdown",
        mode: "interface",
        modeLabel: "Interface Config (config-if)#",
        category: "ip",
        categoryLabel: "العنونة والمنافذ",
        desc: "تشغيل المنفذ ورفعه إلى الحالة الإدارية النشطة (Line protocol is UP).",
        syntax: "Router(config-if)# no shutdown",
        example: "Router(config-if)# no shut",
        tip: "منافذ موجهات سيسكو تكون في وضع الإغلاق الإداري (administratively down) افتراضياً."
    },
    {
        cmd: "show ip interface brief",
        mode: "priv",
        modeLabel: "Privileged EXEC (Router#)",
        category: "troubleshoot",
        categoryLabel: "التشخيص والفحص",
        desc: "عرض ملخص فوري لكافة منافذ الجهاز، عناوين IP المرتبطة بها، وحالتها الفيزيائية والمنطقية.",
        syntax: "Router# show ip interface brief",
        example: "Router# sh ip int br",
        tip: "أهم أمر فحص على الإطلاق للتأكد من حالة المنافذ (Status: Up / Protocol: Up)."
    },
    {
        cmd: "show ip route",
        mode: "priv",
        modeLabel: "Privileged EXEC (Router#)",
        category: "routing",
        categoryLabel: "التوجيه والمسارات",
        desc: "عرض جدول التوجيه (Routing Table) الذي يعتمد عليه الموجه في اتخاذ قرارات تمرير الحزم.",
        syntax: "Router# show ip route",
        example: "Codes: C - connected, S - static, O - OSPF",
        tip: "المسارات المتصلة مباشرة تأخذ الحرف C وتعتبر المسافة الإدارية لها 0."
    },
    {
        cmd: "ip route <network> <mask> <next_hop>",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "routing",
        categoryLabel: "التوجيه والمسارات",
        desc: "إضافة مسار ثابت يدوي (Static Route) في جدول التوجيه مع تحديد عنوان القفزة التالية.",
        syntax: "Router(config)# ip route 192.168.2.0 255.255.255.0 10.0.0.2",
        example: "Router(config)# ip route 0.0.0.0 0.0.0.0 10.0.0.2 (Default Route)",
        tip: "المسار الافتراضي (Default Route: 0.0.0.0 0.0.0.0) يستخدم لإرسال الحزم غير المعروفة للإنترنت."
    },
    {
        cmd: "router ospf <process_id>",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "routing",
        categoryLabel: "التوجيه والمسارات",
        desc: "تفعيل بروتوكول التوجيه الديناميكي المتطور OSPF المعتمد على حالة الوصلات (Link-State).",
        syntax: "Router(config)# router ospf 1",
        example: "Router(config-router)# network 192.168.1.0 0.0.0.255 area 0",
        tip: "يستخدم OSPF قناع التجاهل (Wildcard Mask) وليس قناع الشبكة العادي في سطر الشبكة."
    },
    {
        cmd: "vlan <vlan_id>",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "switching",
        categoryLabel: "السويتشات والـ VLAN",
        desc: "إنشاء شبكة محلية افتراضية (VLAN) جديدة لعزل حركة البيانات وتقسيم نطاق البث (Broadcast).",
        syntax: "Switch(config)# vlan 10",
        example: "Switch(config-vlan)# name Engineering",
        tip: "النطاق الافتراضي لـ VLAN القياسي هو من 1 إلى 1005، و VLAN 1 هو الافتراضي غير القابل للحذف."
    },
    {
        cmd: "switchport mode access",
        mode: "interface",
        modeLabel: "Interface Config (config-if)#",
        category: "switching",
        categoryLabel: "السويتشات والـ VLAN",
        desc: "ضبط منفذ السويتش ليعمل كمنفذ وصول (Access Port) ينقل ترافيك شبكة VLAN واحدة للأجهزة الطرفية.",
        syntax: "Switch(config-if)# switchport mode access",
        example: "Switch(config-if)# switchport access vlan 10",
        tip: "منافذ Access تكون متصلة بالحواسيب والطابعات، بعكس منافذ Trunk المتصلة بالسويتشات."
    },
    {
        cmd: "switchport mode trunk",
        mode: "interface",
        modeLabel: "Interface Config (config-if)#",
        category: "switching",
        categoryLabel: "السويتشات والـ VLAN",
        desc: "ضبط المنفذ ليعمل كخط جذع (Trunk Link) ينقل حزم عدة شبكات VLAN موسومة بمعيار 802.1Q.",
        syntax: "Switch(config-if)# switchport mode trunk",
        example: "Switch(config-if)# switchport trunk allowed vlan 10,20",
        tip: "يستخدم Trunk بين السويتشات أو بين السويتش والموجه في تقنية Router-on-a-Stick."
    },
    {
        cmd: "enable secret <password>",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "security",
        categoryLabel: "الأمان وكلمات المرور",
        desc: "تشفير كلمة المرور الخاصة بوضع الامتيازات باستخدام خوارزمية MD5 أو SHA-256.",
        syntax: "Router(config)# enable secret Cisco@1234",
        example: "Router(config)# enable secret P@ssw0rd2026",
        tip: "تتفوق 'enable secret' على 'enable password' العادية لأنها تخزن الرمز مشفراً ولا تظهر كنص واضح."
    },
    {
        cmd: "service password-encryption",
        mode: "global",
        modeLabel: "Global Config (config)#",
        category: "security",
        categoryLabel: "الأمان وكلمات المرور",
        desc: "تشفير كافة كلمات المرور غير المشفرة في ملف التكوين (مثل خطوط Console و VTY) باستخدام Type 7.",
        syntax: "Router(config)# service password-encryption",
        example: "password 7 0822455D0A16",
        tip: "يمنع المتطفلين من قراءة كلمات المرور بمجرد النظر إلى الشاشة عند تنفيذ show running-config."
    },
    {
        cmd: "copy running-config startup-config",
        mode: "priv",
        modeLabel: "Privileged EXEC (Router#)",
        category: "basic",
        categoryLabel: "التهيئة الأساسية",
        desc: "حفظ الإعدادات الحالية من الذاكرة العشوائية RAM إلى الذاكرة الدائمة غير المتطايرة NVRAM.",
        syntax: "Router# copy running-config startup-config",
        example: "Router# write memory (أو wr)",
        tip: "إذا أعدت تشغيل الجهاز دون حفظ التكوين، فستفقد كافة الإعدادات التي قمت ببرمجتها!"
    },
    {
        cmd: "ping <ip_address>",
        mode: "priv",
        modeLabel: "Privileged EXEC (Router#)",
        category: "troubleshoot",
        categoryLabel: "التشخيص والفحص",
        desc: "إرسال حزم ICMP Echo Request للتحقق من الاتصال بطبقة الشبكة ومعرفة زمن الاستجابة.",
        syntax: "Router# ping 192.168.1.1",
        example: "Sending 5, 100-byte ICMP Echos to 192.168.1.1, timeout is 2 seconds: !!!!!",
        tip: "علامة '!' تعني نجاح الاستجابة، وعلامة '.' تعني انتهاء مهلة الانتظار (Request timed out)."
    }
];

let activeCategory = '';

function renderCommands(list) {
    const container = document.getElementById('commands-container');
    const noResults = document.getElementById('no-results');
    container.innerHTML = '';

    if (list.length === 0) {
        noResults.style.display = 'block';
        return;
    }
    noResults.style.display = 'none';

    list.forEach(item => {
        const card = document.createElement('div');
        card.className = 'academic-card';
        card.style.padding = '22px';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.justifyContent = 'space-between';

        card.innerHTML = `
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 8px;">
                    <span class="badge badge-primary" style="font-size: 0.76rem;">
                        <i class="fa-solid fa-terminal"></i> ${item.modeLabel}
                    </span>
                    <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">
                        ${item.categoryLabel}
                    </span>
                </div>

                <div style="background: #0F172A; color: #38BDF8; font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 12px; direction: ltr; text-align: left; display: flex; justify-content: space-between; align-items: center;">
                    <span>${item.cmd}</span>
                    <button type="button" onclick="copyCommand('${item.cmd}')" title="نسخ الأمر" style="background: rgba(255,255,255,0.1); border: none; color: #FFFFFF; cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>

                <p style="font-size: 0.9rem; color: var(--text-body); line-height: 1.6; margin-bottom: 12px;">
                    ${item.desc}
                </p>

                <div style="background: rgba(244, 246, 251, 0.8); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-xs); padding: 8px 12px; margin-bottom: 14px; font-size: 0.82rem;">
                    <div style="color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">الصيغة المعتمدة (Syntax):</div>
                    <code style="background: none; border: none; color: var(--brand-primary); padding: 0; font-weight: 700;">${item.syntax}</code>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 12px; margin-top: 6px;">
                <span style="font-size: 0.8rem; color: var(--text-dim);" title="${item.tip}">
                    <i class="fa-solid fa-lightbulb" style="color: #F59E0B;"></i> نصيحة CCNA
                </span>

                <div style="display: flex; gap: 6px;">
                    <button type="button" onclick="copyCommand('${item.cmd}')" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 5px 12px;">
                        <i class="fa-solid fa-copy"></i> نسخ
                    </button>
                    <a href="cisco_cli.php" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 5px 12px;">
                        <i class="fa-solid fa-play"></i> تجربة في CLI
                    </a>
                </div>
            </div>
        `;

        container.appendChild(card);
    });
}

function filterCommands() {
    const searchVal = document.getElementById('command-search').value.toLowerCase().trim();
    const modeVal = document.getElementById('mode-filter').value;

    const filtered = commandsList.filter(item => {
        const matchSearch = item.cmd.toLowerCase().includes(searchVal) || 
                            item.desc.toLowerCase().includes(searchVal) || 
                            item.syntax.toLowerCase().includes(searchVal);
        const matchCategory = !activeCategory || item.category === activeCategory;
        const matchMode = !modeVal || item.mode === modeVal;
        return matchSearch && matchCategory && matchMode;
    });

    renderCommands(filtered);
}

function setCategory(cat) {
    activeCategory = cat;
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active', 'btn-primary'));
    if (!cat) {
        document.getElementById('cat-all').classList.add('active');
    } else {
        const btn = document.getElementById('cat-' + cat);
        if (btn) btn.classList.add('active');
    }
    filterCommands();
}

function copyCommand(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`تم نسخ الأمر: "${text}"`);
    }).catch(() => {
        showToast(`تم نسخ الأمر`);
    });
}

function showToast(msg) {
    const toast = document.getElementById('copy-toast');
    document.getElementById('toast-msg').textContent = msg;
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(100px)';
    }, 2000);
}

// عرض القائمة الأولية
renderCommands(commandsList);
</script>

<?php require_once '../includes/footer.php'; ?>
