<?php
session_start();
$pageTitle = "الرئيسية - منصة التعلم التكيفي لهندسة وإدارة الشبكات";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}

require_once 'includes/header.php';
?>

<style>
/* أنماط خاصة بالصفحة الرئيسية فقط */
.hero-main {
    position: relative;
    padding: 55px 0 70px;
    overflow: hidden;
    min-height: 90vh;
    display: flex;
    align-items: center;
}
.hero-main .hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}
.hero-main .hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.15;
    filter: blur(2px) saturate(1.4);
}
.hero-main .hero-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(232,236,244,0.3) 0%, rgba(232,236,244,0.88) 55%, var(--bg-body) 100%);
}
.hero-inner {
    position: relative;
    z-index: 3;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: center;
}
.hero-text { max-width: 580px; }
.hero-visual {
    position: relative;
}
.hero-visual img {
    width: 100%;
    border-radius: var(--radius-xl);
    box-shadow: 0 30px 80px -16px rgba(26,26,62,0.25), 0 0 50px rgba(91,79,232,0.08);
    border: 2px solid rgba(91,79,232,0.12);
    animation: floatSlow 6s ease-in-out infinite;
}
.hero-float-badge {
    position: absolute;
    bottom: -18px;
    left: 20px;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(16px);
    padding: 14px 22px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card-hover);
    border: 1px solid rgba(208,213,229,0.5);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: floatSlow 5s ease-in-out infinite;
    animation-delay: 1s;
}
@media (max-width: 900px) {
    .hero-inner { grid-template-columns: 1fr; text-align: center; }
    .hero-text { max-width: 100%; }
    .hero-main { min-height: auto; padding: 40px 0 50px; }
}

/* قسم المحاكي مع صورة */
.terminal-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: center;
    margin-bottom: 70px;
}
.terminal-section .terminal-image img {
    width: 100%;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-float);
    border: 2px solid rgba(91,79,232,0.12);
}
@media (max-width: 900px) {
    .terminal-section { grid-template-columns: 1fr; }
}

/* قسم المسار الأكاديمي */
.curriculum-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
    margin-bottom: 70px;
}
@media (max-width: 900px) {
    .curriculum-section { grid-template-columns: 1fr; }
}

/* قسم لوحة التحكم */
.preview-section {
    margin-bottom: 70px;
    text-align: center;
}
.preview-section img {
    max-width: 950px;
    width: 100%;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-float);
    border: 2px solid rgba(91,79,232,0.1);
    margin-top: 30px;
}
</style>

