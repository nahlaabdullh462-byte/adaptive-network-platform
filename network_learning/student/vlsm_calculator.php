<?php
session_start();
$pageTitle = "حاسبة تقسيم الشبكات متغيرة الطول (VLSM Subnet Matrix)";

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
            <i class="fa-solid fa-calculator"></i>
            <span>حاسبة هندسة العنونة المتقدمة (Variable Length Subnet Masking - VLSM)</span>
        </div>
        <h1 style="font-size: 1.85rem; color: var(--text-heading); margin-bottom: 6px;">
            حاسبة VLSM الذكية وتقسيم الشبكات بلا هدر (Zero-Waste Slicer)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.94rem; margin-bottom: 0;">
            أدخل الشبكة الأم ومتطلبات الأقسام لحساب التوزيع الأمثل تصاعدياً وفق معايير سيسكو، مع خريطة بصرية للذاكرة.
        </p>
    </div>

    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="loadSampleScenario()" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-wand-magic-sparkles"></i> تحميل سيناريو جامعي جاهز
        </button>
        <a href="subnet_simulator.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-network-wired"></i> مختبر Subnetting الثابت
        </a>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 24px;">
    
    <!-- مدخلات الشبكة الأم والأقسام -->
    <div class="academic-card" style="padding: 24px;">
        <div style="font-weight: 700; color: var(--text-heading); font-size: 1.05rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-sliders" style="color: var(--brand-primary);"></i>
            <span>1. معايير الشبكة الرئيسية (Major Network):</span>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 20px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>عنوان الشبكة الأم (Major IP):</label>
                <input type="text" id="major-ip" class="form-control" value="192.168.10.0" style="direction: ltr; font-family: var(--font-mono); font-weight: 700;">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label>القناع الأساسي (CIDR):</label>
                <select id="major-mask" class="form-control" style="font-family: var(--font-mono); font-weight: 700;">
                    <option value="16">/16 (65,536 عنوان)</option>
                    <option value="20">/20 (4,096 عنوان)</option>
                    <option value="22">/22 (1,024 عنوان)</option>
                    <option value="24" selected>/24 (256 عنوان)</option>
                    <option value="25">/25 (128 عنوان)</option>
                    <option value="26">/26 (64 عنوان)</option>
                </select>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
            <div style="font-weight: 700; color: var(--text-heading); font-size: 0.95rem;">
                <i class="fa-solid fa-users-rectangle" style="color: #0D9488;"></i> متطلبات الأقسام (Subnets / Departments):
            </div>
            <button type="button" onclick="addDepartmentRow()" class="btn btn-secondary btn-sm" style="font-size: 0.8rem;">
                <i class="fa-solid fa-plus"></i> إضافة قسم
            </button>
        </div>

        <div id="departments-container" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; max-height: 280px; overflow-y: auto; padding-left: 4px;">
            <!-- صفوف الأقسام -->
        </div>

        <button type="button" onclick="calculateVLSM()" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 700;">
            <i class="fa-solid fa-calculator"></i> حساب وتوليد مصفوفة الـ VLSM
        </button>
    </div>

    <!-- بطاقة الملخص الإحصائي والخريطة البصرية للذاكرة -->
    <div class="academic-card" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-weight: 700; color: var(--text-heading); font-size: 1.05rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chart-pie" style="color: var(--brand-primary);"></i>
                <span>2. المخطط البياني لتوزيع عناوين الذاكرة (Memory Map):</span>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.84rem; margin-bottom: 6px; font-weight: 600;">
                    <span style="color: var(--text-heading);">نسبة العناوين المحجوزة:</span>
                    <span id="allocation-pct" style="color: var(--brand-primary); font-family: var(--font-mono);">0%</span>
                </div>
                
                <!-- شريط الذاكرة البصري المقسم -->
                <div id="memory-bar" style="height: 38px; background: #E2E8F0; border-radius: var(--radius-sm); display: flex; overflow: hidden; border: 1.5px solid var(--border-main); box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="width: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                        اضغط "حساب مصفوفة الـ VLSM" لتوليد الخريطة
                    </div>
                </div>
            </div>

            <div class="grid-2" style="gap: 12px; margin-bottom: 16px;">
                <div style="background: rgba(244, 246, 251, 0.85); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-sm); padding: 12px;">
                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">إجمالي عناوين الشبكة الأم:</div>
                    <div id="stat-total-ips" style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--text-heading);">256</div>
                </div>

                <div style="background: rgba(244, 246, 251, 0.85); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-sm); padding: 12px;">
                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">العناوين المخصصة للأقسام:</div>
                    <div id="stat-used-ips" style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: var(--brand-primary);">0</div>
                </div>

                <div style="background: rgba(244, 246, 251, 0.85); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-sm); padding: 12px;">
                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">العناوين الحرة المتبقية للتوسع:</div>
                    <div id="stat-free-ips" style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: #10B981;">256</div>
                </div>

                <div style="background: rgba(244, 246, 251, 0.85); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: var(--radius-sm); padding: 12px;">
                    <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-bottom: 2px;">كفاءة استغلال العناوين:</div>
                    <div id="stat-efficiency" style="font-size: 1.3rem; font-weight: 800; font-family: var(--font-mono); color: #D97706;">100%</div>
                </div>
            </div>
        </div>

        <div style="background: #F0FDF4; border: 1px solid #A7F3D0; border-radius: var(--radius-sm); padding: 12px 16px; font-size: 0.85rem; color: #065F46;">
            <i class="fa-solid fa-circle-check"></i> الخوارزمية ترتب الأقسام تنازلياً بحسب عدد الأجهزة لمنع تشتت الذاكرة (Fragmentation Free).
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- جدول نتائج العنونة والـ VLSM (VLSM Results Matrix Table)                   -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div style="font-weight: 700; color: var(--text-heading); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-table" style="color: var(--brand-primary);"></i>
            <span>3. جدول مصفوفة العنونة التفصيلية (VLSM Allocation Table):</span>
        </div>
        
        <button type="button" onclick="copyTableText()" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-copy"></i> نسخ بيانات الجدول
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table class="academic-table" id="vlsm-table" style="margin: 0; font-family: var(--font-mono); font-size: 0.85rem;">
            <thead>
                <tr>
                    <th style="font-family: var(--font-sans); text-align: right;">اسم القسم / الشبكة</th>
                    <th style="text-align: center;">الأجهزة المطلوبة</th>
                    <th style="text-align: center;">الأجهزة المخصصة</th>
                    <th style="text-align: left;">عنوان الشبكة (Network ID)</th>
                    <th style="text-align: left;">نطاق الأجهزة الصالحة (Usable Range)</th>
                    <th style="text-align: left;">عنوان البث (Broadcast)</th>
                    <th style="text-align: center;">قناع الشبكة (Subnet Mask)</th>
                    <th style="text-align: center;">اللاحقة CIDR</th>
                </tr>
            </thead>
            <tbody id="vlsm-tbody">
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 25px; font-family: var(--font-sans);">
                        قم بإدخال متطلبات الأقسام ثم اضغط "حساب وتوليد مصفوفة الـ VLSM"
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
let departmentRows = [
    { name: "قسم التطوير البرمجي (IT & Dev)", hosts: 60 },
    { name: "قسم المبيعات والتسويق (Sales)", hosts: 28 },
    { name: "قسم الإدارة المالية (Finance)", hosts: 12 },
    { name: "رابط WAN بين الراوترات (Router Link)", hosts: 2 }
];

