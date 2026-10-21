<?php
$pageTitle = "المختبر التفاعلي لحساب وتجزئة الشبكات";
require_once '../config/security.php';
require_auth('student');
require_once '../includes/header.php';
?>

<div style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <span class="badge badge-secondary" style="margin-bottom: 6px;">
                <i class="fa-solid fa-flask"></i> المختبر الهندسي المتقدم (CIDR & Subnetting Lab)
            </span>
            <h1 style="font-size: 1.7rem; color: var(--text-heading); margin-bottom: 4px;">
                محاكي وتحليل عناوين الـ IPv4 وتقسيم الشبكات الفرعية
            </h1>
            <p style="font-size: 0.92rem; color: var(--text-muted); margin: 0;">
                أداة تفاعلية مستوحاة من معايير Cidr.xyz لتمثيل الـ 32 بت بصرياً، وحساب أقنعة الشبكة والـ Wildcard واختبار الحالات المعيارية (/30، /31، /32).
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-house"></i> لوحة التحكم
            </a>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- شريط المدخلات الهندسي الفاخر (IPv4 & CIDR Control Bar)                     -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 25px; border-top: 4px solid var(--brand-primary); padding: 28px;">
    <div class="grid-2" style="align-items: center; margin-bottom: 20px;">
        <div>
            <label style="display: block; font-weight: 700; margin-bottom: 8px; color: var(--text-heading); font-size: 0.92rem;">
                <i class="fa-solid fa-network-wired" style="color: var(--brand-primary); margin-left: 6px;"></i>
                عنوان الـ IPv4 (IPv4 Address):
            </label>
            <input type="text" id="calc-ip" class="form-control" value="192.168.10.65" placeholder="مثال: 192.168.1.1" style="direction: ltr; font-family: var(--font-mono); font-size: 1.15rem; font-weight: 800; color: var(--brand-primary); letter-spacing: 0.05em;">
        </div>

        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label style="margin: 0; font-weight: 700; color: var(--text-heading); font-size: 0.92rem;">
                    بادئة الشبكة (CIDR Prefix):
                </label>
                <span class="code-font" style="color: var(--brand-primary); font-weight: 800; font-size: 1.25rem;">/<span id="cidr-display">26</span></span>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <input type="range" id="calc-cidr-range" min="0" max="32" value="26" style="flex: 1; accent-color: var(--brand-primary); cursor: pointer; height: 6px;">
                <input type="number" id="calc-cidr" min="0" max="32" value="26" class="form-control" style="width: 75px; text-align: center; font-family: var(--font-mono); font-weight: 800;">
            </div>
        </div>
    </div>

    <!-- أزرار الاختصارات السريعة للأقنعة الشائعة والحالات الخاصة -->
    <div style="border-top: 1px solid var(--border-subtle); padding-top: 16px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 700; margin-left: 6px;">اختصارات سريعة:</span>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(24)" style="font-family: var(--font-mono);">/24 (Class C)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(26)" style="font-family: var(--font-mono);">/26 (Subnet 64)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(30)" style="font-family: var(--font-mono);">/30 (P2P Link)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(31)" style="font-family: var(--font-mono); border-color: var(--brand-secondary-border); color: var(--brand-secondary);" title="RFC 3021 Point-to-Point">/31 (RFC 3021)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(32)" style="font-family: var(--font-mono); border-color: var(--brand-primary-border); color: var(--brand-primary);" title="Host Route">/32 (Host Route)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(16)" style="font-family: var(--font-mono);">/16 (Class B)</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setPresetCidr(8)" style="font-family: var(--font-mono);">/8 (Class A)</button>
    </div>

    <div id="calc-error" class="academic-alert alert-danger" style="display: none; margin-top: 16px; margin-bottom: 0;">
        <i class="fa-solid fa-triangle-exclamation"></i> يرجى التأكد من كتابة عنوان IPv4 صحيح (مثلاً: 10.0.0.1 أو 192.168.1.50).
    </div>

    <div id="calc-note" class="academic-alert alert-info" style="display: none; margin-top: 16px; margin-bottom: 0;"></div>
</div>

<!-- ========================================================================= -->
<!-- تمثيل الـ 32 بت الثنائي البصري (Binary Breakdown Matrix)                   -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 25px; padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h3 style="font-size: 1.1rem; color: var(--text-heading); margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-binary" style="color: var(--brand-primary);"></i>
            تمثيل الـ 32 بت الثنائي (Binary Octets Breakdown)
        </h3>
        <div style="display: flex; align-items: center; gap: 16px; font-size: 0.84rem; font-weight: 700;">
            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--brand-primary);">
                <span style="width: 10px; height: 10px; border-radius: 2px; background: var(--brand-primary); display: inline-block;"></span>
                بتات الشبكة Network Bits (1)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted);">
                <span style="width: 10px; height: 10px; border-radius: 2px; background: #CBD5E1; display: inline-block;"></span>
                بتات المضيف Host Bits (0)
            </span>
        </div>
    </div>

    <div id="binary-bits-container" class="subnet-bit-row"></div>
</div>