<!-- ===== HERO SECTION مع صورتين مختلفتين ===== -->
<section class="hero-main">
    <div class="hero-bg">
        <img src="assets/img/hero-network.jpg" alt="">
    </div>
    <canvas id="hero-constellation-canvas" style="position:absolute;inset:0;z-index:1;pointer-events:auto;opacity:0.4;"></canvas>
    
    <div class="hero-inner">
        <!-- النص -->
        <div class="hero-text" style="animation: slideUpFade 0.8s ease forwards;">
            <div style="display:inline-flex;align-items:center;gap:10px;background:rgba(91,79,232,0.08);backdrop-filter:blur(12px);border:1px solid rgba(197,194,247,0.5);padding:7px 20px;border-radius:var(--radius-full);font-size:0.84rem;font-weight:700;color:var(--brand-primary);margin-bottom:24px;">
                <span style="width:8px;height:8px;border-radius:50%;background:#10B981;display:inline-block;box-shadow:0 0 8px #10B981;animation:blink 2s ease-in-out infinite;"></span>
                <i class="fa-solid fa-brain"></i>
                منصة تعلم تكيفي ذكي | Cisco CCNA
            </div>

            <h1 style="font-size:2.8rem;line-height:1.3;margin-bottom:20px;font-weight:900;letter-spacing:-0.02em;">
                منصة التعلم التكيفي الذكي للشبكات<br>
                <span style="background:var(--brand-gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-size:200% auto;animation:bgShift 4s ease-in-out infinite;">
                    المسار التدريبي الشامل | Cisco CCNA
                </span>
            </h1>

            <p style="font-size:1.1rem;color:var(--text-body);margin-bottom:32px;line-height:1.85;">
                بيئة تعليمية سحابية تجمع بين التقييم الذكي، ومحاكي Cisco IOS، ومحلل حزم Wireshark، وحاسبة VLSM، ومحلل طبقات OSI — كل ذلك مباشرة من متصفحك.
            </p>

            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                <a href="register.php" class="btn btn-primary btn-lg" style="font-weight:800;padding:15px 34px;font-size:1.05rem;">
                    <i class="fa-solid fa-rocket"></i> انضم وابدأ المسار
                </a>
                <a href="login.php" class="btn btn-secondary btn-lg" style="font-weight:700;padding:15px 28px;font-size:1rem;">
                    <i class="fa-solid fa-key"></i> تسجيل الدخول
                </a>
            </div>
        </div>

        <!-- صورة مختلفة: لقطة لوحة التحكم -->
        <div class="hero-visual" style="animation: slideUpFade 1s ease forwards; animation-delay: 0.2s; opacity: 0;">
            <img src="assets/img/dashboard-preview.jpg" alt="لوحة تحكم NetLearn Pro">
            <div class="hero-float-badge">
                <div style="width:42px;height:42px;border-radius:var(--radius-sm);background:var(--brand-gradient);display:flex;align-items:center;justify-content:center;color:#FFF;font-size:18px;">
                    <i class="fa-solid fa-flask"></i>
                </div>
                <div>
                    <div style="font-weight:800;color:var(--text-heading);font-size:1.15rem;font-family:var(--font-mono);">7 مختبرات</div>
                    <div style="font-size:0.76rem;color:var(--text-muted);font-weight:600;">تفاعلية حية بالمتصفح</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== شريط التقنيات المتحرك ===== -->
<div class="feature-marquee">
    <div class="marquee-track">
        <div class="marquee-item"><i class="fa-solid fa-terminal" style="color:var(--brand-primary);"></i> محاكي Cisco CLI</div>
        <div class="marquee-item"><i class="fa-solid fa-microchip" style="color:#06B6D4;"></i> محلل حزم Wireshark</div>
        <div class="marquee-item"><i class="fa-solid fa-network-wired" style="color:#10B981;"></i> مختبر Subnetting</div>
        <div class="marquee-item"><i class="fa-solid fa-calculator" style="color:#8B5CF6;"></i> حاسبة VLSM</div>
        <div class="marquee-item"><i class="fa-solid fa-layer-group" style="color:#F59E0B;"></i> محلل OSI</div>
        <div class="marquee-item"><i class="fa-solid fa-diagram-project" style="color:#F43F5E;"></i> طوبولوجيا الشبكات</div>
        <div class="marquee-item"><i class="fa-solid fa-book-bookmark" style="color:#0D9488;"></i> موسوعة أوامر سيسكو</div>
        <div class="marquee-item"><i class="fa-solid fa-terminal" style="color:var(--brand-primary);"></i> محاكي Cisco CLI</div>
        <div class="marquee-item"><i class="fa-solid fa-microchip" style="color:#06B6D4;"></i> محلل حزم Wireshark</div>
        <div class="marquee-item"><i class="fa-solid fa-network-wired" style="color:#10B981;"></i> مختبر Subnetting</div>
        <div class="marquee-item"><i class="fa-solid fa-calculator" style="color:#8B5CF6;"></i> حاسبة VLSM</div>
        <div class="marquee-item"><i class="fa-solid fa-layer-group" style="color:#F59E0B;"></i> محلل OSI</div>
        <div class="marquee-item"><i class="fa-solid fa-diagram-project" style="color:#F43F5E;"></i> طوبولوجيا الشبكات</div>
        <div class="marquee-item"><i class="fa-solid fa-book-bookmark" style="color:#0D9488;"></i> موسوعة أوامر سيسكو</div>
    </div>
</div>