const barColors = ['#4F46E5', '#0EA5E9', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4'];

function renderDepartmentInputs() {
    const container = document.getElementById('departments-container');
    container.innerHTML = '';

    departmentRows.forEach((dept, index) => {
        const row = document.createElement('div');
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '2fr 1.2fr 40px';
        row.style.gap = '8px';
        row.style.alignItems = 'center';

        row.innerHTML = `
            <input type="text" value="${escapeHtml(dept.name)}" oninput="updateDeptName(${index}, this.value)" class="form-control" placeholder="اسم القسم" style="font-size: 0.88rem;">
            <div style="position: relative;">
                <input type="number" min="1" max="16000" value="${dept.hosts}" oninput="updateDeptHosts(${index}, this.value)" class="form-control" placeholder="الأجهزة" style="font-size: 0.88rem; direction: ltr; font-family: var(--font-mono); font-weight: 700; padding-left: 8px;">
            </div>
            <button type="button" onclick="removeDepartmentRow(${index})" style="background: none; border: none; color: #E11D48; cursor: pointer; font-size: 1rem; padding: 6px;" title="حذف القسم">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(row);
    });
}

function updateDeptName(idx, val) {
    departmentRows[idx].name = val;
}

function updateDeptHosts(idx, val) {
    departmentRows[idx].hosts = parseInt(val) || 1;
}

function addDepartmentRow() {
    departmentRows.push({ name: `قسم جديد #${departmentRows.length + 1}`, hosts: 10 });
    renderDepartmentInputs();
}

function removeDepartmentRow(idx) {
    if (departmentRows.length <= 1) {
        alert("يجب إبقاء قسم واحد على الأقل!");
        return;
    }
    departmentRows.splice(idx, 1);
    renderDepartmentInputs();
}

function loadSampleScenario() {
    document.getElementById('major-ip').value = "172.16.0.0";
    document.getElementById('major-mask').value = "20";
    departmentRows = [
        { name: "كلية الهندسة والحاسوب (Labs)", hosts: 1000 },
        { name: "كلية الطب والعلوم الطبية", hosts: 480 },
        { name: "مبنى الإدارة وشؤون الطلاب", hosts: 200 },
        { name: "مختبر الأمن السيبراني", hosts: 55 },
        { name: "رابط WAN الأساسي مع السيرفرات", hosts: 2 }
    ];
    renderDepartmentInputs();
    calculateVLSM();
}

function ipToLong(ip) {
    return ip.split('.').reduce((acc, octet) => (acc << 8) + parseInt(octet, 10), 0) >>> 0;
}

function longToIp(long) {
    return [
        (long >>> 24) & 255,
        (long >>> 16) & 255,
        (long >>> 8) & 255,
        long & 255
    ].join('.');
}

function cidrToMask(cidr) {
    let mask = 0;
    for (let i = 0; i < cidr; i++) {
        mask += (1 << (31 - i)) >>> 0;
    }
    return longToIp(mask);
}

function calculateVLSM() {
    const majorIp = document.getElementById('major-ip').value.trim();
    const majorCidr = parseInt(document.getElementById('major-mask').value);

    const totalAvailableIps = Math.pow(2, 32 - majorCidr);
    let startLong = ipToLong(majorIp);

    // ترتيب الأقسام تنازلياً حسب عدد الأجهزة المطلوبة (قاعدة VLSM الذهبية)
    const sortedDepts = [...departmentRows].sort((a, b) => b.hosts - a.hosts);

    let currentIpLong = startLong;
    let totalUsedIps = 0;
    const results = [];

    for (let dept of sortedDepts) {
        const needed = dept.hosts;
        // حساب حجم الكتلة المطلوب: 2^h بحيث 2^h - 2 >= needed
        let hostBits = 1;
        while (Math.pow(2, hostBits) - 2 < needed) {
            hostBits++;
        }
        const blockSize = Math.pow(2, hostBits);
        const subCidr = 32 - hostBits;

        // التحقق من محاذاة العنوان (Alignment)
        if (currentIpLong % blockSize !== 0) {
            currentIpLong = (Math.floor(currentIpLong / blockSize) + 1) * blockSize;
        }

        const netId = currentIpLong;
        const firstIp = netId + 1;
        const lastIp = netId + blockSize - 2;
        const broadcast = netId + blockSize - 1;

        results.push({
            name: dept.name,
            needed: needed,
            allocated: blockSize - 2,
            blockSize: blockSize,
            netId: longToIp(netId),
            firstIp: longToIp(firstIp),
            lastIp: longToIp(lastIp),
            broadcast: longToIp(broadcast),
            mask: cidrToMask(subCidr),
            cidr: '/' + subCidr
        });

        currentIpLong += blockSize;
        totalUsedIps += blockSize;
    }

    // عرض الإحصائيات
    document.getElementById('stat-total-ips').textContent = totalAvailableIps.toLocaleString();
    document.getElementById('stat-used-ips').textContent = totalUsedIps.toLocaleString();
    const freeIps = Math.max(0, totalAvailableIps - totalUsedIps);
    document.getElementById('stat-free-ips').textContent = freeIps.toLocaleString();

    const pct = Math.min(100, Math.round((totalUsedIps / totalAvailableIps) * 100));
    document.getElementById('allocation-pct').textContent = pct + '%';

    // رسم شريط الذاكرة البصري
    const bar = document.getElementById('memory-bar');
    bar.innerHTML = '';
    results.forEach((res, idx) => {
        const segPct = (res.blockSize / totalAvailableIps) * 100;
        const seg = document.createElement('div');
        seg.style.width = Math.max(2, segPct) + '%';
        seg.style.backgroundColor = barColors[idx % barColors.length];
        seg.style.height = '100%';
        seg.style.transition = 'width 0.3s ease';
        seg.title = `${res.name}: ${res.blockSize} عنوان (${segPct.toFixed(1)}%)`;
        bar.appendChild(seg);
    });

    if (freeIps > 0) {
        const freePct = (freeIps / totalAvailableIps) * 100;
        const freeSeg = document.createElement('div');
        freeSeg.style.width = freePct + '%';
        freeSeg.style.backgroundColor = '#CBD5E1';
        freeSeg.style.height = '100%';
        freeSeg.title = `مساحة حرة: ${freeIps} عنوان (${freePct.toFixed(1)}%)`;
        bar.appendChild(freeSeg);
    }

    // تعبئة الجدول
    const tbody = document.getElementById('vlsm-tbody');
    tbody.innerHTML = '';

    results.forEach((r, idx) => {
        const tr = document.createElement('tr');
        const color = barColors[idx % barColors.length];

        tr.innerHTML = `
            <td style="font-family: var(--font-sans); text-align: right; font-weight: 700; color: var(--text-heading);">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: ${color}; margin-left: 6px;"></span>
                ${escapeHtml(r.name)}
            </td>
            <td style="text-align: center; color: var(--text-muted);">${r.needed}</td>
            <td style="text-align: center; font-weight: 700; color: var(--brand-primary);">${r.allocated}</td>
            <td style="text-align: left; font-weight: 700;">${r.netId}</td>
            <td style="text-align: left; color: #0D9488;">${r.firstIp} - ${r.lastIp}</td>
            <td style="text-align: left; color: #D97706;">${r.broadcast}</td>
            <td style="text-align: center;">${r.mask}</td>
            <td style="text-align: center; font-weight: 800; color: var(--brand-primary);">${r.cidr}</td>
        `;
        tbody.appendChild(tr);
    });
}

function copyTableText() {
    const table = document.getElementById('vlsm-table');
    let text = "نتائج تقسيم الشبكات متغيرة الطول VLSM - NetLearn Pro\n";
    text += "القسم\tالأجهزة المطلوبة\tالأجهزة المخصصة\tعنوان الشبكة\tالنطاق الصالح\tعنوان البث\tالقناع\tCIDR\n";

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(tr => {
        const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
        text += cols.join('\t') + '\n';
    });

    navigator.clipboard.writeText(text).then(() => {
        alert("تم نسخ بيانات جدول الـ VLSM بالكامل بنجاح!");
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// تهيئة البداية
renderDepartmentInputs();
calculateVLSM();
</script>

<?php require_once '../includes/footer.php'; ?>