<!-- ========================================================================= -->
<!-- شبكة النتائج الهندسية المستخرجة (Calculated Metrics Matrix)                -->
<!-- ========================================================================= -->
<div class="grid-2" style="margin-bottom: 30px;">
    
    <div class="academic-card">
        <h3 style="font-size: 1.15rem; color: var(--brand-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-column"></i> العناوين والأقنعة المعمارية
        </h3>

        <div class="calc-result-grid">
            <div class="calc-metric-box">
                <div class="calc-metric-label">عنوان الشبكة (Network ID):</div>
                <div id="res-network" class="calc-metric-value" style="color: var(--brand-primary);">192.168.10.64</div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">عنوان البث العام (Broadcast):</div>
                <div id="res-broadcast" class="calc-metric-value" style="color: var(--status-danger-text);">192.168.10.127</div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">قناع الشبكة (Subnet Mask):</div>
                <div id="res-mask" class="calc-metric-value">255.255.255.192</div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">قناع البدل (Wildcard Mask):</div>
                <div id="res-wildcard" class="calc-metric-value" style="color: var(--accent-amber);">0.0.0.63</div>
            </div>
        </div>
    </div>

    <div class="academic-card">
        <h3 style="font-size: 1.15rem; color: var(--brand-secondary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-server"></i> طاقة النطاق وتصنيف الأجهزة
        </h3>

        <div class="calc-result-grid">
            <div class="calc-metric-box" style="grid-column: span 2;">
                <div class="calc-metric-label">نطاق الأجهزة الصالحة للاستخدام (Usable Host Range):</div>
                <div id="res-range" class="calc-metric-value" style="color: var(--status-success-text); font-size: 1.05rem;">
                    192.168.10.65 - 192.168.10.126
                </div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">الأجهزة المتاحة (Usable Hosts):</div>
                <div id="res-usable" class="calc-metric-value" style="color: var(--brand-secondary);">62</div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">إجمالي العناوين (Total Addresses):</div>
                <div id="res-total" class="calc-metric-value">64</div>
            </div>

            <div class="calc-metric-box" style="grid-column: span 2;">
                <div class="calc-metric-label">تصنيف العنوان ونوعه الهندسي:</div>
                <div id="res-type" style="font-weight: 700; color: var(--text-heading); font-size: 0.94rem; margin-top: 4px;">
                    Class C | عنوان خاص محلي (Private RFC 1918)
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- جدول تجزئة الشبكات الفرعية (Subnet Division Matrix)                        -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h3 style="font-size: 1.15rem; color: var(--text-heading); margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-table-cells" style="color: var(--brand-primary);"></i>
            جدول تجزئة الشبكات الفرعية الناتجة (Subnet Division Matrix)
        </h3>
        <span class="badge badge-primary">حساب القفزة الهندسية التلقائي</span>
    </div>
    <div id="subnet-division-container"></div>
</div>

<!-- ========================================================================= -->
<!-- وضع التحدي التفاعلي وتوليد المسائل (Interactive Challenge Generator)       -->
<!-- ========================================================================= -->
<div class="academic-card" style="border-top: 4px solid var(--brand-secondary);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 18px;">
        <div>
            <h3 style="margin: 0; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bullseye" style="color: var(--brand-secondary);"></i>
                وضع التحدي التفاعلي وتوليد المسائل العشوائية
            </h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 4px 0 0;">
                توليد مسائل شبكية متعددة الأنواع مع تصحيح لحظي وشرح هندسي مفصل وتوثيق المحاولة.
            </p>
        </div>
        <button type="button" onclick="generateSubnetChallenge()" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrows-rotate"></i> مسألة تدريبية جديدة
        </button>
    </div>
    
    <div style="background: var(--brand-primary-light); padding: 24px; border-radius: var(--radius-md); border: 1.5px solid var(--brand-primary-border); margin-bottom: 20px;">
        <div id="challenge-question" style="font-size: 1.12rem; font-weight: 700; color: var(--text-heading); margin-bottom: 18px; line-height: 1.6;">
            جارٍ تجهيز المسألة...
        </div>

        <div style="display: flex; gap: 12px; max-width: 540px; flex-wrap: wrap;">
            <input type="text" id="challenge-user-answer" class="form-control" placeholder="اكتب إجابتك هنا (مثال: 192.168.10.0)" style="direction: ltr; font-family: var(--font-mono); font-size: 1rem; flex: 1; min-width: 220px; font-weight: 700;">
            <button type="button" onclick="verifyChallengeAnswer()" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fa-solid fa-circle-check"></i> تحقق من الإجابة
            </button>
        </div>
    </div>

    <div id="challenge-feedback" style="display: none;"></div>
</div>

<script>
    function setPresetCidr(val) {
        const cidrInput = document.getElementById('calc-cidr');
        const cidrRange = document.getElementById('calc-cidr-range');
        if (cidrInput && cidrRange) {
            cidrInput.value = val;
            cidrRange.value = val;
            updateCalculatorUI();
        }
    }
</script>
<script src="../assets/js/subnet_calc.js"></script>

<?php require_once '../includes/footer.php'; ?>