<!-- ===== إحصائيات ===== -->
<div class="stats-ribbon">
    <div class="stat-block">
        <div class="stat-number">4</div>
        <div class="stat-label">مستويات أكاديمية متدرجة</div>
    </div>
    <div class="stat-block">
        <div class="stat-number">68+</div>
        <div class="stat-label">سؤال تقييم بشروح CCNA</div>
    </div>
    <div class="stat-block">
        <div class="stat-number">38+</div>
        <div class="stat-label">مصدر تعليمي حقيقي</div>
    </div>
    <div class="stat-block">
        <div class="stat-number">7</div>
        <div class="stat-label">مختبرات تفاعلية حية</div>
    </div>
</div>

<!-- ===== قسم المحاكي مع صورة سيسكو ===== -->
<div class="terminal-section" id="terminal">
    <!-- صورة سيسكو -->
    <div class="terminal-image" style="order: 2;">
        <img src="assets/img/cisco-terminal.jpg" alt="محاكي أوامر سيسكو الحي">
    </div>

    <!-- الطرفية التفاعلية -->
    <div style="order: 1;">
        <span class="badge badge-primary" style="margin-bottom:12px;font-size:0.82rem;padding:6px 16px;">
            <i class="fa-solid fa-bolt"></i> تجربة حية مباشرة
        </span>
        <h2 style="font-size:1.9rem;color:var(--text-heading);font-weight:800;margin-bottom:12px;">
            جرّب المحاكي قبل التسجيل
        </h2>
        <p style="color:var(--text-muted);font-size:0.95rem;margin-bottom:24px;line-height:1.7;">
            اضغط الأزرار أدناه لتشغيل أوامر Cisco حقيقية ومشاهدة النتائج فوراً — بدون تسجيل حساب.
        </p>

        <div class="cisco-terminal" style="border:1.5px solid rgba(91,79,232,0.25);">
            <div class="cisco-terminal-header">
                <div class="cisco-terminal-dots">
                    <span style="background:#EF4444;"></span>
                    <span style="background:#F59E0B;"></span>
                    <span style="background:#10B981;"></span>
                </div>
                <div style="font-family:var(--font-mono);font-size:0.8rem;color:#64748B;">
                    <i class="fa-solid fa-terminal" style="color:#06B6D4;margin-left:6px;"></i>
                    Cisco Catalyst 2960 — Live
                </div>
                <div style="display:flex;gap:7px;">
                    <button type="button" onclick="heroRunCommand('ping')" class="btn btn-cyan btn-sm" style="font-size:0.76rem;padding:4px 11px;border-radius:8px;">
                        <i class="fa-solid fa-play"></i> Ping
                    </button>
                    <button type="button" onclick="heroRunCommand('route')" class="btn btn-sm" style="font-size:0.76rem;padding:4px 11px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#A5B4FC !important;">
                        <i class="fa-solid fa-route"></i> Route
                    </button>
                    <button type="button" onclick="heroRunCommand('int')" class="btn btn-sm" style="font-size:0.76rem;padding:4px 11px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#A5B4FC !important;">
                        <i class="fa-solid fa-list"></i> Int
                    </button>
                </div>
            </div>
            <div id="hero-terminal-screen" class="cisco-terminal-body" style="height:230px;font-size:0.86rem;">
                <div style="color:#64748B;margin-bottom:8px;">
                    Cisco IOS Software, C2960 Software, Version 15.2(2)E<br>
                    Press buttons above to simulate Cisco commands!
                </div>
                <div id="hero-terminal-output" style="color:#38BDF8;">
                    Switch# <span style="display:inline-block;width:8px;height:14px;background:#38BDF8;vertical-align:middle;animation:blink 1s step-end infinite;"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== المختبرات مع صورة بانورامية (صورة labs-showcase) ===== -->
