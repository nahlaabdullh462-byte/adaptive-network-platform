<?php
/**
 * Shared Header Component - NetLearn Pro (Premium Academic Platform)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/security.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$userRole = $_SESSION['user_role'] ?? null;
$userName = $_SESSION['user_name'] ?? 'مستخدم';
$isLoggedIn = isset($_SESSION['user_id']);

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isInSubdir = (strpos($scriptPath, '/admin/') !== false || strpos($scriptPath, '/student/') !== false);
$basePath = $isInSubdir ? '../' : './';

$cssFile = __DIR__ . '/../assets/css/style.css';
$cssVer = file_exists($cssFile) ? filemtime($cssFile) : time();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="منصة التعلم التكيفي الذكي للشبكات — بيئة تعليمية ذكية لهندسة وإدارة الشبكات، محاكاة Cisco IOS، ومختبرات Wireshark و Subnetting وVLSM وفق معايير CCNA.">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | منصة التعلم التكيفي الذكي للشبكات' : 'منصة التعلم التكيفي الذكي للشبكات'; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath; ?>assets/img/icon.svg">
    
    <!-- خطوط جوجل -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- نظام التصميم -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css?v=<?php echo $cssVer; ?>">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if ($isLoggedIn && !isset($examMode)): ?>
    <!-- ========================================================================= -->
    <!-- تخطيط لوحة التحكم للمستخدمين المسجلين                                      -->
    <!-- ========================================================================= -->
    <div class="app-layout">
        
        <!-- القائمة الجانبية -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo $basePath; ?>index.php" class="sidebar-brand-link" style="display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 4px 0;">
                    <img src="<?php echo $basePath; ?>assets/img/icon.svg" alt="منصة التعلم التكيفي الذكي للشبكات" style="width: 40px; height: 40px; border-radius: 12px; box-shadow: 0 4px 16px rgba(91, 79, 232, 0.4); flex-shrink: 0; transition: transform 0.3s ease;">
                    <div style="overflow: hidden;">
                        <strong style="color: #FFFFFF; font-size: 0.94rem; font-family: var(--font-heading); display: block; line-height: 1.25;">
                            منصة التعلم التكيفي
                            <span style="color: #818CF8; display: block; font-size: 0.82rem;">الذكي للشبكات</span>
                        </strong>
                        <span style="font-size: 0.62rem; color: #38BDF8; font-weight: 700; font-family: var(--font-mono); letter-spacing: 0.08em; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 6px #10B981;"></span>
                            NETLEARN PRO
                        </span>
                    </div>
                </a>
            </div>

            <nav class="sidebar-nav">
                <?php if ($userRole === 'student'): ?>
                    <div class="nav-section-title">المسار الأكاديمي</div>
                    
                    <a href="<?php echo $basePath; ?>student/dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie nav-icon"></i>
                        <span>لوحة التحكم والتقدم</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/placement_test.php" class="<?php echo ($currentPage == 'placement_test.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-file-signature nav-icon"></i>
                        <span>اختبار تحديد المستوى</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/learning_resources.php" class="<?php echo ($currentPage == 'learning_resources.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-books nav-icon"></i>
                        <span>مركز الموارد التعليمية</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/study_module.php" class="<?php echo ($currentPage == 'study_module.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-book-open nav-icon"></i>
                        <span>الدروس التكيفية المدمجة</span>
                    </a>

                    <div class="nav-section-title">المختبرات والمحاكاة الحية</div>

                    <a href="<?php echo $basePath; ?>student/cisco_cli.php" class="<?php echo ($currentPage == 'cisco_cli.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-terminal nav-icon"></i>
                        <span>محاكي أوامر سيسكو CLI</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/cisco_cheatsheet.php" class="<?php echo ($currentPage == 'cisco_cheatsheet.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-book-bookmark nav-icon"></i>
                        <span>موسوعة أوامر سيسكو Hub</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/packet_sniffer.php" class="<?php echo ($currentPage == 'packet_sniffer.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-microchip nav-icon"></i>
                        <span>محلل الحزم (Wireshark Lab)</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/subnet_simulator.php" class="<?php echo ($currentPage == 'subnet_simulator.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-network-wired nav-icon"></i>
                        <span>مختبر Subnetting الحي</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/vlsm_calculator.php" class="<?php echo ($currentPage == 'vlsm_calculator.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-calculator nav-icon"></i>
                        <span>حاسبة تقسيم الشبكات VLSM</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/network_topology.php" class="<?php echo ($currentPage == 'network_topology.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-diagram-project nav-icon"></i>
                        <span>محاكي مسار الحزم الحي</span>
                    </a>

                    <a href="<?php echo $basePath; ?>student/osi_explorer.php" class="<?php echo ($currentPage == 'osi_explorer.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-layer-group nav-icon"></i>
                        <span>محلل طبقات OSI السبع</span>
                    </a>

                <?php elseif ($userRole === 'admin'): ?>
                    <div class="nav-section-title">إدارة المنصة الأكاديمية</div>

                    <a href="<?php echo $basePath; ?>admin/dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-line nav-icon"></i>
                        <span>مركز الإحصائيات والتحليلات</span>
                    </a>

                    <a href="<?php echo $basePath; ?>admin/manage_questions.php" class="<?php echo ($currentPage == 'manage_questions.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-database nav-icon"></i>
                        <span>بنك الأسئلة والاختبارات</span>
                    </a>

                    <a href="<?php echo $basePath; ?>admin/manage_students.php" class="<?php echo ($currentPage == 'manage_students.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-user-graduate nav-icon"></i>
                        <span>إدارة شؤون الطلاب</span>
                    </a>
                    <a href="<?php echo $basePath; ?>admin/manage_resources.php" class="<?php echo ($currentPage == 'manage_resources.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-layer-group nav-icon"></i>
                        <span>الموارد التعليمية</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile-badge">
                    <div class="user-avatar">
                        <?php echo mb_substr($userName, 0, 1, 'UTF-8'); ?>
                    </div>
                    <div class="user-meta">
                        <div class="user-meta-name" title="<?php echo htmlspecialchars($userName); ?>">
                            <?php echo htmlspecialchars($userName); ?>
                        </div>
                        <div class="user-meta-role">
                            <?php echo $userRole === 'admin' ? 'مشرف النظام' : 'طالب هندسة شبكات'; ?>
                        </div>
                    </div>
                    <a href="<?php echo $basePath; ?>logout.php" title="تسجيل الخروج" style="color: rgba(200, 200, 235, 0.6); font-size: 1.1rem; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='#F43F5E'" onmouseout="this.style.color='rgba(200, 200, 235, 0.6)'">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- مساحة العمل الرئيسية -->
        <div class="app-workspace">
            <header class="app-topbar">
                <div class="topbar-breadcrumbs">
                    <i class="fa-solid fa-house" style="font-size: 0.8rem;"></i>
                    <span>/</span>
                    <span><?php echo $userRole === 'admin' ? 'الإدارة' : 'الطالب'; ?></span>
                    <span>/</span>
                    <strong><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'لوحة القيادة'; ?></strong>
                </div>

                <div class="topbar-actions">
                    <?php if ($userRole === 'student'): ?>
                        <a href="<?php echo $basePath; ?>student/cisco_cli.php" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-terminal" style="color: var(--brand-secondary);"></i> المحاكي
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $basePath; ?>student/change_password.php" class="btn btn-secondary btn-sm" title="الإعدادات">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                    <a href="<?php echo $basePath; ?>logout.php" class="btn btn-danger btn-sm">
                        خروج <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </header>

            <main class="app-page-body">

<?php elseif ($isLoggedIn && isset($examMode)): ?>
    <!-- وضع الامتحان: لا يوجد sidebar — exam.php يبني هيكله الخاص -->

<?php else: ?>
    <!-- ========================================================================= -->
    <!-- شريط التنقل الاحترافي للزوار (Premium Guest Navbar)                        -->
    <!-- ========================================================================= -->
    <header class="landing-navbar" id="main-navbar">
        <div class="landing-nav-inner">
            <!-- اللوجو الجديد فائق الحداثة -->
            <a href="<?php echo $basePath; ?>index.php" class="landing-brand-link" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <img src="<?php echo $basePath; ?>assets/img/icon.svg" alt="منصة التعلم التكيفي الذكي للشبكات" style="width: 44px; height: 44px; border-radius: 13px; box-shadow: 0 4px 18px rgba(91, 79, 232, 0.35); flex-shrink: 0; transition: transform 0.3s ease;">
                <div>
                    <strong style="color: var(--text-heading); font-size: 1.15rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 6px; line-height: 1.15;">
                        منصة التعلم التكيفي الذكي للشبكات
                    </strong>
                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; font-family: var(--font-mono); letter-spacing: 0.07em; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                        <span style="width: 5px; height: 5px; border-radius: 50%; background: #10B981; display: inline-block; box-shadow: 0 0 6px #10B981;"></span>
                        SMART ADAPTIVE NETWORKING PLATFORM • CCNA
                    </span>
                </div>
            </a>

            <!-- روابط التنقل الوسطى المتناسقة مع الأيقونات -->
            <nav class="nav-links-center" id="nav-links">
                <a href="<?php echo $basePath; ?>index.php" class="nav-link-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i> الرئيسية
                </a>
                <a href="<?php echo $basePath; ?>index.php#features" class="nav-link-item">
                    <i class="fa-solid fa-flask"></i> المختبرات
                </a>
                <a href="<?php echo $basePath; ?>index.php#curriculum" class="nav-link-item">
                    <i class="fa-solid fa-route"></i> المسار الأكاديمي
                </a>
                <a href="<?php echo $basePath; ?>index.php#terminal" class="nav-link-item">
                    <i class="fa-solid fa-terminal"></i> المحاكي
                </a>
            </nav>

            <!-- أزرار الإجراءات والمصادقة الذكية حسب الصفحة -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if ($currentPage === 'login.php'): ?>
                    <a href="<?php echo $basePath; ?>index.php" class="btn btn-secondary btn-sm" style="font-weight: 700; padding: 9px 18px; border-radius: 10px;">
                        <i class="fa-solid fa-house"></i> الرئيسية
                    </a>
                    <a href="<?php echo $basePath; ?>register.php" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 9px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(91,79,232,0.25);">
                        <i class="fa-solid fa-user-plus"></i> حساب جديد
                    </a>
                <?php elseif ($currentPage === 'register.php'): ?>
                    <a href="<?php echo $basePath; ?>index.php" class="btn btn-secondary btn-sm" style="font-weight: 700; padding: 9px 18px; border-radius: 10px;">
                        <i class="fa-solid fa-house"></i> الرئيسية
                    </a>
                    <a href="<?php echo $basePath; ?>login.php" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 9px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(91,79,232,0.25);">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> تسجيل الدخول
                    </a>
                <?php else: ?>
                    <a href="<?php echo $basePath; ?>login.php" class="btn btn-secondary btn-sm" style="font-weight: 700; padding: 9px 20px; border-radius: 10px;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> دخول
                    </a>
                    <a href="<?php echo $basePath; ?>register.php" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 9px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(91,79,232,0.25);">
                        <i class="fa-solid fa-user-plus"></i> تسجيل حساب
                    </a>
                <?php endif; ?>

                <!-- زر القائمة للموبايل -->
                <button type="button" class="mobile-menu-btn" id="mobile-menu-btn" onclick="toggleMobileMenu()" style="display:none;background:none;border:1px solid var(--border-main);border-radius:8px;padding:8px 10px;cursor:pointer;color:var(--text-heading);font-size:1.1rem;">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <style>
    /* شريط التنقل الاحترافي */
    .landing-navbar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        background: rgba(240, 243, 250, 0.88); backdrop-filter: blur(20px) saturate(1.2);
        border-bottom: 1px solid rgba(208, 213, 229, 0.5);
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(26,26,62,0.04);
    }
    .landing-navbar.scrolled {
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 4px 20px rgba(26,26,62,0.08);
    }
    .landing-nav-inner {
        max-width: 1240px; margin: 0 auto;
        padding: 12px 28px;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* روابط التنقل الوسطى */
    .nav-links-center {
        display: flex; align-items: center; gap: 6px;
        background: rgba(232,236,244,0.6); padding: 4px;
        border-radius: 12px; border: 1px solid rgba(208,213,229,0.4);
    }
    .nav-link-item {
        padding: 8px 18px; border-radius: 9px;
        font-size: 0.88rem; font-weight: 600;
        color: var(--text-muted); text-decoration: none;
        transition: all 0.25s ease;
        display: flex; align-items: center; gap: 6px;
    }
    .nav-link-item:hover { color: var(--brand-primary); background: rgba(91,79,232,0.06); }
    .nav-link-item.active { color: var(--brand-primary); background: #FFF; box-shadow: 0 2px 8px rgba(26,26,62,0.06); }
    .nav-link-item i { font-size: 0.82rem; }

    /* المحتوى تحت الـ navbar */
    .landing-content { padding-top: 72px; }

    @media (max-width: 768px) {
        .nav-links-center { display: none; }
        .mobile-menu-btn { display: block !important; }
        .nav-links-center.open {
            display: flex; flex-direction: column;
            position: absolute; top: 100%; left: 16px; right: 16px;
            background: #FFF; border-radius: 12px;
            box-shadow: 0 12px 40px rgba(26,26,62,0.12);
            padding: 8px; border: 1px solid rgba(208,213,229,0.5);
            z-index: 50;
        }
    }
    </style>

    <script>
    // تغيير مظهر الـ navbar عند التمرير
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('main-navbar');
        if (nav) {
            if (window.scrollY > 40) { nav.classList.add('scrolled'); }
            else { nav.classList.remove('scrolled'); }
        }
    });

    function toggleMobileMenu() {
        document.getElementById('nav-links').classList.toggle('open');
    }
    </script>

    <main class="landing-content">
<?php endif; ?>