<section style="margin-bottom:70px;" id="features">
    <div class="labs-showcase-card" style="margin-bottom:30px;box-shadow:var(--shadow-float);">
        <img src="assets/img/labs-showcase.jpg" alt="مختبرات NetLearn Pro التفاعلية" style="height:280px;">
        <div class="labs-showcase-overlay">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);backdrop-filter:blur(10px);padding:5px 16px;border-radius:var(--radius-full);font-size:0.8rem;font-weight:700;margin-bottom:12px;border:1px solid rgba(255,255,255,0.15);">
                <i class="fa-solid fa-microchip"></i> بيئة التطبيق العملي والمحاكاة الحية
            </div>
            <h2 style="font-size:1.8rem;color:#FFF;font-weight:800;margin-bottom:8px;">
                7 مختبرات تفاعلية احترافية داخل المتصفح
            </h2>
            <p style="color:rgba(255,255,255,0.8);font-size:0.92rem;margin:0;max-width:600px;">
                كل أداة تعمل مباشرة داخل المتصفح — بدون تحميل، بدون إعدادات
            </p>
        </div>
    </div>

    <div class="grid-3" style="gap:22px;">
        <div class="feature-glow-card" style="border-top:3px solid var(--brand-primary);">
            <div class="feature-glow-icon" style="background:rgba(91,79,232,0.1);color:var(--brand-primary);"><i class="fa-solid fa-terminal"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">محاكي أوامر سيسكو (Cisco CLI)</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">محاكاة حقيقية لأوامر Cisco IOS مع دعم الأوضاع المختلفة وتحديات إعداد المنافذ.</p>
            <span class="badge badge-primary"><i class="fa-solid fa-check"></i> تفاعلي 100%</span>
        </div>

        <div class="feature-glow-card" style="border-top:3px solid #06B6D4;">
            <div class="feature-glow-icon" style="background:rgba(6,182,212,0.1);color:#06B6D4;"><i class="fa-solid fa-microchip"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">محلل الحزم (Wireshark Lab)</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">التقاط وتفكيك حزم HTTP و DNS و TCP 3-Way Handshake مع فحص Hex Dump.</p>
            <span class="badge badge-cyan"><i class="fa-solid fa-check"></i> فحص عميق</span>
        </div>

        <div class="feature-glow-card" style="border-top:3px solid #10B981;">
            <div class="feature-glow-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="fa-solid fa-network-wired"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">مختبر Subnetting الحي</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">تمثيل بصري للـ 32 بت مع حساب فوري لعناوين الشبكة وتصحيح لحظي.</p>
            <span class="badge badge-success"><i class="fa-solid fa-check"></i> مستوحى من Cidr.xyz</span>
        </div>

        <div class="feature-glow-card" style="border-top:3px solid #8B5CF6;">
            <div class="feature-glow-icon" style="background:rgba(139,92,246,0.1);color:#8B5CF6;"><i class="fa-solid fa-calculator"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">حاسبة VLSM وخريطة الذاكرة</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">توزيع عناوين IP بلا هدر مع رسم بصري لخريطة الذاكرة.</p>
            <span class="badge" style="background:rgba(139,92,246,0.1);color:#8B5CF6;border-color:rgba(139,92,246,0.25);"><i class="fa-solid fa-check"></i> Zero-Waste</span>
        </div>

        <div class="feature-glow-card" style="border-top:3px solid #0D9488;">
            <div class="feature-glow-icon" style="background:rgba(13,148,136,0.1);color:#0D9488;"><i class="fa-solid fa-book-bookmark"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">موسوعة أوامر سيسكو Hub</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">أكثر من 60 أمراً مصنفاً لشهادة CCNA مع النسخ الفوري.</p>
            <span class="badge badge-secondary"><i class="fa-solid fa-check"></i> بحث فوري</span>
        </div>

        <div class="feature-glow-card" style="border-top:3px solid #F59E0B;">
            <div class="feature-glow-icon" style="background:rgba(245,158,11,0.1);color:#F59E0B;"><i class="fa-solid fa-layer-group"></i></div>
            <h3 style="font-size:1.15rem;margin-bottom:8px;">محلل طبقات OSI وتدفق الحزم</h3>
            <p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:16px;line-height:1.7;">محاكاة تغليف وفك تغليف الحزم عبر الطبقات السبع مع طوبولوجيا تفاعلية.</p>
            <span class="badge badge-warning"><i class="fa-solid fa-check"></i> رسوم متحركة</span>
        </div>
    </div>
</section>

<!-- ===== المسار الأكاديمي مع صورة الأمان (صورة security-education) ===== -->
<section class="curriculum-section" id="curriculum">
    <div style="position:relative;">
        <img src="assets/img/security-education.jpg" alt="المسار التعليمي" style="width:100%;border-radius:var(--radius-xl);box-shadow:var(--shadow-float);border:2px solid rgba(91,79,232,0.12);">
        <div class="hero-float-badge" style="position:absolute;bottom:-18px;right:20px;">
            <div style="width:42px;height:42px;border-radius:var(--radius-sm);background:var(--brand-gradient);display:flex;align-items:center;justify-content:center;color:#FFF;font-size:18px;">
                <i class="fa-solid fa-award"></i>
            </div>
            <div>
                <div style="font-weight:800;color:var(--text-heading);font-size:1.15rem;font-family:var(--font-mono);">4 مستويات</div>
                <div style="font-size:0.76rem;color:var(--text-muted);font-weight:600;">متوافقة مع Cisco CCNA</div>
            </div>
        </div>
    </div>

    <div>
        <span class="badge badge-primary" style="margin-bottom:12px;font-size:0.82rem;padding:6px 16px;">
            <i class="fa-solid fa-route"></i> المسار الأكاديمي التكيفي
        </span>
        <h2 style="font-size:1.8rem;color:var(--text-heading);font-weight:800;margin-bottom:14px;line-height:1.4;">
            أربعة مستويات متدرجة تحاكي<br>منهج Cisco CCNA 200-301
        </h2>
        <p style="color:var(--text-muted);font-size:0.94rem;margin-bottom:26px;line-height:1.75;">
            مسار أكاديمي يبدأ من الأساسيات وينتهي بأمن الشبكات. لا يسمح بالانتقال إلا بعد إثبات إتقانك.
        </p>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <div style="background:var(--bg-surface-card);padding:16px 20px;border-radius:var(--radius-md);border:1px solid rgba(208,213,229,0.5);border-right:4px solid var(--brand-primary);box-shadow:var(--shadow-subtle);transition:all 0.3s;" onmouseover="this.style.transform='translateX(-5px)'" onmouseout="this.style.transform=''">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <span class="badge badge-primary" style="font-size:0.72rem;">المستوى 1</span>
                    <strong style="color:var(--text-heading);font-size:0.95rem;">معمارية الشبكات ونموذج OSI</strong>
                </div>
                <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">الطبقات السبع، PDU، أجهزة الشبكة، TCP/UDP</p>
            </div>

            <div style="background:var(--bg-surface-card);padding:16px 20px;border-radius:var(--radius-md);border:1px solid rgba(208,213,229,0.5);border-right:4px solid #06B6D4;box-shadow:var(--shadow-subtle);transition:all 0.3s;" onmouseover="this.style.transform='translateX(-5px)'" onmouseout="this.style.transform=''">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <span class="badge badge-cyan" style="font-size:0.72rem;">المستوى 2</span>
                    <strong style="color:var(--text-heading);font-size:0.95rem;">العنونة واقتطاع الشبكات الفرعية</strong>
                </div>
                <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">IPv4 Subnetting، أقنعة الشبكة، VLANs</p>
            </div>

            <div style="background:var(--bg-surface-card);padding:16px 20px;border-radius:var(--radius-md);border:1px solid rgba(208,213,229,0.5);border-right:4px solid #10B981;box-shadow:var(--shadow-subtle);transition:all 0.3s;" onmouseover="this.style.transform='translateX(-5px)'" onmouseout="this.style.transform=''">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <span class="badge badge-success" style="font-size:0.72rem;">المستوى 3</span>
                    <strong style="color:var(--text-heading);font-size:0.95rem;">بروتوكولات التوجيه والتبديل</strong>
                </div>
                <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">OSPF، BGP، STP، التوجيه الثابت والديناميكي</p>
            </div>

            <div style="background:var(--bg-surface-card);padding:16px 20px;border-radius:var(--radius-md);border:1px solid rgba(208,213,229,0.5);border-right:4px solid #F43F5E;box-shadow:var(--shadow-subtle);transition:all 0.3s;" onmouseover="this.style.transform='translateX(-5px)'" onmouseout="this.style.transform=''">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <span class="badge badge-danger" style="font-size:0.72rem;">المستوى 4</span>
                    <strong style="color:var(--text-heading);font-size:0.95rem;">أمن الشبكات والجدران النارية</strong>
                </div>
                <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">ACLs، Stateful Firewall، VPN، Zero Trust</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== معاينة لوحة التحكم (صورة hero-network) ===== -->
<section class="preview-section">
    <span class="badge badge-primary" style="margin-bottom:10px;font-size:0.82rem;padding:6px 16px;">
        <i class="fa-solid fa-chart-pie"></i> لوحة تحكم ذكية
    </span>
    <h2 style="font-size:1.8rem;color:var(--text-heading);font-weight:800;margin-bottom:8px;">
        بيئة هندسية متكاملة تتتبع تقدمك
    </h2>
    <p style="color:var(--text-muted);font-size:0.96rem;max-width:600px;margin:0 auto;">
        لوحة تحكم تكيفية تعرض مستواك الحالي، إنجازاتك، وتوصيات المحرك الذكي
    </p>
    <img src="assets/img/hero-network.jpg" alt="بيئة هندسة الشبكات السحابية">
</section>

<!-- ===== CTA النهائي ===== -->
<section class="cta-section">
    <img src="assets/img/security-education.jpg" alt="">
    <div style="position:relative;z-index:2;">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);padding:6px 18px;border-radius:var(--radius-full);font-size:0.82rem;font-weight:700;margin-bottom:20px;border:1px solid rgba(255,255,255,0.15);">
            <i class="fa-solid fa-shield-halved"></i> بيئة آمنة ومعتمدة
        </div>
        <h2 style="font-size:2.2rem;color:#FFF;margin-bottom:14px;font-weight:900;">
            جاهز لاختبار قدراتك؟
        </h2>
        <p style="font-size:1.05rem;color:rgba(255,255,255,0.85);max-width:620px;margin:0 auto 32px;line-height:1.7;">
            انضم الآن وخض اختبار تحديد المستوى لتكتشف نقطة انطلاقك في مسار CCNA
        </p>
        <div style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap;">
            <a href="register.php" class="btn" style="background:#FFF;color:#1A1A3E !important;font-weight:800;padding:15px 34px;font-size:1.05rem;border-radius:var(--radius-md);box-shadow:0 8px 24px rgba(0,0,0,0.2);">
                <i class="fa-solid fa-user-plus"></i> إنشاء حساب مجاني
            </a>
            <a href="login.php" class="btn" style="background:rgba(255,255,255,0.12);color:#FFF !important;border:1.5px solid rgba(255,255,255,0.25);padding:15px 28px;font-size:1rem;border-radius:var(--radius-md);backdrop-filter:blur(8px);">
                <i class="fa-solid fa-key"></i> تسجيل الدخول
            </a>
        </div>
    </div>
</section>

<!-- تضمين محرك الصوت للمنصة -->
<script src="assets/js/sound_effects.js"></script>

<!-- ===== السكربتات ===== -->
<script>
// كانفاس الشبكات التفاعلي
(function() {
    const canvas = document.getElementById('hero-constellation-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let W, H;
    function resize() { W = canvas.width = canvas.offsetWidth; H = canvas.height = canvas.offsetHeight; }
    resize();
    window.addEventListener('resize', resize);

    const dots = [];
    const N = Math.min(50, Math.floor(W / 25));
    for (let i = 0; i < N; i++) {
        dots.push({ x: Math.random()*W, y: Math.random()*H, vx: (Math.random()-0.5)*0.6, vy: (Math.random()-0.5)*0.6, r: Math.random()*2+1, c: ['#5B4FE8','#06B6D4','#10B981','#8B5CF6'][i%4] });
    }

    let mx = -9e3, my = -9e3;
    canvas.addEventListener('mousemove', e => { const r = canvas.getBoundingClientRect(); mx = e.clientX-r.left; my = e.clientY-r.top; });
    canvas.addEventListener('mouseleave', () => { mx = -9e3; my = -9e3; });

    function frame() {
        ctx.clearRect(0, 0, W, H);
        for (let i = 0; i < dots.length; i++) {
            for (let j = i+1; j < dots.length; j++) {
                const dx = dots[i].x-dots[j].x, dy = dots[i].y-dots[j].y, d = Math.hypot(dx, dy);
                if (d < 130) { ctx.beginPath(); ctx.moveTo(dots[i].x, dots[i].y); ctx.lineTo(dots[j].x, dots[j].y); ctx.strokeStyle = `rgba(91,79,232,${0.1*(1-d/130)})`; ctx.lineWidth = 1; ctx.stroke(); }
            }
            const md = Math.hypot(dots[i].x-mx, dots[i].y-my);
            if (md < 120) { ctx.beginPath(); ctx.moveTo(dots[i].x, dots[i].y); ctx.lineTo(mx, my); ctx.strokeStyle = `rgba(6,182,212,${0.3*(1-md/120)})`; ctx.lineWidth = 1.5; ctx.stroke(); }
        }
        for (const p of dots) {
            p.x += p.vx; p.y += p.vy;
            if (p.x < 0 || p.x > W) p.vx *= -1;
            if (p.y < 0 || p.y > H) p.vy *= -1;
            ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
            ctx.fillStyle = p.c; ctx.shadowColor = p.c; ctx.shadowBlur = 8; ctx.fill(); ctx.shadowBlur = 0;
        }
        requestAnimationFrame(frame);
    }
    frame();
})();

// محاكي أوامر سيسكو مع المؤثرات الصوتية الحقيقية
function heroRunCommand(type) {
    if (window.SoundFX) SoundFX.play('click');
    const out = document.getElementById('hero-terminal-output');
    if (!out) return;
    out.style.opacity = '0';
    setTimeout(() => {
        out.style.transition = 'opacity 0.3s'; out.style.opacity = '1';
        const cursor = '<span style="display:inline-block;width:8px;height:14px;background:#38BDF8;vertical-align:middle;animation:blink 1s step-end infinite;"></span>';
        if (type === 'ping') {
            if (window.SoundFX) setTimeout(() => SoundFX.play('packet'), 200);
            out.innerHTML = 'Switch# ping 8.8.8.8<br>Type escape sequence to abort.<br>Sending 5, 100-byte ICMP Echos to 8.8.8.8, timeout is 2 seconds:<br><span style="color:#34D399;font-weight:bold;">!!!!!</span><br>Success rate is 100 percent (5/5), round-trip min/avg/max = 14/16/22 ms<br>Switch# '+cursor;
        } else if (type === 'route') {
            if (window.SoundFX) setTimeout(() => SoundFX.play('hop'), 150);
            out.innerHTML = 'Switch# show ip route<br>Codes: C - connected, S - static, O - OSPF<br>Gateway of last resort is 192.168.1.1<br>O    10.0.0.0/8 [110/2] via 192.168.1.254, GigabitEthernet0/1<br>C    192.168.1.0/24 is directly connected, Vlan10<br>S*   0.0.0.0/0 [1/0] via 192.168.1.1<br>Switch# '+cursor;
        } else if (type === 'int') {
            if (window.SoundFX) setTimeout(() => SoundFX.play('hop'), 150);
            out.innerHTML = 'Switch# show ip interface brief<br>Interface              IP-Address      OK? Method Status                Protocol<br>Vlan1                  unassigned      YES manual administratively down down<br>Vlan10                 192.168.1.2     YES manual <span style="color:#34D399;">up                    up</span><br>FastEthernet0/1        unassigned      YES unset  <span style="color:#34D399;">up                    up</span><br>GigabitEthernet0/1     unassigned      YES unset  <span style="color:#34D399;">up                    up</span><br>Switch# '+cursor;
        }
    }, 150);
}

// ظهور تدريجي عند التمرير
document.addEventListener('DOMContentLoaded', () => {
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; obs.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.feature-glow-card, .stat-block').forEach((el, i) => {
        el.style.opacity = '0'; el.style.transform = 'translateY(25px)';
        el.style.transition = `opacity 0.5s ease ${i*0.08}s, transform 0.5s ease ${i*0.08}s`;
        obs.observe(el);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>